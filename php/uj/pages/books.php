<?php
$pageTitle = 'Books';
$activeNav = 'books';
require __DIR__ . '/../layout-head.php';

$p = cg_prefix();
$books = cg_query("SELECT * FROM `{$p}uj_books` WHERE published=1 ORDER BY sort_order");
?>

<div class="page-header">
  <h1>Urban Jungle Books</h1>
  <p>Sourcebooks and supplements for Urban Jungle.</p>
</div>

<div style="display:flex; flex-direction:column; gap:1.5rem;">
<?php foreach ($books as $book): ?>
  <div style="background:var(--uj-surface); border:1px solid var(--uj-border-cool); border-radius:var(--uj-radius-lg);
              display:flex; gap:1.5rem; padding:1.5rem; align-items:flex-start;">

    <?php if ($book['cover_url']): ?>
      <a href="/uj/books/<?= htmlspecialchars($book['slug']) ?>" style="flex-shrink:0;">
        <img src="<?= htmlspecialchars($book['cover_url']) ?>"
             alt="<?= htmlspecialchars($book['name']) ?> cover"
             style="width:120px; border-radius:4px; box-shadow:0 4px 16px rgba(0,0,0,0.5); display:block;">
      </a>
    <?php endif; ?>

    <div style="flex:1; min-width:0;">
      <p style="font-family:'Cinzel',serif; font-size:1.1rem; font-weight:700; color:var(--uj-teal-light); letter-spacing:0.04em; margin:0 0 0.5rem;">
        <a href="/uj/books/<?= htmlspecialchars($book['slug']) ?>" style="color:inherit; text-decoration:none;"><?= htmlspecialchars($book['name']) ?></a>
      </p>

      <?php if ($book['blurb']): ?>
        <?php $blurb = explode("\n", $book['blurb'])[0]; ?>
        <p style="font-size:0.95rem; color:var(--uj-text-muted); line-height:1.65; margin:0 0 1rem;">
          <?= htmlspecialchars($blurb) ?>
        </p>
      <?php endif; ?>

      <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
        <a href="/uj/books/<?= htmlspecialchars($book['slug']) ?>"
           style="display:inline-flex; align-items:center; gap:0.4rem;
                  background:rgba(75,191,216,0.06); border:1px solid rgba(75,191,216,0.2);
                  color:var(--uj-text-muted); border-radius:var(--uj-radius);
                  font-family:'Cinzel',serif; font-size:0.72rem; font-weight:700;
                  letter-spacing:0.06em; text-transform:uppercase; padding:0.45rem 1rem;
                  text-decoration:none;">
          View Details →
        </a>
        <?php if ($book['buy_url']): ?>
          <a href="<?= htmlspecialchars($book['buy_url']) ?>" target="_blank" rel="noopener"
             style="display:inline-flex; align-items:center; gap:0.4rem;
                    background:rgba(75,191,216,0.12); border:1px solid rgba(75,191,216,0.3);
                    color:var(--uj-teal); border-radius:var(--uj-radius);
                    font-family:'Cinzel',serif; font-size:0.72rem; font-weight:700;
                    letter-spacing:0.06em; text-transform:uppercase; padding:0.45rem 1rem;
                    text-decoration:none;">
            Buy on DriveThruRPG →
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>

<?php require __DIR__ . '/../layout-foot.php'; ?>
