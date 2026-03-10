module.exports = {
  apps: [
    {
      name:         'character-generator',
      script:       'server/index.js',
      instances:    1,
      autorestart:  true,
      watch:        false,
      max_memory_restart: '512M',
      env: {
        NODE_ENV: 'production',
        PORT:     5000,
      },
    },
  ],
};
