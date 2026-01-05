// binds DOM events for traits

import TraitsService from './service.js';

const $ = window.jQuery;

// Coalesce multiple Traits refresh requests into at most one refresh per tick.
// Also prevents re-entrant storms if upstream events fire while refreshAll() is running.
let _cgTraitsRefreshQueued  = false;
let _cgTraitsRefreshRunning = false;
let _cgTraitsRefreshPending = false;

function scheduleTraitsRefresh(reason = 'auto') {
  // If we're mid-refresh, mark that we owe one more refresh and bail.
  if (_cgTraitsRefreshRunning) {
    _cgTraitsRefreshPending = true;
    return;
  }

  if (_cgTraitsRefreshQueued) return;
  _cgTraitsRefreshQueued = true;

  const run = () => {
    _cgTraitsRefreshQueued = false;
    _cgTraitsRefreshRunning = true;

    try {
      TraitsService.refreshAll();
    } catch (e) {
      console.error('[Traits] refreshAll failed', e);
    } finally {
      _cgTraitsRefreshRunning = false;

      // If any upstream event arrived during refresh, do one more pass next tick.
      if (_cgTraitsRefreshPending) {
        _cgTraitsRefreshPending = false;
        scheduleTraitsRefresh('pending');
      }
    }
  };

  if (typeof requestAnimationFrame === 'function') requestAnimationFrame(run);
  else setTimeout(run, 0);
}

let bound = false;

// Stable handler reference (never inline anonymous in addEventListener)
function onTraitsRefreshAll() {
  scheduleTraitsRefresh('event');
}

export default {
  bind() {
    if (bound) return;
    bound = true;

    // on initial page load (or panel insert)
    $(function () {
      scheduleTraitsRefresh('init');
    });

    // re-apply limits & labels on any dropdown change
    $(document)
      .off('change.cgtraits', '.cg-trait-select')
      .on('change.cgtraits', '.cg-trait-select', () => {
        scheduleTraitsRefresh('trait-change');
      });

    // refresh when gifts/species/career change, or when extra-career / boost-target changes
    const jqEvents = [
      'cg:free-gift:changed.cgtraits',
      'cg:species:changed.cgtraits',
      'cg:career:changed.cgtraits',
      'cg:traits:changed.cgtraits',
      'cg:extra-careers:changed.cgtraits',
    ].join(' ');

    $(document)
      .off(jqEvents)
      .on(jqEvents, () => {
        scheduleTraitsRefresh('upstream');
      });

    // also catch native CustomEvent dispatches (idempotent bind via window.__CG_EVT__)
    const nativeEvents = [
      'cg:free-gift:changed',
      'cg:species:changed',
      'cg:career:changed',
      'cg:traits:changed',
      'cg:extra-careers:changed',
    ];

    nativeEvents.forEach((evt) => {
      try {
        window.__CG_EVT__ = window.__CG_EVT__ || {};
        const EVT = window.__CG_EVT__;
        const key = `traitsRefreshAll_${evt}`;

        if (EVT[key]) {
          try { document.removeEventListener(evt, EVT[key]); } catch (_) {}
        }

        EVT[key] = onTraitsRefreshAll;
        document.addEventListener(evt, EVT[key]);
      } catch (e) {
        // Best-effort: named handler (still non-anonymous)
        try { document.addEventListener(evt, onTraitsRefreshAll); } catch (_) {}
      }
    });
  }
};
