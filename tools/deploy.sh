#!/bin/bash
# ──────────────────────────────────────────────────────────────────────────────
# deploy.sh — First-time setup for the Character Generator on your server
#
# Run from ANYWHERE:
#   bash ~/public_html/characters/tools/deploy.sh
#   OR (after cloning):
#   bash ~/charbuilder/tools/deploy.sh
#
# What it does:
#   1. Clones the GitHub repo to ~/charbuilder (if not already there)
#   2. Creates .env from .env.example (if not already there)
#   3. npm install + npm run build
#   4. Starts the app via PM2
#   5. Prints what to do next (cron + .htaccess)
# ──────────────────────────────────────────────────────────────────────────────

set -e

REPO_URL="https://github.com/Entdragon/charbuilder.git"
INSTALL_DIR="$HOME/charbuilder"
HTACCESS_SRC="$INSTALL_DIR/tools/characters-subdomain.htaccess"
HTACCESS_DST="$HOME/public_html/characters/.htaccess"

echo ""
echo "=== Character Generator Deploy Script ==="
echo ""

# ── Load nvm ──────────────────────────────────────────────────────────────────
export NVM_DIR="$HOME/.nvm"
if [ -s "$NVM_DIR/nvm.sh" ]; then
    . "$NVM_DIR/nvm.sh"
else
    echo "ERROR: nvm not found at $NVM_DIR"
    echo "Make sure Node.js is installed and nvm is set up."
    exit 1
fi

# ── Step 1: Clone or update repo ─────────────────────────────────────────────
if [ -d "$INSTALL_DIR/.git" ]; then
    echo "► Updating existing repo at $INSTALL_DIR..."
    cd "$INSTALL_DIR"
    git pull
else
    echo "► Cloning repo to $INSTALL_DIR..."
    echo "  When prompted for a password, use your GitHub personal access token."
    git clone "$REPO_URL" "$INSTALL_DIR"
    cd "$INSTALL_DIR"
fi

# ── Step 2: Create .env if missing ───────────────────────────────────────────
if [ ! -f "$INSTALL_DIR/.env" ]; then
    cp "$INSTALL_DIR/.env.example" "$INSTALL_DIR/.env"
    echo ""
    echo "► Created $INSTALL_DIR/.env from .env.example"
    echo ""
    echo "  ┌─────────────────────────────────────────────────────────────────┐"
    echo "  │  IMPORTANT: Fill in .env before the app will work.             │"
    echo "  │                                                                 │"
    echo "  │  Get DB_HOST, DB_NAME, DB_USER, DB_PASS from:                  │"
    echo "  │    ~/public_html/wp-config.php                                  │"
    echo "  │  (look for DB_HOST, DB_NAME, DB_USER, DB_PASSWORD)              │"
    echo "  │                                                                 │"
    echo "  │  DB_PREFIX=DcVnchxg4_   (already correct for this site)        │"
    echo "  │                                                                 │"
    echo "  │  Generate SESSION_SECRET with:                                  │"
    echo "  │    node -e \"console.log(require('crypto').randomBytes(48)       │"
    echo "  │      .toString('hex'))\"                                         │"
    echo "  │                                                                 │"
    echo "  │  Edit the file now:  nano $INSTALL_DIR/.env                    │"
    echo "  └─────────────────────────────────────────────────────────────────┘"
    echo ""
    read -rp "  Press ENTER after you have filled in .env to continue..."
fi

# ── Step 3: Install dependencies ─────────────────────────────────────────────
echo ""
echo "► Installing npm dependencies..."
cd "$INSTALL_DIR"
npm install --omit=dev --prefer-offline

# ── Step 4: Build JS/CSS assets ──────────────────────────────────────────────
echo ""
echo "► Building JS/CSS assets..."
npm run build

# ── Step 5: Start (or restart) via PM2 ───────────────────────────────────────
echo ""
echo "► Starting app via PM2..."
mkdir -p "$HOME/logs"

if pm2 describe char-generator > /dev/null 2>&1; then
    pm2 restart char-generator
else
    pm2 start "$INSTALL_DIR/tools/pm2.config.js"
fi
pm2 save

# ── Step 6: Install .htaccess proxy ──────────────────────────────────────────
echo ""
if [ -f "$HTACCESS_SRC" ]; then
    cp "$HTACCESS_SRC" "$HTACCESS_DST"
    echo "► Copied proxy .htaccess to $HTACCESS_DST"
else
    echo "► (Skipped .htaccess copy — source not found)"
fi

# ── Done ──────────────────────────────────────────────────────────────────────
NODE_BIN="$(which pm2 2>/dev/null || echo "$NVM_DIR/versions/node/$(node -v)/bin/pm2")"

echo ""
echo "=== Done! ==="
echo ""
echo "  App status:   pm2 list"
echo "  App logs:     pm2 logs char-generator"
echo ""
echo "  ► To auto-start after server reboots, add this to your crontab:"
echo "    Run: crontab -e"
echo "    Add: @reboot $NODE_BIN resurrect"
echo ""
echo "  ► Test the app is running:"
echo "    curl -s http://127.0.0.1:5000/api/ajax -d 'action=cg_ping' | head -c 200"
echo ""
echo "  ► If characters.libraryofcalbria.com still shows errors, check that"
echo "    mod_proxy is enabled — contact your host if the .htaccess has no effect."
echo ""
