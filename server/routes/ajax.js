const express = require('express');
const router  = express.Router();

const authActions    = require('./actions/auth');
const charActions    = require('./actions/character');
const careerActions  = require('./actions/career');
const giftsActions   = require('./actions/gifts');
const skillsActions  = require('./actions/skills');
const speciesActions = require('./actions/species');
const diagActions    = require('./actions/diagnostics');

const PUBLIC_ACTIONS = new Set([
  'cg_login_user',
  'cg_logout_user',
  'cg_register_user',
  'cg_get_current_user',
]);

const ACTION_MAP = {
  cg_login_user:        authActions.cg_login_user,
  cg_logout_user:       authActions.cg_logout_user,
  cg_register_user:     authActions.cg_register_user,
  cg_get_current_user:  authActions.cg_get_current_user,

  cg_load_characters:   charActions.cg_load_characters,
  cg_list_characters:   charActions.cg_load_characters,
  cg_get_character:     charActions.cg_get_character,
  cg_save_character:    charActions.cg_save_character,

  cg_get_career_list:   careerActions.cg_get_career_list,
  cg_get_career_gifts:  careerActions.cg_get_career_gifts,

  cg_get_local_knowledge: giftsActions.cg_get_local_knowledge,
  cg_get_language_gift:   giftsActions.cg_get_language_gift,
  cg_get_free_gifts:      giftsActions.cg_get_free_gifts,

  cg_get_skills_list:   skillsActions.cg_get_skills_list,
  cg_get_skill_detail:  skillsActions.cg_get_skill_detail,

  cg_get_species_list:    speciesActions.cg_get_species_list,
  cg_get_species_profile: speciesActions.cg_get_species_profile,

  cg_ping:              diagActions.cg_ping,
  cg_run_diagnostics:   diagActions.cg_run_diagnostics,
};

function requireAuth(req, res, next) {
  if (!req.session || !req.session.userId) {
    return res.status(401).json({ success: false, data: 'Not logged in.' });
  }
  next();
}

router.post('/', async (req, res) => {
  const action = (req.body.action || '').trim();

  if (!action) {
    return res.json({ success: false, data: 'No action specified.' });
  }

  const handler = ACTION_MAP[action];
  if (!handler) {
    return res.json({ success: false, data: `Unknown action: ${action}` });
  }

  if (!PUBLIC_ACTIONS.has(action)) {
    if (!req.session || !req.session.userId) {
      return res.status(401).json({ success: false, data: 'Not logged in.' });
    }
  }

  try {
    await handler(req, res);
  } catch (err) {
    console.error(`[CG] Error in action ${action}:`, err.message);
    res.status(500).json({ success: false, data: 'Server error. Please try again.' });
  }
});

module.exports = router;
