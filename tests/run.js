/**
 * Character Generator — Integration Test Suite
 *
 * Runs against a live server (default: http://localhost:5000).
 *
 * Usage:
 *   node tests/run.js
 *   CG_TEST_URL=https://my-deploy.repl.co node tests/run.js
 *
 * The script registers a fresh throw-away user on each run and tears
 * down any character records it created, so it is safe to run repeatedly.
 */

'use strict';

const BASE = process.env.CG_TEST_URL || 'http://localhost:5000';
const TIMESTAMP = Date.now();
const TEST_USER = `_testuser_${TIMESTAMP}`;
const TEST_PASS = `TestPass_${TIMESTAMP}!`;
const TEST_EMAIL = `${TEST_USER}@example.com`;

// ── Minimal cookie jar ────────────────────────────────────────────────────────
class CookieJar {
  constructor() { this._cookies = {}; }

  ingest(headers) {
    // Node 18+ native fetch: use getSetCookie() to get each Set-Cookie header
    // separately (headers.get('set-cookie') incorrectly joins them with commas).
    let parts;
    if (typeof headers.getSetCookie === 'function') {
      parts = headers.getSetCookie();
    } else {
      const raw = headers.get ? headers.get('set-cookie') : headers['set-cookie'];
      parts = raw ? (Array.isArray(raw) ? raw : [raw]) : [];
    }
    for (const part of parts) {
      const [pair] = part.split(';');
      if (!pair) continue;
      const eq = pair.indexOf('=');
      if (eq < 0) continue;
      this._cookies[pair.slice(0, eq).trim()] = pair.slice(eq + 1).trim();
    }
  }

  header() {
    return Object.entries(this._cookies).map(([k, v]) => `${k}=${v}`).join('; ');
  }

  clear() { this._cookies = {}; }
}

const jar = new CookieJar();

// ── HTTP helpers ──────────────────────────────────────────────────────────────
// Common headers that simulate a request arriving through the Replit HTTPS proxy.
// The server has "trust proxy: 1", so it reads X-Forwarded-Proto to determine
// req.secure — required for express-session to set its Secure cookie.
const PROXY_HEADERS = {
  'x-forwarded-proto': 'https',
  'x-forwarded-for':   '127.0.0.1',
};

async function get(path) {
  const r = await fetch(`${BASE}${path}`, {
    headers: { ...PROXY_HEADERS, cookie: jar.header() },
  });
  jar.ingest(r.headers);
  const text = await r.text();
  try { return { status: r.status, body: JSON.parse(text) }; }
  catch { return { status: r.status, body: text }; }
}

async function post(action, data = {}) {
  const params = new URLSearchParams({ action, nonce: '1', security: '1', ...data });
  const r = await fetch(`${BASE}/api/ajax`, {
    method:  'POST',
    headers: {
      ...PROXY_HEADERS,
      'content-type': 'application/x-www-form-urlencoded',
      cookie:          jar.header(),
    },
    body:    params.toString(),
  });
  jar.ingest(r.headers);
  return { status: r.status, body: await r.json() };
}

async function postJson(path, data) {
  const r = await fetch(`${BASE}${path}`, {
    method:  'POST',
    headers: {
      ...PROXY_HEADERS,
      'content-type': 'application/json',
      cookie:          jar.header(),
    },
    body:    JSON.stringify(data),
  });
  jar.ingest(r.headers);
  return { status: r.status, body: await r.json() };
}

// ── Test runner ───────────────────────────────────────────────────────────────
let passed = 0, failed = 0, skipped = 0;
let currentSection = '';

function section(name) {
  currentSection = name;
  console.log(`\n  ${name}`);
  console.log('  ' + '─'.repeat(name.length));
}

function pass(label) {
  passed++;
  console.log(`  \x1b[32m✔\x1b[0m  ${label}`);
}

function fail(label, reason) {
  failed++;
  console.log(`  \x1b[31m✘\x1b[0m  ${label}`);
  if (reason !== undefined) console.log(`       \x1b[2m↳ ${JSON.stringify(reason)}\x1b[0m`);
}

function skip(label, reason) {
  skipped++;
  console.log(`  \x1b[33m⊘\x1b[0m  ${label} \x1b[2m(${reason})\x1b[0m`);
}

function expect(label, condition, detail) {
  if (condition) pass(label);
  else fail(label, detail);
}

// ── Test state ────────────────────────────────────────────────────────────────
let savedCharId = null;
let speciesId   = null;
let careerId    = null;

// ── Tests ─────────────────────────────────────────────────────────────────────

async function testServerHealth() {
  section('Server Health');

  const { status, body } = await get('/');
  expect(
    'GET / returns 200',
    status === 200,
    status
  );
  expect(
    'Response is HTML',
    typeof body === 'string' && body.includes('<!DOCTYPE html'),
    typeof body
  );
  expect(
    'Contains correct app title',
    typeof body === 'string' && body.includes('Library of Calabria'),
    'title not found'
  );
}

async function testUnauthenticated() {
  section('Unauthenticated Behaviour');

  const me = await get('/api/auth/me');
  expect('GET /api/auth/me → not logged in', me.body.success === false, me.body);

  const load = await post('cg_load_characters');
  expect('cg_load_characters without session → 401', load.status === 401, load.status);
  expect('cg_load_characters without session → success:false', load.body.success === false, load.body);

  const save = await post('cg_save_character', { 'character[name]': 'Hacked' });
  expect('cg_save_character without session → 401', save.status === 401, save.status);

  const unknown = await post('cg_totally_unknown_action');
  expect('Unknown action → success:false', unknown.body.success === false, unknown.body);

  const noAction = await post('');
  expect('Missing action → success:false', noAction.body.success === false, noAction.body);
}

async function testLoginFailures() {
  section('Login Failures');

  const empty = await post('cg_login_user', { username: '', password: '' });
  expect('Empty credentials → success:false', empty.body.success === false, empty.body);

  const badPass = await post('cg_login_user', { username: 'admin', password: 'definitely-wrong-password' });
  expect('Wrong password → success:false', badPass.body.success === false, badPass.body);

  const noUser = await post('cg_login_user', { username: 'no_such_user_xyzzy', password: 'whatever' });
  expect('Unknown user → success:false', noUser.body.success === false, noUser.body);
}

async function testRegistration() {
  section('Registration & Login');

  // Register new user
  const reg = await post('cg_register_user', {
    username: TEST_USER,
    email:    TEST_EMAIL,
    password: TEST_PASS,
  });
  expect('Register new user → success:true', reg.body.success === true, reg.body);

  // Check session
  const me = await get('/api/auth/me');
  expect('/api/auth/me after register → success:true', me.body.success === true, me.body);
  expect('/api/auth/me returns correct username', me.body.data?.username === TEST_USER, me.body.data);

  // Duplicate username
  const dup = await post('cg_register_user', {
    username: TEST_USER,
    email:    'other@example.com',
    password: TEST_PASS,
  });
  expect('Duplicate username → success:false', dup.body.success === false, dup.body);

  // Logout
  const out = await post('cg_logout_user');
  expect('Logout → success:true', out.body.success === true, out.body);

  // Confirm session gone
  const meOut = await get('/api/auth/me');
  expect('/api/auth/me after logout → success:false', meOut.body.success === false, meOut.body);

  // Log back in
  jar.clear();
  const login = await post('cg_login_user', { username: TEST_USER, password: TEST_PASS });
  expect('Login with registered credentials → success:true', login.body.success === true, login.body);
}

async function testReferenceData() {
  section('Reference Data');

  const species = await post('cg_get_species_list');
  expect('cg_get_species_list → success:true',      species.body.success === true,     species.body);
  expect('Species list is non-empty array',          Array.isArray(species.body.data) && species.body.data.length > 0, species.body.data?.length);
  speciesId = species.body.data?.[0]?.id ?? null;
  if (speciesId) pass(`First species ID: ${speciesId} ("${species.body.data[0].name}")`);

  const careers = await post('cg_get_career_list');
  expect('cg_get_career_list → success:true',        careers.body.success === true,     careers.body);
  expect('Career list is non-empty array',           Array.isArray(careers.body.data) && careers.body.data.length > 0, careers.body.data?.length);
  careerId = careers.body.data?.[0]?.id ?? null;
  if (careerId) pass(`First career ID: ${careerId} ("${careers.body.data[0].name}")`);

  const gifts = await post('cg_get_free_gifts');
  expect('cg_get_free_gifts → success:true',         gifts.body.success === true,       gifts.body);
  expect('Free gifts list is non-empty array',       Array.isArray(gifts.body.data) && gifts.body.data.length > 0, gifts.body.data?.length);

  const skills = await post('cg_get_skills_list');
  expect('cg_get_skills_list → success:true',        skills.body.success === true,      skills.body);
  expect('Skills list is non-empty array',           Array.isArray(skills.body.data) && skills.body.data.length > 0, skills.body.data?.length);

  const localKnow = await post('cg_get_local_knowledge');
  expect('cg_get_local_knowledge → success:true',    localKnow.body.success === true,   localKnow.body);

  const lang = await post('cg_get_language_gift');
  expect('cg_get_language_gift → success:true',      lang.body.success === true,        lang.body);

  if (careerId) {
    const careerGifts = await post('cg_get_career_gifts', { id: String(careerId) });
    expect('cg_get_career_gifts → success:true',     careerGifts.body.success === true, careerGifts.body);
  } else {
    skip('cg_get_career_gifts', 'no careerId available');
  }

  if (speciesId) {
    const profile = await post('cg_get_species_profile', { id: String(speciesId) });
    expect('cg_get_species_profile → success:true',  profile.body.success === true,     profile.body);
  } else {
    skip('cg_get_species_profile', 'no speciesId available');
  }
}

async function testCharacterCRUD() {
  section('Character CRUD');

  // Initial load
  const list0 = await post('cg_load_characters');
  expect('cg_load_characters → success:true', list0.body.success === true, list0.body);
  expect('Returns array of characters',       Array.isArray(list0.body.data),          list0.body.data);
  const countBefore = list0.body.data?.length ?? 0;

  // Create new character
  const saveNew = await post('cg_save_character', {
    'character[name]':        'Aldric the Bold',
    'character[player_name]': 'Test Player',
    'character[age]':         '28',
    'character[gender]':      'Male',
    'character[will]':        'd8',
    'character[speed]':       'd6',
    'character[body]':        'd6',
    'character[mind]':        'd4',
    ...(speciesId ? { 'character[species_id]': String(speciesId) } : {}),
    ...(careerId  ? { 'character[career_id]':  String(careerId)  } : {}),
    'character[motto]':       'Fortune favours the bold.',
    'character[description]': 'A test character.',
  });
  expect('cg_save_character (new) → success:true', saveNew.body.success === true, saveNew.body);
  savedCharId = saveNew.body.data?.id ?? null;
  expect('New character gets an ID',               savedCharId !== null && savedCharId !== '0', savedCharId);

  if (!savedCharId || savedCharId === '0') {
    skip('Remaining character tests', 'no valid character ID');
    return;
  }

  // Retrieve it
  const getChar = await post('cg_get_character', { id: savedCharId });
  expect('cg_get_character → success:true',            getChar.body.success === true,                       getChar.body);
  expect('Character name matches what was saved',      getChar.body.data?.name === 'Aldric the Bold',       getChar.body.data?.name);
  expect('Character will matches',                     getChar.body.data?.will === 'd8',                    getChar.body.data?.will);
  expect('Character ID is a string',                   typeof getChar.body.data?.id === 'string',           typeof getChar.body.data?.id);

  // List now includes new character
  const list1 = await post('cg_load_characters');
  expect('Character list grows by 1 after save',
    Array.isArray(list1.body.data) && list1.body.data.length === countBefore + 1,
    `before=${countBefore} after=${list1.body.data?.length}`
  );
  const inList = list1.body.data?.find(c => c.id === savedCharId);
  expect('New character appears in list',              !!inList,                                            savedCharId);

  // Update it
  const saveUpdate = await post('cg_save_character', {
    'character[id]':          savedCharId,
    'character[name]':        'Aldric the Wise',
    'character[player_name]': 'Test Player',
    'character[age]':         '30',
    'character[gender]':      'Male',
    'character[will]':        'd8',
    'character[speed]':       'd6',
    'character[body]':        'd6',
    'character[mind]':        'd8',
    'character[motto]':       'Updated motto.',
  });
  expect('cg_save_character (update) → success:true', saveUpdate.body.success === true, saveUpdate.body);
  expect('Update returns same ID',                    saveUpdate.body.data?.id === savedCharId,             saveUpdate.body.data);

  // Verify update persisted
  const getUpdated = await post('cg_get_character', { id: savedCharId });
  expect('Updated name persists',     getUpdated.body.data?.name  === 'Aldric the Wise', getUpdated.body.data?.name);
  expect('Updated mind persists',     getUpdated.body.data?.mind  === 'd8',              getUpdated.body.data?.mind);
  expect('Updated age persists',      getUpdated.body.data?.age   === '30',              getUpdated.body.data?.age);
}

async function testOwnershipIsolation() {
  section('Ownership Isolation');

  if (!savedCharId) {
    skip('All ownership tests', 'no savedCharId from character tests');
    return;
  }

  // Log out, register a different user, and try to access the first user's character.
  const out = await post('cg_logout_user');
  expect('Second user: logout first user', out.body.success === true, out.body);
  jar.clear();

  const user2 = `_testuser2_${TIMESTAMP}`;
  const reg2 = await post('cg_register_user', {
    username: user2,
    email:    `${user2}@example.com`,
    password: TEST_PASS,
  });
  expect('Second user: register', reg2.body.success === true, reg2.body);

  const steal = await post('cg_get_character', { id: savedCharId });
  expect('Second user cannot read first user\'s character',
    steal.body.success === false,
    steal.body
  );

  const stealUpdate = await post('cg_save_character', {
    'character[id]':   savedCharId,
    'character[name]': 'Stolen!',
    'character[will]': 'd4',
  });
  expect('Second user cannot update first user\'s character',
    stealUpdate.body.success === false,
    stealUpdate.body
  );

  // Log back in as test user 1
  await post('cg_logout_user');
  jar.clear();
  const reLogin = await post('cg_login_user', { username: TEST_USER, password: TEST_PASS });
  expect('Re-login as test user 1', reLogin.body.success === true, reLogin.body);

  // Confirm original record untouched
  const check = await post('cg_get_character', { id: savedCharId });
  expect('Character name still intact after theft attempt',
    check.body.data?.name === 'Aldric the Wise',
    check.body.data?.name
  );
}

async function testInvalidInputs() {
  section('Invalid / Edge-Case Inputs');

  const badId = await post('cg_get_character', { id: '0' });
  expect('cg_get_character with id=0 → success:false', badId.body.success === false, badId.body);

  const negId = await post('cg_get_character', { id: '-1' });
  expect('cg_get_character with id=-1 → success:false', negId.body.success === false, negId.body);

  const strId = await post('cg_get_character', { id: 'notanumber' });
  expect('cg_get_character with id="notanumber" → success:false', strId.body.success === false, strId.body);

  const hugeId = await post('cg_get_character', { id: '999999999' });
  expect('cg_get_character with nonexistent id → success:false', hugeId.body.success === false, hugeId.body);
}

async function testLogoutCleanup() {
  section('Logout & Session Cleanup');

  const out = await post('cg_logout_user');
  expect('Final logout → success:true', out.body.success === true, out.body);

  jar.clear();
  const me = await get('/api/auth/me');
  expect('Session gone after logout', me.body.success === false, me.body);

  const blocked = await post('cg_load_characters');
  expect('cg_load_characters blocked after logout', blocked.status === 401, blocked.status);
}

// ── Main ──────────────────────────────────────────────────────────────────────
async function main() {
  console.log('\n\x1b[1m  Character Generator — Test Suite\x1b[0m');
  console.log(`  Target: ${BASE}`);
  console.log(`  Test user: ${TEST_USER}`);

  try {
    await testServerHealth();
    await testUnauthenticated();
    await testLoginFailures();
    await testRegistration();
    await testReferenceData();
    await testCharacterCRUD();
    await testOwnershipIsolation();
    await testInvalidInputs();
    await testLogoutCleanup();
  } catch (err) {
    console.error('\n\x1b[31m  Fatal error during test run:\x1b[0m', err.message);
    process.exitCode = 1;
  }

  const total = passed + failed + skipped;
  console.log('\n' + '─'.repeat(50));
  console.log(`  Results: \x1b[32m${passed} passed\x1b[0m  \x1b[31m${failed} failed\x1b[0m  \x1b[33m${skipped} skipped\x1b[0m  (${total} total)`);
  console.log('─'.repeat(50) + '\n');

  if (failed > 0) process.exitCode = 1;
}

main();
