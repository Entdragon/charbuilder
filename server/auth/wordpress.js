const bcrypt   = require('bcryptjs');
const { PasswordHash } = require('phpass');

const hasher = new PasswordHash();

function verifyPassword(plaintext, hash) {
  if (!hash || !plaintext) return false;

  if (hash.startsWith('$wp$')) {
    // WordPress 6+ bcrypt: "$wp$" + bcrypt hash without its leading "$"
    // e.g. "$wp$2y$10$..." → prepend "$" → "$2y$10$..." → swap to "$2b$..."
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
  // Store in WP6 format: "$wp$" + bcrypt without its leading "$"
  const bcryptHash = bcrypt.hashSync(plaintext, 10);
  return '$wp$' + bcryptHash.slice(1).replace('2b$', '2y$');
}

module.exports = { verifyPassword, hashPassword };
