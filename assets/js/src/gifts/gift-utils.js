;(function($){
  window.CG_GiftUtils = {
    /**
     * Render a labeled dropdown.
     * @param {string} label - Label text
     * @param {string} value - Default selected value
     * @param {Array<string>} options - Dropdown options
     * @param {boolean} editable - Whether dropdown should be enabled
     * @param {string} name - Name attribute for <select> (optional)
     * @returns {string} HTML
     */
    renderDropdown(label, value, options = [], editable = false, name = '') {
      const opts = options.length
        ? options.map(v => `<option value="${v}"${v === value ? ' selected' : ''}>${v}</option>`).join('')
        : `<option>${value || '—'}</option>`;

      const props = editable ? `name="${name}"` : 'disabled';
      return `
        <label><strong>${label}</strong></label>
        <select ${props}>${opts}</select>
      `;
    }
  };
})(jQuery);
