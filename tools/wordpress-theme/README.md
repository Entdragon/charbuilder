# WordPress Child Theme Files

This directory holds the canonical source for all WordPress child theme customisations for `libraryofcalbria.com`. Files are version-controlled here and copied manually to the live server — Replit cannot deploy to the WordPress server directly.

## Structure

```
tools/wordpress-theme/
  functions/          ← PHP snippets that go into functions.php (or as separate includes)
  templates/          ← Custom page templates (page-*.php)
  css/                ← Theme CSS overrides
  js/                 ← Theme JS additions
  README.md           ← This file
```

As files are added for specific features (requirements display fix, admin editor, etc.), they will be placed in the appropriate subdirectory with a comment at the top indicating where on the server each file should be placed.

## How to update the live site

### When Replit has changes to copy to the server

1. In Replit, edit the file(s) in `tools/wordpress-theme/`
2. `git add`, `git commit`, `git push`
3. On your local machine (or via cPanel File Manager / SFTP): copy the changed file(s) to the correct path in your child theme directory (`/public_html/wp-content/themes/your-child-theme/`)
4. Test on the live site

### When you edit files directly on the server

1. Copy the changed file(s) back to `tools/wordpress-theme/` in Replit
2. `git add`, `git commit`, `git push`

**Replit is the source of truth.** Always commit server-side edits back here so the repo stays current.

## Child theme location on the server

```
/public_html/wp-content/themes/<child-theme-name>/
```

If you are unsure of the child theme directory name, check **WordPress Admin → Appearance → Themes** — the active child theme name is shown there.

## Files currently tracked

| File in this repo | Goes to on server | Purpose |
|---|---|---|
| *(none yet — files will be added as features are built)* | | |
