<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_nopriv_cg_get_species_list', 'cg_get_species_list');
add_action('wp_ajax_cg_get_species_list',        'cg_get_species_list');
function cg_get_species_list() {
  check_ajax_referer('cg_nonce', 'security');
  global $wpdb;
  $table = $wpdb->prefix . 'customtables_table_species';
  $rows  = $wpdb->get_results("
    SELECT id, ct_species_name AS name
    FROM {$table}
    WHERE ct_species_name <> '' AND ct_species_name IS NOT NULL
    ORDER BY ct_species_name ASC
  ");
  wp_send_json_success($rows ?: []);
}

add_action('wp_ajax_nopriv_cg_get_species_profile', 'cg_get_species_profile');
add_action('wp_ajax_cg_get_species_profile',        'cg_get_species_profile');
function cg_get_species_profile() {
  check_ajax_referer('cg_nonce', 'security');
  global $wpdb;
  $id    = absint($_POST['id'] ?? 0);
  $table = $wpdb->prefix . 'customtables_table_species';
  $row   = $wpdb->get_row($wpdb->prepare("
    SELECT
      id,
      ct_species_name   AS speciesName,
      ct_species_gift_one_choice   AS gift_1,
      ct_species_gift_two_choice   AS gift_2,
      ct_species_gift_three_choice AS gift_3,
      -- if you have manifold columns, alias them here; default to 1 otherwise in JS
      1 AS manifold_1,
      1 AS manifold_2,
      1 AS manifold_3,
      -- skills (store IDs in these columns)
      skill_one,
      skill_two,
      skill_three,
      -- raw IDs for trait boosting
      gift_id_1,
      gift_id_2,
      gift_id_3
    FROM {$table}
    WHERE id = %d
  ", $id));
  wp_send_json_success($row ?: []);
}

add_action('wp_ajax_nopriv_cg_get_career_list', 'cg_get_career_list');
add_action('wp_ajax_cg_get_career_list',        'cg_get_career_list');
function cg_get_career_list() {
  check_ajax_referer('cg_nonce', 'security');
  global $wpdb;
  $table = $wpdb->prefix . 'customtables_table_careers';
  $rows  = $wpdb->get_results("
    SELECT id, ct_career_name AS name
    FROM {$table}
    WHERE ct_career_name <> '' AND ct_career_name IS NOT NULL
    ORDER BY ct_career_name ASC
  ");
  wp_send_json_success($rows ?: []);
}

add_action('wp_ajax_nopriv_cg_get_career_gifts', 'cg_get_career_gifts');
add_action('wp_ajax_cg_get_career_gifts',        'cg_get_career_gifts');
function cg_get_career_gifts() {
  check_ajax_referer('cg_nonce', 'security');
  global $wpdb;
  $id    = absint($_POST['id'] ?? 0);
  $table = $wpdb->prefix . 'customtables_table_careers';
  $row   = $wpdb->get_row($wpdb->prepare("
    SELECT
      id,
      ct_career_name AS careerName,
      ct_career_gift_one_choice   AS gift_1,
      ct_career_gift_two_choice   AS gift_2,
      ct_career_gift_three_choice AS gift_3,
      1 AS manifold_1,
      1 AS manifold_2,
      1 AS manifold_3,
      skill_one,
      skill_two,
      skill_three,
      gift_id_1,
      gift_id_2,
      gift_id_3
    FROM {$table}
    WHERE id = %d
  ", $id));
  wp_send_json_success($row ?: []);
}

add_action('wp_ajax_nopriv_cg_get_free_gifts', 'cg_get_free_gifts');
add_action('wp_ajax_cg_get_free_gifts',        'cg_get_free_gifts');
function cg_get_free_gifts() {
  check_ajax_referer('cg_nonce', 'security');
  global $wpdb;
  $table = $wpdb->prefix . 'customtables_table_gifts';
  // Adjust WHERE to your definition of “free-choice” gifts; here we include everything with a name
  $rows = $wpdb->get_results("
    SELECT
      id,
      ct_gifts_name AS name,
      COALESCE(NULLIF(ct_gifts_manifold,''), '1') AS ct_gifts_manifold,
      -- Chain of requirement columns (some may not exist; comment out those that don’t)
      ct_gifts_requires           AS ct_gifts_requires,
      ct_gifts_requires_two       AS ct_gifts_requires_two,
      ct_gifts_requires_three     AS ct_gifts_requires_three,
      ct_gifts_requires_four      AS ct_gifts_requires_four,
      ct_gifts_requires_five      AS ct_gifts_requires_five,
      ct_gifts_requires_six       AS ct_gifts_requires_six,
      ct_gifts_requires_seven     AS ct_gifts_requires_seven,
      ct_gifts_requires_eight     AS ct_gifts_requires_eight
    FROM {$table}
    WHERE ct_gifts_name <> '' AND ct_gifts_name IS NOT NULL
    ORDER BY ct_gifts_name ASC
  ");
  wp_send_json_success($rows ?: []);
}

add_action('wp_ajax_nopriv_cg_load_characters', 'cg_load_characters');
add_action('wp_ajax_cg_load_characters',        'cg_load_characters');
function cg_load_characters() {
  check_ajax_referer('cg_nonce', 'security');
  global $wpdb;
  $table = $wpdb->prefix . 'character_records';
  $rows = $wpdb->get_results("
    SELECT id, name
    FROM {$table}
    ORDER BY id DESC
    LIMIT 200
  ");
  wp_send_json_success($rows ?: []);
}

add_action('wp_ajax_nopriv_cg_get_character', 'cg_get_character');
add_action('wp_ajax_cg_get_character',        'cg_get_character');
function cg_get_character() {
  check_ajax_referer('cg_nonce', 'security');
  global $wpdb;
  $id    = absint($_POST['id'] ?? 0);
  $table = $wpdb->prefix . 'character_records';
  $row = $wpdb->get_row($wpdb->prepare("
    SELECT
      id, name, player_name, age, gender, motto,
      goal1, goal2, goal3, description, backstory,
      species_id, career_id,
      will, speed, body, mind, trait_species, trait_career,
      skill_marks, free_gifts
    FROM {$table}
    WHERE id = %d
  ", $id), ARRAY_A);

  if (!$row) wp_send_json_error('Not found', 404);

  // Decode JSON fields for the JS
  $row['skillMarks'] = $row['skill_marks'] ? json_decode($row['skill_marks'], true) : new stdClass();
  $row['freeGifts']  = $row['free_gifts']  ? json_decode($row['free_gifts'],  true) : ['', '', ''];
  unset($row['skill_marks'], $row['free_gifts']);
  wp_send_json_success($row);
}

add_action('wp_ajax_nopriv_cg_save_character', 'cg_save_character');
add_action('wp_ajax_cg_save_character',        'cg_save_character');
function cg_save_character() {
  check_ajax_referer('cg_nonce', 'security');
  global $wpdb;
  $table = $wpdb->prefix . 'character_records';

  $c = isset($_POST['character']) ? (array) $_POST['character'] : [];
  // sanitize
  $id    = isset($c['id']) ? absint($c['id']) : 0;
  $data  = [
    'name'         => sanitize_text_field($c['name'] ?? ''),
    'player_name'  => sanitize_text_field($c['player_name'] ?? ''),
    'age'          => sanitize_text_field($c['age'] ?? ''),
    'gender'       => sanitize_text_field($c['gender'] ?? ''),
    'motto'        => sanitize_text_field($c['motto'] ?? ''),
    'goal1'        => sanitize_text_field($c['goal1'] ?? ''),
    'goal2'        => sanitize_text_field($c['goal2'] ?? ''),
    'goal3'        => sanitize_text_field($c['goal3'] ?? ''),
    'description'  => wp_kses_post($c['description'] ?? ''),
    'backstory'    => wp_kses_post($c['backstory'] ?? ''),
    'species_id'   => absint($c['species_id'] ?? 0),
    'career_id'    => absint($c['career_id'] ?? 0),
    'will'         => sanitize_text_field($c['will'] ?? ''),
    'speed'        => sanitize_text_field($c['speed'] ?? ''),
    'body'         => sanitize_text_field($c['body'] ?? ''),
    'mind'         => sanitize_text_field($c['mind'] ?? ''),
    'trait_species'=> sanitize_text_field($c['trait_species'] ?? ''),
    'trait_career' => sanitize_text_field($c['trait_career'] ?? ''),
    'skill_marks'  => wp_json_encode($c['skillMarks'] ?? new stdClass()),
    'free_gifts'   => wp_json_encode($c['free_gifts'] ?? ['', '', '']),
  ];

  if ($id) {
    $wpdb->update($table, $data, ['id' => $id]);
  } else {
    $wpdb->insert($table, $data);
    $id = (int) $wpdb->insert_id;
  }
  wp_send_json_success(['id' => $id]);
}
