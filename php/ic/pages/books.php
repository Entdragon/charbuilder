<?php
$pageTitle = 'Books';
$activeNav = 'books';
require __DIR__ . '/../layout-head.php';

$p = cg_prefix();
$books = cg_query("SELECT * FROM `{$p}customtables_table_books` WHERE published=1 ORDER BY id");
?>

<div class="page-header">
  <div class="header-row">
    <h1>Ironclaw Books</h1>
    <?php if ($isAdmin): ?><a href="/admin?pane=ic-books&amp;action=new" class="admin-edit-btn">+ Add New</a><?php endif; ?>
  </div>
  <p>The full Ironclaw library — sourcebooks, supplements, and adventures.</p>
</div>

<div style="display:flex; flex-direction:column; gap:1.5rem;">
<?php foreach ($books as $book):
  $name    = $book['ct_book_name'];
  $desc    = $book['ct_book_abstract'] ?? '';
  $url     = $book['ct_url_to_buy']   ?? '';
  $cover   = $book['ct_cover_image']  ?? '';
  $slug    = $book['ct_ct_slug']      ?? '';

  // The raw DB abstract repeats the same paragraph 3×; strip everything from the second occurrence
  if ($desc && strlen($desc) > 120) {
    $marker = substr($desc, 0, 80);
    $repeat = strpos($desc, $marker, 80);
    if ($repeat !== false) {
      $desc = trim(substr($desc, 0, $repeat));
    }
    // Also trim trailing ellipsis or whitespace
    $desc = rtrim($desc, ". \t\n\r");
  }
?>
  <div style="background:var(--ic-surface); border:1px solid var(--ic-border-warm); border-radius:var(--ic-radius-lg);
              display:flex; gap:1.5rem; padding:1.5rem; align-items:flex-start;">

    <?php if ($cover): ?>
      <a href="<?= htmlspecialchars($url) ?>" target="_blank" rel="noopener" style="flex-shrink:0;">
        <img src="<?= htmlspecialchars($cover) ?>"
             alt="<?= htmlspecialchars($name) ?> cover"
             style="width:120px; border-radius:4px; box-shadow:0 4px 16px rgba(0,0,0,0.5); display:block;">
      </a>
    <?php endif; ?>

    <div style="flex:1; min-width:0;">
      <p style="font-family:'Cinzel',serif; font-size:1.15rem; font-weight:700; color:var(--ic-gold-light); letter-spacing:0.04em; margin:0 0 0.5rem;">
        <?= htmlspecialchars($name) ?>
      </p>

      <?php if ($desc): ?>
        <p style="font-size:0.95rem; color:var(--ic-text-muted); line-height:1.65; margin:0 0 1rem;">
          <?= htmlspecialchars($desc) ?>
        </p>
      <?php endif; ?>

      <div style="display:flex; gap:0.75rem; flex-wrap:wrap; align-items:center;">
        <?php if ($slug): ?>
          <a href="/ic/books/<?= htmlspecialchars($slug) ?>"
             style="display:inline-flex; align-items:center; gap:0.4rem;
                    background:rgba(201,168,76,0.06); border:1px solid rgba(201,168,76,0.2);
                    color:var(--ic-text-muted); border-radius:var(--ic-radius);
                    font-family:'Cinzel',serif; font-size:0.72rem; font-weight:700;
                    letter-spacing:0.06em; text-transform:uppercase; padding:0.45rem 1rem;
                    text-decoration:none; transition:background 0.2s;">
            View Contents →
          </a>
        <?php endif; ?>
        <?php if ($url): ?>
          <a href="<?= htmlspecialchars($url) ?>" target="_blank" rel="noopener"
             style="display:inline-flex; align-items:center; gap:0.4rem;
                    background:rgba(201,168,76,0.12); border:1px solid rgba(201,168,76,0.3);
                    color:var(--ic-gold); border-radius:var(--ic-radius);
                    font-family:'Cinzel',serif; font-size:0.72rem; font-weight:700;
                    letter-spacing:0.06em; text-transform:uppercase; padding:0.45rem 1rem;
                    text-decoration:none; transition:background 0.2s;">
            Buy on DriveThruRPG →
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>

<?php require __DIR__ . '/../layout-foot.php'; ?>
