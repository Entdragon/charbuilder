// assets/js/src/utils/ajax-logger.js
import Logger from './logger.js';
const $ = window.jQuery;

function parseData(data) {
  if (typeof data === 'string') {
    return data
      .split('&')
      .map(pair => pair.split('='))
      .reduce((obj, [k,v]) => {
        obj[k] = decodeURIComponent(v || '');
        return obj;
      }, {});
  }
  return data;
}

$(document).ajaxSend((e, jqxhr, settings) => {
  const payload = parseData(settings.data);
  Logger.log('Ajax→Send', settings.url, payload);
});

$(document).ajaxComplete((e, jqxhr, settings) => {
  Logger.log('Ajax←Done', settings.url, jqxhr.responseJSON);
});

$(document).ajaxError((e, jqxhr, settings, err) => {
  Logger.error('Ajax✖Error', settings.url, err);
});
