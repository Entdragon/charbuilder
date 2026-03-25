// assets/js/src/utils/compact-pool.js
//
// compactPool(poolStr) — collapses repeated dice in a pool string.
//   "d6 + d6 + d8 + d6"  →  "3d6 + d8"
//   "d8 + d4 + d8"        →  "2d8 + d4"
//
// Dice are sorted largest to smallest (d12 … d4) so the output is tidy.

const DIE_ORDER = ['d12', 'd10', 'd8', 'd6', 'd4'];

function dieRank(die) {
  const idx = DIE_ORDER.indexOf(die.toLowerCase());
  return idx === -1 ? 99 : idx;
}

export function compactPool(poolStr) {
  if (!poolStr || poolStr === '—') return poolStr || '—';

  const counts = {};
  const seen   = [];

  poolStr.split('+').forEach(token => {
    const t = token.trim();
    if (!t) return;

    // Already in NdX form (e.g. "3d6") — expand then re-count
    const ndx = t.match(/^(\d+)(d\d+)$/i);
    if (ndx) {
      const n   = parseInt(ndx[1], 10);
      const die = ndx[2].toLowerCase();
      if (!counts[die]) { counts[die] = 0; seen.push(die); }
      counts[die] += n;
    } else {
      const die = t.toLowerCase();
      if (!counts[die]) { counts[die] = 0; seen.push(die); }
      counts[die] += 1;
    }
  });

  if (!seen.length) return '—';

  return seen
    .sort((a, b) => dieRank(a) - dieRank(b))
    .map(die => counts[die] > 1 ? `${counts[die]}${die}` : die)
    .join(' + ');
}
