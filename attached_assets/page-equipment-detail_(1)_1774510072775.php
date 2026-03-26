<?php
/**
 * Template Name: Equipment Detail Page
 *
 * Detail renderer for /equipment/{slug}/
 * Resolves first against DcVnchxg4_customtables_table_equipment,
 * then falls back to DcVnchxg4_customtables_table_weapons.
 *
 * This lets manufactured weapons share the main equipment detail route.
 */

get_header();

/* -----------------------------
 * Helpers
 * ----------------------------- */
if ( ! function_exists( 'cg_eq_norm_text' ) ) {
    function cg_eq_norm_text( $t ) {
        $t = is_string( $t ) ? $t : '';
        $t = str_replace( array( "\r\n", "\r" ), "\n", $t );
        return trim( $t );
    }
}

if ( ! function_exists( 'cg_eq_table_exists' ) ) {
    function cg_eq_table_exists( $wpdb, $table_name ) {
        $found = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );
        return $found === $table_name;
    }
}

if ( ! function_exists( 'cg_eq_column_exists' ) ) {
    function cg_eq_column_exists( $wpdb, $table_name, $column_name ) {
        $sql = $wpdb->prepare(
            "SHOW COLUMNS FROM `{$table_name}` LIKE %s",
            $column_name
        );
        $row = $wpdb->get_row( $sql );
        return ! empty( $row );
    }
}

if ( ! function_exists( 'cg_eq_badge' ) ) {
    function cg_eq_badge( $text, $extra_class = '' ) {
        if ( $text === '' || $text === null ) {
            return '';
        }
        $class = 'equipment-badge';
        if ( $extra_class !== '' ) {
            $class .= ' ' . $extra_class;
        }
        return '<span class="' . esc_attr( $class ) . '">' . esc_html( $text ) . '</span>';
    }
}

if ( ! function_exists( 'cg_eq_fact_row' ) ) {
    function cg_eq_fact_row( $label, $value ) {
        $value = cg_eq_norm_text( (string) $value );
        if ( $value === '' ) {
            return '';
        }
        return '<div><strong>' . esc_html( $label ) . '</strong> ' . esc_html( $value ) . '</div>';
    }
}

if ( ! function_exists( 'cg_eq_obj_val' ) ) {
    function cg_eq_obj_val( $obj, $key, $default = '' ) {
        if ( ! is_object( $obj ) ) {
            return $default;
        }
        return isset( $obj->$key ) ? $obj->$key : $default;
    }
}

if ( ! function_exists( 'cg_eq_weapon_cost_display' ) ) {
    function cg_eq_weapon_cost_display( $weapon ) {
        $cost_text = cg_eq_norm_text( (string) cg_eq_obj_val( $weapon, 'ct_cost_text', '' ) );
        if ( $cost_text !== '' ) {
            return $cost_text;
        }

        $cost_d = cg_eq_norm_text( (string) cg_eq_obj_val( $weapon, 'ct_cost_d', '' ) );
        if ( $cost_d !== '' ) {
            return $cost_d . 'D';
        }

        $price_tier = cg_eq_norm_text( (string) cg_eq_obj_val( $weapon, 'ct_price_tier', '' ) );
        if ( $price_tier !== '' ) {
            return $price_tier;
        }

        $cost_tier = cg_eq_norm_text( (string) cg_eq_obj_val( $weapon, 'ct_cost_tier', '' ) );
        if ( $cost_tier !== '' ) {
            return $cost_tier;
        }

        return '';
    }
}

if ( ! function_exists( 'cg_eq_weapon_weight_display' ) ) {
    function cg_eq_weapon_weight_display( $weapon ) {
        $weight_text = cg_eq_norm_text( (string) cg_eq_obj_val( $weapon, 'ct_weight_text', '' ) );
        if ( $weight_text !== '' ) {
            return $weight_text;
        }

        $weight_stone = cg_eq_norm_text( (string) cg_eq_obj_val( $weapon, 'ct_weight_stone', '' ) );
        if ( $weight_stone !== '' ) {
            return $weight_stone . ' Stone';
        }

        return '';
    }
}

if ( ! function_exists( 'cg_eq_book_link_html' ) ) {
    function cg_eq_book_link_html( $row ) {
        $book_name = cg_eq_norm_text( (string) cg_eq_obj_val( $row, 'ct_book_name', '' ) );
        $book_slug = cg_eq_norm_text( (string) cg_eq_obj_val( $row, 'book_slug', '' ) );
        $pg_no     = cg_eq_norm_text( (string) cg_eq_obj_val( $row, 'ct_pg_no', '' ) );

        if ( $book_name === '' ) {
            return '';
        }

        ob_start();
        if ( $book_slug !== '' ) {
            ?>
            <a href="<?php echo esc_url( home_url( '/book/' . $book_slug . '/' ) ); ?>">
                <?php echo esc_html( $book_name ); ?>
            </a>
            <?php
        } else {
            echo esc_html( $book_name );
        }

        if ( $pg_no !== '' ) {
            echo ' — p. ' . intval( $pg_no );
        }

        return trim( ob_get_clean() );
    }
}
?>

<main class="site-main equipment-detail">
<?php
global $wpdb;

/* -----------------------------
 * Table names
 * ----------------------------- */
$equipment_table = 'DcVnchxg4_customtables_table_equipment';
$weapons_table   = 'DcVnchxg4_customtables_table_weapons';
$books_table     = 'DcVnchxg4_customtables_table_books';

/* -----------------------------
 * Slug lookup
 * ----------------------------- */
$slug = sanitize_text_field( get_query_var( 'equipment_slug' ) );

if ( $slug === '' && get_query_var( 'slug' ) ) {
    $slug = sanitize_text_field( get_query_var( 'slug' ) );
}

if ( $slug === '' && isset( $_GET['equipment_slug'] ) ) {
    $slug = sanitize_text_field( wp_unslash( $_GET['equipment_slug'] ) );
}

if ( $slug === '' && isset( $_GET['slug'] ) ) {
    $slug = sanitize_text_field( wp_unslash( $_GET['slug'] ) );
}

/* -----------------------------
 * Load equipment first
 * ----------------------------- */
$item_kind = '';
$item      = null;

$equipment = $wpdb->get_row( $wpdb->prepare( "
    SELECT
        e.*,
        b.ct_book_name,
        b.ct_ct_slug AS book_slug
    FROM {$equipment_table} e
    LEFT JOIN {$books_table} b
        ON b.ct_id = e.ct_source_book
    WHERE e.ct_slug = %s
      AND e.published = 1
    LIMIT 1
", $slug ) );

if ( $equipment ) {
    $item_kind = 'equipment';
    $item      = $equipment;
}

/* -----------------------------
 * Fall back to weapons table
 * ----------------------------- */
if ( ! $item && cg_eq_table_exists( $wpdb, $weapons_table ) ) {
    $weapon_name_col = '';
    if ( cg_eq_column_exists( $wpdb, $weapons_table, 'ct_name' ) ) {
        $weapon_name_col = 'ct_name';
    } elseif ( cg_eq_column_exists( $wpdb, $weapons_table, 'ct_weapons_name' ) ) {
        $weapon_name_col = 'ct_weapons_name';
    }

    $has_slug_col      = cg_eq_column_exists( $wpdb, $weapons_table, 'ct_slug' );
    $has_published_col = cg_eq_column_exists( $wpdb, $weapons_table, 'published' );
    $has_source_book   = cg_eq_column_exists( $wpdb, $weapons_table, 'ct_source_book' );
    $has_pg_no         = cg_eq_column_exists( $wpdb, $weapons_table, 'ct_pg_no' );

    if ( $weapon_name_col !== '' && $has_slug_col ) {
        $weapon_select = array(
            'w.*',
            "w.{$weapon_name_col} AS display_name",
        );

        if ( $has_source_book && cg_eq_table_exists( $wpdb, $books_table ) ) {
            if ( cg_eq_column_exists( $wpdb, $books_table, 'ct_book_name' ) ) {
                $weapon_select[] = 'b.ct_book_name';
            }
            if ( cg_eq_column_exists( $wpdb, $books_table, 'ct_ct_slug' ) ) {
                $weapon_select[] = 'b.ct_ct_slug AS book_slug';
            }
        }

        if ( ! $has_pg_no ) {
            $weapon_select[] = 'NULL AS ct_pg_no';
        }

        $weapon_join = '';
        if ( $has_source_book && cg_eq_table_exists( $wpdb, $books_table ) && cg_eq_column_exists( $wpdb, $books_table, 'ct_id' ) ) {
            $weapon_join = "LEFT JOIN {$books_table} b ON b.ct_id = w.ct_source_book";
        }

        $weapon_where = array( 'w.ct_slug = %s' );
        if ( $has_published_col ) {
            $weapon_where[] = 'w.published = 1';
        }

        $weapon_sql = "
            SELECT
                " . implode( ",\n                ", $weapon_select ) . "
            FROM {$weapons_table} w
            {$weapon_join}
            WHERE " . implode( ' AND ', $weapon_where ) . "
            LIMIT 1
        ";

        $weapon = $wpdb->get_row( $wpdb->prepare( $weapon_sql, $slug ) );

        if ( $weapon ) {
            $item_kind = 'weapon';
            $item      = $weapon;
        }
    }
}

if ( ! $item ) {
    global $wp_query;
    $wp_query->set_404();
    status_header( 404 );
    nocache_headers();
    echo '<p>Sorry, that equipment item couldn’t be found.</p>';
    get_footer();
    exit;
}

$item_slug = cg_eq_norm_text( (string) cg_eq_obj_val( $item, 'ct_slug', $slug ) );

/* -----------------------------
 * Optional trappings usage lookup
 * ----------------------------- */
$trappings_sources = array();

$trap_table    = 'DcVnchxg4_customtables_table_trappings_map';
$careers_table = 'DcVnchxg4_customtables_table_careers';
$gifts_table   = 'DcVnchxg4_customtables_table_gifts';

if (
    cg_eq_table_exists( $wpdb, $trap_table ) &&
    cg_eq_column_exists( $wpdb, $trap_table, 'ct_item_slug' )
) {
    $has_source_type = cg_eq_column_exists( $wpdb, $trap_table, 'ct_source_type' );
    $has_source_slug = cg_eq_column_exists( $wpdb, $trap_table, 'ct_source_slug' );
    $has_source_name = cg_eq_column_exists( $wpdb, $trap_table, 'ct_source_name' );
    $has_gift_id     = cg_eq_column_exists( $wpdb, $trap_table, 'ct_gift_id' );
    $has_gift_slug   = cg_eq_column_exists( $wpdb, $trap_table, 'ct_gift_slug' );
    $has_gift_name   = cg_eq_column_exists( $wpdb, $trap_table, 'ct_gift_name' );
    $has_career_id   = cg_eq_column_exists( $wpdb, $trap_table, 'ct_career_id' );
    $has_career_slug = cg_eq_column_exists( $wpdb, $trap_table, 'ct_career_slug' );

    if ( $has_source_type && $has_source_slug ) {
        $trappings_sources = $wpdb->get_results( $wpdb->prepare( "
            SELECT DISTINCT
                tm.ct_source_type,
                tm.ct_source_id,
                tm.ct_source_slug,
                tm.ct_source_name,
                tm.ct_gift_id,
                tm.ct_gift_slug,
                tm.ct_gift_name,
                tm.ct_career_id,
                tm.ct_career_slug,
                c.ct_career_name,
                g.ct_gifts_name,
                g.ct_slug AS live_gift_slug
            FROM {$trap_table} tm
            LEFT JOIN {$careers_table} c
                ON tm.ct_source_type = 'career'
               AND tm.ct_source_id = c.ct_id
               AND c.published = 1
            LEFT JOIN {$gifts_table} g
                ON tm.ct_source_type = 'gift'
               AND tm.ct_source_id = g.ct_id
               AND g.published = 1
            WHERE tm.ct_item_slug = %s
            ORDER BY tm.ct_source_type ASC, COALESCE( tm.ct_source_name, c.ct_career_name, g.ct_gifts_name ) ASC
        ", $item_slug ) );
    } elseif ( $has_career_id || $has_career_slug ) {
        $trappings_sources = $wpdb->get_results( $wpdb->prepare( "
            SELECT DISTINCT
                'career' AS ct_source_type,
                tm.ct_career_id AS ct_source_id,
                tm.ct_career_slug AS ct_source_slug,
                c.ct_career_name AS ct_source_name,
                NULL AS ct_gift_id,
                NULL AS ct_gift_slug,
                NULL AS ct_gift_name,
                tm.ct_career_id,
                tm.ct_career_slug,
                c.ct_career_name,
                NULL AS ct_gifts_name,
                NULL AS live_gift_slug
            FROM {$trap_table} tm
            LEFT JOIN {$careers_table} c
                ON tm.ct_career_id = c.ct_id
               AND c.published = 1
            WHERE tm.ct_item_slug = %s
            ORDER BY c.ct_career_name ASC
        ", $item_slug ) );
    }
}

$item_title = ( $item_kind === 'weapon' )
    ? cg_eq_norm_text( (string) cg_eq_obj_val( $item, 'display_name', cg_eq_obj_val( $item, 'ct_name', cg_eq_obj_val( $item, 'ct_weapons_name', $item_slug ) ) ) )
    : cg_eq_norm_text( (string) cg_eq_obj_val( $item, 'ct_name', $item_slug ) );

$source_html = cg_eq_book_link_html( $item );
?>

<style id="cg-equipment-detail-style">
  .equipment-detail,
  .equipment-detail * {
    color: #f2e6c9;
  }

  .equipment-detail .equipment-sep {
    border-color: rgba(240, 195, 90, 0.24);
    margin: 18px 0 24px;
  }

  .equipment-detail .equipment-meta,
  .equipment-detail .equipment-grid,
  .equipment-detail .equipment-source-grid,
  .equipment-detail .equipment-usage-grid {
    display: grid;
    gap: 12px;
  }

  .equipment-detail .equipment-meta {
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    margin-bottom: 16px;
  }

  .equipment-detail .equipment-card,
  .equipment-detail .equipment-panel,
  .equipment-detail .equipment-source-card,
  .equipment-detail .equipment-usage-card {
    border: 1px solid rgba(240, 195, 90, 0.24);
    border-radius: 12px;
    padding: 14px 16px;
    background: rgba(0, 0, 0, 0.12);
  }

  .equipment-detail .equipment-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin: 12px 0 18px;
  }

  .equipment-detail .equipment-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 999px;
    border: 1px solid rgba(240, 195, 90, 0.28);
    color: #f0c35a;
    font-size: 0.92rem;
  }

  .equipment-detail .equipment-badge--warn {
    border-color: rgba(255, 145, 100, 0.4);
    color: #ffb394;
  }

  .equipment-detail .equipment-facts {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 10px 16px;
  }

  .equipment-detail .equipment-facts div strong {
    display: inline-block;
    min-width: 88px;
    color: #f0c35a;
  }

  .equipment-detail .equipment-panel h2,
  .equipment-detail .equipment-card h2,
  .equipment-detail .equipment-source-card h2 {
    margin-top: 0;
  }

  .equipment-detail .equipment-notes {
    white-space: pre-line;
  }

  .equipment-detail .equipment-back {
    margin-top: 36px;
  }

  .equipment-detail a {
    color: #f0c35a;
  }
</style>

<header class="equipment-header">
  <h1 class="equipment-title"><?php echo esc_html( $item_title ); ?></h1>

  <div class="equipment-badges">
    <?php if ( $item_kind === 'equipment' ) : ?>
      <?php echo cg_eq_badge( cg_eq_obj_val( $item, 'ct_category', '' ) ); ?>
      <?php if ( cg_eq_obj_val( $item, 'ct_subcategory', '' ) !== '' ) echo cg_eq_badge( cg_eq_obj_val( $item, 'ct_subcategory', '' ) ); ?>
      <?php echo cg_eq_badge( cg_eq_obj_val( $item, 'ct_item_type', '' ) ); ?>
      <?php if ( cg_eq_obj_val( $item, 'ct_cost_tier', '' ) !== '' ) echo cg_eq_badge( cg_eq_obj_val( $item, 'ct_cost_tier', '' ) ); ?>
      <?php if ( (int) cg_eq_obj_val( $item, 'ct_is_rare', 0 ) === 1 ) echo cg_eq_badge( 'Rare', 'equipment-badge--warn' ); ?>
      <?php if ( (int) cg_eq_obj_val( $item, 'ct_is_proscribed', 0 ) === 1 ) echo cg_eq_badge( 'Proscribed', 'equipment-badge--warn' ); ?>
    <?php else : ?>
      <?php echo cg_eq_badge( 'Weapon' ); ?>
      <?php if ( cg_eq_obj_val( $item, 'ct_weapon_class', '' ) !== '' ) echo cg_eq_badge( cg_eq_obj_val( $item, 'ct_weapon_class', '' ) ); ?>
      <?php if ( cg_eq_obj_val( $item, 'ct_equip', '' ) !== '' ) echo cg_eq_badge( cg_eq_obj_val( $item, 'ct_equip', '' ) ); ?>
      <?php if ( cg_eq_obj_val( $item, 'ct_cost_tier', '' ) !== '' ) echo cg_eq_badge( cg_eq_obj_val( $item, 'ct_cost_tier', '' ) ); ?>
      <?php if ( cg_eq_obj_val( $item, 'ct_price_tier', '' ) !== '' ) echo cg_eq_badge( cg_eq_obj_val( $item, 'ct_price_tier', '' ) ); ?>
      <?php if ( (int) cg_eq_obj_val( $item, 'ct_is_species_weapon', 0 ) === 1 ) echo cg_eq_badge( 'Species Weapon', 'equipment-badge--warn' ); ?>
    <?php endif; ?>
  </div>
</header>

<hr class="equipment-sep">

<section class="equipment-panel">
  <h2>Details</h2>
  <div class="equipment-facts">
    <?php if ( $item_kind === 'equipment' ) : ?>
      <?php echo cg_eq_fact_row( 'Slug', cg_eq_obj_val( $item, 'ct_slug', '' ) ); ?>
      <?php echo cg_eq_fact_row( 'Cost', cg_eq_obj_val( $item, 'ct_cost_text', '' ) ); ?>
      <?php echo cg_eq_fact_row( 'Cost Note', cg_eq_obj_val( $item, 'ct_cost_note', '' ) ); ?>
      <?php echo cg_eq_fact_row( 'Weight', cg_eq_obj_val( $item, 'ct_weight_text', '' ) ); ?>
      <?php echo cg_eq_fact_row( 'Capacity', cg_eq_obj_val( $item, 'ct_capacity_text', '' ) ); ?>
      <?php echo cg_eq_fact_row( 'Rent', cg_eq_obj_val( $item, 'ct_rent_cost_text', '' ) ); ?>
      <?php echo cg_eq_fact_row( 'Own', cg_eq_obj_val( $item, 'ct_own_cost_text', '' ) ); ?>
      <?php echo cg_eq_fact_row( 'Cover', cg_eq_obj_val( $item, 'ct_cover_dice', '' ) ); ?>
      <?php echo cg_eq_fact_row( 'Armor', cg_eq_obj_val( $item, 'ct_armor_dice', '' ) ); ?>
      <?php echo cg_eq_fact_row( 'Skill', cg_eq_obj_val( $item, 'ct_skill_dice', '' ) ); ?>
    <?php else : ?>
      <?php echo cg_eq_fact_row( 'Slug', cg_eq_obj_val( $item, 'ct_slug', '' ) ); ?>
      <?php echo cg_eq_fact_row( 'Weapon Class', cg_eq_obj_val( $item, 'ct_weapon_class', '' ) ); ?>
      <?php echo cg_eq_fact_row( 'Equip', cg_eq_obj_val( $item, 'ct_equip', '' ) ); ?>
      <?php echo cg_eq_fact_row( 'Range', cg_eq_obj_val( $item, 'ct_range_band', '' ) ); ?>
      <?php echo cg_eq_fact_row( 'Attack', cg_eq_obj_val( $item, 'ct_attack_dice', '' ) ); ?>
      <?php echo cg_eq_fact_row( 'Cost', cg_eq_weapon_cost_display( $item ) ); ?>
      <?php echo cg_eq_fact_row( 'Weight', cg_eq_weapon_weight_display( $item ) ); ?>
      <?php echo cg_eq_fact_row( 'Price Tier', cg_eq_obj_val( $item, 'ct_price_tier', '' ) ); ?>
      <?php echo cg_eq_fact_row( 'Cost Tier', cg_eq_obj_val( $item, 'ct_cost_tier', '' ) ); ?>
    <?php endif; ?>
  </div>
</section>

<?php
$effect_text = cg_eq_norm_text( (string) cg_eq_obj_val( $item, 'ct_effect', '' ) );
if ( $effect_text !== '' ) :
?>
  <section class="equipment-panel">
    <h2>Effect</h2>
    <p><?php echo esc_html( $effect_text ); ?></p>
  </section>
<?php endif; ?>

<?php
$notes_text = '';
if ( $item_kind === 'equipment' ) {
    $notes_text = cg_eq_norm_text( (string) cg_eq_obj_val( $item, 'ct_notes', '' ) );
} else {
    $notes_text = cg_eq_norm_text( (string) cg_eq_obj_val( $item, 'ct_notes', '' ) );
}
if ( $notes_text !== '' ) :
?>
  <section class="equipment-panel">
    <h2>Notes</h2>
    <div class="equipment-notes"><?php echo esc_html( $notes_text ); ?></div>
  </section>
<?php endif; ?>

<?php
if ( $item_kind === 'weapon' ) {
    $desc_text = cg_eq_norm_text( (string) cg_eq_obj_val( $item, 'ct_descriptors', '' ) );
    if ( $desc_text !== '' ) :
?>
  <section class="equipment-panel">
    <h2>Descriptors</h2>
    <div class="equipment-notes"><?php echo esc_html( $desc_text ); ?></div>
  </section>
<?php
    endif;
}
?>

<?php if ( $source_html !== '' ) : ?>
  <section class="equipment-panel">
    <h2>Source</h2>
    <div class="equipment-source-grid">
      <div class="equipment-source-card">
        <p><?php echo wp_kses_post( $source_html ); ?></p>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php if ( ! empty( $trappings_sources ) ) : ?>
  <section class="equipment-panel">
    <h2>Appears in Trappings</h2>
    <div class="equipment-usage-grid">
      <?php foreach ( $trappings_sources as $src ) : ?>
        <?php
        $source_type = cg_eq_norm_text( $src->ct_source_type ?? '' );
        $source_name = cg_eq_norm_text( $src->ct_source_name ?? '' );
        $source_slug = cg_eq_norm_text( $src->ct_source_slug ?? '' );

        if ( $source_type === 'career' ) {
            $source_name = $source_name !== '' ? $source_name : cg_eq_norm_text( $src->ct_career_name ?? '' );
            $source_slug = $source_slug !== '' ? $source_slug : cg_eq_norm_text( $src->ct_career_slug ?? '' );
        } elseif ( $source_type === 'gift' ) {
            $source_name = $source_name !== '' ? $source_name : cg_eq_norm_text( $src->ct_gifts_name ?? '' );
            $source_slug = $source_slug !== '' ? $source_slug : cg_eq_norm_text( $src->live_gift_slug ?? '' );
        }
        ?>
        <div class="equipment-usage-card">
          <p>
            <strong><?php echo esc_html( ucfirst( $source_type ) ); ?>:</strong>
            <?php if ( $source_slug !== '' ) : ?>
              <?php $base = ( $source_type === 'gift' ) ? '/gift/' : '/career/'; ?>
              <a href="<?php echo esc_url( home_url( $base . $source_slug . '/' ) ); ?>">
                <?php echo esc_html( $source_name ); ?>
              </a>
            <?php else : ?>
              <?php echo esc_html( $source_name !== '' ? $source_name : '(unknown source)' ); ?>
            <?php endif; ?>
          </p>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
<?php endif; ?>

<div class="equipment-back">
  <div class="equipment-card">
    <a href="<?php echo esc_url( home_url( '/equipment/' ) ); ?>">← Back to Equipment</a>
  </div>
</div>

</main>

<?php get_footer(); ?>