const { query } = require('../../db');
const os = require('os');

async function cg_ping(req, res) {
  res.json({ success: true, data: {
    memory_usage_bytes: process.memoryUsage().heapUsed,
    peak_usage_bytes:   process.memoryUsage().heapTotal,
    timestamp:          new Date().toISOString(),
  }});
}

async function cg_run_diagnostics(req, res) {
  if (!req.session.isAdmin) {
    return res.status(403).json({ success: false, data: 'Forbidden.' });
  }
  let db_status = 'unknown';
  try {
    const rows = await query('SELECT 1 AS val');
    db_status = rows[0]?.val === 1 ? 'ok' : 'unexpected result';
  } catch (e) {
    db_status = 'error: ' + e.message;
  }
  res.json({ success: true, data: {
    node_version:        process.version,
    platform:            process.platform,
    memory_usage_bytes:  process.memoryUsage().heapUsed,
    peak_usage_bytes:    process.memoryUsage().heapTotal,
    db_status,
    timestamp:           new Date().toISOString(),
  }});
}

module.exports = { cg_ping, cg_run_diagnostics };
