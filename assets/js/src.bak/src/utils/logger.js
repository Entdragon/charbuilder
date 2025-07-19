// assets/js/src/utils/logger.js

/**
 * A simple logger utility for structured console output.
 */
class Logger {
  constructor(namespace) {
    this.ns = namespace || 'app';
  }

  log(...args) {
    console.log(`[${this.ns}]`, ...args);
  }

  warn(...args) {
    console.warn(`[${this.ns}]`, ...args);
  }

  error(...args) {
    console.error(`[${this.ns}]`, ...args);
  }
}

export default Logger;
