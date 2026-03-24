const $ = window.jQuery;

function giftEffect(giftId) {
  if (!giftId) return '';
  const fc = window.CG_FreeChoices;
  const allGifts = (fc && Array.isArray(fc._allGifts)) ? fc._allGifts : [];
  const g = allGifts.find(g => String(g.ct_id || g.id || '') === String(giftId));
  if (!g) return '';
  return String(g.effect ?? '').trim() || String(g.effect_description ?? g.ct_gifts_effect_description ?? '').trim();
}

export default {
  renderGifts(profile) {
    const gifts = [
      { label: 'Species Gift One', name: profile.gift_1, id: profile.gift_id_1 },
      { label: 'Species Gift Two', name: profile.gift_2, id: profile.gift_id_2 },
      { label: 'Species Gift Three', name: profile.gift_3, id: profile.gift_id_3 }
    ].filter(g => g.name);

    const html = gifts.map(g => {
      const desc = giftEffect(g.id);
      return `<li><strong>${g.label}:</strong> ${g.name}${desc ? `<span class="cg-gift-effect-inline"> — ${desc}</span>` : ''}</li>`;
    }).join('');

    $('#species-gift-block').html(html);
  },

  clearUI() {
    $('#species-gift-block').empty();
    $('#cg-species').val('');
  }
};
