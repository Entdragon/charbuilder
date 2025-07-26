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
      type.ct_careertype_name, type.ct_slug AS type_slug,
      arch.ct_archtype_name, arch.ct_slug AS archtype_slug,

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

  if ( $career ) :
  ?>


    <!-- Type and Archetype -->
    <div style="margin-bottom: 20px;">
      <h2>Classification</h2>
      <div class="skill-grid">
          <div class="skill-card">
            </a>
          </div>
          <div class="skill-card">
            </a>
          </div>
      </div>
    </div>

    <!-- Skills -->
    <h2>Career Skills</h2>
    <div class="skill-grid" style="margin-bottom: 20px;">
        if ( $career->$s ) :
          $slug = $s . '_slug'; ?>
          <div class="skill-card">
            </a>
          </div>
    </div>

    <!-- Gifts -->
<h2>Career Gifts</h2>
<ul class="skill-grid" style="gap: 20px; list-style: none; padding: 0; margin-bottom: 20px;">
    <li class="skill-card">
      </a>
    </li>

    <li class="skill-card">
      </a>
    </li>

    <li class="skill-card">
      </a>
    </li>
</ul>


    <!-- Description -->
    <h2>Description</h2>

    <!-- Trappings -->
    <h2>Trappings</h2>

    <!-- Requirements -->
      <h2>Requirements</h2>

<h2>Source</h2>
  <div class="skill-grid" style="margin-bottom: 20px;">
    <div class="skill-card">
      </a>
    </div>
  </div>

<div class="skill-grid" style="margin-top: 40px;">
  <div class="skill-card">
    <a href="/careers/">
      ← Back to Careers
    </a>
  </div>
</div>


   

    <p>Career not found.</p>
</main>


} // namespace CharacterGeneratorDev
