# Character Generator – WordPress Plugin

## Project Overview
This is a WordPress plugin that provides a modular character creation system for tabletop RPGs. It features a dynamic AJAX-driven UI for managing character details, skills, species, careers, and gifts.

- **Staging URL**: https://stage.libraryofcalbria.com/character-generator/
- **Production URL**: https://libraryofcalbria.com/character-generator/

## Technology Stack
- **PHP 8.2** – WordPress plugin backend (AJAX handlers, database, shortcodes)
- **JavaScript (ES6)** – Frontend UI modules bundled with esbuild
- **SCSS (Dart Sass)** – Styles compiled to CSS
- **Node.js / npm** – Build tooling

## Project Structure
```
character-generator/
├── character-generator.php     # Main plugin entry point
├── includes/                   # PHP backend logic
│   ├── index.php
│   ├── shortcode-ui.php        # [character_generator] shortcode HTML
│   ├── enqueue-hardening.php   # Asset loading + AJAX nonce hardening
│   ├── activation.php
│   ├── auth/, career/, character/, gifts/, skills/, species/
│   └── diagnostics/
├── assets/
│   ├── js/src/core/            # ES6 source modules
│   ├── js/dist/                # Bundled JS (esbuild output)
│   ├── css/src/                # SCSS source
│   └── css/dist/               # Compiled CSS
├── dev-preview/                # Replit dev preview (static HTML + Node server)
│   ├── index.html
│   └── server.js
└── docs/                       # Documentation
```

## Build System
- `npm run build` – Build both JS and CSS
- `npm run build:core` – Bundle JS with esbuild
- `npm run build:css` – Compile SCSS with Dart Sass
- `npm run watch:core` / `npm run watch:css` – Watch modes for development

## Replit Setup
- **Workflow**: `node dev-preview/server.js` on port 5000 (webview)
- The dev preview serves a static informational page since the plugin requires WordPress to run fully
- **Deployment**: Configured as static deployment serving `dev-preview/`

## WordPress Integration
- Copy the entire plugin folder to `wp-content/plugins/character-generator/`
- Activate in WordPress admin
- Use shortcode `[character_generator]` on any page
- Requires user to be logged in (WordPress auth)
- Uses custom database tables (created on plugin activation via `cg_activate()`)
