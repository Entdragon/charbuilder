<?php
/**
 * Template Name: Career Detail Page
 *
 * Updated for Migration 002: skills and gifts now come from junction tables
 * (customtables_table_career_skills and customtables_table_career_gifts)
 * rather than direct FK columns on the careers table.
 */
get_header();
?>

<main class="site-main" style="padding: 20px;">
  <?php
  global $wpdb;
  $p = 'DcVnchxg4_';

  $career_slug = sanitize_text_field( get_query_var( 'slug' ) );

  $career = $wpdb->get_row( $wpdb->prepare( "
    SELECT
      careers.*,
      type.ct_careertype_name,  type.ct_slug  AS type_slug,
      arch.ct_archtype_name,    arch.ct_slug  AS archtype_slug,

      sk1.ct_skill_name AS skill_one,   sk1.ct_slug AS skill_one_slug,
      sk2.ct_skill_name AS skill_two,   sk2.ct_slug AS skill_two_slug,
      sk3.ct_skill_name AS skill_three, sk3.ct_slug AS skill_three_slug,

      g1.ct_gifts_name AS gift_one,   g1.ct_slug AS gift_one_slug,
      g2.ct_gifts_name AS gift_two,   g2.ct_slug AS gift_two_slug,
      g3.ct_gifts_name AS gift_three, g3.ct_slug AS gift_three_slug,

      book.ct_book_name, book.ct_ct_slug AS book_slug

    FROM {$p}customtables_table_careers AS careers

    LEFT JOIN {$p}customtables_table_careertype AS type
           ON careers.ct_career_type = type.ct_id
    LEFT JOIN {$p}customtables_table_archtype AS arch
           ON careers.ct_career_archtype = arch.ct_id

    LEFT JOIN {$p}customtables_table_career_skills AS cs1
           ON cs1.career_id = careers.ct_id AND cs1.sort = 1
    LEFT JOIN {$p}customtables_table_career_skills AS cs2
           ON cs2.career_id = careers.ct_id AND cs2.sort = 2
    LEFT JOIN {$p}customtables_table_career_skills AS cs3
           ON cs3.career_id = careers.ct_id AND cs3.sort = 3
    LEFT JOIN {$p}customtables_table_skills AS sk1 ON sk1.id = cs1.skill_id
    LEFT JOIN {$p}customtables_table_skills AS sk2 ON sk2.id = cs2.skill_id
    LEFT JOIN {$p}customtables_table_skills AS sk3 ON sk3.id = cs3.skill_id

    LEFT JOIN {$p}customtables_table_career_gifts AS cg1
           ON cg1.career_id = careers.ct_id AND cg1.sort = 1
    LEFT JOIN {$p}customtables_table_career_gifts AS cg2
           ON cg2.career_id = careers.ct_id AND cg2.sort = 2
    LEFT JOIN {$p}customtables_table_career_gifts AS cg3
           ON cg3.career_id = careers.ct_id AND cg3.sort = 3
    LEFT JOIN {$p}customtables_table_gifts AS g1 ON g1.ct_id = cg1.gift_id
    LEFT JOIN {$p}customtables_table_gifts AS g2 ON g2.ct_id = cg2.gift_id
    LEFT JOIN {$p}customtables_table_gifts AS g3 ON g3.ct_id = cg3.gift_id

    LEFT JOIN {$p}customtables_table_books AS book
           ON careers.ct_career_source_book = book.ct_id

    WHERE careers.ct_slug = %s
  ", $career_slug ) );

  if ( $career ) :

    $normalize_choice = static function ( $value ) {
      $value = is_string( $value ) ? trim( $value ) : '';
      return preg_replace( '/\s+/', ' ', $value );
    };

    $is_gift_display_override = static function ( $choice ) use ( $normalize_choice ) {
      $override_choices = [ "Cleric's Trappings: Any" ];
      return in_array( $normalize_choice( $choice ), $override_choices, true );
    };

    $render_gift_card = static function ( $career_obj, $gift_field, $gift_slug_field, $choice_field )
      use ( $normalize_choice, $is_gift_display_override )
    {
      $gift_name = isset( $career_obj->$gift_field )      ? trim( (string) $career_obj->$gift_field )      : '';
      $gift_slug = isset( $career_obj->$gift_slug_field ) ? trim( (string) $career_obj->$gift_slug_field ) : '';
      $choice    = isset( $career_obj->$choice_field )    ? $normalize_choice( $career_obj->$choice_field ) : '';

      if ( $gift_name === '' && $choice === '' ) {
        return;
      }

      echo '<li class="skill-card">';

      if ( $is_gift_display_override( $choice ) ) {
        echo '<span>' . esc_html( $choice ) . '</span>';
      } elseif ( $gift_name !== '' && $gift_slug !== '' ) {
        echo '<a href="/gift/' . esc_attr( $gift_slug ) . '/">' . esc_html( $gift_name ) . '</a>';
        if ( $choice !== '' ) {
          echo '<br><small>' . esc_html( $choice ) . '</small>';
        }
      } elseif ( $choice !== '' ) {
        echo '<span>' . esc_html( $choice ) . '</span>';
      }

      echo '</li>';
    };
  ?>

    <h1><?php echo esc_html( $career->ct_career_name ); ?></h1>

    <!-- Type and Archetype -->
    <div style="margin-bottom: 20px;">
      <h2>Classification</h2>
      <div class="skill-grid">
        <?php if ( $career->ct_careertype_name ) : ?>
          <div class="skill-card">
            <a href="/career-type/<?php echo esc_attr( $career->type_slug ); ?>/">
              <?php echo esc_html( $career->ct_careertype_name ); ?>
            </a>
          </div>
        <?php endif; ?>

        <?php if ( $career->ct_archtype_name ) : ?>
          <div class="skill-card">
            <a href="/career-archtype/<?php echo esc_attr( $career->archtype_slug ); ?>/">
              <?php echo esc_html( $career->ct_archtype_name ); ?>
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Skills -->
    <h2>Career Skills</h2>
    <div class="skill-grid" style="margin-bottom: 20px;">
      <?php foreach ( [ 'skill_one', 'skill_two', 'skill_three' ] as $skill_field ) : ?>
        <?php if ( ! empty( $career->$skill_field ) ) : ?>
          <?php $skill_slug_field = $skill_field . '_slug'; ?>
          <div class="skill-card">
            <a href="/skill/<?php echo esc_attr( $career->$skill_slug_field ); ?>/">
              <?php echo esc_html( $career->$skill_field ); ?>
            </a>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>

    <!-- Gifts -->
    <h2>Career Gifts</h2>
    <ul class="skill-grid" style="gap: 20px; list-style: none; padding: 0; margin-bottom: 20px;">
      <?php
      $render_gift_card( $career, 'gift_one',   'gift_one_slug',   'ct_career_gift_one_choice' );
      $render_gift_card( $career, 'gift_two',   'gift_two_slug',   'ct_career_gift_two_choice' );
      $render_gift_card( $career, 'gift_three', 'gift_three_slug', 'ct_career_gift_three_choice' );
      ?>
    </ul>

    <!-- Description -->
    <h2>Description</h2>
    <p><?php echo nl2br( esc_html( $career->ct_career_description ) ); ?></p>

    <!-- Trappings -->
    <h2>Trappings</h2>
    <p><?php echo nl2br( esc_html( $career->ct_career_trappings ) ); ?></p>

    <!-- Requirements -->
    <?php if ( ! empty( $career->ct_requires ) ) : ?>
      <h2>Requirements</h2>
      <p><?php echo esc_html( $career->ct_requires ); ?></p>
    <?php endif; ?>

    <!-- Source -->
    <h2>Source</h2>
    <?php if ( ! empty( $career->ct_book_name ) ) : ?>
      <div class="skill-grid" style="margin-bottom: 20px;">
        <div class="skill-card">
          <a href="/book/<?php echo esc_attr( $career->book_slug ); ?>/" target="_blank" rel="noopener noreferrer">
            <?php echo esc_html( $career->ct_book_name ); ?>
          </a>
          <?php if ( ! empty( $career->ct_pg_no ) ) : ?>
            <br><small>Pg. No. <?php echo intval( $career->ct_pg_no ); ?></small>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

    <div class="skill-grid" style="margin-top: 40px;">
      <div class="skill-card">
        <a href="/careers/">← Back to Careers</a>
      </div>
    </div>

  <?php else : ?>
    <p>Career not found.</p>
  <?php endif; ?>
</main>

<?php get_footer(); ?>
