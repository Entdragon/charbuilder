/**
 * PM2 Process Config — Character Generator (Node/Express)
 *
 * HOW TO USE
 * ──────────────────────────────────────────────────────────────────────────────
 * 1. Install PM2 globally on your server (run once as your deploy user):
 *      npm install -g pm2
 *
 * 2. From the project root on your server, start the app:
 *      pm2 start tools/pm2.config.js
 *
 * 3. Save the process list so it restarts after a server reboot:
 *      pm2 save
 *      pm2 startup        ← follow the printed command (run it as root/sudo)
 *
 * Common PM2 commands
 * ──────────────────────────────────────────────────────────────────────────────
 *   pm2 list                  – see all running processes
 *   pm2 logs char-generator   – tail the app logs
 *   pm2 restart char-generator
 *   pm2 stop char-generator
 *   pm2 delete char-generator
 *
 * The app listens on port 5000. Apache proxies
 * characters.libraryofcalbria.com → localhost:5000
 * (see tools/apache-vhost-characters.conf.example).
 */

module.exports = {
  apps: [
    {
      name: 'char-generator',

      // Path to the Express entry point — adjust if your project root differs
      script: 'server/index.js',

      // Run from the project root so relative paths in the app resolve correctly
      cwd: '/home/libraryo/charbuilder',   // ← UPDATE to your actual project path

      // Restart the app if it crashes, with exponential backoff
      autorestart: true,
      watch:       false,
      max_restarts: 10,
      restart_delay: 2000,

      // Environment variables
      env: {
        NODE_ENV: 'production',
        PORT:     5000,
        // SESSION_SECRET is read from the real environment — set it with:
        //   pm2 set char-generator:SESSION_SECRET "your-secret-here"
        // OR export SESSION_SECRET before running pm2 start.
      },

      // Log files
      out_file:  '/home/libraryo/logs/char-generator-out.log',
      error_file: '/home/libraryo/logs/char-generator-error.log',
      merge_logs: true,
      log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
    },
  ],
};
