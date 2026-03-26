<?php
/**
 * Character Generator — PHP API endpoint.
 *
 * Receives POST requests with an `action` parameter and dispatches to the
 * appropriate handler. Mirrors the Node.js /api/ajax route exactly so the
 * frontend JavaScript requires no changes.
 *
 * Deploy this file (and the includes/ + actions/ directories) to your server.
 * Point window.CG_AJAX.ajax_url at the URL of this file.
 */

declare(strict_types=1);

// ── Includes ──────────────────────────────────────────────────────────────────
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

// ── Bootstrap ─────────────────────────────────────────────────────────────────
cg_session_start();
cg_try_wp_sso();

// ── JSON helper ───────────────────────────────────────────────────────────────
function cg_json(array $data): void {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

// ── CORS ─────────────────────────────────────────────────────────────────────
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin) {
    header("Access-Control-Allow-Origin: {$origin}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
}
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ── Accept POST; also GET for the /api/auth/me → cg_get_current_user rewrite ─
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'GET') {
    // Only allow GET for the session-check endpoint
    $_POST['action'] = $_GET['action'] ?? '';
    if ($_POST['action'] !== 'cg_get_current_user') {
        http_response_code(405);
        cg_json(['success' => false, 'data' => 'Method not allowed.']);
        exit;
    }
} elseif ($method !== 'POST') {
    http_response_code(405);
    cg_json(['success' => false, 'data' => 'Method not allowed.']);
    exit;
}

// ── Parse body (JSON or form-encoded) ────────────────────────────────────────
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if ($method === 'POST' && str_contains($contentType, 'application/json')) {
    $body = json_decode(file_get_contents('php://input'), true) ?? [];
    foreach ($body as $k => $v) {
        $_POST[$k] = $v;
    }
}

$action = trim($_POST['action'] ?? '');

if (!$action) {
    cg_json(['success' => false, 'data' => 'No action specified.']);
    exit;
}

// ── Public actions (no auth required) ────────────────────────────────────────
$publicActions = ['cg_login_user', 'cg_logout_user', 'cg_register_user', 'cg_get_current_user', 'cg_sso_login', 'cg_ping'];

// ── Action map ────────────────────────────────────────────────────────────────
$actionFiles = [
    'cg_login_user'          => 'actions/auth.php',
    'cg_logout_user'         => 'actions/auth.php',
    'cg_register_user'       => 'actions/auth.php',
    'cg_get_current_user'    => 'actions/auth.php',
    'cg_sso_login'           => 'actions/auth.php',

    'cg_load_characters'     => 'actions/character.php',
    'cg_get_character'       => 'actions/character.php',
    'cg_save_character'      => 'actions/character.php',

    'cg_get_career_list'      => 'actions/career.php',
    'cg_get_career_gifts'     => 'actions/career.php',

    'cg_get_career_trappings' => 'actions/equipment.php',
    'cg_get_gift_trappings'   => 'actions/equipment.php',
    'cg_get_equipment_catalog'=> 'actions/equipment.php',
    'cg_get_money_list'       => 'actions/equipment.php',

    'cg_get_local_knowledge'  => 'actions/gifts.php',
    'cg_get_language_gift'    => 'actions/gifts.php',
    'cg_get_combat_save'      => 'actions/gifts.php',
    'cg_get_free_gifts'       => 'actions/gifts.php',
    'cg_get_language_list'    => 'actions/gifts.php',
    'cg_get_personality_list' => 'actions/gifts.php',
    'cg_get_personality_gift' => 'actions/gifts.php',

    'cg_get_skills_list'     => 'actions/skills.php',
    'cg_get_skill_detail'    => 'actions/skills.php',

    'cg_get_species_list'    => 'actions/species.php',
    'cg_get_species_profile' => 'actions/species.php',

    'cg_ping'                => 'actions/diagnostics.php',
    'cg_run_diagnostics'     => 'actions/diagnostics.php',

    'cg_get_spells_for_gifts' => 'actions/spells.php',
    'cg_install_spells'       => 'actions/spells.php',

    'cg_admin_list_gifts'         => 'actions/admin.php',
    'cg_admin_get_gift'           => 'actions/admin.php',
    'cg_admin_save_gift'          => 'actions/admin.php',
    'cg_admin_get_gift_children'  => 'actions/admin.php',
    'cg_admin_save_gift_rule'     => 'actions/admin.php',
    'cg_admin_delete_gift_rule'   => 'actions/admin.php',
    'cg_admin_save_gift_section'  => 'actions/admin.php',
    'cg_admin_delete_gift_section'=> 'actions/admin.php',
    'cg_admin_gift_quality_report'=> 'actions/admin.php',
    'cg_admin_list_weapons'       => 'actions/admin.php',
    'cg_admin_get_weapon'         => 'actions/admin.php',
    'cg_admin_save_weapon'        => 'actions/admin.php',
];

if (!isset($actionFiles[$action])) {
    cg_json(['success' => false, 'data' => "Unknown action: {$action}"]);
    exit;
}

// ── Auth gate ─────────────────────────────────────────────────────────────────
if (!in_array($action, $publicActions, true) && !cg_is_logged_in()) {
    http_response_code(401);
    cg_json(['success' => false, 'data' => 'Not logged in.']);
    exit;
}

// ── Load handler file and call the action function ────────────────────────────
try {
    require_once __DIR__ . '/' . $actionFiles[$action];
    $action();
} catch (Throwable $e) {
    error_log("[CG] Error in action {$action}: " . $e->getMessage());
    http_response_code(500);
    cg_json(['success' => false, 'data' => 'Server error. Please try again.']);
}
