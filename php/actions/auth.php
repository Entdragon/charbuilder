<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/password.php';

function cg_login_user(): void {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        cg_json(['success' => false, 'data' => 'Username and password are required.']);
        return;
    }

    $p   = cg_prefix();
    $row = cg_query_one(
        "SELECT ID, user_pass, user_login, user_email
         FROM {$p}users WHERE user_login = ? OR user_email = ? LIMIT 1",
        [$username, $username]
    );

    if (!$row || !cg_check_password($password, $row['user_pass'])) {
        cg_json(['success' => false, 'data' => 'Invalid username or password.']);
        return;
    }

    $meta = cg_query_one(
        "SELECT meta_value FROM {$p}usermeta
         WHERE user_id = ? AND meta_key = '{$p}capabilities' LIMIT 1",
        [$row['ID']]
    );
    $capStr = $meta['meta_value'] ?? '';

    $_SESSION['cg_user_id']  = (int) $row['ID'];
    $_SESSION['cg_username'] = $row['user_login'];
    $_SESSION['cg_email']    = $row['user_email'];
    $_SESSION['cg_is_admin'] = str_contains($capStr, 'administrator');

    cg_json(['success' => true, 'data' => [
        'username' => $row['user_login'],
        'redirect' => '/',
    ]]);
}

function cg_logout_user(): void {
    session_destroy();
    cg_json(['success' => true, 'data' => ['redirect' => '/']]);
}

function cg_register_user(): void {
    $username = trim($_POST['username'] ?? '');
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (!$username || !$email || !$password) {
        cg_json(['success' => false, 'data' => 'All fields are required.']);
        return;
    }

    $p = cg_prefix();

    if (cg_query_one("SELECT ID FROM {$p}users WHERE user_login = ? LIMIT 1", [$username])) {
        cg_json(['success' => false, 'data' => 'Username already taken.']);
        return;
    }
    if (cg_query_one("SELECT ID FROM {$p}users WHERE user_email = ? LIMIT 1", [$email])) {
        cg_json(['success' => false, 'data' => 'Email already registered.']);
        return;
    }

    $hash     = cg_hash_password($password);
    $now      = date('Y-m-d H:i:s');
    $userKey  = bin2hex(random_bytes(12));

    cg_exec(
        "INSERT INTO {$p}users
           (user_login, user_pass, user_email, user_registered, user_activation_key, user_status, display_name)
         VALUES (?, ?, ?, ?, ?, 0, ?)",
        [$username, $hash, $email, $now, $userKey, $username]
    );

    $uid = (int) cg_last_insert_id();
    if (!$uid) {
        cg_json(['success' => false, 'data' => 'Registration failed.']);
        return;
    }

    cg_exec(
        "INSERT INTO {$p}usermeta (user_id, meta_key, meta_value) VALUES (?, '{$p}capabilities', ?)",
        [$uid, 'a:1:{s:10:"subscriber";b:1;}']
    );
    cg_exec(
        "INSERT INTO {$p}usermeta (user_id, meta_key, meta_value) VALUES (?, '{$p}user_level', '0')",
        [$uid]
    );

    $_SESSION['cg_user_id']  = $uid;
    $_SESSION['cg_username'] = $username;
    $_SESSION['cg_email']    = $email;
    $_SESSION['cg_is_admin'] = false;

    cg_json(['success' => true, 'data' => [
        'username' => $username,
        'redirect' => '/',
    ]]);
}

function cg_get_current_user(): void {
    if (!cg_is_logged_in()) {
        cg_json(['success' => false, 'data' => 'Not logged in.']);
        return;
    }
    cg_json(['success' => true, 'data' => [
        'id'       => $_SESSION['cg_user_id'],
        'username' => $_SESSION['cg_username'],
        'email'    => $_SESSION['cg_email'],
        'isAdmin'  => $_SESSION['cg_is_admin'] ?? false,
    ]]);
}

function cg_sso_login(): void {
    $token = trim($_POST['token'] ?? '');

    if (!$token || !preg_match('/^[a-f0-9]{32}$/', $token)) {
        cg_json(['success' => false, 'data' => 'Invalid SSO token.']);
        return;
    }

    $p           = cg_prefix();
    $transientKey = "_transient_cg_sso_{$token}";

    $row = cg_query_one(
        "SELECT option_value FROM {$p}options WHERE option_name = ? LIMIT 1",
        [$transientKey]
    );

    if (!$row) {
        cg_json(['success' => false, 'data' => 'SSO token not found or expired.']);
        return;
    }

    $userId = (int) $row['option_value'];
    if ($userId <= 0) {
        cg_json(['success' => false, 'data' => 'SSO token invalid.']);
        return;
    }

    // Delete transient (one-time use)
    cg_exec(
        "DELETE FROM {$p}options WHERE option_name = ? OR option_name = ?",
        [$transientKey, "_transient_timeout_cg_sso_{$token}"]
    );

    $user = cg_query_one(
        "SELECT ID, user_login, user_email FROM {$p}users WHERE ID = ? LIMIT 1",
        [$userId]
    );

    if (!$user) {
        cg_json(['success' => false, 'data' => 'User not found.']);
        return;
    }

    $meta   = cg_query_one(
        "SELECT meta_value FROM {$p}usermeta
         WHERE user_id = ? AND meta_key = '{$p}capabilities' LIMIT 1",
        [(int) $user['ID']]
    );
    $capStr = $meta['meta_value'] ?? '';

    $_SESSION['cg_user_id']  = (int) $user['ID'];
    $_SESSION['cg_username'] = $user['user_login'];
    $_SESSION['cg_email']    = $user['user_email'];
    $_SESSION['cg_is_admin'] = str_contains($capStr, 'administrator');

    cg_json(['success' => true, 'data' => [
        'username' => $user['user_login'],
        'redirect' => '/',
    ]]);
}
