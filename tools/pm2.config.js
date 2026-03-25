/**
 * PM2 Process Config — Character Generator (Node/Express)
 *
 * HOW TO USE (cPanel / no-sudo shared hosting)
 * ──────────────────────────────────────────────────────────────────────────────
 * 1. Install PM2 globally (run once):
 *      npm install -g pm2
 *
 * 2. Start the app — run this script from ANYWHERE using its full path:
 *      pm2 start ~/charbuilder/tools/pm2.config.js
 *    (replace ~/charbuilder with your actual project path)
 *
 * 3. Save the process list:
 *      pm2 save
 *
 * 4. Auto-start on reboot via cron (no sudo needed):
 *      crontab -e
 *    Add this line (adjust the path to match your nvm node version):
 *      @reboot /home/libraryo/.nvm/versions/node/v22.17.0/bin/pm2 resurrect
 *
 * Common PM2 commands
 * ──────────────────────────────────────────────────────────────────────────────
 *   pm2 list                  – see all running processes
 *   pm2 logs char-generator   – tail the app logs
 *   pm2 restart char-generator
 *   pm2 stop char-generator
 *   pm2 delete char-generator
 */

const path = require('path');

// __dirname is tools/ — one level up is the project root
const PROJECT_ROOT = path.resolve(__dirname, '..');

module.exports = {
  apps: [
    {
      name: 'char-generator',

      // Absolute path derived automatically — no manual editing needed
      script: path.join(PROJECT_ROOT, 'server', 'index.js'),
      cwd:    PROJECT_ROOT,

      autorestart:  true,
      watch:        false,
      max_restarts: 10,
      restart_delay: 2000,

      env: {
        NODE_ENV: 'production',
        PORT:     5000,
      },

      out_file:       path.join(process.env.HOME || '/home/libraryo', 'logs', 'char-generator-out.log'),
      error_file:     path.join(process.env.HOME || '/home/libraryo', 'logs', 'char-generator-error.log'),
      merge_logs:     true,
      log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
    },
  ],
};
