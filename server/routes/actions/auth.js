const { query, queryOne, prefix } = require('../../db');
const { verifyPassword, hashPassword } = require('../../auth/wordpress');
const crypto = require('crypto');

async function cg_login_user(req, res) {
  const username = (req.body.username || '').trim();
  const password = (req.body.password || '');

  if (!username || !password) {
    return res.json({ success: false, data: 'Username and password are required.' });
  }

  const p = prefix();
  const user = await queryOne(
    `SELECT ID, user_pass, user_login, user_email FROM ${p}users WHERE user_login = ? OR user_email = ? LIMIT 1`,
    [username, username]
  );

  if (!user || !verifyPassword(password, user.user_pass)) {
    return res.json({ success: false, data: 'Invalid username or password.' });
  }

  const isAdmin = await queryOne(
    `SELECT meta_value FROM ${p}usermeta WHERE user_id = ? AND meta_key = '${p}capabilities' LIMIT 1`,
    [user.ID]
  );
  const capStr = isAdmin ? (isAdmin.meta_value || '') : '';

  req.session.userId    = user.ID;
  req.session.username  = user.user_login;
  req.session.email     = user.user_email;
  req.session.isAdmin   = capStr.includes('administrator');

  res.json({ success: true, data: { redirect: '/' } });
}

async function cg_logout_user(req, res) {
  req.session.destroy(() => {
    res.json({ success: true, data: { redirect: '/' } });
  });
}

async function cg_register_user(req, res) {
  const username = (req.body.username || '').trim();
  const email    = (req.body.email    || '').trim().toLowerCase();
  const password = (req.body.password || '');

  if (!username || !email || !password) {
    return res.json({ success: false, data: 'All fields are required.' });
  }

  const p = prefix();

  const existingUser  = await queryOne(`SELECT ID FROM ${p}users WHERE user_login = ? LIMIT 1`, [username]);
  if (existingUser) return res.json({ success: false, data: 'Username already taken.' });

  const existingEmail = await queryOne(`SELECT ID FROM ${p}users WHERE user_email = ? LIMIT 1`, [email]);
  if (existingEmail) return res.json({ success: false, data: 'Email already registered.' });

  const hash    = hashPassword(password);
  const now     = new Date().toISOString().slice(0, 19).replace('T', ' ');
  const userKey = crypto.randomBytes(12).toString('hex');

  await query(
    `INSERT INTO ${p}users
      (user_login, user_pass, user_email, user_registered, user_activation_key, user_status, display_name)
     VALUES (?, ?, ?, ?, ?, 0, ?)`,
    [username, hash, email, now, userKey, username]
  );

  const newUser = await queryOne(`SELECT ID FROM ${p}users WHERE user_login = ? LIMIT 1`, [username]);
  if (!newUser) return res.json({ success: false, data: 'Registration failed.' });

  const uid = newUser.ID;
  await query(`INSERT INTO ${p}usermeta (user_id, meta_key, meta_value) VALUES (?, '${p}capabilities', ?)`, [uid, 'a:1:{s:10:"subscriber";b:1;}']);
  await query(`INSERT INTO ${p}usermeta (user_id, meta_key, meta_value) VALUES (?, '${p}user_level', '0')`, [uid]);

  req.session.userId   = uid;
  req.session.username = username;
  req.session.email    = email;
  req.session.isAdmin  = false;

  res.json({ success: true, data: { redirect: '/' } });
}

async function cg_get_current_user(req, res) {
  if (!req.session.userId) {
    return res.json({ success: false, data: 'Not logged in.' });
  }
  res.json({ success: true, data: {
    id:       req.session.userId,
    username: req.session.username,
    email:    req.session.email,
    isAdmin:  req.session.isAdmin,
  }});
}

module.exports = { cg_login_user, cg_logout_user, cg_register_user, cg_get_current_user };
