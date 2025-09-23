// assets/js/src/gifts/free-choices.js
// Initializes a “free choice” Gifts <select> and updates it when species/career change.
// Replace the demo pool logic with your real endpoint when ready.

const log = (...a) => console.log('[Gifts.FreeChoices]', ...a);
const $ = window.jQuery;

const FreeChoices = {
  _init: false,
  _selId: '#cg-gift-free', // adjust if your markup differs

  init() {
    if (this._init) return;
    this._init = true;

    // Build select if missing (non-fatal)
    if (!document.querySelector(this._selId)) {
      const container = document.querySelector('#cg-form-container') || document.body;
      const wrap = document.createElement('div');
      wrap.innerHTML = `
        <label>Gift (free choice)<br>
          <select id="${this._selId.replace('#','')}"></select>
        </label>`;
      container.appendChild(wrap.firstElementChild);
      log('Injected temporary free-choice select.');
    }

    const refresh = () => this.populatePool();

    $(document)
      .off('.gifts.cg') // clear any previous namespaced handlers in case of HMR
      .on('cg:species:changed.gifts.cg', refresh)
      .on('cg:career:changed.gifts.cg',  refresh);

    // First fill
    this.populatePool();
  },

  populatePool() {
    const sel = document.querySelector(this._selId);
    if (!sel) return;

    sel.innerHTML = '';
    const ph = document.createElement('option');
    ph.value = ''; ph.textContent = '— Select Gift —';
    sel.appendChild(ph);

    // DEMO POOL: Use skills list so it obviously changes.
    const s = document.querySelector('#cg-species')?.value || '';
    const c = document.querySelector('#cg-career')?.value || '';
    const base = (window.CG_SKILLS_LIST || []);

    const pool = base.filter(x =>
      (!s || x.name.toLowerCase().includes('a')) ||
      (!c || x.name.toLowerCase().includes('s'))
    ).slice(0, 40);

    pool.forEach(x => {
      const o = document.createElement('option');
      o.value = x.id; o.textContent = x.name;
      sel.appendChild(o);
    });

    log('Pool updated', {species: s || '—', career: c || '—', count: pool.length});
  }
};

export default FreeChoices;
