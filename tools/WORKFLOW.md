# Development Workflow

This document describes the day-to-day process for working on the Library of Calabria character generator and WordPress site.

---

## Replit is home base

All code lives in Replit and is pushed to GitHub from here. The WordPress server and phpMyAdmin are production-only environments — we never edit code directly on the server as a first step.

---

## Day-to-day: character generator (Node.js app)

```
Edit source files in Replit
  └─ assets/js/src/   (frontend JS)
  └─ assets/css/src/  (SCSS)
  └─ server/          (Express routes)

Run the build after JS or CSS changes:
  npm run build

Test in the Replit preview pane (port 5000).

When done:
  git add .
  git commit -m "feat: describe what changed"
  git push
```

The live site at `characters.libraryofcalbria.com` is served from Replit. Changes are live as soon as the Replit workflow restarts with the new code.

---

## Day-to-day: WordPress child theme

```
Edit PHP/CSS/JS files in tools/wordpress-theme/ in Replit.

git add .
git commit -m "feat: describe what changed"
git push

Copy changed file(s) to the live server:
  Via SFTP or cPanel File Manager →
  /public_html/wp-content/themes/<child-theme-name>/

Test on the live site.
```

If you ever edit files directly on the server (emergency fix etc.):
```
Copy the edited file back to tools/wordpress-theme/ in Replit.
git add .
git commit -m "chore: sync server edit back to repo"
git push
```

---

## Database migrations

Migrations are SQL files in `tools/migrations/`. They are applied manually in phpMyAdmin and are never run automatically.

### Adding a new migration

1. Create a new file in `tools/migrations/` following the naming convention:
   ```
   NNN-short-description.sql
   ```
   where `NNN` is the next number in sequence (e.g. `003`).

2. Write the SQL. Follow these rules:
   - Use `IF EXISTS` / `IF NOT EXISTS` guards so the script is idempotent
   - Add a comment block at the top explaining what the migration does and why
   - Group related statements with section comments
   - For destructive changes (DROP, ALTER removing columns): note what backup to take first

3. Update the migration log in `tools/migrations/README.md`.

4. `git add`, `git commit`, `git push`.

### Applying a migration in phpMyAdmin

1. Open **phpMyAdmin** → select `libraryo_wp_ainng`
2. Click the **SQL** tab
3. Paste the migration file contents
4. Read through the SQL before clicking Go
5. Click **Go** and confirm success
6. Update the "Applied" date in `tools/migrations/README.md`
7. `git commit` the README update

### Before any destructive migration (DROP TABLE, ALTER TABLE dropping columns)

Take a fresh phpMyAdmin dump first:
- **Export** → Format: SQL → tick "Add DROP TABLE" → Go
- Save the file somewhere safe before proceeding

---

## Pulling GitHub changes into Replit

If you've pushed commits from another machine and need them in Replit:

```bash
git pull
npm install          # if package.json changed
npm run build        # if JS or CSS source changed
```

Then restart the Replit workflow if the server code changed.

---

## Branch strategy

For now, all work happens on `main`. If a change is large or risky, use a feature branch:

```bash
git checkout -b feature/my-change
# ... work ...
git push -u origin feature/my-change
# Open a PR on GitHub, review, merge to main
git checkout main && git pull
```
