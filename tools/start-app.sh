#!/bin/bash
# ──────────────────────────────────────────────────────────────────────────────
# start-app.sh — start (or restart) the character generator on your server
#
# Run from ANYWHERE:
#   bash ~/charbuilder/tools/start-app.sh
# ──────────────────────────────────────────────────────────────────────────────

set -e

# Resolve project root (one level up from this script)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

echo "Project root: $PROJECT_ROOT"

# Load nvm so pm2/node are on PATH
export NVM_DIR="$HOME/.nvm"
if [ -s "$NVM_DIR/nvm.sh" ]; then
    # shellcheck source=/dev/null
    . "$NVM_DIR/nvm.sh"
else
    echo "WARNING: nvm not found at $NVM_DIR — make sure node/pm2 are on PATH"
fi

# Create log directory if it doesn't exist
mkdir -p "$HOME/logs"

# Start or restart via PM2
if pm2 describe char-generator > /dev/null 2>&1; then
    echo "Restarting existing PM2 process..."
    pm2 restart char-generator
else
    echo "Starting char-generator for the first time..."
    pm2 start "$PROJECT_ROOT/tools/pm2.config.js"
fi

pm2 save
echo ""
echo "Done. Check status with: pm2 list"
echo "Tail logs with:          pm2 logs char-generator"
echo ""
echo "To auto-start on reboot, add this to your crontab (crontab -e):"
echo "  @reboot $(which pm2 2>/dev/null || echo '/home/$USER/.nvm/versions/node/YOURVERSION/bin/pm2') resurrect"
