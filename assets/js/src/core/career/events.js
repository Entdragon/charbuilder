// assets/js/src/core/career/events.js
// One-time, namespaced bindings for Career select.
// Fetches + normalizes profile, stores it on CareerAPI.currentProfile,
// renders Profile tab gifts, and announces via cg:career:changed.

import CareerAPI from './api.js';

const $ = window.jQuery;
let _bound = false;

function normalizeCareerProfile(raw = {}) {
  const out = { ...raw };

  // Readable name used by Skills/Summary headers
  out.careerName =
    raw.careerName ||
    raw.career_name ||
    raw.ct_career_name ||
    raw.name ||
    raw.title ||
    '';

  // Ensure skill ids are strings (Skills module compares as strings)
  if (raw.skill_one   != null) out.skill_one   = String(raw.skill_one);
  if (raw.skill_two   != null) out.skill_two   = String(raw.skill_two);
  if (raw.skill_three != null) out.skill_three = String(raw.skill_three);

  // Gifts (keep id fields if present; Traits service references gift_id_*)
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

export default function bindCareerEvents() {
  if (_bound) return;
  _bound = true;

  $(document)
    .off('change.cg', '#cg-career')
    .on('change.cg', '#cg-career', e => {
      const val = (e.currentTarget && e.currentTarget.value) || '';
      console.log('[CareerEvents] selected career →', val);

      // Clear UI when nothing selected
      if (!val) {
        CareerAPI.currentProfile = null;
        renderGiftList($('#career-gifts'), []);
        $(document).trigger('cg:career:changed', [{ id: '', profile: null }]);
        return;
      }

      // Fetch the profile, store it, show gifts, and broadcast
      CareerAPI.fetchProfile(val).done(profileRaw => {
        const profile = normalizeCareerProfile(profileRaw || {});
        CareerAPI.currentProfile = profile;

        // Try to render gifts from profile; if empty, fall back to gifts endpoint
        const $ul = $('#career-gifts');
        const fromProfile = namesFromProfile(profile);

        if (fromProfile.length) {
          renderGiftList($ul, fromProfile);
          $(document).trigger('cg:career:changed', [{ id: String(val), profile }]);
        } else {
          CareerAPI.fetchGifts(val).done(gifts => {
            const giftNames = []
              .concat(gifts || [])
              .map(g => (g && typeof g === 'object') ? (g.name || g.title || g.ct_gift_name || '') : String(g))
              .filter(Boolean);
            renderGiftList($ul, giftNames);
            $(document).trigger('cg:career:changed', [{ id: String(val), profile }]);
          });
        }
      });
    });
}
