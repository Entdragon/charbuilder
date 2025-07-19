// Shared utilities for Summary

/**
 * Capitalize first letter and replace underscores/dashes with spaces.
 */
export function capitalize(str) {
  if (typeof str !== 'string') return '';
  const cleaned = str.replace(/[_\-]/g, ' ');
  return cleaned.charAt(0).toUpperCase() + cleaned.slice(1);
}

/**
 * Given a die string like "d8", return half (rounded up) as a "dX".
 */
export function halfDie(die) {
  if (typeof die !== 'string' || die[0] !== 'd') return '';
  const n = parseInt(die.slice(1), 10);
  return `d${Math.ceil(n / 2)}`;
}

/**
 * Maximum values for dice types.
 */
export const DIE_MAX = {
  d4:  4,
  d6:  6,
  d8:  8,
  d10: 10,
  d12: 12
};
