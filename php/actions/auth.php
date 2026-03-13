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

    if (!$row) {
        cg_json(['success' => false, 'data' => 'DEBUG: User not found for: ' . $username]);
        return;
    }
    if (!cg_check_password($password, $row['user_pass'])) {
        cg_json(['success' => false, 'data' => 'DEBUG: Password mismatch. Hash type: ' . substr($row['user_pass'], 0, 4)]);
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
