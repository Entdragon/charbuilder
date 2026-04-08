# Library of Calabria — Character Generator Feature Matrix

> **Purpose:** Living reference for agent sessions. Read this at the start of any
> session to understand what is already built, where parity gaps exist between the
> main character and ally flows, and which PHP backend endpoints are available.
>
> **Last updated:** 2026-04-08  
> **Key JS entry points:** `assets/js/src/core/main/index.js` (main char),
> `assets/js/src/core/ally/index.js` (ally)  
> **Backend router:** `php/ajax.php`

---

## 1. Feature Parity Table

A ✅ means the feature is fully implemented. A ❌ means it is absent or not wired
in that flow. "Partial" means some subset is present but not the full feature.
`—` in the endpoint column means the feature is client-side only (no AJAX needed).

| Feature / System | Main Char | Ally | PHP endpoint(s) | Gap note |
|---|:---:|:---:|---|---|
| **Identity — name** | ✅ | ✅ | — | |
| **Identity — age** | ✅ | ✅ | — | |
| **Identity — gender** | ✅ | ✅ | — | |
| **Identity — player name** | ✅ | ❌ | — | Ally uses Description field instead |
| **Identity — motto** | ✅ | ❌ | — | |
| **Species selection** | ✅ | ✅ | `cg_get_species_list` | |
| **Species profile** (habitat/diet/cycle/senses) | ✅ | ✅ | `cg_get_species_profile` | |
| **Species gifts** (names + trigger text on sheet) | ✅ | ✅ | `cg_get_species_profile` | Gift IDs returned as `gift_id_1/2/3` |
| **Species skills** | ✅ | ✅ | `cg_get_species_profile` + `cg_get_skills_list` | |
| **Career selection** | ✅ | ✅ | `cg_get_career_list` | |
| **Career gifts** (names + trigger text on sheet) | ✅ | ✅ | `cg_get_career_gifts` | |
| **Career skills** | ✅ | ✅ | `cg_get_career_gifts` + `cg_get_skills_list` | |
| **Extra careers** | ✅ | ❌ | `cg_get_career_gifts` | Intentional — ally has one career only |
| **Trait dice** (Will/Mind/Speed/Body/Species/Career) | ✅ | ✅ | — | Ally reads from main char + applies gift boosts |
| **Trait die boosts** (gift IDs 78/89/85/100/224/223) | ✅ | ✅ | — | |
| **Qualifications panel** (Language/Literacy/Insider/Mystic/Piety) | ✅ | ❌ | `cg_get_language_list` | **G5** — quals panel absent from ally |
| **Default gift — Local Knowledge** | ✅ | ❌ | `cg_get_local_knowledge` | **G2** |
| **Default gift — Language** | ✅ | ❌ | `cg_get_language_gift` | **G2** — ally shows main char's language on print only via `_getMainLang()` |
| **Default gift — Combat Save** | ✅ | ❌ | `cg_get_combat_save` | **G2** |
| **Default gift — Personality** | ✅ | ❌ | `cg_get_personality_list`, `cg_get_personality_gift` | **G2** |
| **Free gift slots** (eligibility-filtered selection) | ✅ | ✅ | `cg_get_free_gifts` | Ally uses this for Improved Ally slots |
| **Gift eligibility filter** (`gift-filter.js`) | ✅ | ✅ | `cg_get_free_gifts` | Both import shared `filterGiftIneligibleReason` |
| **Passive gift effects on soak** (Gift 21/79/133) | ✅ | ✅ | — | Ally: `_buildAllysoakParts(tr, armor)` |
| **Battle array** (Initiative/Dodge/Soak) | ✅ | ✅ | — | |
| **Movement table** (Stride/Dash/Sprint/Run) | ✅ | ✅ | — | Both derive from Speed+Body dice maxima |
| **Damage track + Healing Quota circles** | ✅ | ✅ | — | |
| **Status conditions** (Burdened/Knockdown/Unconscious etc.) | ✅ | ✅ | — | |
| **Spells tied to gifts** | ✅ | ❌ | `cg_get_spells_for_gifts` | **G3** — never called for ally |
| **Skills display** (species + career dice pools) | ✅ | ✅ | `cg_get_skills_list` | |
| **Skill detail** (individual skill lookup) | ❌ | ❌ | `cg_get_skill_detail` | **G6** — endpoint exists; unused in both flows |
| **Career trappings** (weapons/armour auto-loaded) | ✅ | ✅ | `cg_get_career_trappings` | |
| **Gift trappings** (equipment auto-loaded from gifts) | ✅ | ❌ | `cg_get_gift_trappings` | **G1** — never called for ally |
| **Equipment shop** (catalog browse + purchase) | ✅ | ✅ | `cg_get_equipment_catalog` | |
| **Money/Denar tracking** | ✅ | Partial | `cg_get_money_list` | **G4** — ally tracks denar only; full denomination list not loaded |
| **Experience/XP pool** | ✅ | ❌ | `cg_get_free_gifts` (XP gift options) | **G8** — intentional; allies don't earn XP |
| **XP — skill marks** | ✅ | ❌ | — | **G8** |
| **XP — gift slots** | ✅ | ❌ | `cg_get_free_gifts` | **G8** |
| **XP — retraining** | ✅ | ❌ | — | **G8** — `experience/retrain.js` |
| **Print/export sheet** | ✅ | ✅ | — | Ally sheet mirrors main char layout |
| **Gift descriptions on print** | ✅ | ✅ | — | Both read trigger text from loaded `_giftList` |
| **Character save/load** | ✅ | ✅ (embedded) | `cg_save_character`, `cg_get_character` | Ally data embedded in main char JSON blob |
| **Description / backstory free text** | ✅ | ✅ | — | Ally has Description; main char has Description + Backstory |
| **"Ally Sheet" banner on print** | N/A | ✅ | — | `.summary-ally-banner` CSS class |
| **"Ally of: [main char]" attribution** | N/A | ✅ | — | Reads `FormBuilderAPI._data.name` |

---

## 2. PHP Endpoint Inventory

All endpoints route through `php/ajax.php`. Unless marked **public**, all require an
authenticated session.

### auth.php — Authentication & Session

| Action | Caller(s) | Returns | Notes |
|---|---|---|---|
| `cg_login_user` | Login form | Session token | Public |
| `cg_logout_user` | Nav logout link | — | Public |
| `cg_register_user` | Register form | — | Public |
| `cg_get_current_user` | App bootstrap | `{ id, name, email, roles }` | Public; also accepts GET |
| `cg_sso_login` | WordPress hook | Session token | WordPress SSO; public |

### character.php — Save / Load

| Action | Caller(s) | Returns | Notes |
|---|---|---|---|
| `cg_load_characters` | `formBuilder/index.js` | `[{ id, name, … }]` | List of saved characters |
| `cg_get_character` | `formBuilder/index.js` | Full character JSON blob | Includes ally sub-object |
| `cg_save_character` | `formBuilder/index.js` | `{ id }` | Upserts the full character blob |

### career.php — Career Data

| Action | Caller(s) | Returns | Notes |
|---|---|---|---|
| `cg_get_career_list` | `career/api.js` | `[{ id, name }]` | Populates career dropdown |
| `cg_get_career_gifts` | `career/api.js`, `career/extra.js`, ally via `CareerAPI.fetchProfile` | `{ careerName, gift_1…3, gift_id_1…3, skill_one…three, manifold_* }` | Full career profile |

### equipment.php — Trappings, Shop & Money

| Action | Caller(s) | Returns | Notes |
|---|---|---|---|
| `cg_get_career_trappings` | `trappings/index.js` (main), `ally/index.js` | `[{ id, name, type, … }]` | Weapons + armour for a career |
| `cg_get_gift_trappings` | `trappings/index.js` (main only) | `[{ id, name, type, … }]` | **Ally gap** — never called for ally |
| `cg_get_equipment_catalog` | `trappings/index.js` (main), `ally/index.js` | `[{ id, name, cost_d, … }]` | Full purchasable catalog |
| `cg_get_money_list` | `trappings/index.js` (main only) | `[{ id, name, value_in_denar }]` | **Ally gap** — ally tracks only denar manually |

### gifts.php — Gift & Qualification Data

| Action | Caller(s) | Returns | Notes |
|---|---|---|---|
| `cg_get_local_knowledge` | `gifts/defaults.js` (main only) | `{ id, name, trigger }` | **Ally gap** |
| `cg_get_language_gift` | `gifts/defaults.js` (main only) | `{ id, name, trigger }` | **Ally gap** — ally shows language from `_getMainLang()` only on print |
| `cg_get_combat_save` | `gifts/defaults.js` (main only) | `{ id, name, trigger }` | **Ally gap** |
| `cg_get_personality_list` | `gifts/defaults.js` (main only) | `[{ id, name }]` | **Ally gap** |
| `cg_get_personality_gift` | `gifts/defaults.js` (main only) | `{ id, name, trigger }` | **Ally gap** |
| `cg_get_free_gifts` | `gifts/free-choices.js` (main), `ally/index.js` (improved slots), `experience/index.js` (XP gifts) | `[{ id, name, trigger, prereqs, … }]` | Full filtered gift list |
| `cg_get_language_list` | `quals/ui.js` (main only) | `[{ id, name }]` | **Ally gap** — quals panel not in ally |

### skills.php — Skills

| Action | Caller(s) | Returns | Notes |
|---|---|---|---|
| `cg_get_skills_list` | `skills/index.js`, `battle/index.js`, `ally/index.js` | `[{ id, name, trait_key }]` | Used by all three flows |
| `cg_get_skill_detail` | (unused) | `{ id, name, description, … }` | Endpoint exists; **neither flow calls it** |

### species.php — Species Data

| Action | Caller(s) | Returns | Notes |
|---|---|---|---|
| `cg_get_species_list` | `species/api.js`, ally via `SpeciesAPI.getList` | `[{ id, name }]` | Populates species dropdown |
| `cg_get_species_profile` | `species/api.js`, ally via `SpeciesAPI.fetchProfile` | `{ speciesName, habitat, diet, cycle, sense_1…3, gift_1…3, gift_id_1…3, … }` | Full species profile |

### spells.php — Spells

| Action | Caller(s) | Returns | Notes |
|---|---|---|---|
| `cg_get_spells_for_gifts` | `battle/index.js` (main only) | `[{ id, name, gift_id, … }]` | **Ally gap** — spells not wired into ally |
| `cg_install_spells` | (admin/migration only) | — | One-time data migration; not called by character UI |

### diagnostics.php — Developer Tools

| Action | Caller(s) | Notes |
|---|---|---|
| `cg_ping` | Health checks | Public; returns `{ ok: true }` |
| `cg_run_diagnostics` | Dev only | Returns DB stats, table counts |

### admin.php — Admin Panel Only

All actions are admin-role only and called only from `php/admin.php` (inline JS).

| Action | Purpose |
|---|---|
| `cg_admin_list_gifts` | List all gifts for editing |
| `cg_admin_get_gift` | Load single gift for edit form |
| `cg_admin_save_gift` | Save gift edits (name/trigger/prereqs/sections) |
| `cg_admin_get_gift_children` | Load prerequisite children of a gift |
| `cg_admin_save_gift_rule` | Save a prereq rule |
| `cg_admin_delete_gift_rule` | Delete a prereq rule |
| `cg_admin_save_gift_section` | Save a gift section (Ally/Free/Species/Career) |
| `cg_admin_delete_gift_section` | Delete a gift section |
| `cg_admin_gift_quality_report` | Run data quality check on gift table |
| `cg_admin_list_weapons` | List all weapons for editing |
| `cg_admin_get_weapon` | Load single weapon |
| `cg_admin_save_weapon` | Save weapon edits |
| `cg_admin_sync_trappings_children` | Sync trappings from gift children (batch) |
| `cg_admin_sync_single_gift` | Sync a single gift's trappings (server-to-server; uses `sync_secret`) |

---

## 3. Known Gaps (Ally vs Main)

These are features present in the main character flow that are **not yet implemented**
for the ally. They are listed here to inform future planning — not as bugs.

| # | Gap | Missing Endpoint(s) | Impact |
|---|---|---|---|
| G1 | **Gift trappings** — equipment auto-granted by a gift (e.g. a gift that includes a specific weapon) is never loaded for ally | `cg_get_gift_trappings` | Ally's trappings list and print sheet will be missing gift-granted items |
| G2 | **Default gifts** — Local Knowledge, Language (as a stored gift record), Combat Save, Personality are not fetched or stored for ally | `cg_get_local_knowledge`, `cg_get_language_gift`, `cg_get_combat_save`, `cg_get_personality_gift`, `cg_get_personality_list` | These gifts are absent from ally's battle array gift list and print sheet |
| G3 | **Spells** — ally gifts can unlock spells but the spell lookup never runs | `cg_get_spells_for_gifts` | Ally sheet shows no spell block even if the ally holds a spell-granting gift |
| G4 | **Full money denominations** — ally tracks only denar; the denomination list is not loaded | `cg_get_money_list` | Minor UX gap; denar is the main currency used in play |
| G5 | **Qualifications** — ally has no Language/Literacy/Insider/Mystic/Piety panel | `cg_get_language_list` | Ally's language qualification is read only from the main char, not independently set |
| G6 | **Skill detail** — neither flow calls `cg_get_skill_detail`; individual skill descriptions are never displayed | `cg_get_skill_detail` | Not user-facing yet; endpoint is available for future tooltip/detail use |
| G7 | **Extra careers** — ally supports only one species + one career | — | Intentional design choice, but noted here for clarity |
| G8 | **XP / Experience** — ally has no experience panel | — | Intentional — allies don't earn XP; noted for clarity |

---

## 4. JS Module Map

Quick reference of where each system lives in the frontend source.

| Module path | Responsibility |
|---|---|
| `core/main/` | App bootstrap, event wiring, save/load, builder UI |
| `core/formBuilder/` | Main character form render, persistence, summary render |
| `core/species/` | Species dropdown, API, event handlers, rendering |
| `core/career/` | Career dropdown, extra careers, API, event handlers |
| `core/traits/` | Trait dice display, boost resolution, event handlers |
| `core/gifts/` | Free gift slots, default gifts, gift-filter logic, gift state |
| `core/quals/` | Qualifications panel (Language/Literacy/Insider/Mystic/Piety) |
| `core/battle/` | Battle array, movement, damage track, spells, skills-in-battle |
| `core/skills/` | Skills list display, dice pool resolution |
| `core/trappings/` | Career trappings, gift trappings, equipment shop, money |
| `core/experience/` | XP pool, skill marks, gift slots, retraining |
| `core/summary/` | Full character sheet (on-screen + print) |
| `core/ally/` | **All ally functionality** — single large module |
| `core/utils/` | Shared helpers (`bind-once.js`) |

---

## 5. CSS Architecture Reference

| File | What it covers |
|---|---|
| `assets/css/src/components/_print.scss` | All `@media print` overrides and print-only layout |
| `assets/css/src/components/_summary.scss` | On-screen character sheet styles |
| `assets/css/src/components/_ally.scss` | Ally panel styles (dark-themed UI) |
| `assets/css/src/components/_battle.scss` | Battle array, damage track, movement |

Key shared CSS classes used in both main char and ally print sheets:
- `.summary-header-block`, `.summary-basic-row`, `.summary-motto`
- `.summary-page1-body`, `.summary-col-left`, `.summary-col-right`
- `.summary-page2`, `.summary-section`
- `.cg-battle-summary-table`, `.summary-battle-pools`
- `.summary-movement`, `.summary-damage-track`
- `.cg-hit-row`, `.cg-hit-pip`, `.cg-hq-circle`, `.cg-healing-quota`
- `.cg-summary-skills`, `.cg-summary-skills-wrap`
- `.summary-gift-desc`, `.summary-ally-banner`
