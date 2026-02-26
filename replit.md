# Character Generator – Standalone Node.js App

## Project Overview
A standalone Express.js web application converted from a WordPress plugin. Provides a character creation system for the Library of Calbria tabletop RPG. Connects to the existing WordPress MySQL database via a PHP proxy (shared hosting firewall blocks direct MySQL access from outside).

- **Staging URL**: https://stage.libraryofcalbria.com/character-generator/
- **Production URL**: https://libraryofcalbria.com/character-generator/

## Technology Stack
- **Node.js / Express** – Web server and REST API (port 5000)
- **mysql2** – MySQL client (direct mode, fallback if proxy not set)
- **express-session** – Session-based authentication
- **bcryptjs** – WordPress 6+ bcrypt password verification
- **phpass** – Legacy WordPress phpass (`$P$`) password verification
- **JavaScript (ES6)** – Frontend UI modules (esbuild bundle)
- **SCSS (Dart Sass)** – Compiled to CSS
- **jQuery** – Required by the frontend bundle (served from node_modules)

## Project Structure
```
server/
  index.js                  # Express app entry point, port 5000
  db.js                     # Database layer: proxy mode or direct MySQL
  auth/
    wordpress.js            # WP password verification (bcrypt + phpass) + hashing
  routes/
    ajax.js                 # POST /api/ajax dispatcher
    actions/
      auth.js               # Login, logout, register
      character.js          # Load, get, save characters
      career.js             # Career list and profiles
      gifts.js              # Gifts (free, local knowledge, language)
      skills.js             # Skills list and detail
      species.js            # Species list and profiles
      diagnostics.js        # Ping and diagnostics
public/
  index.html                # App shell: login UI + character builder
assets/
  js/src/                   # ES6 source (unchanged from plugin)
  js/dist/core.bundle.js    # Bundled JS (esbuild)
  css/src/                  # SCSS source
  css/dist/core.css         # Compiled CSS
tools/
  cg-db-proxy.php           # PHP proxy deployed to WordPress server
includes/                   # Original PHP plugin (reference only)
character-generator.php     # Original plugin entry (reference only)
```

## Environment Secrets
Set in Replit Secrets:
- `CG_PROXY_URL` — Full URL to `cg-db-proxy.php` on the WordPress server
- `CG_PROXY_SECRET` — Shared secret for authenticating proxy requests
- `SESSION_SECRET` — Session signing secret
- `DB_PREFIX` — WordPress table prefix (defaults to `wp_` if not set)

Direct MySQL fallback (only if proxy not used):
- `DB_HOST` — MySQL host (supports `host:port`)
- `DB_NAME` — WordPress database name
- `DB_USER` — MySQL username
- `DB_PASS` — MySQL password

## API Routes
- `GET /` → Serves `public/index.html` (login + app shell)
- `GET /setup` → Password reset form (protected by `CG_PROXY_SECRET`)
- `GET /api/auth/me` → Returns current logged-in user info
- `POST /api/ajax` → Main action dispatcher (mirrors WordPress admin-ajax.php)
  - Auth: `cg_login_user`, `cg_logout_user`, `cg_register_user`
  - Characters: `cg_load_characters`, `cg_get_character`, `cg_save_character`
  - Reference data: `cg_get_career_list`, `cg_get_career_gifts`, `cg_get_species_list`, `cg_get_species_profile`, `cg_get_skills_list`, `cg_get_skill_detail`, `cg_get_free_gifts`, `cg_get_local_knowledge`, `cg_get_language_gift`
  - Diagnostics: `cg_ping`, `cg_run_diagnostics`
- `POST /api/admin/reset-password` → Resets a WP user's password (requires `CG_PROXY_SECRET` in body)

## Password Handling
WordPress 6+ stores passwords as `$wp$2y$10$...` (bcrypt with a `$wp$` prefix). The server handles:
- `$wp$...` → strip prefix, convert `$2y$` → `$2b$`, verify with bcryptjs
- `$P$` / `$H$` → legacy phpass, verify with phpass library
- `$2y$` / `$2b$` → plain bcrypt

New passwords are stored in `$wp$` format for WordPress compatibility.

## PHP Proxy
`tools/cg-db-proxy.php` must be deployed to the WordPress server (e.g. `stage.libraryofcalbria.com/cg-db-proxy.php`). It:
- Reads `X-CG-Secret` header and validates against a hard-coded secret
- Accepts `{ sql, params }` POST body
- Runs the query against the WordPress database and returns JSON

## Build System
- `npm run build` – Rebuild JS bundle and CSS (after source changes)
- `npm run watch:core` / `npm run watch:css` – Watch modes for development

## Replit Workflow
- Command: `node server/index.js`
- Port: 5000 (webview)
