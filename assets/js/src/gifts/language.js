// assets/js/gifts/language.js

const Language = {
  // Holds the last‐entered language text
  loadedLangText: '',

  // Called from index.js to kick things off
  init() {
    this.loadLanguage();
  },

  /**
   * Fetches the “language” gift and renders a dropdown + text input.
   * @param {string} prefill  Optional text to prefill the input with
   */
  loadLanguage(prefill = '') {
    // Preserve previously entered value if no new prefill
    this.loadedLangText = prefill || this.loadedLangText || '';

    const $container = jQuery('#cg-language');
    if (!$container.length) return;

    jQuery.post(CG_Ajax.ajax_url, {
      action:   'cg_get_language_gift',
      security: CG_Ajax.nonce
    }).done(res => {
      if (!res.success) return;

      const g = res.data;
      // Render the gift dropdown and a text input for specifying the language
      const dropdown = CG_GiftUtils.renderDropdown(g.ct_gifts_name, g.ct_gifts_name);
      const input    = `<input
                          type="text"
                          id="cg-language-area"
                          placeholder="Specify language"
                          value="${this.loadedLangText}"
                        />`;

      $container.html(`${dropdown}${input}`);
    });
  }
};

export default Language;
