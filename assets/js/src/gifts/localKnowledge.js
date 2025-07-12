// assets/js/gifts/localKnowledge.js

const LocalKnowledge = {
  // Holds the last‐entered local knowledge text
  loadedLocalText: '',

  // Called from index.js to kick things off
  init() {
    this.loadLocalKnowledge();
  },

  /**
   * Fetches the “local knowledge” gift and renders a dropdown + text input.
   * @param {string} prefill  Optional text to prefill the input with
   */
  loadLocalKnowledge(prefill = '') {
    // Preserve previously entered value if no new prefill
    this.loadedLocalText = prefill || this.loadedLocalText || '';

    const $container = jQuery('#cg-local-knowledge');
    if (!$container.length) return;

    jQuery.post(CG_Ajax.ajax_url, {
      action:   'cg_get_local_knowledge',
      security: CG_Ajax.nonce
    }).done(res => {
      if (!res.success) return;

      const g = res.data;
      // Render the gift dropdown and a text input for the area
      const dropdown = CG_GiftUtils.renderDropdown(g.ct_gifts_name, g.ct_gifts_name);
      const input    = `<input
                          type="text"
                          id="cg-local-knowledge-area"
                          placeholder="Enter area"
                          value="${this.loadedLocalText}"
                        />`;

      $container.html(`${dropdown}${input}`);
    });
  }
};

export default LocalKnowledge;
