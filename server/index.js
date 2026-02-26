const express    = require('express');
const session    = require('express-session');
const bodyParser = require('body-parser');
const path       = require('path');

const ajaxRouter = require('./routes/ajax');

const app  = express();
const PORT = 5000;

app.set('trust proxy', 1);

app.use(bodyParser.urlencoded({ extended: true }));
app.use(bodyParser.json());

app.use((req, res, next) => {
  console.log(`[${new Date().toISOString()}] ${req.method} ${req.path}`, req.body?.action || '');
  next();
});

const isProduction = process.env.NODE_ENV === 'production';

app.use(session({
  secret:            process.env.SESSION_SECRET || 'cg-dev-secret-change-me',
  resave:            false,
  saveUninitialized: false,
  cookie: {
    httpOnly: true,
    sameSite: 'none',
    secure:   true,
    maxAge:   7 * 24 * 60 * 60 * 1000,
  },
}));

app.use('/assets', express.static(path.join(__dirname, '..', 'assets')));
app.use('/vendor', express.static(path.join(__dirname, '..', 'node_modules')));

app.use('/api/ajax', ajaxRouter);

app.get('/api/auth/me', (req, res) => {
  if (!req.session.userId) {
    return res.json({ success: false, data: 'Not logged in.' });
  }
  res.json({ success: true, data: {
    id:       req.session.userId,
    username: req.session.username,
    email:    req.session.email,
    isAdmin:  req.session.isAdmin,
  }});
});

app.get('/{*path}', (req, res) => {
  res.setHeader('Cache-Control', 'no-store, no-cache, must-revalidate');
  res.sendFile(path.join(__dirname, '..', 'public', 'index.html'));
});

app.listen(PORT, '0.0.0.0', () => {
  console.log(`Character Generator running at http://0.0.0.0:${PORT}`);
});
