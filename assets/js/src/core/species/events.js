// assets/js/src/core/species/events.js
// One-time, namespaced bindings for Species select.
// Fetches + normalizes profile, stores it on SpeciesAPI.currentProfile,
// renders Profile tab species gifts, and announces via cg:species:changed.

import SpeciesAPI from './api.js';

const $ = window.jQuery;
let _bound = false;

// Normalize various backend shapes into something our renderers expect.
function normalizeSpeciesProfile(raw = {}) {
  const out = { ...raw };

  // A readable name used by Skills/Summary headers
  out.speciesName =
    raw.speciesName ||
    raw.species_name ||
    raw.ct_species_name ||
    raw.name ||
    raw.title ||
    '';

  // Ensure skill ids are strings (Skills module compares as strings)
  if (raw.skill_one   != null) out.skill_one   = String(raw.skill_one);
  if (raw.skill_two   != null) out.skill_two   = String(raw.skill_two);
  if (raw.skill_three != null) out.skill_three = String(raw.skill_three);

  // Gifts referenced by Traits service (id fields)
  ['gift_id_1', 'gift_id_2', 'gift_id_3'].forEach(k => {
    if (raw[k] != null) out[k] = String(raw[k]);
  });

  // Manifold counts (default 1)
  ['manifold_1', 'manifold_2', 'manifold_3'].forEach(k => {
    if (raw[k] != null) out[k] = parseInt(raw[k], 10) || 1;
  });

  return out;
}

function escapeHtml(s) {
  return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function giftEffect(giftId) {
  if (!giftId) return '';
  const fc = window.CG_FreeChoices;
  const all = (fc && Array.isArray(fc._allGifts)) ? fc._allGifts : [];
  const g = all.find(g => String(g.ct_id || g.id || '') === String(giftId));
  if (!g) return '';
  return String(g.effect ?? '').trim() || String(g.effect_description ?? g.ct_gifts_effect_description ?? '').trim();
}

function renderGiftList($ul, items = []) {
  if (!$ul || !$ul.length) return;
  $ul.empty();
  items.forEach(item => {
    if (!item) return;
    const display = typeof item === 'string' ? item : item.display;
    const eff     = typeof item === 'string' ? '' : (item.eff || '');
    if (!display) return;
    $ul.append(`<li>${escapeHtml(display)}${eff ? `<span class="cg-gift-effect-inline"> — ${escapeHtml(eff)}</span>` : ''}</li>`);
  });
}

function namesFromProfile(profile = {}) {
  const entries = [
    { name: profile.gift_1, id: profile.gift_id_1, m: profile.manifold_1 },
    { name: profile.gift_2, id: profile.gift_id_2, m: profile.manifold_2 },
    { name: profile.gift_3, id: profile.gift_id_3, m: profile.manifold_3 },
  ].filter(e => e.name);

  return entries.map(e => {
    const mult    = parseInt(e.m, 10) || 1;
    const display = mult > 1 ? `${e.name} \xd7 ${mult}` : e.name;
    const eff     = giftEffect(e.id);
    return { display, eff };
  });
}

export default function bindSpeciesEvents() {
  if (_bound) return;
  _bound = true;

  $(document)
    .off('change.cg', '#cg-species')
    .on('change.cg', '#cg-species', e => {
      const val = (e.currentTarget && e.currentTarget.value) || '';
      console.log('[SpeciesEvents] selected species \u2192', val);

      if (!val) {
        SpeciesAPI.currentProfile = null;
        renderGiftList($('#species-gift-block'), []);
        $(document).trigger('cg:species:changed', [{ id: '', profile: null }]);
        return;
      }

      SpeciesAPI.fetchProfile(val).done(profileRaw => {
        const profile = normalizeSpeciesProfile(profileRaw || {});
        SpeciesAPI.currentProfile = profile;

        // Render gifts (from profile) with short effect descriptions
        const giftItems = namesFromProfile(profile);
        renderGiftList($('#species-gift-block'), giftItems);

        $(document).trigger('cg:species:changed', [{ id: String(val), profile }]);
      });
    });
}
