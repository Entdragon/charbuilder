<?php
/**
 * Character Generator – Database Proxy
 *
 * Drop this file into your WordPress root directory.
 * It relays authenticated SQL queries from the Replit app
 * to your local MySQL database over HTTPS.
 *
 * SETUP:
 *   1. Upload this file to your WordPress root (same folder as wp-config.php)
 *   2. Set PROXY_SECRET below to match your CG_PROXY_SECRET Replit secret
 *   3. Set CG_PROXY_URL in Replit to: https://yourdomain.com/cg-db-proxy.php
 */

define( 'PROXY_SECRET', 'YOUR_SECRET_HERE' );

// ── Security ────────────────────────────────────────────────────────────────

header( 'Content-Type: application/json' );

// Only allow POST
if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
    http_response_code( 405 );
    echo json_encode( [ 'error' => 'Method not allowed' ] );
    exit;
}

// Verify secret header
$provided = $_SERVER['HTTP_X_CG_SECRET'] ?? '';
if ( ! hash_equals( PROXY_SECRET, $provided ) ) {
    http_response_code( 403 );
    echo json_encode( [ 'error' => 'Forbidden' ] );
    exit;
}

// ── Parse request ────────────────────────────────────────────────────────────

$body   = json_decode( file_get_contents( 'php://input' ), true );
$sql    = trim( $body['sql']    ?? '' );
$params = $body['params'] ?? [];

if ( empty( $sql ) ) {
    http_response_code( 400 );
    echo json_encode( [ 'error' => 'No SQL provided' ] );
    exit;
}

// ── Load WordPress DB credentials ────────────────────────────────────────────

$wp_config = __DIR__ . '/wp-config.php';
if ( ! file_exists( $wp_config ) ) {
    // Try one level up (if placed in a subdirectory)
    $wp_config = dirname( __DIR__ ) . '/wp-config.php';
}

if ( ! file_exists( $wp_config ) ) {
    http_response_code( 500 );
    echo json_encode( [ 'error' => 'wp-config.php not found' ] );
    exit;
}

// Extract DB constants without fully executing wp-config (safe parse)
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

// ── Connect and execute ───────────────────────────────────────────────────────

try {
    // Parse host:port
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
