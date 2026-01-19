// assets/js/src/core/quals/state.js
import FormBuilderAPI from '../formBuilder';

const TYPES = ['language', 'literacy', 'insider', 'mystic', 'piety'];

function stripDiacritics(s) {
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

function emptyData() {
  return {
    language: [],
    literacy: [],
    insider: [],
    mystic: [],
    piety: [],
  };
}

function normalizeList(v) {
  if (v == null) return [];
  const arr = Array.isArray(v) ? v : [v];
  const out = [];
  const seen = new Set();

  arr.forEach(x => {
    const raw = String(x || '').trim().replace(/\s+/g, ' ');
    if (!raw) return;
    const k = canon(raw);
    if (!k || seen.has(k)) return;
    seen.add(k);
    out.push(raw);
  });

  return out;
}

function getBuilderData() {
  // Prefer live _data (mutable) if present, else fallback to getData()
  if (FormBuilderAPI && FormBuilderAPI._data) return FormBuilderAPI._data;
  if (typeof FormBuilderAPI?.getData === 'function') return FormBuilderAPI.getData() || {};
  return {};
}

const QualState = {
  data: emptyData(),

  init() {
    const src = getBuilderData();
    const q = src.qualifications || src.quals || src.cg_quals || {};
    const next = emptyData();

    TYPES.forEach(t => {
      next[t] = normalizeList(q[t]);
    });

    this.data = next;

    // Persist normalized version back (so save/load is consistent)
    this.persist();
  },

  persist() {
    const src = getBuilderData();
    const payload = {};
    TYPES.forEach(t => (payload[t] = (this.data[t] || []).slice()));

    if (FormBuilderAPI && FormBuilderAPI._data) {
      FormBuilderAPI._data.qualifications = payload;
      // aliases (safe)
      FormBuilderAPI._data.quals = payload;
      FormBuilderAPI._data.cg_quals = payload;
    } else {
      // best-effort if only getData() exists
      src.qualifications = payload;
    }

    // Notify others
    document.dispatchEvent(new CustomEvent('cg:quals:changed', { detail: { qualifications: payload } }));
    const $ = window.jQuery;
    if ($) $(document).trigger('cg:quals:changed', [{ qualifications: payload }]);
  },

  getAll() {
    return JSON.parse(JSON.stringify(this.data || emptyData()));
  },

  get(type) {
    const t = String(type || '').toLowerCase();
    return (this.data && Array.isArray(this.data[t])) ? this.data[t] : [];
  },

  has(type, value) {
    const t = String(type || '').toLowerCase();
    const k = canon(value);
    return !!(this.data?.[t] || []).some(v => canon(v) === k);
  },

  add(type, value) {
    const t = String(type || '').toLowerCase();
    if (!TYPES.includes(t)) return false;

    const raw = String(value || '').trim().replace(/\s+/g, ' ');
    if (!raw) return false;

    if (this.has(t, raw)) return false;

    this.data[t] = (this.data[t] || []).concat([raw]);
    this.persist();
    return true;
  },

  remove(type, value) {
    const t = String(type || '').toLowerCase();
    if (!TYPES.includes(t)) return false;

    const k = canon(value);
    const before = this.get(t);
    const after = before.filter(v => canon(v) !== k);
    if (after.length === before.length) return false;

    this.data[t] = after;
    this.persist();
    return true;
  },
};

window.CG_QualState = QualState;
export default QualState;
