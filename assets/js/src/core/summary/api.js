// assets/js/src/core/summary/api.js

import FormBuilderAPI from '../formBuilder';
import * as Utils     from './utils.js';
import TraitsService  from '../traits/service.js';
import SpeciesAPI     from '../species/api.js';
import CareerAPI      from '../career/api.js';

const $ = window.jQuery;
const TRAITS   = TraitsService.TRAITS;
const MARK_DIE = { 1: 'd4', 2: 'd6', 3: 'd8' };

const SummaryAPI = {
  /**
   * Entry point when the Summary tab is shown.
   */
  init() {
    // CG HARDEN: make init() idempotent
    // builder-refresh may call init() many times; this prevents duplicate bindings.
    if (this.__cg_inited) {
      try {
        // Still refresh summary from current builder state on repeated calls
        if (typeof window !== 'undefined' && window.FormBuilderAPI && typeof window.FormBuilderAPI.getData === 'function') {
          this.renderSummary(window.FormBuilderAPI.getData() || {});
            // Keep Summary bindings alive even on repeated init() calls
            try { this.bindExportButton();

      // Live updates: keep #cg-summary-sheet in sync while typing on other tabs
      this.bindLiveUpdates(); } catch (_) {}
            try { this.bindLiveUpdates(); } catch (_) {}

        }
      } catch (_) {}
      return;
    }
    this.__cg_inited = true;
    const data = FormBuilderAPI.getData();
    console.log('[SummaryAPI] init — builder state:', data);

    // 1) Render all summary sections
    this.renderSummary(data);

    

      // CG HARDEN: auto-render on form changes
      this.bindAutoRender();// 2) Wire up the Export to PDF button
    this.bindExportButton();
  },

  /**
   * Build and inject the full summary into #cg-summary-sheet.
   */
  renderSummary(data = {}) {
    const $sheet = $('#cg-summary-sheet').empty();

    // Basic Info
    const name   = data.name   || '—';
    const age    = data.age    || '—';
    const gender = data.gender || '—';
    const motto  = data.motto  || '—';

    // Goals
    const goals = [1, 2, 3]
      .map(i => data[`goal${i}`] || '—')
      .filter(v => v !== '—')
      .join(', ')
      || '—';

    // Description & Backstory
    const description = data.description || '—';
    const backstory   = data.backstory   || '—';

    // Species & Career
    const species = SpeciesAPI.currentProfile || {};
    const career  = CareerAPI.currentProfile  || {};

    // Skills, Marks, Battle
    const skills = window.CG_SKILLS_LIST || [];
    const marks  = data.skillMarks  || {};
    const battle = data.battle      || [];

    // Assemble HTML
    let html = `
      <div class="summary-section summary-basic">
        <h2>${name}</h2>
        <p><strong>Age:</strong> ${age}</p>
        <p><strong>Gender:</strong> ${gender}</p>
        <p><strong>Motto:</strong> ${motto}</p>
      </div>

      <div class="summary-section summary-goals">
        <p><strong>Goals:</strong> ${goals}</p>
      </div>

      <div class="summary-section summary-description">
        <p><strong>Description:</strong> ${description}</p>
      </div>

      <div class="summary-section summary-backstory">
        <p><strong>Backstory:</strong> ${backstory}</p>
      </div>

      <div class="summary-section summary-species">
        <h3>Species: ${species.speciesName || '—'}</h3>
        <ul>
    `;
    ['gift_1','gift_2','gift_3'].forEach((_, idx) => {
      const gift = species[`gift_${idx+1}`];
      const mult = species[`manifold_${idx+1}`] || 1;
      if (gift) {
        html += `<li><strong>Gift ${idx+1}:</strong> ${gift} × ${mult}</li>`;
      }
    });
    html += `</ul></div>`;

    html += `
      <div class="summary-section summary-career">
        <h3>Career: ${career.careerName || '—'}</h3>
        <ul>
    `;
    ['gift_1','gift_2','gift_3'].forEach((_, idx) => {
      const gift = career[`gift_${idx+1}`];
      const mult = career[`manifold_${idx+1}`] || 1;
      if (gift) {
        html += `<li><strong>Gift ${idx+1}:</strong> ${gift} × ${mult}</li>`;
      }
    });
    html += `</ul></div>`;

    html += `
      <div class="summary-section summary-traits">
        <h3>Traits</h3>
        <ul>
    `;
    TRAITS.forEach(key => {
      let label = key.replace(/^trait_/, '');
      if (label === 'species') label = 'Species';
      else if (label === 'career') label = 'Career';
      else label = Utils.capitalize(label);

      const base  = data[key] || '—';
      const boost = TraitsService.getBoostedDie(key);
      const display = boost ? `${base} → ${boost}` : base;

      html += `<li><strong>${label}:</strong> ${display}</li>`;
    });
    html += `</ul></div>`;

    html += `
      <div class="summary-section summary-skills">
        <h3>Skills</h3>
        <table class="cg-summary-skills">
          <thead><tr><th>Skill</th><th>Dice Pool</th></tr></thead>
          <tbody>
    `;
    const spIds = [species.skill_one, species.skill_two, species.skill_three].map(String);
    const cpIds = [career.skill_one,  career.skill_two,  career.skill_three].map(String);

    skills.forEach(skill => {
      const id = String(skill.id);
      const sp = spIds.includes(id) ? 'd4' : '';
      const cp = cpIds.includes(id) ? 'd6' : '';
      const mk = MARK_DIE[marks[id]] || '';
      const pool = [sp, cp, mk].filter(Boolean).join(' + ') || '—';
      html += `<tr><td>${skill.name}</td><td>${pool}</td></tr>`;
    });
    html += `
          </tbody>
        </table>
      </div>
    `;

    if (battle.length) {
      html += `
        <div class="summary-section summary-battle">
          <h3>Battle</h3>
          <ul>
      `;
      battle.forEach(item => {
        html += `<li><strong>${Utils.capitalize(item.key)}:</strong> ${item.value}</li>`;
      });
      html += `</ul></div>`;
    }

    // Inject all HTML
    $sheet.html(html);
  },
  // CG HARDEN: live summary updates across tabs
  bindLiveUpdates() {
    // One-time binding
    if (this.__cg_live_bound) return;
    this.__cg_live_bound = true;
  
    const sel = '#cg-modal input, #cg-modal select, #cg-modal textarea';
    const self = this;
  
    function scheduleRender(e) {
      try {
        // Only if the summary sheet exists in DOM
        if (!document.getElementById('cg-summary-sheet')) return;
  
        const t = e && e.target;
        if (!t) return;
  
        // Ignore anything inside the summary sheet itself (avoid feedback loops)
        if (t.closest && t.closest('#cg-summary-sheet')) return;
  
        clearTimeout(self.__cg_live_timer);
        self.__cg_live_timer = setTimeout(() => {
          try {
            const data = (FormBuilderAPI && typeof FormBuilderAPI.getData === 'function')
              ? (FormBuilderAPI.getData() || {})
              : ((window.FormBuilderAPI && typeof window.FormBuilderAPI.getData === 'function')
                ? (window.FormBuilderAPI.getData() || {})
                : {});
            self.renderSummary(data);
          } catch (err) {
            console.warn('[SummaryAPI] live update failed', err);
          }
        }, 200);
      } catch (_) {}
    }
  
    // Delegated + namespaced (idempotent)
    $(document)
      .off('input.cgSummary change.cgSummary', sel)
      .on('input.cgSummary change.cgSummary', sel, scheduleRender);
  
    // First sync (in case user already typed before Summary tab was opened)
    try { this.renderSummary(FormBuilderAPI.getData() || {}); } catch (_) {}
  },


  /**
   * Open a new window, inject the summary + CSS, and print it.
   */

  // CG HARDEN: bindAutoRender (live summary updates)
  // - Debounced re-render so typing doesn't spam heavy DOM work.
  _scheduleRender(delayMs = 150) {
    try {
      if (this.__cg_render_timer) clearTimeout(this.__cg_render_timer);
      this.__cg_render_timer = setTimeout(() => {
        this.__cg_render_timer = null;
        try {
          const data = (typeof FormBuilderAPI !== 'undefined' && FormBuilderAPI.getData)
            ? (FormBuilderAPI.getData() || {})
            : (window.FormBuilderAPI?.getData?.() || {});
          this.renderSummary(data);
        } catch (_) {}
      }, delayMs);
    } catch (_) {}
  },

  bindAutoRender() {
    // Idempotent: bind only once per page-load
    if (this.__cg_autobound) return;
    this.__cg_autobound = true;

    // Remove old listeners if they exist (paranoia / hot reload)
    try {
      if (this.__cg_auto_handler) {
        document.removeEventListener('input',  this.__cg_auto_handler, true);
        document.removeEventListener('change', this.__cg_auto_handler, true);
      }
    } catch (_) {}

    this.__cg_auto_handler = (e) => {
      try {
        const t = e && e.target;
        if (!t) return;
        const modal = document.getElementById('cg-modal');
        if (!modal || !modal.contains(t)) return;

        // input = typing (debounce a bit), change = selects/radios (render sooner)
        const isChange = (e.type === 'change');
        this._scheduleRender(isChange ? 0 : 150);
      } catch (_) {}
    };

    document.addEventListener('input',  this.__cg_auto_handler, true);
    document.addEventListener('change', this.__cg_auto_handler, true);
  },

  bindExportButton() {
    $(document)
      .off('click', '#cg-export-pdf')
      .on('click', '#cg-export-pdf', e => {
        e.preventDefault();
        console.log('[SummaryAPI] Export to PDF clicked');

        // 1) Grab the summary HTML
        const sheetHtml = document
          .getElementById('cg-summary-sheet')
          .outerHTML;

        // 2) Collect current <link rel="stylesheet"> tags
        const cssLinks = Array.from(
          document.querySelectorAll('link[rel="stylesheet"]')
        )
        .map(link => link.outerHTML)
        .join('\n');

        // 3) Open a print window and write minimal HTML
        const printWin = window.open('', '_blank', 'width=800,height=600');
        printWin.document.open();
        printWin.document.write(`
          <!doctype html>
          <html>
            <head>
              <meta charset="utf-8">
              <title>Character Sheet</title>
              ${cssLinks}
              <style>
                @page { margin: 1cm; }
                body { margin:0; padding:0; }
              </style>
            </head>
            <body>
              ${sheetHtml}
            </body>
          </html>
        `);
        printWin.document.close();
        printWin.focus();

        // 4) Trigger print and close window
        setTimeout(() => {
          printWin.print();
          printWin.close();
        }, 300);
      });
  }
};

export default SummaryAPI;
