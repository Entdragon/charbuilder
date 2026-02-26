const express    = require('express');
const session    = require('express-session');
const FileStore  = require('session-file-store')(session);
const bodyParser = require('body-parser');
const path       = require('path');
const fs         = require('fs');

const ajaxRouter = require('./routes/ajax');

const app  = express();
const PORT = 5000;

app.set('trust proxy', 1);

app.use(bodyParser.urlencoded({ extended: true }));
app.use(bodyParser.json());

// ── Static assets (no session needed, serve first) ────────────────────────────
app.use('/assets', express.static(path.join(__dirname, '..', 'assets')));
app.use('/vendor',  express.static(path.join(__dirname, '..', 'node_modules')));

// ── Session ───────────────────────────────────────────────────────────────────
const SESSION_DIR = path.join(__dirname, '..', '.sessions');
if (!fs.existsSync(SESSION_DIR)) fs.mkdirSync(SESSION_DIR, { recursive: true });

app.use(session({
  store: new FileStore({
    path:        SESSION_DIR,
    ttl:         7 * 24 * 60 * 60,
    retries:     0,
    logFn:       () => {},
  }),
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

// ── Request logging (API only) ────────────────────────────────────────────────
app.use((req, res, next) => {
  if (req.path.startsWith('/api')) {
    console.log(`[${new Date().toISOString()}] ${req.method} ${req.path}`, req.body?.action || '');
  }
  next();
});

// ── API routes ────────────────────────────────────────────────────────────────
app.use('/api/ajax', ajaxRouter);

// ── Setup / password reset (protected by proxy secret) ────────────────────────
app.get('/setup', (req, res) => {
  res.setHeader('Cache-Control', 'no-store');
  res.send(`<!DOCTYPE html><html><head><meta charset="UTF-8">
<title>CG Setup</title>
<style>body{font-family:sans-serif;background:#1a1a2e;color:#e0e0e0;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}
.card{background:#24243e;border:1px solid #3a3a5c;border-radius:8px;padding:2rem;width:100%;max-width:420px}
h2{margin:0 0 1.5rem;color:#a9a4ff}label{display:block;font-size:.85rem;color:#aaa;margin-bottom:.25rem}
input{width:100%;padding:.6rem .8rem;background:#1a1a2e;border:1px solid #3a3a5c;border-radius:4px;color:#e0e0e0;font-size:.95rem;margin-bottom:1rem;box-sizing:border-box}
button{width:100%;padding:.75rem;background:#6c63ff;color:#fff;border:none;border-radius:4px;font-size:1rem;cursor:pointer}
#msg{margin-top:1rem;padding:.75rem;border-radius:4px;display:none}</style></head>
<body><div class="card">
<h2>Password Reset</h2>
<label>Admin Secret</label><input type="password" id="secret" placeholder="Your CG_PROXY_SECRET">
<label>Username</label><input type="text" id="uname" placeholder="e.g. admin_25l0dxf3">
<label>New Password</label><input type="password" id="pw1" placeholder="New password">
<label>Confirm Password</label><input type="password" id="pw2" placeholder="Confirm">
<button onclick="doReset()">Reset Password</button>
<div id="msg"></div></div>
<script>
function doReset(){
  var secret=document.getElementById('secret').value;
  var uname=document.getElementById('uname').value.trim();
  var pw1=document.getElementById('pw1').value;
  var pw2=document.getElementById('pw2').value;
  var msg=document.getElementById('msg');
  if(!secret||!uname||!pw1){msg.style.display='block';msg.style.background='#5a2020';msg.textContent='All fields required.';return;}
  if(pw1!==pw2){msg.style.display='block';msg.style.background='#5a2020';msg.textContent='Passwords do not match.';return;}
  msg.style.display='block';msg.style.background='#2a3a2a';msg.textContent='Working\u2026';
  fetch('/api/admin/reset-password',{method:'POST',headers:{'Content-Type':'application/json'},
    body:JSON.stringify({secret:secret,username:uname,password:pw1})})
  .then(r=>r.json()).then(d=>{
    if(d.success){msg.style.background='#1a3a1a';msg.textContent='Password updated! You can now log in.';}
    else{msg.style.background='#5a2020';msg.textContent='Error: '+d.data;}
  }).catch(e=>{msg.style.background='#5a2020';msg.textContent='Request failed: '+e.message;});
}
</script></body></html>`);
});

app.post('/api/admin/reset-password', async (req, res, next) => {
  try {
    const { secret, username, password } = req.body;
    if (!secret || secret !== process.env.CG_PROXY_SECRET) {
      return res.json({ success: false, data: 'Invalid secret.' });
    }
    if (!username || !password || password.length < 6) {
      return res.json({ success: false, data: 'Username and password (min 6 chars) required.' });
    }
    const { query, queryOne, prefix } = require('./db');
    const { hashPassword } = require('./auth/wordpress');
    const p = prefix();
    const user = await queryOne(
      `SELECT ID FROM ${p}users WHERE user_login = ? OR user_email = ? LIMIT 1`,
      [username, username]
    );
    if (!user) return res.json({ success: false, data: `No user found: "${username}"` });
    const hash = hashPassword(password);
    await query(`UPDATE ${p}users SET user_pass = ? WHERE ID = ?`, [hash, user.ID]);
    console.log(`[CG] Password reset for user ID ${user.ID} (${username})`);
    res.json({ success: true });
  } catch (e) {
    next(e);
  }
});

app.get('/api/auth/me', (req, res) => {
  if (!req.session.userId) {
    return res.json({ success: false, data: 'Not logged in.' });
  }
  res.json({ success: true, data: {
    id:      req.session.userId,
    username:req.session.username,
    email:   req.session.email,
    isAdmin: req.session.isAdmin,
  }});
});

// ── SPA catch-all ─────────────────────────────────────────────────────────────
app.get('/{*path}', (req, res, next) => {
  res.setHeader('Cache-Control', 'no-store, no-cache, must-revalidate');
  res.sendFile(path.join(__dirname, '..', 'public', 'index.html'), err => {
    if (err) next(err);
  });
});

// ── Global error handler ──────────────────────────────────────────────────────
app.use((err, req, res, next) => {
  console.error('[CG] Unhandled error:', err.message || err);
  if (res.headersSent) return next(err);
  const status = err.status || err.statusCode || 500;
  if (req.path.startsWith('/api')) {
    res.status(status).json({ success: false, data: 'Server error. Please try again.' });
  } else {
    res.status(status).send('Server error. Please try again.');
  }
});

app.listen(PORT, '0.0.0.0', () => {
  console.log(`Character Generator running at http://0.0.0.0:${PORT}`);
});
