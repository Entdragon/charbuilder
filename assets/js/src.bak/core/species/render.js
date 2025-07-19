const $ = window.jQuery;

export default {
  renderGifts(profile) {
    const gifts = [
      { label: 'Species Gift One', name: profile.gift_1 },
      { label: 'Species Gift Two', name: profile.gift_2 },
      { label: 'Species Gift Three', name: profile.gift_3 }
    ].filter(g => g.name);

    const html = gifts
      .map(g => `<li><strong>${g.label}:</strong> ${g.name}</li>`)
      .join('');

    $('#species-gift-block').html(html);
  },

  clearUI() {
    $('#species-gift-block').empty();
    $('#cg-species').val('');
  }
};
