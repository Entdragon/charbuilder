const mysql = require('mysql2/promise');

const rawHost = process.env.DB_HOST || 'localhost';
const [dbHost, dbPortStr] = rawHost.includes(':') ? rawHost.split(':') : [rawHost, '3306'];
const dbPort = parseInt(dbPortStr, 10) || 3306;

const pool = mysql.createPool({
  host:     dbHost,
  port:     dbPort,
  user:     process.env.DB_USER     || '',
  password: process.env.DB_PASS     || '',
  database: process.env.DB_NAME     || '',
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0,
});

function prefix() {
  return process.env.DB_PREFIX || 'wp_';
}

async function query(sql, params = []) {
  const [rows] = await pool.execute(sql, params);
  return rows;
}

async function queryOne(sql, params = []) {
  const rows = await query(sql, params);
  return rows[0] || null;
}

module.exports = { pool, query, queryOne, prefix };
