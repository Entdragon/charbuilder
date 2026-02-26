const { PasswordHash } = require('phpass');

const hasher = new PasswordHash();

function verifyPassword(plaintext, hash) {
  return hasher.checkPassword(plaintext, hash);
}

module.exports = { verifyPassword };
