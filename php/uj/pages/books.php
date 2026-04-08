<?php
$pageTitle = 'Books';
$activeNav = 'books';
require __DIR__ . '/../layout-head.php';
?>

<div class="page-header">
  <h1>Urban Jungle Books</h1>
  <p>Sourcebooks, supplements, and adventures for Urban Jungle.</p>
</div>

<div style="background:var(--uj-surface); border:1px solid var(--uj-border-cool); border-radius:var(--uj-radius-lg); padding:2rem; text-align:center;">
  <p style="color:var(--uj-text-muted); font-size:1rem; font-style:italic; margin:0 0 1rem;">
    Urban Jungle books data is coming soon. Check back for sourcebook listings, covers, and buy links.
  </p>
  <a href="https://www.drivethrurpg.com/browse?keywords=urban+jungle+sanguine" target="_blank" rel="noopener"
     style="display:inline-block; background:rgba(75,191,216,0.1); border:1px solid rgba(75,191,216,0.3);
            color:var(--uj-teal); border-radius:var(--uj-radius); font-family:'Cinzel',serif;
            font-size:0.72rem; font-weight:700; letter-spacing:0.06em; text-transform:uppercase;
            padding:0.5rem 1.25rem; text-decoration:none;">
    Browse on DriveThruRPG →
  </a>
</div>

<?php require __DIR__ . '/../layout-foot.php'; ?>
