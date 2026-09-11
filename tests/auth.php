<?php
// Offline regression suite: no application config, network, or database access.
require_once __DIR__ . '/../php/includes/password.php';
require_once __DIR__ . '/../php/includes/auth-proxy.php';

$checks = 0;
function check(bool $ok, string $label): void {
    global $checks;
    if (!$ok) throw new RuntimeException($label);
    $checks++;
}
function wp_fixture(string $password): string {
    return '$wp' . password_hash(
        base64_encode(hash_hmac('sha384', $password, 'wp-sha384', true)),
        PASSWORD_BCRYPT,
        ['cost' => 4]
    );
}
set_error_handler(function ($severity, $message) {
    throw new ErrorException($message, 0, $severity);
});
$log = tempnam(sys_get_temp_dir(), 'cg-auth-log-');
ini_set('error_log', $log);
try {
    foreach (['Example password!', '  keep spaces  ', '猫 Māori 🔒', str_repeat('x', 100), str_repeat('z', 4096)] as $password) {
        $hash = wp_fixture($password);
        check(cg_check_password($password, $hash), 'WordPress correct password');
        check(!cg_check_password('wrong', $hash), 'WordPress wrong password');
        check(!cg_check_password(trim($password) . '!', $hash), 'Preserve password bytes');
    }
    $long = str_repeat('x', 100);
    check(!cg_check_password(substr($long, 0, 72) . 'y', wp_fixture($long)), 'No bcrypt truncation collision for WP');
    check(!cg_check_password(str_repeat('z', 4097), wp_fixture(str_repeat('z', 4097))), 'WordPress maximum length');
    $password = 'Legacy test password';
    $bcrypt = password_hash($password, PASSWORD_BCRYPT, ['cost' => 4]);
    $portable = cg_hash_password($password);
    foreach ([$bcrypt, str_replace('$2y$', '$2a$', $bcrypt), str_replace('$2y$', '$2b$', $bcrypt), $portable, '$H$' . substr($portable, 3), md5($password)] as $hash) {
        check(cg_check_password($password, $hash), 'Legacy format correct password');
        check(!cg_check_password('wrong', $hash), 'Legacy format wrong password');
    }
    foreach (['', '$wp', '$wp$$2y$bad', '$P$', '$P$!12345678' . str_repeat('x', 22), '$2y$bad', '*0', 'garbage'] as $hash) {
        check(!cg_check_password($password, $hash), 'Malformed hash fails closed');
    }
    check(!cg_check_password($password, '$wp' . $bcrypt), 'No raw-password fallback for WP hashes');

    $cases = [
        [false, 0, false, 'transport_failed'],
        ['{"success":true}', 403, false, 'http_failed status=403'],
        ['{"success":true}', 500, false, 'http_failed status=500'],
        ['{"success":true}', 302, false, 'http_failed status=302'],
        ['', 200, false, 'response_invalid'],
        ['<html>Error</html>', 200, false, 'response_invalid'],
        ['{"success":"true"}', 200, false, 'response_invalid'],
        ['{"success":1}', 200, false, 'response_invalid'],
        ['{"error":"internal details"}', 200, false, 'response_invalid'],
        ['{"success":false}', 200, false, 'rejected'],
        ['{"success":true}', 200, true, 'authenticated'],
    ];
    foreach ($cases as [$body, $status, $expected, $reason]) {
        file_put_contents($log, '');
        $result = cg_auth_proxy_check('https://test.invalid', 'synthetic-secret', 'synthetic-user', $password,
            function ($url, $options) use ($body, $status) {
                check($options['http']['follow_location'] === 0, 'No credential forwarding on redirect');
                return [$body, $status];
            });
        check($result === $expected, 'Proxy result');
        check(str_contains(file_get_contents($log), $reason), 'Safe proxy reason');
        foreach (['synthetic-secret', 'synthetic-user', $password, 'internal details'] as $sensitive) {
            check(!str_contains(file_get_contents($log), $sensitive), 'No sensitive log data');
        }
    }
    check(!cg_auth_proxy_check('', '', 'test', 'test'), 'Missing configuration');
    check(!cg_auth_proxy_check('https://test.invalid', 'test', 'test', 'test', function () {
        throw new RuntimeException('sensitive exception');
    }), 'Transport exception');
    check(!str_contains(file_get_contents($log), 'sensitive exception'), 'Exception sanitized');

    // Load the actual login handler against a tiny, isolated DB fixture.
    // Never execute production config.
    function cg_query_one($sql, $params = []) {
        if (str_contains($sql, 'usermeta')) return ['meta_value' => ''];
        return $GLOBALS['test_user'];
    }
    function cg_prefix() { return 'test_'; }
    function cg_json($data) { $GLOBALS['test_response'] = $data; }
    define('CG_PROXY_URL', '');
    define('CG_PROXY_SECRET', '');
    // Use a temporary mirrored directory so the real handler resolves stub DB.
    $root = sys_get_temp_dir() . '/cg-auth-fixture-' . bin2hex(random_bytes(6));
    mkdir($root . '/actions', 0700, true);
    mkdir($root . '/includes', 0700);
    file_put_contents($root . '/includes/db.php', '<?php // offline DB stub');
    copy(__DIR__ . '/../php/actions/auth.php', $root . '/actions/auth.php');
    file_put_contents($root . '/includes/password.php', '<?php // verifier already loaded');
    file_put_contents($root . '/includes/auth-proxy.php', '<?php // proxy already loaded');
    try {
        require $root . '/actions/auth.php';
        foreach ([true, false] as $correct) {
            $_SESSION = [];
            $GLOBALS['test_user'] = ['ID' => 123, 'user_pass' => wp_fixture('Synthetic login'), 'user_login' => 'tester', 'user_email' => 'test@example.invalid'];
            $_POST = ['username' => 'tester', 'password' => $correct ? 'Synthetic login' : 'wrong'];
            cg_login_user();
            check($GLOBALS['test_response']['success'] === $correct, 'Login response');
            check(isset($_SESSION['cg_user_id']) === $correct, 'Only valid login creates session');
            if (!$correct) check($GLOBALS['test_response']['data'] === 'Invalid username or password.', 'Generic failure');
        }
        $_SESSION = [];
        $GLOBALS['test_user'] = null;
        cg_login_user();
        check(!isset($_SESSION['cg_user_id']), 'Missing account no session');
        check($GLOBALS['test_response']['data'] === 'Invalid username or password.', 'Missing account generic failure');
    } finally {
        foreach (glob($root . '/actions/*') as $file) unlink($file);
        foreach (glob($root . '/includes/*') as $file) unlink($file);
        rmdir($root . '/actions'); rmdir($root . '/includes'); rmdir($root);
    }
    echo "PASS: $checks offline authentication checks\n";
} finally {
    unlink($log);
}