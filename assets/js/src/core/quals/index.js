// assets/js/src/core/quals/index.js
import { buildQualCatalogFromGifts } from './catalog.js';

const Quals = {
  _catalog: { language: [], literacy: [], insider: [], mystic: [], piety: [] },

  updateFromGifts(gifts) {
    this._catalog = buildQualCatalogFromGifts(gifts || []);
    window.CG_QualCatalog = this._catalog; // debug-friendly
  },

  get(type) {
    const t = String(type || '').toLowerCase();
    return this._catalog[t] || [];
  },

  debugTop(limit = 30) {
    const rows = [];
    Object.keys(this._catalog || {}).forEach(type => {
      (this._catalog[type] || []).forEach(o => {
        rows.push({ type, count: o.count, text: `${type}: ${o.label}` });
      });
    });
    rows.sort((a, b) => b.count - a.count);
    const top = rows.slice(0, limit);
    // eslint-disable-next-line no-console
    console.table(top);
    return top;
  }
};

window.CG_Quals = Quals;

export default Quals;
