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
