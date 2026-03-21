# Library of Calabria – Character Generator

A standalone Express.js web application for the [Library of Calabria](https://libraryofcalbria.com) tabletop RPG. Provides a full character creation system for the Ironclaw game system.

- **Live app**: https://characters.libraryofcalbria.com (served from Replit)
- **Production site**: https://libraryofcalbria.com

---

## Repository structure

```
server/                     Express app (Node.js)
  index.js                  Entry point, port 5000
  db.js                     Database layer (PHP proxy mode)
  auth/                     WordPress password verification
  routes/                   API route handlers

assets/
  js/src/                   ES6 frontend source modules
  js/dist/                  Compiled JS bundle (esbuild)
  css/src/                  SCSS source
  css/dist/                 Compiled CSS

public/
  index.html                App shell (login + character builder)

tools/
  cg-db-proxy.php           PHP proxy — deploy to WordPress server
  cg-proxy-config.example.php  Config template for the proxy
  migrations/               Database migration SQL files (apply via phpMyAdmin)
  wordpress-theme/          WordPress child theme files (copy to live server)
  WORKFLOW.md               Day-to-day development workflow guide

includes/                   Original PHP plugin (reference only — not active)
php/                        PHP rewrite attempt (reference only — not active)
```

---

## Quick start

```bash
npm install
npm run build        # compile JS bundle + CSS
node server/index.js # start on port 5000
```

Set the following in Replit Secrets:

| Secret | Purpose |
|--------|---------|
| `CG_PROXY_URL` | Full URL to `cg-db-proxy.php` on the WordPress server |
| `CG_PROXY_SECRET` | Shared secret for authenticating proxy requests |
| `SESSION_SECRET` | Session signing secret |
| `DB_PREFIX` | WordPress table prefix (`DcVnchxg4_`) |

---

## Database

The app connects to the production WordPress MySQL database (`libraryo_wp_ainng`) via a PHP proxy deployed at `libraryofcalbria.com`. Direct MySQL access from Replit is blocked by the shared hosting firewall.

See `tools/migrations/README.md` for the database migration history and process.

---

## WordPress theme

Custom page templates and functions additions for the `libraryofcalbria.com` child theme live in `tools/wordpress-theme/`. See that directory's README for the copy-to-server workflow.

---

## Development workflow

See `tools/WORKFLOW.md` for the full day-to-day process covering:
- Character generator development
- WordPress theme changes
- Database migrations
- Git workflow
