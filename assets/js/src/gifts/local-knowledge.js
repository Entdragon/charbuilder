import API from './api.js';
const $ = window.jQuery;

export default {
  init() {
    const $container = $('#cg-local-knowledge');
    API.fetchLocalKnowledge(data => {
      $container.text(data.name);
    });
  }
};
