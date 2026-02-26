/**
 * Database access layer.
 *
 * Supports two modes:
 *  1. PROXY mode  – when CG_PROXY_URL + CG_PROXY_SECRET are set.
 *     Sends SQL to the PHP proxy script running on the WordPress server.
 *     Use this when direct MySQL access is blocked (shared hosting firewall).
 *
 *  2. DIRECT mode – when DB_HOST/DB_USER/DB_PASS/DB_NAME are set.
 *     Connects directly to MySQL via mysql2.
 */

const PROXY_URL    = process.env.CG_PROXY_URL    || '';
const PROXY_SECRET = process.env.CG_PROXY_SECRET || '';

function prefix() {
  return process.env.DB_PREFIX || 'wp_';
}

// ── Proxy mode ────────────────────────────────────────────────────────────────

async function proxyQuery(sql, params = []) {
  const res = await fetch(PROXY_URL, {
    method:  'POST',
    headers: {
      'Content-Type':  'application/json',
      'X-CG-Secret':   PROXY_SECRET,
    },
    body: JSON.stringify({ sql, params }),
  });

  if (!res.ok) {
    const text = await res.text();
    throw new Error(`Proxy error ${res.status}: ${text}`);
  }

  const data = await res.json();

  if (data.error) throw new Error(`Proxy DB error: ${data.error}`);

  // SELECT → { rows: [...] }  |  INSERT/UPDATE → { rowCount, lastInsertId }
  if (Array.isArray(data.rows)) return data;
  return data;
}

// ── Direct mode ───────────────────────────────────────────────────────────────

let _pool = null;
function getPool() {
  if (!_pool) {
    const mysql  = require('mysql2/promise');
    const rawHost = process.env.DB_HOST || 'localhost';
    const [dbHost, dbPortStr] = rawHost.includes(':')
      ? rawHost.split(':')
      : [rawHost, '3306'];

    _pool = mysql.createPool({
      host:               dbHost,
      port:               parseInt(dbPortStr, 10) || 3306,
      user:               process.env.DB_USER || '',
      password:           process.env.DB_PASS || '',
      database:           process.env.DB_NAME || '',
      waitForConnections: true,
      connectionLimit:    10,
      queueLimit:         0,
    });
  }
  return _pool;
}

// ── Unified API ───────────────────────────────────────────────────────────────

async function query(sql, params = []) {
  if (PROXY_URL && PROXY_SECRET) {
    const data = await proxyQuery(sql, params);
    // SELECT returns { rows }, mutations return { rowCount, lastInsertId }
    if (Array.isArray(data.rows)) return data.rows;
    // Mimic mysql2 insert result shape so character.js can read insertId
    return Object.assign([], { insertId: data.lastInsertId, affectedRows: data.rowCount });
  }

  const pool = getPool();
  const [rows] = await pool.execute(sql, params);
  return rows;
}

async function queryOne(sql, params = []) {
  const rows = await query(sql, params);
  return Array.isArray(rows) ? (rows[0] || null) : null;
}

module.exports = { query, queryOne, prefix };
