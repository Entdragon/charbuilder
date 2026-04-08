# Library of Calabria

## Project Overview
A PHP web application serving the Library of Calabria — the main reference site and character generator for Ironclaw and Urban Jungle tabletop RPGs. Connects to the existing WordPress MySQL database via a PHP proxy. Hosted on cPanel/Apache; Replit is dev-only.

- **Production URL**: https://libraryofcalbria.com/ (consolidating from characters.libraryofcalbria.com)

## URL Structure
| Path | Description |
|---|---|
| `/` | Main landing page — Ironclaw + Urban Jungle tiles + blog |
| `/builder` | Ironclaw character generator |
| `/ic` | Ironclaw reference home |
| `/ic/species`, `/ic/careers`, etc. | Ironclaw reference pages |
| `/ic/books` | Ironclaw books with covers and buy links |
| `/uj` | Urban Jungle reference home |
| `/uj/species`, `/uj/types`, etc. | Urban Jungle reference pages |
| `/uj/books` | Urban Jungle books (placeholder) |
| `/blog-admin` | Blog post management (login required) |
| `/admin` | Database editor |

## Feature Reference
See **`docs/feature-matrix.md`** for a full inventory of:
- Every feature in the main character and ally build flows, with parity status (✅/❌)
- Every PHP backend endpoint, which JS flow calls it, and what it returns
- Known gaps between the ally and main character flows
- JS module map and CSS architecture reference

Read this at the start of each session before planning new features.

## Technology Stack
- **PHP 8.2** – Web server (`php -S` in dev, Apache on live) and all API logic
- **JavaScript (ES6 + esbuild)** – Frontend UI modules (built bundle)
- **SCSS (Dart Sass)** – Compiled to CSS
- **jQuery** – Required by the frontend bundle (CDN in PHP template)

## Project Structure
```
php/
  router.php                # Dev-only: routes requests for `php -S` built-in server
  index.php                 # Main HTML template (auth screen + app shell)
  ajax.php                  # POST /ajax.php dispatcher (mirrors WP admin-ajax.php)
  includes/
    config.php              # DB constants from env vars
    db.php                  # DB layer: proxy mode (Replit) or direct PDO (live)
    auth.php                # Session helpers + WordPress SSO cookie verification
    password.php            # WP password verification (bcrypt + phpass) + hashing
  actions/
    auth.php                # cg_login_user, cg_logout_user, cg_register_user
    character.php           # cg_load_characters, cg_get_character, cg_save_character
    career.php              # cg_get_career_list, cg_get_career_profile, cg_get_career_gifts
    gifts.php               # cg_get_free_gifts, cg_get_local_knowledge, cg_get_language_gift
    skills.php              # cg_get_skills_list, cg_get_skill_detail
    species.php             # cg_get_species_list, cg_get_species_profile
    equipment.php           # cg_get_money_list, cg_get_equipment_catalog, cg_get_career_trappings, cg_get_gift_trappings
    diagnostics.php         # cg_ping, cg_run_diagnostics
    spells.php              # cg_install_spells (seed table), cg_get_spells_for_gifts
assets/
  js/src/                   # ES6 source modules
  js/dist/core.bundle.js    # Bundled JS (esbuild output)
  css/src/                  # SCSS source
  css/dist/core.css         # Compiled CSS
server/                     # Legacy Node.js server (kept as reference; no longer the dev server)
tools/
  cg-db-proxy.php           # PHP proxy deployed to WordPress server for DB access
```

## Environment Secrets
Set in Replit Secrets:
- `CG_PROXY_URL` — Full URL to `cg-db-proxy.php` on the WordPress server
- `CG_PROXY_SECRET` — Shared secret for authenticating proxy requests
- `DB_PREFIX` — WordPress table prefix (`DcVnchxg4_`)

Direct MySQL (used automatically on the live server where firewall is not an issue):
- `DB_HOST` — MySQL host
- `DB_NAME` — WordPress database name
- `DB_USER` — MySQL username
- `DB_PASS` — MySQL password

## Dev vs Live
| | Replit Dev | Live (cPanel) |
|---|---|---|
| Server | `php -S 0.0.0.0:5000 php/router.php` | Apache + mod_php |
| DB access | HTTP proxy → `libraryofcalbria.com/cg-db-proxy.php` | Direct PDO (localhost MySQL) |
| Ajax URL | `/ajax.php` | `/ajax.php` |
| Session | PHP file-based sessions | PHP file-based sessions |

## Build System
- `npm run build` – Rebuild JS bundle + CSS
- `npm run watch:core` / `npm run watch:css` – Watch modes

## Deploy to Live

Production `.htaccess` routes:
- `/builder` → `builder.php`  (not index.php — builder.php is the IC builder page)
- `/` → `home.php`
- `/ic/*` → `ic.php`, `/uj/*` → `uj.php`, `/ajax.php` → `ajax.php`
- `php/router.php` is **Replit-only** — do NOT copy to live

```bash
cd ~/charbuilder && git pull

# PHP pages & backend
cp php/ajax.php   ~/public_html/ajax.php
cp php/home.php   ~/public_html/home.php
cp php/index.php  ~/public_html/builder.php   # NOTE: index.php → builder.php on live
cp -r php/uj php/actions php/includes ~/public_html/

# Frontend assets (only if JS/CSS changed)
cp -r assets/js/dist ~/public_html/assets/js/dist
cp assets/css/dist/core.css ~/public_html/assets/css/dist/core.css
```

## Spells Table
`customtables_table_spells` — 124 rows across these gift groups:
- Common Magic (15 apprentice spells, all magic users)
- Element schools: Journeyman/Master Air, Earth, Fire, Water (12 each tier)
- Journeyman White Magic, Journeyman Cognoscente Magic
- Secret Star Magic of the Dunwasser Academy
- Necromancy
- Druids Apprentice (3), Secrets of Druid Magic (6)
- Blessed Way (13 tree-based magic weapon spells)
- Interdictions × 7 (each spell = its own gift; matched by ct_name)
- Antiñgero Apprentice (2), Pawang Apprentice (2), Dukun Apprentice (2)
- Budjaduya spells × 4 (matched by ct_name), Tazekar's Path (1)
- The Way of Changes (5), Secrets of Changes Magic (10 hexagram spells)
- Taoist Apprentice (5 base element spells)
- Secrets of Fire/Earth/Water/Metal/Wood Taoist Magic (hexagram combos)
- Purity Apprentice (2)

Seed with `action=cg_install_spells` (idempotent truncate + re-insert).

## Replit Workflow
- Command: `php -S 0.0.0.0:5000 php/router.php`
- Port: 5000 (webview)

## CSS Scoping Rule
Any CSS affecting generic HTML elements MUST be scoped under `#cg-modal` to avoid WordPress theme interference (the generator is embedded in a WP page).

## Gift Requirements Schema
- `gift_requirements` table: `ct_req_kind` is either `gift_ref` or `special_text`; `ct_sort` values are multiples of 10 (not sequential 1,2,3…).
- PHP uses **position counters** (not raw sort values) to map `gift_ref` rows to flat columns `ct_gifts_requires`, `ct_gifts_requires_two`, etc.
- All `special_text` rows are concatenated with `\n` into `ct_gifts_requires_special`.
- `gift_prereq` table holds structured prerequisites (trait_min, species_anyof, pair, etc.).

## Character Data – XP Fields
Three columns on `character_records`:
- `experience_points` INT – Total XP
- `xp_skill_marks` TEXT (JSON) – Extra marks bought with XP `{skillId: count}`
- `xp_gifts` TEXT (JSON) – Gifts bought with XP `[{id, name}]`

Costs: 4 XP per skill mark, 10 XP per gift. Marks capped at 3 (d4→d6→d8).

## Password Handling
WordPress 6+ format: `$wp$2y$10$…` (bcrypt with `$wp$` prefix). Also handles legacy `$P$` / `$H$` (phpass) and plain `$2y$`/`$2b$` bcrypt. New passwords stored in `$wp$` format.
