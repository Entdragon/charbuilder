// assets/js/src/core/quals/catalog.js

const TYPES = ['language', 'literacy', 'insider', 'mystic', 'piety'];

function stripDiacritics(s) {
  // ES2015-safe diacritic stripping
  return String(s || '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '');
}

function canon(s) {
  return stripDiacritics(String(s || ''))
    .trim()
    .replace(/\s+/g, ' ')
    .toLowerCase();
}

function matchCategoryLine(text) {
  // Match "Language: X", "Literacy: Y", etc (case-insensitive)
  const m = String(text || '').trim().match(/^(Language|Literacy|Insider|Mystic|Piety)\s*:\s*(.+)\s*$/i);
  if (!m) return null;

  const type = canon(m[1]);
  const value = String(m[2] || '').trim();
  if (!type || !value) return null;

  const key = canon(value);
  if (!key) return null;

  return { type, value, key };
}

export function buildQualCatalogFromGifts(gifts = []) {
  const catalog = Object.create(null);
  TYPES.forEach(t => (catalog[t] = Object.create(null)));

  (Array.isArray(gifts) ? gifts : []).forEach(g => {
    const rs = g?.requires_special ?? g?.ct_gifts_requires_special ?? '';
    const hit = matchCategoryLine(rs);
    if (!hit || !catalog[hit.type]) return;

    const bucket = catalog[hit.type];
    if (!bucket[hit.key]) {
      bucket[hit.key] = { key: hit.key, forms: Object.create(null), count: 0 };
    }

    bucket[hit.key].count += 1;
    bucket[hit.key].forms[hit.value] = (bucket[hit.key].forms[hit.value] || 0) + 1;
  });

  // Convert to arrays, choose best display label per canonical key
  const out = Object.create(null);
  TYPES.forEach(type => {
    out[type] = Object.values(catalog[type]).map(entry => {
      const forms = entry.forms || {};
      let bestLabel = Object.keys(forms)[0] || entry.key;
      let bestCount = -1;
      for (const k of Object.keys(forms)) {
        const c = forms[k] || 0;
        if (c > bestCount) { bestCount = c; bestLabel = k; }
      }
      return { type, key: entry.key, label: bestLabel, count: entry.count };
    }).sort((a, b) => a.label.localeCompare(b.label));
  });

  return out;
}
