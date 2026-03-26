<?php
/**
 * Template Name: Equipment Listing Page
 */

get_header();

global $wpdb;

/* -----------------------------
 * Table names
 * ----------------------------- */
$equipment_table = 'DcVnchxg4_customtables_table_equipment';
$weapons_table   = 'DcVnchxg4_customtables_table_weapons';
$books_table     = 'DcVnchxg4_customtables_table_books';
$spells_table    = 'DcVnchxg4_customtables_table_spells';

/* -----------------------------
 * Helpers
 * ----------------------------- */
if (!function_exists('loc_table_has_column')) {
  function loc_table_has_column($table_name, $column_name) {
    global $wpdb;
    static $cache = array();

    $key = $table_name . '::' . $column_name;
    if (isset($cache[$key])) {
      return $cache[$key];
    }

    $result = $wpdb->get_var(
      $wpdb->prepare("SHOW COLUMNS FROM {$table_name} LIKE %s", $column_name)
    );

    $cache[$key] = !empty($result);
    return $cache[$key];
  }
}

if (!function_exists('loc_equipment_clean_multi_param')) {
  function loc_equipment_clean_multi_param($raw_values) {
    $clean = array();

    foreach ((array) $raw_values as $value) {
      $value = sanitize_text_field((string) $value);
      if ($value !== '') {
        $clean[] = $value;
      }
    }

    return array_values(array_unique($clean));
  }
}

if (!function_exists('loc_eq_display_value')) {
  function loc_eq_display_value($value) {
    $value = trim((string) $value);
    if ($value === '') {
      return '';
    }

    if (function_exists('loc_library_humanize_filter_value')) {
      return loc_library_humanize_filter_value($value);
    }

    $value = str_replace(array('_', '-'), ' ', $value);
    $value = preg_replace('/\s+/u', ' ', $value);
    $value = trim((string) $value);

    if ($value === '') {
      return '';
    }

    if (function_exists('mb_convert_case')) {
      return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    return ucwords($value);
  }
}

if (!function_exists('loc_eq_apply_prepare')) {
  function loc_eq_apply_prepare($sql, $params) {
    global $wpdb;
    return !empty($params) ? $wpdb->prepare($sql, $params) : $sql;
  }
}

/* -----------------------------
 * Input / filters
 * ----------------------------- */
$search = isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '';

/* Equipment filters */
$selected_categories = isset($_GET['category']) ? loc_equipment_clean_multi_param((array) wp_unslash($_GET['category'])) : array();
$subcategory         = isset($_GET['subcategory']) ? sanitize_text_field(wp_unslash($_GET['subcategory'])) : '';
$item_type           = isset($_GET['item_type']) ? sanitize_text_field(wp_unslash($_GET['item_type'])) : '';
$equipment_cost_tier = isset($_GET['cost_tier']) ? sanitize_text_field(wp_unslash($_GET['cost_tier'])) : '';
$rare_only           = isset($_GET['rare']) ? (int) $_GET['rare'] : 0;
$hide_proscribed     = isset($_GET['hide_proscribed']) ? (int) $_GET['hide_proscribed'] : 0;

/* Weapons filters */
$weapon_class_filter     = isset($_GET['weapon_class']) ? sanitize_text_field(wp_unslash($_GET['weapon_class'])) : '';
$weapon_equip_filter     = isset($_GET['weapon_equip']) ? sanitize_text_field(wp_unslash($_GET['weapon_equip'])) : '';
$weapon_range_filter     = isset($_GET['weapon_range']) ? sanitize_text_field(wp_unslash($_GET['weapon_range'])) : '';
$weapon_cost_tier_filter = isset($_GET['weapon_cost_tier']) ? sanitize_text_field(wp_unslash($_GET['weapon_cost_tier'])) : '';
$weapon_species_mode     = isset($_GET['weapon_species_mode']) ? sanitize_text_field(wp_unslash($_GET['weapon_species_mode'])) : '';

/* Spell filters */
$spell_gift_filter = isset($_GET['spell_gift']) ? sanitize_text_field(wp_unslash($_GET['spell_gift'])) : '';

/* Shared sorting */
$sort = isset($_GET['sort']) ? sanitize_text_field(wp_unslash($_GET['sort'])) : 'name_asc';

/* -----------------------------
 * Sort maps
 * ----------------------------- */
$equipment_order_sql = "e.ct_category ASC, e.ct_subcategory ASC, e.ct_name ASC";
$weapon_order_sql    = "weapon_class ASC, weapon_name ASC";

switch ($sort) {
  case 'name_desc':
    $equipment_order_sql = "e.ct_category ASC, e.ct_subcategory ASC, e.ct_name DESC";
    $weapon_order_sql    = "weapon_class ASC, weapon_name DESC";
    break;

  case 'category_asc':
    $equipment_order_sql = "e.ct_category ASC, e.ct_subcategory ASC, e.ct_name ASC";
    $weapon_order_sql    = "weapon_class ASC, weapon_name ASC";
    break;

  case 'cost_low':
    $equipment_order_sql = "e.ct_category ASC, e.ct_subcategory ASC, COALESCE(e.ct_cost_d, 999999) ASC, e.ct_name ASC";
    $weapon_order_sql    = "weapon_class ASC, COALESCE(weapon_cost_numeric, 999999) ASC, weapon_name ASC";
    break;

  case 'cost_high':
    $equipment_order_sql = "e.ct_category ASC, e.ct_subcategory ASC, COALESCE(e.ct_cost_d, 0) DESC, e.ct_name ASC";
    $weapon_order_sql    = "weapon_class ASC, COALESCE(weapon_cost_numeric, 0) DESC, weapon_name ASC";
    break;

  case 'weight_low':
    $equipment_order_sql = "e.ct_category ASC, e.ct_subcategory ASC, COALESCE(e.ct_weight_stone, 999999) ASC, e.ct_name ASC";
    $weapon_order_sql    = "weapon_class ASC, COALESCE(weapon_weight_numeric, 999999) ASC, weapon_name ASC";
    break;

  case 'weight_high':
    $equipment_order_sql = "e.ct_category ASC, e.ct_subcategory ASC, COALESCE(e.ct_weight_stone, 0) DESC, e.ct_name ASC";
    $weapon_order_sql    = "weapon_class ASC, COALESCE(weapon_weight_numeric, 0) DESC, weapon_name ASC";
    break;

  case 'name_asc':
  default:
    $equipment_order_sql = "e.ct_category ASC, e.ct_subcategory ASC, e.ct_name ASC";
    $weapon_order_sql    = "weapon_class ASC, weapon_name ASC";
    break;
}

/* -----------------------------
 * Distinct filter values
 * ----------------------------- */
$categories = $wpdb->get_col("
  SELECT DISTINCT ct_category
  FROM {$equipment_table}
  WHERE " . (loc_table_has_column($equipment_table, 'published') ? "published = 1 AND " : "") . " ct_category <> ''
  ORDER BY ct_category ASC
");

$subcategories = $wpdb->get_col("
  SELECT DISTINCT ct_subcategory
  FROM {$equipment_table}
  WHERE " . (loc_table_has_column($equipment_table, 'published') ? "published = 1 AND " : "") . " ct_subcategory IS NOT NULL AND ct_subcategory <> ''
  ORDER BY ct_subcategory ASC
");

$item_types = $wpdb->get_col("
  SELECT DISTINCT ct_item_type
  FROM {$equipment_table}
  WHERE " . (loc_table_has_column($equipment_table, 'published') ? "published = 1 AND " : "") . " ct_item_type <> ''
  ORDER BY ct_item_type ASC
");

$equipment_cost_tiers = $wpdb->get_col("
  SELECT DISTINCT ct_cost_tier
  FROM {$equipment_table}
  WHERE " . (loc_table_has_column($equipment_table, 'published') ? "published = 1 AND " : "") . " ct_cost_tier IS NOT NULL AND ct_cost_tier <> ''
  ORDER BY ct_cost_tier ASC
");

$weapon_classes = $wpdb->get_col("
  SELECT DISTINCT ct_weapon_class
  FROM {$weapons_table}
  WHERE " . (loc_table_has_column($weapons_table, 'published') ? "published = 1 AND " : "") . " ct_weapon_class IS NOT NULL AND ct_weapon_class <> ''
  ORDER BY ct_weapon_class ASC
");

$weapon_equips = $wpdb->get_col("
  SELECT DISTINCT ct_equip
  FROM {$weapons_table}
  WHERE " . (loc_table_has_column($weapons_table, 'published') ? "published = 1 AND " : "") . " ct_equip IS NOT NULL AND ct_equip <> ''
  ORDER BY ct_equip ASC
");

$weapon_ranges = $wpdb->get_col("
  SELECT DISTINCT ct_range_band
  FROM {$weapons_table}
  WHERE " . (loc_table_has_column($weapons_table, 'published') ? "published = 1 AND " : "") . " ct_range_band IS NOT NULL AND ct_range_band <> ''
  ORDER BY ct_range_band ASC
");

$weapon_cost_tiers = $wpdb->get_col("
  SELECT DISTINCT ct_cost_tier
  FROM {$weapons_table}
  WHERE " . (loc_table_has_column($weapons_table, 'published') ? "published = 1 AND " : "") . " ct_cost_tier IS NOT NULL AND ct_cost_tier <> ''
  ORDER BY ct_cost_tier ASC
");

$spell_gifts = $wpdb->get_col("
  SELECT DISTINCT ct_gift_name
  FROM {$spells_table}
  WHERE ct_gift_name IS NOT NULL AND ct_gift_name <> ''
  ORDER BY ct_gift_name ASC
");

/* -----------------------------
 * Equipment WHERE builder
 * ----------------------------- */
$equipment_where  = array();
$equipment_params = array();

if (loc_table_has_column($equipment_table, 'published')) {
  $equipment_where[] = "e.published = 1";
}

if ($search !== '') {
  $search_parts = array();

  if (loc_table_has_column($equipment_table, 'ct_name')) {
    $search_parts[] = "e.ct_name LIKE %s";
  }
  if (loc_table_has_column($equipment_table, 'ct_slug')) {
    $search_parts[] = "e.ct_slug LIKE %s";
  }
  if (loc_table_has_column($equipment_table, 'ct_effect')) {
    $search_parts[] = "e.ct_effect LIKE %s";
  }
  if (loc_table_has_column($equipment_table, 'ct_notes')) {
    $search_parts[] = "e.ct_notes LIKE %s";
  }

  if (!empty($search_parts)) {
    $equipment_where[] = '(' . implode(' OR ', $search_parts) . ')';
    $like = '%' . $wpdb->esc_like($search) . '%';
    foreach ($search_parts as $unused) {
      $equipment_params[] = $like;
    }
  }
}

if (!empty($selected_categories) && loc_table_has_column($equipment_table, 'ct_category')) {
  $placeholders = implode(', ', array_fill(0, count($selected_categories), '%s'));
  $equipment_where[] = "e.ct_category IN ({$placeholders})";
  foreach ($selected_categories as $cat_value) {
    $equipment_params[] = $cat_value;
  }
}

if ($subcategory !== '' && loc_table_has_column($equipment_table, 'ct_subcategory')) {
  $equipment_where[] = "e.ct_subcategory = %s";
  $equipment_params[] = $subcategory;
}

if ($item_type !== '' && loc_table_has_column($equipment_table, 'ct_item_type')) {
  $equipment_where[] = "e.ct_item_type = %s";
  $equipment_params[] = $item_type;
}

if ($equipment_cost_tier !== '' && loc_table_has_column($equipment_table, 'ct_cost_tier')) {
  $equipment_where[] = "e.ct_cost_tier = %s";
  $equipment_params[] = $equipment_cost_tier;
}

if ($rare_only === 1 && loc_table_has_column($equipment_table, 'ct_is_rare')) {
  $equipment_where[] = "e.ct_is_rare = 1";
}

if ($hide_proscribed === 1 && loc_table_has_column($equipment_table, 'ct_is_proscribed')) {
  $equipment_where[] = "e.ct_is_proscribed = 0";
}

$equipment_where_sql = !empty($equipment_where) ? implode(' AND ', $equipment_where) : '1=1';

/* -----------------------------
 * Equipment query
 * ----------------------------- */
$equipment_select_fields = array('e.*');
$join_books_sql = '';

if (
  loc_table_has_column($equipment_table, 'ct_source_book') &&
  loc_table_has_column($books_table, 'ct_id') &&
  loc_table_has_column($books_table, 'ct_book_name')
) {
  $equipment_select_fields[] = 'b.ct_book_name';
  if (loc_table_has_column($books_table, 'ct_ct_slug')) {
    $equipment_select_fields[] = 'b.ct_ct_slug AS book_slug';
  }

  $join_books_sql = "
    LEFT JOIN {$books_table} b
      ON b.ct_id = e.ct_source_book
  ";
}

$equipment_sql = "
  SELECT " . implode(",\n         ", $equipment_select_fields) . "
  FROM {$equipment_table} e
  {$join_books_sql}
  WHERE {$equipment_where_sql}
  ORDER BY {$equipment_order_sql}
";

$equipment_items = $wpdb->get_results(loc_eq_apply_prepare($equipment_sql, $equipment_params));

/* -----------------------------
 * Group equipment by category/subcategory
 * ----------------------------- */
$grouped_equipment = array();

if (!empty($equipment_items)) {
  foreach ($equipment_items as $item) {
    $cat = !empty($item->ct_category) ? $item->ct_category : 'Other';
    $sub = !empty($item->ct_subcategory) ? $item->ct_subcategory : 'General';

    if (!isset($grouped_equipment[$cat])) {
      $grouped_equipment[$cat] = array();
    }

    if (!isset($grouped_equipment[$cat][$sub])) {
      $grouped_equipment[$cat][$sub] = array();
    }

    $grouped_equipment[$cat][$sub][] = $item;
  }
}

/* -----------------------------
 * Weapons schema detection
 * ----------------------------- */
$weapon_name_column = loc_table_has_column($weapons_table, 'ct_name')
  ? 'ct_name'
  : (loc_table_has_column($weapons_table, 'ct_weapons_name') ? 'ct_weapons_name' : '');

$weapon_slug_column   = loc_table_has_column($weapons_table, 'ct_slug') ? 'ct_slug' : '';
$weapon_class_col     = loc_table_has_column($weapons_table, 'ct_weapon_class') ? 'ct_weapon_class' : '';
$weapon_cost_col      = loc_table_has_column($weapons_table, 'ct_cost_tier') ? 'ct_cost_tier' : '';
$weapon_cost_d        = loc_table_has_column($weapons_table, 'ct_cost_d') ? 'ct_cost_d' : '';
$weapon_equip_col     = loc_table_has_column($weapons_table, 'ct_equip') ? 'ct_equip' : '';
$weapon_range_col     = loc_table_has_column($weapons_table, 'ct_range_band') ? 'ct_range_band' : '';
$weapon_attack_col    = loc_table_has_column($weapons_table, 'ct_attack_dice') ? 'ct_attack_dice' : '';
$weapon_effect_col    = loc_table_has_column($weapons_table, 'ct_effect') ? 'ct_effect' : '';
$weapon_desc_col      = loc_table_has_column($weapons_table, 'ct_descriptors') ? 'ct_descriptors' : '';
$weapon_species_col   = loc_table_has_column($weapons_table, 'ct_is_species_weapon') ? 'ct_is_species_weapon' : '';
$weapon_weight_num    = loc_table_has_column($weapons_table, 'ct_weight_stone') ? 'ct_weight_stone' : '';
$weapon_published     = loc_table_has_column($weapons_table, 'published');

/* -----------------------------
 * Weapons query
 * ----------------------------- */
$weapon_rows     = array();
$grouped_weapons = array();

if ($weapon_name_column !== '') {
  $weapon_selects = array();

  $weapon_selects[] = "w.{$weapon_name_column} AS weapon_name";
  $weapon_selects[] = ($weapon_slug_column !== '') ? "w.{$weapon_slug_column} AS weapon_slug" : "'' AS weapon_slug";
  $weapon_selects[] = ($weapon_class_col !== '')   ? "w.{$weapon_class_col} AS weapon_class"  : "'Other' AS weapon_class";
  $weapon_selects[] = ($weapon_cost_col !== '')    ? "w.{$weapon_cost_col} AS weapon_cost_tier" : "'' AS weapon_cost_tier";
  $weapon_selects[] = ($weapon_cost_d !== '')      ? "w.{$weapon_cost_d} AS weapon_cost_numeric" : "NULL AS weapon_cost_numeric";
  $weapon_selects[] = ($weapon_weight_num !== '')  ? "w.{$weapon_weight_num} AS weapon_weight_numeric" : "NULL AS weapon_weight_numeric";
  $weapon_selects[] = ($weapon_equip_col !== '')   ? "w.{$weapon_equip_col} AS weapon_equip" : "'' AS weapon_equip";
  $weapon_selects[] = ($weapon_range_col !== '')   ? "w.{$weapon_range_col} AS weapon_range_band" : "'' AS weapon_range_band";
  $weapon_selects[] = ($weapon_attack_col !== '')  ? "w.{$weapon_attack_col} AS weapon_attack_dice" : "'' AS weapon_attack_dice";
  $weapon_selects[] = ($weapon_effect_col !== '')  ? "w.{$weapon_effect_col} AS weapon_effect" : "'' AS weapon_effect";
  $weapon_selects[] = ($weapon_desc_col !== '')    ? "w.{$weapon_desc_col} AS weapon_descriptors" : "'' AS weapon_descriptors";
  $weapon_selects[] = ($weapon_species_col !== '') ? "w.{$weapon_species_col} AS weapon_is_species_weapon" : "0 AS weapon_is_species_weapon";

  $weapon_where  = array();
  $weapon_params = array();

  if ($weapon_published) {
    $weapon_where[] = "w.published = 1";
  }

  if ($search !== '') {
    $weapon_search_parts = array();

    $weapon_search_parts[] = "w.{$weapon_name_column} LIKE %s";

    if ($weapon_slug_column !== '') {
      $weapon_search_parts[] = "w.{$weapon_slug_column} LIKE %s";
    }
    if ($weapon_effect_col !== '') {
      $weapon_search_parts[] = "w.{$weapon_effect_col} LIKE %s";
    }
    if ($weapon_desc_col !== '') {
      $weapon_search_parts[] = "w.{$weapon_desc_col} LIKE %s";
    }

    $weapon_where[] = '(' . implode(' OR ', $weapon_search_parts) . ')';

    $w_like = '%' . $wpdb->esc_like($search) . '%';
    foreach ($weapon_search_parts as $unused) {
      $weapon_params[] = $w_like;
    }
  }

  if ($weapon_class_filter !== '' && $weapon_class_col !== '') {
    $weapon_where[] = "w.{$weapon_class_col} = %s";
    $weapon_params[] = $weapon_class_filter;
  }

  if ($weapon_equip_filter !== '' && $weapon_equip_col !== '') {
    $weapon_where[] = "w.{$weapon_equip_col} = %s";
    $weapon_params[] = $weapon_equip_filter;
  }

  if ($weapon_range_filter !== '' && $weapon_range_col !== '') {
    $weapon_where[] = "w.{$weapon_range_col} = %s";
    $weapon_params[] = $weapon_range_filter;
  }

  if ($weapon_cost_tier_filter !== '' && $weapon_cost_col !== '') {
    $weapon_where[] = "w.{$weapon_cost_col} = %s";
    $weapon_params[] = $weapon_cost_tier_filter;
  }

  if ($weapon_species_mode === 'species_only' && $weapon_species_col !== '') {
    $weapon_where[] = "w.{$weapon_species_col} = 1";
  } elseif ($weapon_species_mode === 'manufactured_only' && $weapon_species_col !== '') {
    $weapon_where[] = "w.{$weapon_species_col} = 0";
  }

  $weapon_where_sql = !empty($weapon_where) ? implode(' AND ', $weapon_where) : '1=1';

  $weapons_sql = "
    SELECT " . implode(",\n           ", $weapon_selects) . "
    FROM {$weapons_table} w
    WHERE {$weapon_where_sql}
    ORDER BY {$weapon_order_sql}
  ";

  $weapon_rows = $wpdb->get_results(loc_eq_apply_prepare($weapons_sql, $weapon_params));

  if (!empty($weapon_rows)) {
    foreach ($weapon_rows as $weapon) {
      $class = !empty($weapon->weapon_class) ? $weapon->weapon_class : 'Other';

      if (!isset($grouped_weapons[$class])) {
        $grouped_weapons[$class] = array();
      }

      $grouped_weapons[$class][] = $weapon;
    }
  }
}

$total_equipment = count($equipment_items);
$total_weapons   = count($weapon_rows);

/* -----------------------------
 * Spells query
 * ----------------------------- */
$spell_where  = array();
$spell_params = array();

if ($search !== '') {
  $spell_where[]  = '(ct_name LIKE %s OR ct_effect LIKE %s OR ct_descriptors LIKE %s OR ct_gift_name LIKE %s)';
  $s_like = '%' . $wpdb->esc_like($search) . '%';
  $spell_params[] = $s_like;
  $spell_params[] = $s_like;
  $spell_params[] = $s_like;
  $spell_params[] = $s_like;
}

if ($spell_gift_filter !== '') {
  $spell_where[]  = 'ct_gift_name = %s';
  $spell_params[] = $spell_gift_filter;
}

$spell_where_sql = !empty($spell_where) ? implode(' AND ', $spell_where) : '1=1';

$spells_sql = "
  SELECT ct_id, ct_name, ct_equip, ct_range, ct_attack_dice, ct_effect, ct_descriptors, ct_gift_name, ct_sort
  FROM {$spells_table}
  WHERE {$spell_where_sql}
  ORDER BY ct_gift_name ASC, ct_sort ASC, ct_name ASC
";

$spell_rows = $wpdb->get_results(loc_eq_apply_prepare($spells_sql, $spell_params));

$grouped_spells = array();
foreach ($spell_rows as $spell) {
  $g = !empty($spell->ct_gift_name) ? $spell->ct_gift_name : 'Other';
  if (!isset($grouped_spells[$g])) {
    $grouped_spells[$g] = array();
  }
  $grouped_spells[$g][] = $spell;
}

$total_spells = count($spell_rows);

/* -----------------------------
 * Active filter summaries
 * ----------------------------- */
$equipment_active_filters = array();
$weapon_active_filters    = array();
$spell_active_filters     = array();

if (!empty($selected_categories)) {
  $equipment_active_filters[] = 'Categories: ' . implode(', ', array_map('loc_eq_display_value', $selected_categories));
}
if ($subcategory !== '') {
  $equipment_active_filters[] = 'Subcategory: ' . loc_eq_display_value($subcategory);
}
if ($item_type !== '') {
  $equipment_active_filters[] = 'Item Type: ' . loc_eq_display_value($item_type);
}
if ($equipment_cost_tier !== '') {
  $equipment_active_filters[] = 'Cost Tier: ' . $equipment_cost_tier;
}
if ($rare_only === 1) {
  $equipment_active_filters[] = 'Rare Only';
}
if ($hide_proscribed === 1) {
  $equipment_active_filters[] = 'Hide Proscribed';
}

if ($weapon_class_filter !== '') {
  $weapon_active_filters[] = 'Class: ' . loc_eq_display_value($weapon_class_filter);
}
if ($weapon_equip_filter !== '') {
  $weapon_active_filters[] = 'Equip: ' . loc_eq_display_value($weapon_equip_filter);
}
if ($weapon_range_filter !== '') {
  $weapon_active_filters[] = 'Range: ' . loc_eq_display_value($weapon_range_filter);
}
if ($weapon_cost_tier_filter !== '') {
  $weapon_active_filters[] = 'Cost Tier: ' . $weapon_cost_tier_filter;
}
if ($weapon_species_mode === 'species_only') {
  $weapon_active_filters[] = 'Species Weapons Only';
} elseif ($weapon_species_mode === 'manufactured_only') {
  $weapon_active_filters[] = 'Manufactured Weapons Only';
}

if ($spell_gift_filter !== '') {
  $spell_active_filters[] = 'School: ' . $spell_gift_filter;
}

/* -----------------------------
 * Section visibility
 * ----------------------------- */
$has_equipment_only_filters = (
  !empty($selected_categories) ||
  $subcategory !== '' ||
  $item_type !== '' ||
  $equipment_cost_tier !== '' ||
  $rare_only === 1 ||
  $hide_proscribed === 1
);

$has_weapon_only_filters = (
  $weapon_class_filter !== '' ||
  $weapon_equip_filter !== '' ||
  $weapon_range_filter !== '' ||
  $weapon_cost_tier_filter !== '' ||
  $weapon_species_mode !== ''
);

$has_spell_only_filters = ($spell_gift_filter !== '');

$show_equipment_section = true;
$show_weapons_section   = true;
$show_spells_section    = true;

if ($has_spell_only_filters && !$has_equipment_only_filters && !$has_weapon_only_filters) {
  $show_equipment_section = false;
  $show_weapons_section   = false;
  $show_spells_section    = true;
} elseif ($has_weapon_only_filters && !$has_equipment_only_filters && !$has_spell_only_filters) {
  $show_equipment_section = false;
  $show_weapons_section   = true;
  $show_spells_section    = false;
} elseif ($has_equipment_only_filters && !$has_weapon_only_filters && !$has_spell_only_filters) {
  $show_equipment_section = true;
  $show_weapons_section   = false;
  $show_spells_section    = false;
}

$visible_equipment_total = $show_equipment_section ? $total_equipment : 0;
$visible_weapons_total   = $show_weapons_section ? $total_weapons : 0;
$visible_spells_total    = $show_spells_section ? $total_spells : 0;
$visible_total           = $visible_equipment_total + $visible_weapons_total + $visible_spells_total;
?>

<style>
  .equipment-page .equipment-filters {
    margin-bottom: 24px;
    padding: 20px;
    border: 1px solid rgba(240,195,90,0.24);
    border-radius: 16px;
    background: rgba(255,255,255,0.08);
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
  }

  .equipment-page .equipment-filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin-bottom: 16px;
  }

  .equipment-page .equipment-filter-panel {
    padding: 14px;
    border: 1px solid rgba(240,195,90,0.20);
    border-radius: 14px;
    background: rgba(255,255,255,0.05);
  }

  .equipment-page .equipment-filter-panel h2 {
    margin: 0 0 10px;
    font-size: 1.05rem;
  }

  .equipment-page .equipment-filter-panel p {
    margin: 0 0 12px;
    opacity: 0.88;
  }

  .equipment-page .equipment-filter-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 12px;
  }

  .equipment-page .equipment-filter-field:last-child {
    margin-bottom: 0;
  }

  .equipment-page .equipment-filter-field--wide {
    grid-column: 1 / -1;
  }

  .equipment-page label,
  .equipment-page legend {
    display: block;
    font-weight: 700;
    margin-bottom: 4px;
  }

  .equipment-page input[type="text"],
  .equipment-page input[type="search"],
  .equipment-page select {
    width: 100%;
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid rgba(240,195,90,0.24);
    background: #fff;
    color: #111;
    font-size: 1rem;
  }

  .equipment-page .equipment-search-row {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: 12px;
    align-items: end;
    margin-bottom: 16px;
  }

  .equipment-page .equipment-search-row button {
    padding: 11px 16px;
    border: 0;
    border-radius: 10px;
    background: #5a8f5c;
    color: #fff;
    font-weight: 700;
    cursor: pointer;
  }

  .equipment-page .equipment-search-help {
    margin-top: 6px;
    font-size: 0.92rem;
    opacity: 0.86;
  }

  .equipment-page .equipment-checkbox-group {
    margin: 0;
    padding: 12px 14px 14px;
    border: 1px solid rgba(240,195,90,0.24);
    border-radius: 12px;
    background: rgba(255,255,255,0.06);
  }

  .equipment-page .equipment-checkbox-group legend {
    padding: 0 6px;
    margin-bottom: 8px;
  }

  .equipment-page .equipment-checkbox-list {
    display: flex;
    flex-wrap: wrap;
    gap: 10px 12px;
    margin-top: 4px;
  }

  .equipment-page .equipment-checkbox-list label {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    margin: 0;
    padding: 6px 10px;
    border: 1px solid rgba(240,195,90,0.18);
    border-radius: 999px;
    background: rgba(255,255,255,0.05);
    font-weight: 500;
    line-height: 1.2;
  }

  .equipment-page .equipment-checkbox-list input[type="checkbox"] {
    width: auto;
    margin: 0;
  }

  .equipment-page .equipment-filter-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
    padding-top: 14px;
    border-top: 1px solid rgba(240,195,90,0.18);
  }

  .equipment-page .equipment-filter-toggles,
  .equipment-page .equipment-filter-buttons {
    display: flex;
    gap: 10px 14px;
    align-items: center;
    flex-wrap: wrap;
  }

  .equipment-page .equipment-filter-toggles label {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 0;
    font-weight: 500;
  }

  .equipment-page .equipment-filter-buttons button {
    padding: 10px 14px;
    border: 0;
    border-radius: 10px;
    background: #5a8f5c;
    color: #fff;
    font-weight: 700;
    cursor: pointer;
  }

  .equipment-page .equipment-filter-buttons a {
    display: inline-flex;
    align-items: center;
    padding: 10px 14px;
    border-radius: 10px;
    border: 1px solid rgba(240,195,90,0.24);
    text-decoration: none;
    color: inherit;
    background: rgba(255,255,255,0.05);
  }

  .equipment-page .equipment-summary {
    margin-bottom: 24px;
  }

  .equipment-page .equipment-summary-note {
    margin-top: 6px;
  }

  .equipment-page .equipment-section {
    margin-bottom: 36px;
  }

  .equipment-page .equipment-category,
  .equipment-page .weapon-group {
    margin-bottom: 28px;
  }

  .equipment-page .equipment-subcategory {
    margin-top: 18px;
    margin-bottom: 18px;
  }

  .equipment-page .equipment-category > h2,
  .equipment-page .weapon-group > h3 {
    margin-bottom: 10px;
  }

  .equipment-page .equipment-subcategory > h3,
  .equipment-page .weapon-group > h4 {
    margin-bottom: 10px;
  }

  .equipment-page .table-wrap {
    overflow-x: auto;
    border: 1px solid rgba(240,195,90,0.22);
    border-radius: 12px;
    background: rgba(0,0,0,0.10);
  }

  .equipment-page table {
    width: 100%;
    border-collapse: collapse;
    min-width: 900px;
  }

  .equipment-page th,
  .equipment-page td {
    padding: 10px 12px;
    border-bottom: 1px solid rgba(240,195,90,0.14);
    vertical-align: top;
    text-align: left;
  }

  .equipment-page th {
    background: rgba(240,195,90,0.10);
    font-weight: 700;
  }

  .equipment-page tr:last-child td {
    border-bottom: 0;
  }

  .equipment-page .badge-list {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
  }

  .equipment-page .equipment-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 999px;
    border: 1px solid rgba(240,195,90,0.22);
    font-size: 0.85rem;
    line-height: 1.2;
    white-space: nowrap;
  }

  .equipment-page .muted {
    opacity: 0.8;
  }

  .equipment-page .section-divider {
    margin: 40px 0 24px;
    border: 0;
    border-top: 1px solid rgba(240,195,90,0.22);
  }

  @media (max-width: 860px) {
    .equipment-page .equipment-search-row {
      grid-template-columns: 1fr;
    }

    .equipment-page .equipment-filter-actions {
      align-items: stretch;
    }

    .equipment-page .equipment-filter-toggles,
    .equipment-page .equipment-filter-buttons {
      width: 100%;
    }
  }
</style>

<main class="site-main equipment-page">
  <header class="page-header">
    <h1>Equipment &amp; Spells</h1>
    <p>Browse equipment, weapons, and spells. Filters that apply only to one section will hide the other sections automatically.</p>
  </header>

  <form method="get" class="equipment-filters">
    <div class="equipment-search-row">
      <div class="equipment-filter-field">
        <label for="q">Search</label>
        <input type="search" name="q" id="q" value="<?php echo esc_attr($search); ?>" placeholder="Optional: name, effect, notes...">
        <div class="equipment-search-help">Leave this empty to browse by filters only.</div>
      </div>

      <div class="equipment-filter-buttons">
        <button type="submit">Apply filters</button>
        <a href="<?php echo esc_url(get_permalink()); ?>">Reset all</a>
      </div>
    </div>

    <div class="equipment-filter-grid">
      <section class="equipment-filter-panel">
        <h2>Equipment filters</h2>
        <p>Using only these hides the Weapons section.</p>

        <div class="equipment-filter-field">
          <fieldset class="equipment-checkbox-group">
            <legend>Categories</legend>
            <div class="equipment-checkbox-list">
              <?php foreach ($categories as $v) : ?>
                <label>
                  <input
                    type="checkbox"
                    name="category[]"
                    value="<?php echo esc_attr($v); ?>"
                    <?php checked(in_array($v, $selected_categories, true)); ?>
                  >
                  <?php echo esc_html(loc_eq_display_value($v)); ?>
                </label>
              <?php endforeach; ?>
            </div>
          </fieldset>
        </div>

        <div class="equipment-filter-field">
          <label for="subcategory">Subcategory</label>
          <select name="subcategory" id="subcategory">
            <option value="">All</option>
            <?php foreach ($subcategories as $v) : ?>
              <option value="<?php echo esc_attr($v); ?>" <?php selected($subcategory, $v); ?>>
                <?php echo esc_html(loc_eq_display_value($v)); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="equipment-filter-field">
          <label for="item_type">Item Type</label>
          <select name="item_type" id="item_type">
            <option value="">All</option>
            <?php foreach ($item_types as $v) : ?>
              <option value="<?php echo esc_attr($v); ?>" <?php selected($item_type, $v); ?>>
                <?php echo esc_html(loc_eq_display_value($v)); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="equipment-filter-field">
          <label for="cost_tier">Cost Tier</label>
          <select name="cost_tier" id="cost_tier">
            <option value="">All</option>
            <?php foreach ($equipment_cost_tiers as $v) : ?>
              <option value="<?php echo esc_attr($v); ?>" <?php selected($equipment_cost_tier, $v); ?>>
                <?php echo esc_html($v); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="equipment-filter-toggles">
          <label>
            <input type="checkbox" name="rare" value="1" <?php checked($rare_only, 1); ?>>
            Rare only
          </label>

          <label>
            <input type="checkbox" name="hide_proscribed" value="1" <?php checked($hide_proscribed, 1); ?>>
            Hide proscribed
          </label>
        </div>
      </section>

      <section class="equipment-filter-panel">
        <h2>Weapons filters</h2>
        <p>Using only these hides the Equipment tables.</p>

        <div class="equipment-filter-field">
          <label for="weapon_class">Weapon Class</label>
          <select name="weapon_class" id="weapon_class">
            <option value="">All</option>
            <?php foreach ($weapon_classes as $v) : ?>
              <option value="<?php echo esc_attr($v); ?>" <?php selected($weapon_class_filter, $v); ?>>
                <?php echo esc_html(loc_eq_display_value($v)); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="equipment-filter-field">
          <label for="weapon_equip">Equip Slot</label>
          <select name="weapon_equip" id="weapon_equip">
            <option value="">All</option>
            <?php foreach ($weapon_equips as $v) : ?>
              <option value="<?php echo esc_attr($v); ?>" <?php selected($weapon_equip_filter, $v); ?>>
                <?php echo esc_html(loc_eq_display_value($v)); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="equipment-filter-field">
          <label for="weapon_range">Range</label>
          <select name="weapon_range" id="weapon_range">
            <option value="">All</option>
            <?php foreach ($weapon_ranges as $v) : ?>
              <option value="<?php echo esc_attr($v); ?>" <?php selected($weapon_range_filter, $v); ?>>
                <?php echo esc_html(loc_eq_display_value($v)); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="equipment-filter-field">
          <label for="weapon_cost_tier">Weapon Cost Tier</label>
          <select name="weapon_cost_tier" id="weapon_cost_tier">
            <option value="">All</option>
            <?php foreach ($weapon_cost_tiers as $v) : ?>
              <option value="<?php echo esc_attr($v); ?>" <?php selected($weapon_cost_tier_filter, $v); ?>>
                <?php echo esc_html($v); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="equipment-filter-field">
          <label for="weapon_species_mode">Weapon Source</label>
          <select name="weapon_species_mode" id="weapon_species_mode">
            <option value="">All</option>
            <option value="manufactured_only" <?php selected($weapon_species_mode, 'manufactured_only'); ?>>Manufactured Weapons Only</option>
            <option value="species_only" <?php selected($weapon_species_mode, 'species_only'); ?>>Species Weapons Only</option>
          </select>
        </div>
      </section>

      <section class="equipment-filter-panel">
        <h2>Spell filters</h2>
        <p>Using only these hides the Equipment and Weapons tables.</p>

        <div class="equipment-filter-field">
          <label for="spell_gift">School / Gift</label>
          <select name="spell_gift" id="spell_gift">
            <option value="">All</option>
            <?php foreach ($spell_gifts as $g) : ?>
              <option value="<?php echo esc_attr($g); ?>" <?php selected($spell_gift_filter, $g); ?>>
                <?php echo esc_html($g); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </section>
    </div>
  </form>

  <div class="equipment-summary">
    <strong><?php echo number_format_i18n($visible_total); ?></strong> visible result<?php echo ($visible_total === 1 ? '' : 's'); ?>.

    <?php if ($show_equipment_section && $total_equipment > 0) : ?>
      <span class="muted"><?php echo number_format_i18n($total_equipment); ?> equipment</span>
    <?php endif; ?>

    <?php if ($show_weapons_section && $total_weapons > 0) : ?>
      <span class="muted"><?php echo number_format_i18n($total_weapons); ?> weapon<?php echo ($total_weapons === 1 ? '' : 's'); ?></span>
    <?php endif; ?>

    <?php if ($show_spells_section && $total_spells > 0) : ?>
      <span class="muted"><?php echo number_format_i18n($total_spells); ?> spell<?php echo ($total_spells === 1 ? '' : 's'); ?></span>
    <?php endif; ?>

    <?php if ($search !== '') : ?>
      <div class="equipment-summary-note muted">
        Search term: <?php echo esc_html($search); ?>.
      </div>
    <?php endif; ?>

    <?php if (!empty($equipment_active_filters)) : ?>
      <div class="equipment-summary-note muted">
        Equipment filters: <?php echo esc_html(implode(' • ', $equipment_active_filters)); ?>.
      </div>
    <?php endif; ?>

    <?php if (!empty($weapon_active_filters)) : ?>
      <div class="equipment-summary-note muted">
        Weapons filters: <?php echo esc_html(implode(' • ', $weapon_active_filters)); ?>.
      </div>
    <?php endif; ?>

    <?php if (!empty($spell_active_filters)) : ?>
      <div class="equipment-summary-note muted">
        Spell filters: <?php echo esc_html(implode(' • ', $spell_active_filters)); ?>.
      </div>
    <?php endif; ?>

    <?php if (!$show_equipment_section && $show_weapons_section && !$show_spells_section) : ?>
      <div class="equipment-summary-note muted">
        Weapon-specific filters are active, so Equipment and Spells tables are hidden.
      </div>
    <?php elseif ($show_equipment_section && !$show_weapons_section && !$show_spells_section) : ?>
      <div class="equipment-summary-note muted">
        Equipment-specific filters are active, so Weapons and Spells tables are hidden.
      </div>
    <?php elseif (!$show_equipment_section && !$show_weapons_section && $show_spells_section) : ?>
      <div class="equipment-summary-note muted">
        Spell-specific filters are active, so Equipment and Weapons tables are hidden.
      </div>
    <?php endif; ?>
  </div>

  <?php if ($show_equipment_section) : ?>
    <section class="equipment-section">
      <h2>Equipment</h2>

      <?php if (empty($grouped_equipment)) : ?>
        <p>No equipment matched your current filters.</p>
      <?php else : ?>
        <?php foreach ($grouped_equipment as $cat_name => $subcategory_groups) : ?>
          <section class="equipment-category">
            <h3><?php echo esc_html(loc_eq_display_value($cat_name)); ?></h3>

            <?php foreach ($subcategory_groups as $sub_name => $rows) : ?>
              <div class="equipment-subcategory">
                <h4><?php echo esc_html(loc_eq_display_value($sub_name)); ?></h4>

                <div class="table-wrap">
                  <table>
                    <thead>
                      <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Cost</th>
                        <th>Weight</th>
                        <th>Effect / Notes</th>
                        <th>Tags</th>
                        <th>Source</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($rows as $item) : ?>
                        <tr>
                          <td>
                            <a href="<?php echo esc_url(home_url('/equipment/' . $item->ct_slug . '/')); ?>">
                              <?php echo esc_html($item->ct_name); ?>
                            </a>
                          </td>

                          <td>
                            <?php if (!empty($item->ct_item_type)) : ?>
                              <?php echo esc_html(loc_eq_display_value($item->ct_item_type)); ?>
                            <?php else : ?>
                              <span class="muted">—</span>
                            <?php endif; ?>
                          </td>

                          <td>
                            <?php if (!empty($item->ct_cost_text)) : ?>
                              <?php echo esc_html($item->ct_cost_text); ?>
                            <?php elseif (!empty($item->ct_cost_tier)) : ?>
                              <?php echo esc_html($item->ct_cost_tier); ?>
                            <?php else : ?>
                              <span class="muted">—</span>
                            <?php endif; ?>
                          </td>

                          <td>
                            <?php if (!empty($item->ct_weight_text)) : ?>
                              <?php echo esc_html($item->ct_weight_text); ?>
                            <?php else : ?>
                              <span class="muted">—</span>
                            <?php endif; ?>
                          </td>

                          <td>
                            <?php if (!empty($item->ct_effect)) : ?>
                              <div><strong>Effect:</strong> <?php echo esc_html($item->ct_effect); ?></div>
                            <?php endif; ?>

                            <?php if (!empty($item->ct_notes)) : ?>
                              <div><?php echo nl2br(esc_html($item->ct_notes)); ?></div>
                            <?php endif; ?>

                            <?php if (
                              empty($item->ct_effect) &&
                              empty($item->ct_notes) &&
                              empty($item->ct_capacity_text) &&
                              empty($item->ct_cover_dice) &&
                              empty($item->ct_armor_dice) &&
                              empty($item->ct_skill_dice) &&
                              empty($item->ct_rent_cost_text) &&
                              empty($item->ct_own_cost_text)
                            ) : ?>
                              <span class="muted">—</span>
                            <?php endif; ?>

                            <?php if (!empty($item->ct_capacity_text)) : ?>
                              <div><strong>Capacity:</strong> <?php echo esc_html($item->ct_capacity_text); ?></div>
                            <?php endif; ?>

                            <?php if (!empty($item->ct_cover_dice)) : ?>
                              <div><strong>Cover:</strong> <?php echo esc_html($item->ct_cover_dice); ?></div>
                            <?php endif; ?>

                            <?php if (!empty($item->ct_armor_dice)) : ?>
                              <div><strong>Armor:</strong> <?php echo esc_html($item->ct_armor_dice); ?></div>
                            <?php endif; ?>

                            <?php if (!empty($item->ct_skill_dice)) : ?>
                              <div><strong>Skill:</strong> <?php echo esc_html($item->ct_skill_dice); ?></div>
                            <?php endif; ?>

                            <?php if (!empty($item->ct_rent_cost_text)) : ?>
                              <div><strong>Rent:</strong> <?php echo esc_html($item->ct_rent_cost_text); ?></div>
                            <?php endif; ?>

                            <?php if (!empty($item->ct_own_cost_text)) : ?>
                              <div><strong>Own:</strong> <?php echo esc_html($item->ct_own_cost_text); ?></div>
                            <?php endif; ?>
                          </td>

                          <td>
                            <div class="badge-list">
                              <?php if (!empty($item->ct_cost_tier)) : ?>
                                <span class="equipment-badge"><?php echo esc_html($item->ct_cost_tier); ?></span>
                              <?php endif; ?>

                              <?php if ((int) $item->ct_is_rare === 1) : ?>
                                <span class="equipment-badge">Rare</span>
                              <?php endif; ?>

                              <?php if ((int) $item->ct_is_proscribed === 1) : ?>
                                <span class="equipment-badge">Proscribed</span>
                              <?php endif; ?>
                            </div>
                          </td>

                          <td>
                            <?php if (!empty($item->ct_book_name) && !empty($item->book_slug)) : ?>
                              <a href="<?php echo esc_url(home_url('/book/' . $item->book_slug . '/')); ?>">
                                <?php echo esc_html($item->ct_book_name); ?>
                              </a>
                              <?php if (!empty($item->ct_pg_no)) : ?>
                                (p. <?php echo intval($item->ct_pg_no); ?>)
                              <?php endif; ?>
                            <?php elseif (!empty($item->ct_book_name)) : ?>
                              <?php echo esc_html($item->ct_book_name); ?>
                              <?php if (!empty($item->ct_pg_no)) : ?>
                                (p. <?php echo intval($item->ct_pg_no); ?>)
                              <?php endif; ?>
                            <?php else : ?>
                              <span class="muted">—</span>
                            <?php endif; ?>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            <?php endforeach; ?>
          </section>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>
  <?php endif; ?>

  <?php if (($show_equipment_section || $show_spells_section) && $show_weapons_section) : ?>
    <hr class="section-divider">
  <?php endif; ?>

  <?php if ($show_weapons_section) : ?>
    <section class="equipment-section">
      <h2>Weapons by Type</h2>

      <?php if (empty($grouped_weapons)) : ?>
        <p>No weapons matched your current filters.</p>
      <?php else : ?>
        <?php foreach ($grouped_weapons as $weapon_class => $rows) : ?>
          <section class="weapon-group">
            <h3><?php echo esc_html(loc_eq_display_value($weapon_class)); ?></h3>

            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Equip</th>
                    <th>Range</th>
                    <th>Attack</th>
                    <th>Effect</th>
                    <th>Descriptors</th>
                    <th>Cost</th>
                    <th>Weight</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($rows as $weapon) : ?>
                    <tr>
                      <td>
                        <?php if (!empty($weapon->weapon_slug)) : ?>
                          <?php
                          $weapon_url = ((int) $weapon->weapon_is_species_weapon === 1)
                            ? home_url('/species-weapons/' . $weapon->weapon_slug . '/')
                            : home_url('/equipment/' . $weapon->weapon_slug . '/');
                          ?>
                          <a href="<?php echo esc_url($weapon_url); ?>">
                            <?php echo esc_html($weapon->weapon_name); ?>
                          </a>
                        <?php else : ?>
                          <?php echo esc_html($weapon->weapon_name); ?>
                        <?php endif; ?>
                      </td>

                      <td>
                        <?php echo $weapon->weapon_equip !== '' ? esc_html($weapon->weapon_equip) : '<span class="muted">—</span>'; ?>
                      </td>

                      <td>
                        <?php echo $weapon->weapon_range_band !== '' ? esc_html($weapon->weapon_range_band) : '<span class="muted">—</span>'; ?>
                      </td>

                      <td>
                        <?php echo $weapon->weapon_attack_dice !== '' ? esc_html($weapon->weapon_attack_dice) : '<span class="muted">—</span>'; ?>
                      </td>

                      <td>
                        <?php echo $weapon->weapon_effect !== '' ? esc_html($weapon->weapon_effect) : '<span class="muted">—</span>'; ?>
                      </td>

                      <td>
                        <?php echo $weapon->weapon_descriptors !== '' ? esc_html($weapon->weapon_descriptors) : '<span class="muted">—</span>'; ?>
                      </td>

                      <td>
                        <?php if ($weapon->weapon_cost_tier !== '') : ?>
                          <?php echo esc_html($weapon->weapon_cost_tier); ?>
                        <?php else : ?>
                          <span class="muted">—</span>
                        <?php endif; ?>
                      </td>

                      <td>
                        <?php
                        if ($weapon->weapon_weight_numeric !== null && $weapon->weapon_weight_numeric !== '') {
                          echo esc_html(rtrim(rtrim((string) $weapon->weapon_weight_numeric, '0'), '.')) . ' stone';
                        } else {
                          echo '<span class="muted">—</span>';
                        }
                        ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </section>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>
  <?php endif; ?>

  <?php if ($show_spells_section) : ?>
    <?php if ($show_equipment_section || $show_weapons_section) : ?>
      <hr class="section-divider">
    <?php endif; ?>

    <section class="equipment-section">
      <h2>Spells</h2>

      <?php if (empty($grouped_spells)) : ?>
        <p>No spells matched your current filters.</p>
      <?php else : ?>
        <?php foreach ($grouped_spells as $gift_name => $spell_group_rows) : ?>
          <section class="weapon-group">
            <h3><?php echo esc_html($gift_name); ?></h3>

            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Equip</th>
                    <th>Range</th>
                    <th>Attack Dice</th>
                    <th>Effect</th>
                    <th>Descriptors</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($spell_group_rows as $spell) : ?>
                    <tr>
                      <td><?php echo esc_html($spell->ct_name); ?></td>
                      <td><?php echo !empty($spell->ct_equip) ? esc_html($spell->ct_equip) : '<span class="muted">—</span>'; ?></td>
                      <td><?php echo !empty($spell->ct_range) ? esc_html($spell->ct_range) : '<span class="muted">—</span>'; ?></td>
                      <td><?php echo !empty($spell->ct_attack_dice) ? esc_html($spell->ct_attack_dice) : '<span class="muted">—</span>'; ?></td>
                      <td><?php echo !empty($spell->ct_effect) ? nl2br(esc_html($spell->ct_effect)) : '<span class="muted">—</span>'; ?></td>
                      <td>
                        <?php if (!empty($spell->ct_descriptors)) : ?>
                          <div class="badge-list">
                            <?php foreach (array_filter(array_map('trim', explode(',', $spell->ct_descriptors))) as $d) : ?>
                              <span class="equipment-badge"><?php echo esc_html($d); ?></span>
                            <?php endforeach; ?>
                          </div>
                        <?php else : ?>
                          <span class="muted">—</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </section>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>
  <?php endif; ?>
</main>

<?php get_footer(); ?>