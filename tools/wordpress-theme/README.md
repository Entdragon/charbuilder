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
| `snippets/requirements-literacy-merge.php` | Add functions to `functions.php` | Merges duplicate literacy requirement lines at render time (PHP, no JS) |
| `page-data-editor.php` | `page-data-editor.php` (root of child theme) | Page template: admin-only front-end data editor |
| `snippets/data-editor-ajax.php` | Add functions to `functions.php` | Three AJAX endpoints that power the data editor |
| `css/data-editor.css` | `css/data-editor.css` | Styles for the data editor page |
| `js/data-editor.js` | `js/data-editor.js` | Client-side JS for the data editor |
| `snippets/detail-page-routing.php` | Add functions to `functions.php` | Registers `slug` query var + rewrite rules for `/gift/`, `/career/`, `/species/` detail pages |
| `gift-detail.php` | `gift-detail.php` (root of child theme) | Page template: gift detail view |
| `career-detail.php` | `career-detail.php` (root of child theme) | Page template: career detail view |
| `species-detail.php` | `species-detail.php` (root of child theme) | Page template: species detail view |

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

This happens because the `gift_requirements` table has a generic "Literacy" gift_ref row **and** `ct_gifts_requires_special` has the language-specific pair "Literacy: Zhongwén". CustomTables renders both, producing two lines.

### Solution

A `the_content` PHP filter (`loc_merge_literacy_requirements`) runs at priority 20 — after CustomTables shortcodes have fully rendered (priority 11). It parses the rendered HTML with DOMDocument and merges the two sibling elements into one:

- Finds every leaf element whose text is exactly `"Literacy"`.
- Searches that element's siblings (within the same parent) for `"Literacy: X"`, `"Must be literate in X"`, or `"Literate in X"`.
- If a sibling match exists: updates the generic element to `"Literacy: X"` and removes the sibling.
- Only runs on pages whose URL path contains `/gifts/`.
- No JavaScript; works entirely server-side.

### Installation

1. Open `wp-content/themes/<child-theme>/functions.php`.
2. Paste the entire contents of `snippets/requirements-literacy-merge.php` at the bottom.
3. Save and upload (cPanel File Manager or SFTP).

### Verification checklist

Test these three cases after installing:

| Page / gift type | Expected result |
|---|---|
| Gift with "Literacy" + "Literacy: Zhongwén" | Single line: "Literacy: Zhongwén" |
| Gift with only generic "Literacy" (no language) | Line unchanged: "Literacy" |
| Gift with non-literacy requirements | All other requirements unchanged |

### Adjusting the URL scope

By default the filter only runs when the page URL contains `/gifts/`. If your gift detail pages live under a different path (e.g. `/gift/` singular or `/catalogue/`), update the `$uri` check in `loc_merge_literacy_requirements`:

```php
if ( stripos( $uri, '/your-path/' ) === false ) {
    return $content;
}
```

### How the DOMDocument merge works

The merge targets **siblings within the same parent container** — so it cannot accidentally remove content from unrelated sections. The XPath query `//*[not(child::*) and normalize-space(text())="Literacy"]` restricts to pure-text leaf elements, excluding headings or labels that happen to contain the word "Literacy" alongside other markup.

**Safe-failure guarantee:** If a gift has "Literacy" and "Literacy: Zhongwén" in *different* parent elements (non-siblings), the sibling search finds no match and neither element is altered. The worst case is the merge does nothing for that gift — the original two-line display is preserved, no content is removed.

**HTML normalisation note:** DOMDocument re-serialises the full `the_content` block on gift pages. In practice this is harmless for CT-generated HTML, but if gift content ever includes inline `<script>` tags or tightly formatted markup, spot-check the rendered output after installation.

---

## Task 11 — Admin-only front-end data editor

### What it is

A WordPress page template that lets the site admin view and edit CustomTables records directly from the front end — no need to go into the CT plugin backend. Invisible to all non-admin visitors (protected by `current_user_can('manage_options')`).

**Editable tables:** Gifts, Careers, Species, Skills, Equipment, Books

**For Gifts only:** the editor also shows and saves child rows from `gift_sections` and `gift_requirements`.

**Out of scope (edit-only):** creating new records, deleting records, bulk editing.

### Files

| File | Purpose |
|---|---|
| `page-data-editor.php` | Page template: admin check, enqueues CSS/JS, renders HTML shell |
| `snippets/data-editor-ajax.php` | Three `wp_ajax_` endpoints (list, get, save) |
| `css/data-editor.css` | Editor styles (WordPress admin aesthetic) |
| `js/data-editor.js` | Client-side editor logic (jQuery) |

### Installation steps

1. **Upload files** to the child theme directory:
   ```
   wp-content/themes/<child-theme>/page-data-editor.php
   wp-content/themes/<child-theme>/css/data-editor.css
   wp-content/themes/<child-theme>/js/data-editor.js
   ```

2. **Add AJAX handlers** — paste the entire contents of `snippets/data-editor-ajax.php` into the child theme's `functions.php`.

3. **Create the WordPress page:**
   - WordPress Admin → Pages → Add New
   - Title: "Data Editor" (or anything you prefer)
   - Permalink: e.g. `/data-editor/` (keep it obscure)
   - Page Template: select **"Data Editor"** from the template dropdown
   - Publish

4. **Test** — visit the page while logged in as admin. You should see the table selector. While logged out (or as a non-admin), visiting the page shows "Access denied."

### Security model

- All three AJAX endpoints are registered as `wp_ajax_` only (never `wp_ajax_nopriv_`) — unauthenticated requests are rejected by WordPress before reaching the code.
- Every AJAX request is verified with `check_ajax_referer('loc_data_editor', 'nonce')`.
- Every AJAX request checks `current_user_can('manage_options')`.
- Table names are validated against a hardcoded whitelist (`LOC_DE_TABLES`); no user-supplied table name reaches a query.
- Column names in UPDATE are validated against `INFORMATION_SCHEMA.COLUMNS`; no user-supplied column name reaches a query.
- All values go through `$wpdb->update()` which uses prepared statements.
- For child row updates, ownership is verified before each UPDATE: the child row's foreign key must match the parent record's `ct_id` (IDOR protection).

### AJAX endpoints

| Action | What it does |
|---|---|
| `loc_de_list` | Returns paginated + searchable record list (preview columns only) |
| `loc_de_get` | Returns all columns for one record + child rows + column metadata |
| `loc_de_save` | UPDATE main record + any submitted child rows |

### Adding a new editable table

Edit the `LOC_DE_TABLES` constant in `data-editor-ajax.php` (and the matching `cfg.tables` object in `page-data-editor.php`) to add more table slugs. The editor is column-driven — no other code changes needed for the main table fields.

### Adding child table support for other tables

Edit the `LOC_DE_CHILD_TABLES` constant in `data-editor-ajax.php`. Add a key matching the table slug, with each child table's `table` name (unprefixed), `fk` (foreign key column), and `editable` column list.

---

## Task — Gift / Career / Species detail page routing

### Problem

All three detail page templates (`gift-detail.php`, `career-detail.php`, `species-detail.php`) read the record slug via `get_query_var('slug')`. WordPress does not know what `slug` is until it is registered, and it has no rewrite rules to route `/gift/agility/` to the gift detail page — so the query always returns empty and the page shows "not found".

### Solution

`snippets/detail-page-routing.php` adds three things to WordPress:

1. **Registers `slug`** as a recognised query variable so `get_query_var('slug')` works.
2. **Adds rewrite rules** so `/gift/{slug}/`, `/career/{slug}/`, `/species/{slug}/` are routed to the corresponding WordPress page with the slug appended as a query var.
3. **Prevents false 404s** — stops WordPress's own 404 logic from firing when a valid slug is present.

### Prerequisites — WordPress pages

Before the routing can work you need three WordPress pages with the right slugs and page templates:

| WordPress page slug | Page template to assign | Example URL |
|---|---|---|
| `gift` | Gift Detail Page | `/gift/agility/` |
| `career` | Career Detail Page | `/career/soldier/` |
| `species` | Species Detail Page | `/species/valka/` |

The page slug is the last segment of the page's permalink. If any of your slugs differ (e.g. `gifts` plural), update the constants at the top of `detail-page-routing.php`:

```php
define( 'LOC_GIFT_BASE',    'gift' );    // change to match your page slug
define( 'LOC_CAREER_BASE',  'career' );
define( 'LOC_SPECIES_BASE', 'species' );
```

### Installation steps

1. **Upload the three template files** to the root of your child theme directory:
   ```
   wp-content/themes/twentytwentyfour-child/gift-detail.php
   wp-content/themes/twentytwentyfour-child/career-detail.php
   wp-content/themes/twentytwentyfour-child/species-detail.php
   ```

2. **Create the three WordPress pages** (if they don't exist yet):
   - WordPress Admin → Pages → Add New
   - Set the slug and assign the matching page template (Gift Detail Page / Career Detail Page / Species Detail Page)
   - Publish

3. **Add the routing code to functions.php** — paste the entire contents of `snippets/detail-page-routing.php` at the bottom of your child theme's `functions.php`.

4. **Flush permalinks** — go to WordPress Admin → Settings → Permalinks and click **Save Changes**. This is mandatory; the rewrite rules do not take effect until the cache is flushed.

5. **Test** — visit `/gift/some-gift-slug/` (use an actual slug from the `DcVnchxg4_customtables_table_gifts` table's `ct_slug` column). You should see the gift detail page render with full data.

### Troubleshooting

| Symptom | Likely cause |
|---|---|
| Page still shows "not found" | Permalinks not flushed — go to Settings → Permalinks and Save Changes |
| Blank page / PHP error | Template file not uploaded, or PHP version mismatch |
| "That gift couldn't be found" | Slug in URL does not match any `ct_slug` value in the database, or the page template constant (`LOC_GIFT_BASE`) does not match the WordPress page slug |
| URL returns the wrong page | Rewrite rule `'top'` priority is being overridden — check for conflicting rewrite rules from other plugins |
