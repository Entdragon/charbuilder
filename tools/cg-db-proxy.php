<?php
/**
 * Character Generator – Database Proxy
 *
 * Drop this file into your WordPress root directory.
 * It relays authenticated SQL queries from the Replit app
 * to your local MySQL database over HTTPS.
 *
 * SETUP (first time):
 *   1. Upload this file to your WordPress root (same folder as wp-config.php)
 *   2. Create cg-proxy-config.php in the same folder (see below)
 *   3. Set CG_PROXY_URL in Replit to: https://yourdomain.com/cg-db-proxy.php
 *
 * cg-proxy-config.php should contain exactly one line:
 *   <?php define('CG_PROXY_SECRET', 'paste-your-CG_PROXY_SECRET-value-here');
 *
 * UPDATING: You can re-upload this file at any time without touching
 * cg-proxy-config.php — the secret lives only in that file.
 */

// ── Load secret from config file ─────────────────────────────────────────────

$config_file = __DIR__ . '/cg-proxy-config.php';
if ( file_exists( $config_file ) ) {
    require_once $config_file;
}

if ( ! defined( 'CG_PROXY_SECRET' ) || CG_PROXY_SECRET === '' ) {
    http_response_code( 500 );
    header( 'Content-Type: application/json' );
    echo json_encode( [ 'error' => 'Proxy not configured: cg-proxy-config.php missing or empty' ] );
    exit;
}

// ── Security ──────────────────────────────────────────────────────────────────

header( 'Content-Type: application/json' );

if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
    http_response_code( 405 );
    echo json_encode( [ 'error' => 'Method not allowed' ] );
    exit;
}

$provided = $_SERVER['HTTP_X_CG_SECRET'] ?? '';
if ( ! hash_equals( CG_PROXY_SECRET, $provided ) ) {
    http_response_code( 403 );
    echo json_encode( [ 'error' => 'Forbidden' ] );
    exit;
}

// ── Parse request ─────────────────────────────────────────────────────────────

$body   = json_decode( file_get_contents( 'php://input' ), true );
$action = trim( $body['action'] ?? '' );

// ── WordPress auth check (delegates to wp_authenticate) ───────────────────────

if ( $action === 'wp_auth_check' ) {
    $username = trim( $body['username'] ?? '' );
    $password = $body['password'] ?? '';

    if ( empty( $username ) || $password === '' ) {
        http_response_code( 400 );
        echo json_encode( [ 'error' => 'Username and password required' ] );
        exit;
    }

    if ( ! defined( 'ABSPATH' ) ) {
        $wp_load = __DIR__ . '/wp-load.php';
        if ( ! file_exists( $wp_load ) ) {
            $wp_load = dirname( __DIR__ ) . '/wp-load.php';
        }
        if ( file_exists( $wp_load ) ) {
            ob_start();
            require_once $wp_load;
            ob_end_clean();
        }
    }

    if ( ! function_exists( 'wp_authenticate' ) ) {
        http_response_code( 500 );
        echo json_encode( [ 'error' => 'WordPress not available for auth check' ] );
        exit;
    }

    $result = wp_authenticate( $username, $password );

    if ( is_wp_error( $result ) ) {
        echo json_encode( [ 'success' => false ] );
    } else {
        echo json_encode( [
            'success'    => true,
            'user_id'    => $result->ID,
            'user_login' => $result->user_login,
            'user_email' => $result->user_email,
        ] );
    }
    exit;
}

// ── SQL proxy ─────────────────────────────────────────────────────────────────

$sql    = trim( $body['sql']    ?? '' );
$params = $body['params'] ?? [];

if ( empty( $sql ) ) {
    http_response_code( 400 );
    echo json_encode( [ 'error' => 'No SQL provided' ] );
    exit;
}

// ── Load WordPress DB credentials ─────────────────────────────────────────────

$wp_config = __DIR__ . '/wp-config.php';
if ( ! file_exists( $wp_config ) ) {
    $wp_config = dirname( __DIR__ ) . '/wp-config.php';
}

if ( ! file_exists( $wp_config ) ) {
    http_response_code( 500 );
    echo json_encode( [ 'error' => 'wp-config.php not found' ] );
    exit;
}

$config_content = file_get_contents( $wp_config );
function extract_wp_const( $content, $name ) {
    if ( preg_match( "/define\s*\(\s*['\"]" . preg_quote( $name, '/' ) . "['\"]\s*,\s*['\"]([^'\"]*)['\"]/" , $content, $m ) ) {
        return $m[1];
    }
    return '';
}

$db_name = extract_wp_const( $config_content, 'DB_NAME' );
$db_user = extract_wp_const( $config_content, 'DB_USER' );
$db_pass = extract_wp_const( $config_content, 'DB_PASSWORD' );
$db_host = extract_wp_const( $config_content, 'DB_HOST' );

// ── Connect and execute ────────────────────────────────────────────────────────

try {
    $port = 3306;
    if ( strpos( $db_host, ':' ) !== false ) {
        [ $db_host, $port ] = explode( ':', $db_host, 2 );
        $port = (int) $port;
    }

    $dsn = "mysql:host={$db_host};port={$port};dbname={$db_name};charset=utf8mb4";
    $pdo = new PDO( $dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT            => 5,
    ] );

    $stmt = $pdo->prepare( $sql );
    $stmt->execute( array_values( $params ) );

    $verb = strtoupper( strtok( ltrim( $sql ), " \t\n\r" ) );

    if ( in_array( $verb, [ 'SELECT', 'SHOW', 'DESCRIBE', 'EXPLAIN' ], true ) ) {
        echo json_encode( [ 'rows' => $stmt->fetchAll() ] );
    } else {
        echo json_encode( [
            'rowCount'     => $stmt->rowCount(),
            'lastInsertId' => $pdo->lastInsertId(),
        ] );
    }

} catch ( PDOException $e ) {
    http_response_code( 500 );
    echo json_encode( [ 'error' => $e->getMessage() ] );
}
