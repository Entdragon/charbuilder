<?php
/**
 * Simple blog post admin.
 * Accessible at /blog-admin — requires logged-in admin session.
 */
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

// Require login
$user = cg_current_user();
if (!$user) {
    header('Location: /?login=1');
    exit;
}

$p = cg_prefix();
$action  = $_POST['action'] ?? '';
$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'save') {
        $id       = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $title    = trim($_POST['title'] ?? '');
        $body     = trim($_POST['body']  ?? '');
        $excerpt  = trim($_POST['excerpt'] ?? '');
        $publish  = isset($_POST['is_published']) ? 1 : 0;

        if ($title === '') { $message = 'Title is required.'; goto show; }

        // Auto-generate slug from title
        $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($title));
        $slug = trim($slug, '-');
        if (!$excerpt && $body) {
            $excerpt = mb_substr(strip_tags($body), 0, 300);
        }

        if ($id > 0) {
            cg_exec("UPDATE `{$p}loc_blog_posts` SET title=?, slug=?, body=?, excerpt=?, is_published=?,
                     published_at = CASE WHEN ? = 1 AND published_at IS NULL THEN NOW() ELSE published_at END,
                     updated_at=NOW() WHERE id=?",
                [$title, $slug, $body, $excerpt, $publish, $publish, $id]);
            $message = 'Post updated.';
        } else {
            cg_exec("INSERT INTO `{$p}loc_blog_posts` (title, slug, body, excerpt, is_published, published_at)
                     VALUES (?, ?, ?, ?, ?, ?)",
                [$title, $slug, $body, $excerpt, $publish, $publish ? date('Y-m-d H:i:s') : null]);
            $message = 'Post created.';
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            cg_exec("DELETE FROM `{$p}loc_blog_posts` WHERE id=?", [$id]);
            $message = 'Post deleted.';
        }
    }
}

// Load posts
$posts  = cg_query("SELECT * FROM `{$p}loc_blog_posts` ORDER BY created_at DESC");

// Load single post for editing
$editing = null;
if (isset($_GET['edit'])) {
    $editId  = (int)$_GET['edit'];
    $editing = cg_query_one("SELECT * FROM `{$p}loc_blog_posts` WHERE id=?", [$editId]);
}

show:
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Blog Admin — Library of Calabria</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Crimson+Pro:ital,wght@0,400;0,600;1,400&display=swap">
  <style>
    *, *::before, *::after { box-sizing: border-box; }
    :root {
      --gold: #c9a84c; --gold-light: #e5c97a; --bg: #1a1714; --surface: #242019;
      --surface-2: #2d2820; --border: rgba(201,168,76,0.2); --text: #e8dcc4;
      --text-muted: #9a8a6a; --text-dim: #5a4f3a; --radius: 6px;
    }
    html, body { margin: 0; font-family:'Crimson Pro',Georgia,serif; background:var(--bg); color:var(--text); font-size:16px; }
    a { color:var(--gold); text-decoration:none; }
    .wrapper { max-width:860px; margin:0 auto; padding:2rem; }
    .page-title { font-family:'Cinzel',serif; font-size:1.4rem; font-weight:700; color:var(--gold-light); margin:0 0 0.25rem; }
    .breadcrumb { font-size:0.85rem; color:var(--text-dim); margin-bottom:1.5rem; }
    .breadcrumb a { color:var(--gold-dark, #a8822a); }
    .msg { background:rgba(93,191,144,0.1); border:1px solid rgba(93,191,144,0.3); color:#5dbf90;
           border-radius:var(--radius); padding:0.6rem 1rem; margin-bottom:1rem; font-size:0.9rem; }
    .card { background:var(--surface); border:1px solid rgba(154,138,106,0.2); border-radius:8px; padding:1.5rem; margin-bottom:1rem; }
    label { display:block; font-family:'Cinzel',serif; font-size:0.7rem; font-weight:700; letter-spacing:0.1em;
            text-transform:uppercase; color:var(--text-dim); margin:0 0 0.35rem; }
    input[type=text], textarea {
      width:100%; background:var(--surface-2); border:1px solid var(--border);
      border-radius:var(--radius); padding:0.6rem 0.85rem; color:var(--text);
      font-family:'Crimson Pro',Georgia,serif; font-size:1rem; outline:none;
    }
    input[type=text]:focus, textarea:focus { border-color:var(--gold); }
    textarea { min-height:200px; resize:vertical; }
    .form-row { margin-bottom:1rem; }
    .btn { display:inline-block; padding:0.6rem 1.5rem; border:none; border-radius:var(--radius);
           font-family:'Cinzel',serif; font-size:0.78rem; font-weight:700; letter-spacing:0.06em;
           text-transform:uppercase; cursor:pointer; transition:opacity 0.2s; }
    .btn-primary { background:var(--gold); color:var(--bg); }
    .btn-danger  { background:rgba(217,83,79,0.15); color:#d9534f; border:1px solid rgba(217,83,79,0.3); }
    .btn:hover   { opacity:0.85; }
    .check-row   { display:flex; align-items:center; gap:0.5rem; margin-bottom:1rem; }
    .check-row label { margin:0; font-size:0.82rem; letter-spacing:0.04em; color:var(--text-muted); }
    table { width:100%; border-collapse:collapse; font-size:0.92rem; }
    th { background:var(--surface-2); color:var(--gold); font-family:'Cinzel',serif; font-size:0.7rem;
         font-weight:700; letter-spacing:0.08em; text-transform:uppercase; padding:0.6rem 0.9rem;
         text-align:left; border-bottom:2px solid rgba(201,168,76,0.2); }
    td { padding:0.55rem 0.9rem; border-bottom:1px solid rgba(201,168,76,0.08); vertical-align:top; }
    tr:hover td { background:rgba(201,168,76,0.03); }
    .badge { display:inline-block; padding:2px 8px; border-radius:10px; font-size:0.72rem; font-family:'Cinzel',serif; }
    .badge-pub { background:rgba(93,191,144,0.12); color:#5dbf90; border:1px solid rgba(93,191,144,0.2); }
    .badge-draft { background:rgba(90,79,58,0.4); color:var(--text-dim); border:1px solid rgba(90,79,58,0.5); }
    .actions { display:flex; gap:0.4rem; align-items:center; }
  </style>
</head>
<body>
<div class="wrapper">
  <p class="breadcrumb"><a href="/">← Library of Calabria</a></p>
  <h1 class="page-title">Blog Admin</h1>

  <?php if ($message): ?><div class="msg"><?= htmlspecialchars($message) ?></div><?php endif; ?>

  <!-- ── New / Edit form ─────────────────────────────────────────────── -->
  <div class="card">
    <h2 style="font-family:'Cinzel',serif; font-size:0.9rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:var(--gold); margin:0 0 1.25rem;">
      <?= $editing ? 'Edit Post' : 'New Post' ?>
    </h2>
    <form method="post">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= $editing ? (int)$editing['id'] : 0 ?>">

      <div class="form-row">
        <label>Title *</label>
        <input type="text" name="title" value="<?= htmlspecialchars($editing['title'] ?? '') ?>" required>
      </div>

      <div class="form-row">
        <label>Excerpt (shown on landing page)</label>
        <input type="text" name="excerpt" value="<?= htmlspecialchars($editing['excerpt'] ?? '') ?>" placeholder="Short summary — leave blank to auto-generate from body">
      </div>

      <div class="form-row">
        <label>Body</label>
        <textarea name="body"><?= htmlspecialchars($editing['body'] ?? '') ?></textarea>
      </div>

      <div class="check-row">
        <input type="checkbox" name="is_published" id="is_published" value="1"
               <?= !empty($editing['is_published']) ? 'checked' : '' ?>>
        <label for="is_published">Published</label>
      </div>

      <button class="btn btn-primary" type="submit">
        <?= $editing ? 'Update Post' : 'Create Post' ?>
      </button>
      <?php if ($editing): ?>
        <a href="/blog-admin" style="margin-left:0.75rem; color:var(--text-muted); font-size:0.9rem;">Cancel</a>
      <?php endif; ?>
    </form>
  </div>

  <!-- ── Posts list ──────────────────────────────────────────────────── -->
  <?php if ($posts): ?>
  <div class="card" style="padding:0; overflow:hidden;">
    <table>
      <thead>
        <tr>
          <th>Title</th>
          <th style="width:80px;">Status</th>
          <th style="width:130px;">Date</th>
          <th style="width:120px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($posts as $post): ?>
        <tr>
          <td style="font-family:'Cinzel',serif; font-size:0.88rem; color:var(--gold-light);">
            <?= htmlspecialchars($post['title']) ?>
          </td>
          <td>
            <?php if ($post['is_published']): ?>
              <span class="badge badge-pub">Live</span>
            <?php else: ?>
              <span class="badge badge-draft">Draft</span>
            <?php endif; ?>
          </td>
          <td style="font-size:0.85rem; color:var(--text-dim);">
            <?= $post['published_at'] ? date('j M Y', strtotime($post['published_at'])) : '—' ?>
          </td>
          <td>
            <div class="actions">
              <a href="/blog-admin?edit=<?= (int)$post['id'] ?>" style="font-size:0.82rem; color:var(--gold-dark,#a8822a);">Edit</a>
              <form method="post" style="display:inline;" onsubmit="return confirm('Delete this post?');">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$post['id'] ?>">
                <button type="submit" class="btn btn-danger" style="padding:0.2rem 0.6rem; font-size:0.7rem;">Delete</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

</div>
</body>
</html>
