// assets/js/src/utils/marks-dice.js
// Shared utility: converts a mark count to its dice string.
//
// Ironclaw extended mark progression:
//  1 = d4     2 = d6     3 = d8     4 = d10    5 = d12
//  6 = d4+d12  7 = d6+d12  8 = d8+d12  9 = d10+d12  10 = d12+d12
// 11 = d4+d12+d12  ...and so on.

const BASE_DICE = ['d4', 'd6', 'd8', 'd10', 'd12'];

/**
 * Returns the dice string for a given mark count.
 * @param {number} n - number of marks (1+)
 * @returns {string} e.g. "d8", "d4 + d12", ""
 */
export function marksToDice(n) {
  const marks = parseInt(n, 10) || 0;
  if (marks <= 0) return '';

  const level = Math.floor((marks - 1) / 5); // number of d12s to add
  const pos   = (marks - 1) % 5;             // index into BASE_DICE

  const base = BASE_DICE[pos];
  if (level === 0) return base;

  const extras = Array(level).fill('d12').join(' + ');
  return `${base} + ${extras}`;
}

/**
 * Returns just the highest single die in the pool for a skill (for sort/compare).
 */
export function marksToMaxDie(n) {
  const marks = parseInt(n, 10) || 0;
  if (marks <= 0) return 0;
  const pos = (marks - 1) % 5;
  return [4, 6, 8, 10, 12][pos];
}
