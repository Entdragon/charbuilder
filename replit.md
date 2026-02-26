# Character Generator – Standalone Node.js App

## Project Overview
A standalone Express.js web application converted from a WordPress plugin. Provides a character creation system for the Library of Calbria tabletop RPG. Connects directly to the existing WordPress MySQL database.

- **Staging URL**: https://stage.libraryofcalbria.com/character-generator/
- **Production URL**: https://libraryofcalbria.com/character-generator/

## Technology Stack
- **Node.js / Express** – Web server and REST API (port 5000)
- **mysql2** – MySQL database client
- **express-session** – Session-based authentication
- **phpass** – WordPress-compatible password verification
- **JavaScript (ES6)** – Frontend UI modules (esbuild bundle)
- **SCSS (Dart Sass)** – Compiled to CSS
- **jQuery** – Required by the frontend bundle (served from node_modules)

## Project Structure
```
server/
  index.js                  # Express app entry point, port 5000
  db.js                     # MySQL connection pool (env secrets)
  auth/
    wordpress.js            # WordPress phpass password verification
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
includes/                   # Original PHP plugin (reference only)
character-generator.php     # Original plugin entry (reference only)
```

## Environment Secrets Required
Set in Replit Secrets:
- `DB_HOST` — MySQL host (supports host:port format)
- `DB_NAME` — WordPress database name
- `DB_USER` — MySQL username
- `DB_PASS` — MySQL password
- `DB_PREFIX` — Table prefix (env var, defaults to `wp_`)
- `SESSION_SECRET` — Session signing secret

## API Routes
- `GET /` → Serves `public/index.html` (login + app shell)
- `GET /api/auth/me` → Returns current logged-in user
- `POST /api/ajax` → Main action dispatcher (mirrors WordPress admin-ajax.php)
  - Auth: `cg_login_user`, `cg_logout_user`, `cg_register_user`
  - Characters: `cg_load_characters`, `cg_get_character`, `cg_save_character`
  - Reference data: `cg_get_career_list`, `cg_get_career_gifts`, `cg_get_species_list`, `cg_get_species_profile`, `cg_get_skills_list`, `cg_get_skill_detail`, `cg_get_free_gifts`, `cg_get_local_knowledge`, `cg_get_language_gift`
  - Diagnostics: `cg_ping`, `cg_run_diagnostics`

## Build System
- `npm run build` – Rebuild JS bundle and CSS (after source changes)
- `npm run watch:core` / `npm run watch:css` – Watch modes for development

## Database Notes
The app connects to the existing WordPress MySQL database. The DB tables used are:
- `{prefix}users` / `{prefix}usermeta` — WordPress user accounts
- `{prefix}character_records` — Plugin-managed character saves
- `{prefix}customtables_table_species/careers/gifts/skills/habitat/diet/cycle/senses/weapons` — Reference data tables

**Important**: If the WordPress database is on a shared hosting server with `DB_HOST=localhost`, it may only be accessible from that server. A remote MySQL grant or SSH tunnel may be needed to connect from Replit.

## Replit Workflow
- Command: `node server/index.js`
- Port: 5000 (webview)
