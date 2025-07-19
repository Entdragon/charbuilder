import API from './api.js';
const $ = window.jQuery;

export default {
  init() {
    const $container = $('#cg-language');
    API.fetchLanguageGift(data => {
      $container.text(data.name);
    });
  }
};
