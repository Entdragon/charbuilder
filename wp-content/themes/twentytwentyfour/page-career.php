<?php

namespace CharacterGeneratorDev {

/**
 * Template Name: Career Detail Page
 */
get_header();
?>

<main class="site-main" style="padding: 20px;">
  global $wpdb;
  $slug = sanitize_text_field(get_query_var('slug'));

  $career = $wpdb->get_row($wpdb->prepare("
    SELECT 
      careers.*,
      type.ct_careertype_name,
      arch.ct_archtype_name,

      skill1.ct_skill_name AS skill_one,
      skill1.ct_slug AS skill_one_slug,
      skill2.ct_skill_name AS skill_two,
      skill2.ct_slug AS skill_two_slug,
      skill3.ct_skill_name AS skill_three,
      skill3.ct_slug AS skill_three_slug,

      gift1.ct_gifts_name AS gift_one,
      gift1.ct_slug AS gift_one_slug,
      gift2.ct_gifts_name AS gift_two,
      gift2.ct_slug AS gift_two_slug,
      gift3.ct_gifts_name AS gift_three,
      gift3.ct_slug AS gift_three_slug,

      book.ct_book_name,
      book.ct_ct_slug AS book_slug
    FROM DcVnchxg4_customtables_table_careers AS careers
    LEFT JOIN DcVnchxg4_customtables_table_careertype AS type ON careers.ct_career_type = type.ct_id
    LEFT JOIN DcVnchxg4_customtables_table_archtype AS arch ON careers.ct_career_archtype = arch.ct_id

    LEFT JOIN DcVnchxg4_customtables_table_skills AS skill1 ON careers.ct_career_skill_one = skill1.ct_id
    LEFT JOIN DcVnchxg4_customtables_table_skills AS skill2 ON careers.ct_career_skill_two = skill2.ct_id
    LEFT JOIN DcVnchxg4_customtables_table_skills AS skill3 ON careers.ct_career_skill_three = skill3.ct_id

    LEFT JOIN DcVnchxg4_customtables_table_gifts AS gift1 ON careers.ct_career_gift_one = gift1.ct_id
    LEFT JOIN DcVnchxg4_customtables_table_gifts AS gift2 ON careers.ct_career_gift_two = gift2.ct_id
    LEFT JOIN DcVnchxg4_customtables_table_gifts AS gift3 ON careers.ct_career_gift_three = gift3.ct_id

    LEFT JOIN DcVnchxg4_customtables_table_books AS book ON careers.ct_career_source_book = book.ct_id
    WHERE careers.ct_slug = %s
  ", $slug));

  if ( $career ) {
      echo '<h1>' . esc_html($career->ct_career_name) . '</h1>';

      // Type and Archetype
      echo '<div style="display: flex; gap: 40px; margin-bottom: 20px;">';
      echo '<div><strong>Type:</strong><br>' . esc_html($career->ct_careertype_name) . '</div>';
      echo '<div><strong>Archetype:</strong><br>' . esc_html($career->ct_archtype_name) . '</div>';
      echo '</div>';

      // Skills (as links)
      echo '<h2>Career Skills</h2>';
      echo '<ul style="display: flex; gap: 30px; list-style: none; padding: 0;">';
      echo '<li><a href="/skill/' . esc_attr($career->skill_one_slug) . '/">' . esc_html($career->skill_one) . '</a></li>';
      echo '<li><a href="/skill/' . esc_attr($career->skill_two_slug) . '/">' . esc_html($career->skill_two) . '</a></li>';
      echo '<li><a href="/skill/' . esc_attr($career->skill_three_slug) . '/">' . esc_html($career->skill_three) . '</a></li>';
      echo '</ul>';

      // Gifts (as links, in a row)
      echo '<h2>Career Gifts</h2>';
      echo '<ul style="display: flex; gap: 30px; list-style: none; padding: 0;">';

      if ($career->gift_one) {
        echo '<li><a href="/gift/' . esc_attr($career->gift_one_slug) . '/"><strong>' . esc_html($career->gift_one) . '</strong></a>';
        if ($career->ct_career_gift_one_choice) {
            echo '<br><small>' . esc_html($career->ct_career_gift_one_choice) . '</small>';
        }
        echo '</li>';
      }

      if ($career->gift_two) {
        echo '<li><a href="/gift/' . esc_attr($career->gift_two_slug) . '/"><strong>' . esc_html($career->gift_two) . '</strong></a>';
        if ($career->ct_career_gift_two_choice) {
            echo '<br><small>' . esc_html($career->ct_career_gift_two_choice) . '</small>';
        }
        echo '</li>';
      }

      if ($career->gift_three) {
        echo '<li><a href="/gift/' . esc_attr($career->gift_three_slug) . '/"><strong>' . esc_html($career->gift_three) . '</strong></a>';
        if ($career->ct_career_gift_three_choice) {
            echo '<br><small>' . esc_html($career->ct_career_gift_three_choice) . '</small>';
        }
        echo '</li>';
      }

      echo '</ul>';

      // Description & Trappings
      echo '<h2>Description</h2>';
      echo '<p>' . nl2br(esc_html($career->ct_career_description)) . '</p>';

      echo '<h2>Trappings</h2>';
      echo '<p>' . nl2br(esc_html($career->ct_career_trappings)) . '</p>';

      if ($career->ct_requires) {
        echo '<h2>Requirements</h2>';
        echo '<p>' . esc_html($career->ct_requires) . '</p>';
      }

      // Source book
      echo '<h2>Source</h2>';
      if ($career->ct_book_name) {
        echo '<p><a href="/book/' . esc_attr($career->book_slug) . '/" target="_blank" rel="noopener noreferrer">'
          . esc_html($career->ct_book_name) . '</a>, Pg. No. ' . intval($career->ct_pg_no) . '</p>';
      }

      echo '<p><a href="/careers/">← Back to Career Catalog</a></p>';
  } else {
      echo '<p>Career not found.</p>';
  }
  ?>
</main>


} // namespace CharacterGeneratorDev
