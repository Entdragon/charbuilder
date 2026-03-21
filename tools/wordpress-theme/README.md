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
| `snippets/requirements-literacy-merge.php` | Copy snippet into `functions.php` | Enqueues the requirements-merge JS on gift detail pages |
| `js/gift-requirements-merge.js` | `js/gift-requirements-merge.js` | Merges duplicate literacy requirement lines in the DOM |

---

## Task 10 — Requirements display: literacy merge (gift pages)

### Problem

Gift detail pages sometimes show two separate lines for a literacy requirement:

```
Literacy
Literacy: Zhongwén
```

These should display as one merged line:

```
Literacy: Zhongwén
```

This happens because the structured `gift_requirements` table has a generic "Literacy" gift_ref row, and `ct_gifts_requires_special` has the language-specific pair "Literacy: Zhongwén" — both are rendered by the CustomTables layout.

### Solution

A JavaScript post-processor (`js/gift-requirements-merge.js`) runs on DOM ready and merges the two items into one. It is enqueued only on gift detail pages via a `functions.php` hook.

### Installation steps

1. Copy `js/gift-requirements-merge.js` to the child theme:
   ```
   wp-content/themes/<child-theme>/js/gift-requirements-merge.js
   ```

2. Open the child theme's `functions.php` and paste the contents of
   `snippets/requirements-literacy-merge.php` at the bottom of the file.

3. Visit a gift detail page that has a Literacy: [language] requirement (e.g. a Zhongwén-literacy gift).

4. Open the browser console (F12 → Console). Look for `[LOC-req-merge]` log lines:
   - "Generic literacy item found: Literacy" — the plain item was detected
   - "Specific literacy item found: Literacy: Zhongwén" — the language item was detected
   - "Merging → 'Literacy: Zhongwén'" — the merge succeeded
   - If you see "No items matched REQUIREMENT_ITEM_SELECTOR" — update the
     `REQUIREMENT_ITEM_SELECTOR` constant inside the JS file to match your CT layout HTML.
     Inspect the page source to find what element wraps each requirement line.

5. Once the merge is confirmed working, set `DEBUG = false` inside the JS file and re-upload it.

6. Commit the final JS back to this repo (`git add`, `git commit`, `git push`).

### Adjusting the selector

If step 4 shows "No items matched", inspect your gift page HTML. Each requirement line will be in some element — common patterns:

| CT layout style | Selector to try |
|---|---|
| Table layout | `'td'` |
| Unordered list | `'li'` |
| CustomTables default | `'.ct-field-value'` or `'.customtables-record td'` |
| Div-based | `'.ct_record div'` |

Set `REQUIREMENT_ITEM_SELECTOR` to the right value and refresh to test.

### URL detection (which pages load the script)

By default the script loads on any page whose URL contains `/gifts/`. If your gift pages use a different URL pattern (e.g. `/gift/` singular, or a page slug like `/catalogue/`), update the `$load_on_this_page` condition in the `loc_enqueue_requirements_merge` function inside `functions.php`.
