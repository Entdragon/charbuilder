const bcrypt   = require('bcryptjs');
const { PasswordHash } = require('phpass');

const hasher = new PasswordHash();

function verifyPassword(plaintext, hash) {
  if (!hash || !plaintext) return false;

  if (hash.startsWith('$wp$')) {
    const bcryptHash = ('$' + hash.slice(4)).replace('$2y$', '$2b$');
    return bcrypt.compareSync(plaintext, bcryptHash);
  }

  if (hash.startsWith('$P$') || hash.startsWith('$H$')) {
    return hasher.checkPassword(plaintext, hash);
  }

  if (hash.startsWith('$2y$') || hash.startsWith('$2b$') || hash.startsWith('$2a$')) {
    const bcryptHash = hash.replace('$2y$', '$2b$');
    return bcrypt.compareSync(plaintext, bcryptHash);
  }

  return false;
}

function hashPassword(plaintext) {
  const bcryptHash = bcrypt.hashSync(plaintext, 10);
  return '$wp$' + bcryptHash.slice(1).replace('2b$', '2y$');
}

/**
 * Delegate authentication to the PHP proxy, which uses WordPress's native
 * wp_authenticate(). Used as fallback when local bcrypt verify fails for
 * $wp$ hashes (e.g. bcryptjs $2y$ compatibility issues).
 *
 * Returns an object { success, user_id, user_login, user_email } or null if
 * the proxy is not configured or the request fails.
 */
async function verifyViaProxy(username, password) {
  const proxyUrl    = process.env.CG_PROXY_URL    || '';
  const proxySecret = process.env.CG_PROXY_SECRET || '';
  if (!proxyUrl || !proxySecret) return null;

  try {
    const res = await fetch(proxyUrl, {
      method:  'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CG-Secret':  proxySecret,
      },
      body: JSON.stringify({ action: 'wp_auth_check', username, password }),
    });
    if (!res.ok) return null;
    const json = await res.json();
    return json;
  } catch (_) {
    return null;
  }
}

module.exports = { verifyPassword, hashPassword, verifyViaProxy };
