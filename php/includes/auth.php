<?php
/**
 * Session management helpers.
 */

function cg_session_start(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;

    $secure   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $lifetime = 7 * 24 * 60 * 60; // 7 days

    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => $secure ? 'None' : 'Lax',
    ]);
    session_start();
}

function cg_is_logged_in(): bool {
    return !empty($_SESSION['cg_user_id']);
}

function cg_current_user_id(): int {
    return (int) ($_SESSION['cg_user_id'] ?? 0);
}

function cg_require_auth(): void {
    if (!cg_is_logged_in()) {
        http_response_code(401);
        cg_json(['success' => false, 'data' => 'Not logged in.']);
        exit;
    }
}

/**
 * Try to authenticate via WordPress auth cookie (SSO).
 *
 * WordPress sets a cookie named wordpress_logged_in_<hash> with the value:
 *   username|expiration|token|hmac
 *
 * The session token is stored in wp_usermeta (meta_key = 'session_tokens')
 * as a PHP-serialized array keyed by sha256(token).
 *
 * Returns true and populates $_SESSION if a valid WP session is found.
 */
function cg_try_wp_sso(): bool {
    if (cg_is_logged_in()) return true;

    // Find wordpress_logged_in_* cookie
    $cookie_val = null;
    foreach ($_COOKIE as $name => $val) {
        if (strpos($name, 'wordpress_logged_in_') === 0) {
            $cookie_val = $val;
            break;
        }
    }
    if (!$cookie_val) return false;

    // Parse: username|expiration|token|hmac
    $parts = explode('|', $cookie_val);
    if (count($parts) < 4) return false;

    $username   = urldecode($parts[0]);
    $expiration = (int) $parts[1];
    $token      = $parts[2];

    if ($expiration < time()) return false;
    if (!$username || !$token) return false;

    // Look up the user
    $p    = cg_prefix();
    $user = cg_query_one(
        "SELECT ID, user_login, user_email FROM {$p}users WHERE user_login = ? LIMIT 1",
        [$username]
    );
    if (!$user) return false;

    // Verify session token against stored sessions in wp_usermeta
    $verifier = hash('sha256', $token);
    $meta     = cg_query_one(
        "SELECT meta_value FROM {$p}usermeta WHERE user_id = ? AND meta_key = 'session_tokens' LIMIT 1",
        [(int) $user['ID']]
    );
    if (!$meta || empty($meta['meta_value'])) return false;

    $sessions = @unserialize($meta['meta_value']);
    if (!is_array($sessions) || !array_key_exists($verifier, $sessions)) return false;

    $session = $sessions[$verifier];
    if (!empty($session['expiration']) && (int)$session['expiration'] < time()) return false;

    // Valid WordPress session — populate our session
    $cap_meta = cg_query_one(
        "SELECT meta_value FROM {$p}usermeta WHERE user_id = ? AND meta_key = '{$p}capabilities' LIMIT 1",
        [(int) $user['ID']]
    );
    $capStr = $cap_meta['meta_value'] ?? '';

    $_SESSION['cg_user_id']  = (int) $user['ID'];
    $_SESSION['cg_username'] = $user['user_login'];
    $_SESSION['cg_email']    = $user['user_email'];
    $_SESSION['cg_is_admin'] = str_contains($capStr, 'administrator');

    return true;
}
