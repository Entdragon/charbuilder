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

function renderGiftList($ul, items = []) {
  if (!$ul || !$ul.length) return;
  $ul.empty();
  items.forEach(txt => {
    if (!txt) return;
    $ul.append(`<li>${txt}</li>`);
  });
}

function namesFromProfile(profile = {}) {
  // Prefer gift_1..gift_3 (names), otherwise nothing
  const names = [profile.gift_1, profile.gift_2, profile.gift_3]
    .map(v => (v == null ? '' : String(v)))
    .filter(Boolean);
  // Add × manifold if > 1
  return names.map((name, i) => {
    const m = profile[`manifold_${i+1}`];
    const mult = parseInt(m, 10) || 1;
    return mult > 1 ? `${name} × ${mult}` : name;
  });
}

export default function bindSpeciesEvents() {
  if (_bound) return;
  _bound = true;

  $(document)
    .off('change.cg', '#cg-species')
    .on('change.cg', '#cg-species', e => {
      const val = (e.currentTarget && e.currentTarget.value) || '';
      console.log('[SpeciesEvents] selected species →', val);

      if (!val) {
        SpeciesAPI.currentProfile = null;
        renderGiftList($('#species-gift-block'), []);
        $(document).trigger('cg:species:changed', [{ id: '', profile: null }]);
        return;
      }

      SpeciesAPI.fetchProfile(val).done(profileRaw => {
        const profile = normalizeSpeciesProfile(profileRaw || {});
        SpeciesAPI.currentProfile = profile;

        // Render gifts (from profile)
        const giftNames = namesFromProfile(profile);
        renderGiftList($('#species-gift-block'), giftNames);

        $(document).trigger('cg:species:changed', [{ id: String(val), profile }]);
      });
    });
}
