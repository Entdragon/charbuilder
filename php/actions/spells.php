<?php
/**
 * Spells — Character Generator
 *
 * Actions:
 *   cg_install_spells      — Create table + seed all spell data (idempotent).
 *   cg_get_spells_for_gifts — Return spells available to the character's gift set.
 */
require_once __DIR__ . '/../includes/db.php';

// ---------------------------------------------------------------------------
// Table helpers
// ---------------------------------------------------------------------------

function cg_spells_table(): string {
    return cg_prefix() . 'customtables_table_spells';
}

function cg_ensure_spells_table(): void {
    $t = cg_spells_table();
    cg_exec("CREATE TABLE IF NOT EXISTS {$t} (
        ct_id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        ct_name       VARCHAR(255)  NOT NULL DEFAULT '',
        ct_equip      VARCHAR(255)  NOT NULL DEFAULT '',
        ct_range      VARCHAR(100)  NOT NULL DEFAULT '',
        ct_attack_dice TEXT         NOT NULL,
        ct_effect     TEXT          NOT NULL,
        ct_descriptors TEXT         NOT NULL,
        ct_gift_name  VARCHAR(255)  NOT NULL DEFAULT '',
        ct_sort       SMALLINT UNSIGNED NOT NULL DEFAULT 0,
        INDEX idx_gift_name (ct_gift_name(100))
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

// ---------------------------------------------------------------------------
// Seed data  (name, equip, range, attack_dice, effect, descriptors, gift_name, sort)
// gift_name = 'Common Magic' is treated specially — included for ANY magic gift.
// All other gift_name values must exactly match ct_gifts_name in the gifts table.
// ---------------------------------------------------------------------------

function cg_spell_rows(): array {
    return [
        // ── Common Magic Spells (Apprentice) ────────────────────────────────
        ['Ataxia',        'Off hand', 'Medium', 'Mind, Will vs. 3',                          'Slowed',                                   'Magic, Psychic, Apprentice, Mystic',              'Common Magic', 10],
        ['Create Air',    'Off hand', 'Medium', 'Speed, Weather Sense vs. defense',           'Damage +2 Critical, Counters Earth',        'Magic, Air, Apprentice, Mystic',                  'Common Magic', 20],
        ['Create Earth',  'Off hand', 'Medium', 'Body, Digging vs. defense',                  'Damage +2 Critical, Counters Air',          'Magic, Earth, Apprentice, Mystic',                'Common Magic', 30],
        ['Create Water',  'Off hand', 'Medium', 'Mind, Swimming vs. defense',                 'Damage +2 Critical, Counters Fire',         'Magic, Water, Apprentice, Mystic',                'Common Magic', 40],
        ['Eyebite',       'Off hand', 'Medium', 'Mind, Gossip vs. 3',                         'Enraged',                                  'Magic, Psychic, Apprentice, Mystic',              'Common Magic', 50],
        ['Ignite Fire',   'Off hand', 'Medium', 'Will, Presence vs. defense',                 'Damage +1 Critical, On Fire, Counters Water','Magic, Fire, Apprentice, Mystic',                'Common Magic', 60],
        ['Misfortune',    'Off hand', 'Medium', 'Mind, Supernatural vs. 3',                   'Disarm, Counters Magic',                   'Magic, Theurgic, Apprentice, Mystic',             'Common Magic', 70],
        ['Move Air',      'Off hand', 'Medium', 'Speed, Weather Sense vs. 3',                 'Damage +0, Push 1, Counters Air',          'Magic, Air, Apprentice, Environmental',           'Common Magic', 80],
        ['Move Earth',    'Off hand', 'Medium', 'Body, Digging vs. 3',                        'Damage +1, Counters Earth',                'Magic, Earth, Apprentice, Environmental',         'Common Magic', 90],
        ['Move Fire',     'Off hand', 'Medium', 'Will, Presence vs. 3',                       'Damage +0, On Fire, Counters Fire',        'Magic, Fire, Apprentice, Environmental',          'Common Magic', 100],
        ['Move Water',    'Off hand', 'Medium', 'Mind, Swimming vs. 3',                       'Damage +2, Weak, Counters Water',          'Magic, Water, Apprentice, Environmental',         'Common Magic', 110],
        ['Perplex',       'Off hand', 'Medium', 'Mind, Negotiation vs. 3',                    'Confusion',                                'Magic, Psychic, Apprentice, Mystic',              'Common Magic', 120],
        ['Rebuke',        'Off hand', 'Short',  'Will, Leadership vs. 3',                     'Damage +0, Penetrating, Sweep Near, Counters Unholy', 'Magic, Holy, Apprentice, Mystic',      'Common Magic', 130],
        ['Repudiation',   'Off hand', 'Short',  'Will, Academics vs. 3',                      'Confused, Counters Magic',                 'Magic, Holy, Apprentice, Mystic',                 'Common Magic', 140],
        ['Silence',       'Off hand', 'Medium', 'Mind, Will, Supernatural vs. 3',             'Silenced, Counters Magic',                 'Magic, Theurgic, Unreal, Apprentice, Mystic',     'Common Magic', 150],

        // ── Journeyman Air Magic ────────────────────────────────────────────
        ['Gust of Wind',  'Off hand', 'Medium', 'Speed, Weather Sense vs. 3',                 'Damage +1, Push 3, Explosion: Short',      'Magic, Air, Journeyman, Loud',                    'Journeyman Air Magic', 10],
        ['Thunderclap',   'Off hand', 'Medium', 'Speed, Weather Sense vs. 3',                 'Damage +2, Explosion: Near',               'Magic, Air, Journeyman, Loud',                    'Journeyman Air Magic', 20],
        ['Lightning Bolt','Off hand', 'Medium', 'Speed, Weather Sense vs. defense',           'Damage +2 Critical, Penetrating, Confusion','Magic, Air, Journeyman, Loud',                   'Journeyman Air Magic', 30],

        // ── Master Air Magic ────────────────────────────────────────────────
        ['Chain Lightning','Off hand','Medium', 'Speed, Weather Sense vs. defense',           'Damage +3 Critical, Confusion, Sweep Short','Magic, Air, Master, Loud',                       'Master Air Magic', 10],
        ['The Tempest',   'Off hand', 'Medium', 'Speed, Weather Sense vs. 3',                 'Damage +1 Penetrating, Push 3, Explosion: Short or Medium', 'Magic, Air, Master, Loud',      'Master Air Magic', 20],

        // ── Journeyman Earth Magic ──────────────────────────────────────────
        ['Landslide',     'Off hand', 'Medium', 'Body, Digging vs. 3',                        'Damage +2, Explosion: Near',               'Magic, Earth, Journeyman, Loud',                  'Journeyman Earth Magic', 10],
        ['Quake',         'Off hand', 'Medium', 'Body, Digging vs. 3',                        'Damage +1 Penetrating, Explosion: Near',   'Magic, Earth, Journeyman, Loud',                  'Journeyman Earth Magic', 20],
        ['Stone Hurlant', 'Off hand', 'Medium', 'Body, Digging vs. defense',                  'Damage +3 Critical, Knockdown',            'Magic, Earth, Journeyman, Loud',                  'Journeyman Earth Magic', 30],

        // ── Master Earth Magic ──────────────────────────────────────────────
        ['Sand Blast',    'Off hand', 'Medium', 'Body, Digging vs. defense',                  'Damage +3 Critical, Penetrating, Sweep Short', 'Magic, Earth, Master, Loud',                 'Master Earth Magic', 10],
        ['Upheaval',      'Off hand', 'Medium', 'Body, Digging vs. 3',                        'Damage +2, Grappled, Explosion: Short or Medium', 'Magic, Earth, Master, Loud',              'Master Earth Magic', 20],

        // ── Journeyman Fire Magic ───────────────────────────────────────────
        ['Fire Ball',     'Off hand', 'Medium', 'Will, Presence vs. 3',                       'Damage +3, Explosion: Reach',              'Magic, Fire, Journeyman, Loud',                   'Journeyman Fire Magic', 10],
        ['Pilum of Fire', 'Off hand', 'Medium', 'Will, Presence vs. defense',                 'Damage +3 Critical, On Fire',              'Magic, Fire, Journeyman, Loud',                   'Journeyman Fire Magic', 20],
        ['Sunburst',      'Off hand', 'Medium', 'Will, Presence vs. 3',                       'Damage +1, Blinded, Explosion: Near',      'Magic, Fire, Journeyman, Loud',                   'Journeyman Fire Magic', 30],

        // ── Master Fire Magic ───────────────────────────────────────────────
        ['Meteor Swarm',  'Off hand', 'Medium', 'Will, Presence vs. 3',                       'Damage +3, On Fire, Explosion: Near or Short', 'Magic, Fire, Master, Loud',                  'Master Fire Magic', 10],
        ['Whips of Flame','Off hand', 'Medium', 'Will, Presence vs. defense',                 'Damage +2 Penetrating, On Fire, Sweep Short', 'Magic, Fire, Master, Loud',                   'Master Fire Magic', 20],

        // ── Journeyman Water Magic ──────────────────────────────────────────
        ['Freeze Arrow',  'Off hand', 'Medium', 'Mind, Swimming vs. defense',                 'Damage +2 Critical, Slowed',               'Magic, Water, Journeyman, Loud',                  'Journeyman Water Magic', 10],
        ['Ice Storm',     'Off hand', 'Medium', 'Mind, Swimming vs. 3',                       'Damage +1 Penetrating, Immobilized, Explosion: Near', 'Magic, Water, Journeyman, Loud',      'Journeyman Water Magic', 20],
        ['Maelstrom',     'Off hand', 'Medium', 'Mind, Swimming vs. 3',                       'Damage +1 Penetrating, Confusion, Explosion: Near', 'Magic, Water, Journeyman, Loud',        'Journeyman Water Magic', 30],

        // ── Master Water Magic ──────────────────────────────────────────────
        ['Aegir',         'Off hand', 'Medium', 'Mind, Swimming vs. defense',                 'Damage +2 Critical Penetrating, Sweep Short', 'Magic, Water, Master, Loud',                  'Master Water Magic', 10],
        ['Hoarfrost',     'Off hand', 'Medium', 'Mind, Swimming vs. 3',                       'Damage +2 Penetrating, Confused, Slowed, Explosion: Short or Medium', 'Magic, Water, Master, Loud', 'Master Water Magic', 20],

        // ── Journeyman White Magic ──────────────────────────────────────────
        ['Fulguration',   'Rod, Wand, or Calendar Sword', 'Medium', 'Will, Leadership vs. defense', 'Damage +4 Weak, Sweep Short, Counters Unholy', 'Magic, Holy, Journeyman',              'Journeyman White Magic', 10],

        // ── Journeyman Cognoscente Magic ────────────────────────────────────
        ['Blindness',     'Off hand', 'Medium', 'Mind, Will vs. 3',                           'Blinded',                                  'Magic, Psyche, Journeyman, Thought, Mystic',      'Journeyman Cognoscente Magic', 10],
        ['Mesmerism',     'Off hand', 'Medium', 'Mind, Will vs. 3',                           'Mesmerized',                               'Magic, Psyche, Journeyman, Emotion, Mystic',      'Journeyman Cognoscente Magic', 20],
        ['Paralysis',     'Off hand', 'Medium', 'Mind, Will vs. 3',                           'Immobilized',                              'Magic, Psyche, Journeyman, Function, Mystic',     'Journeyman Cognoscente Magic', 30],

        // ── Secret Star Magic of the Dunwasser Academy ──────────────────────
        ['Comet Fall',    'Off hand', 'Medium', 'Body, Speed, Mind, Will, Academics vs. 3',   'Damage +0 Penetrating, Slaying, Explosion: Reach, Near, Short, or Medium', 'Magic, Air, Earth, Fire, Water, Star, Loud', 'Secret Star Magic of the Dunwasser Academy', 10],
        ['Starlight',     'Off hand', 'Medium', 'Body, Speed, Mind, Will, Academics vs. defense', 'Damage +0 Penetrating, Slaying',       'Magic, Air, Earth, Fire, Water, Star, Loud',      'Secret Star Magic of the Dunwasser Academy', 20],

        // ── Necromantic Weapons ─────────────────────────────────────────────
        ['Curse',         'Off hand', 'Medium', 'Will, Deceit, 3d6 vs. special',              'See page 313',                             'Magic, Unholy, Journeyman, Mystic, Proscribed, Counters Magic', 'Necromancy', 10],
        ['Horror',        'Off hand', 'Medium', 'Mind, Deceit, 2d6 vs. target\'s Mind, Will, Inquiry, Supernatural', 'Afraid',           'Magic, Unholy, Apprentice, Mystic, Proscribed, Counters Magic', 'Necromancy', 20],
        ['Illness',       'Off hand', 'Medium', 'Will, Negotiation, 2d6 vs. target\'s Body, Mind, Will, Endurance, Supernatural', 'Fatigued', 'Magic, Unholy, Apprentice, Mystic, Proscribed, Counters Magic', 'Necromancy', 30],
        ['Mass Agony',    'Off hand', 'Medium', 'Will, Leadership, 3d6 vs. 3',                'Damage +1 Critical, Penetrating, Weak, Explosion: Near', 'Magic, Unholy, Journeyman, Mystic, Proscribed, Counters Magic', 'Necromancy', 40],
        ['Torment',       'Off hand', 'Medium', 'Will, Leadership, 2d6 vs. target\'s Mind, Supernatural', 'Damage +1 Critical, Penetrating, Weak', 'Magic, Unholy, Apprentice, Mystic, Proscribed, Counters Magic', 'Necromancy', 50],
        ['Unmaking',      'Off hand', 'Medium', 'Will, Supernatural, 3d6 vs. special',        'See page 315',                             'Magic, Unholy, Journeyman, Mystic, Proscribed, Counters Magic', 'Necromancy', 60],

        // ── Apprentice Druid Magic ───────────────────────────────────────────
        ['Faerie Fire',    'Off hand', 'Medium (Counters Magic)', 'Mind, Species, Supernatural vs. 3',           'Resist with Mind, Species, Academics, Stealth vs. 3 / Target Illuminates to Short Range; others claim bonus d8 to hit', 'Magic, Druid, Apprentice, Unreal',    'Druids Apprentice', 10],
        ['Hateful Curse',  'Off hand', 'Medium (Counters Magic)', 'Mind, Species, Presence vs. 3',               'Resist with Will, Species, Academics, Inquiry vs. 3 / Damage +0, Penetrating',                                         'Magic, Druid, Apprentice, Unholy',   'Druids Apprentice', 20],
        ['Remove Glamour', 'Off hand', 'Medium (Counters Magic)', 'Mind, Species, Observation vs. 3',            'Resist with Mind, Species, Academics, Deceit vs. 3 / Confused, Dispel All Unreal',                                      'Magic, Druid, Apprentice, Theurgic', 'Druids Apprentice', 30],

        // ── Journeyman & Master Druid Magic ─────────────────────────────────
        ['Rain of Blood',      'Off hand', 'Medium (Counters Magic)',       'Mind, Species, Supernatural vs. 3',   'From Above: Group resists with Will, Species, Weather Sense vs. 3 / Damage +0, Afraid',         'Magic, Druid, Journeyman, Unholy, Indirect', 'Secrets of Druid Magic', 10],
        ['Rain of Fire',       'Off hand', 'Medium (Counters Magic)',       'Mind, Species, Supernatural vs. 3',   'From Above: Group resists with Mind, Species, Weather Sense vs. 3 / Damage +0, On Fire',          'Magic, Druid, Journeyman, Fire, Indirect',   'Secrets of Druid Magic', 20],
        ['Rain of Ice',        'Off hand', 'Medium (Counters Magic)',       'Mind, Species, Supernatural vs. 3',   'From Above: Group resists with Speed, Species, Weather Sense vs. 3 / Damage +0, Dispel All Unreal','Magic, Druid, Journeyman, Water, Indirect',  'Secrets of Druid Magic', 30],
        ['Blight',             'Off hand', 'Medium (Counters Magic)',       'Mind, Species, Supernatural vs. 3',   'Explosion: Close, Reach, Near, or Short / Resist with Speed, Mind, Will, Endurance, Supernatural vs. 3 / Damage +0, Penetrating, Sick, ruins landscape', 'Magic, Druid, Master, Unholy', 'Secrets of Druid Magic', 40],
        ['Curse of Femininity','Off hand', 'Medium (Counters any man\'s attack)', 'Mind, Species, Presence vs. 3','Group of Men Only: Resist with Mind, Species, Academics, Supernatural vs. 3 / Afraid, Sick',    'Magic, Druid, Master, Female',               'Secrets of Druid Magic', 50],
        ['Steal Guise',        'Off hand', 'Medium (Counters Magic)',       'Mind, Deceit, Supernatural vs. 3',    'Resist with Will, Inquiry, Presence, Supernatural vs. 3 / Damage +0, Penetrating, Caster Steals Appearance', 'Magic, Druid, Master, Unreal', 'Secrets of Druid Magic', 60],

        // ── Blessed Spells as Magic Weapons ─────────────────────────────────
        ['Alder\'s Calamity',   'None',     'Short (Counters Magic)', 'Mind, Species, Weather Sense, d6 vs. 3',            'Resist with Speed, Will, Presence, Supernatural, d6 vs. 3 / Damage +1 Penetrating & Weak / Disarm',                                     'Magic, Blessed, Alder, Unholy',              'Blessed Way', 10],
        ['Ash Mallet',          'Good hand','Close (Counters Magic)', 'Body, Melee Combat vs. defense / Bonus 2d8 Tactics', 'Damage +2 Critical, Push 1 / Sweep Close',                                                                                              'Magic, Blessed, Ash, Unholy',                'Blessed Way', 20],
        ['Birch\'s Banishment', 'None',     'Short (Counters Magic)', 'Mind, Species, Presence vs. 3',                     'Resist with Speed, Will, Presence, Supernatural, d6 vs. 3 / Damage +2, Slaying vs. supernatural only / Afraid',                          'Magic, Blessed, Birch, Staff, Unholy',       'Blessed Way', 30],
        ['Elderberry\'s Smoke', 'None',     'Short (Counters Magic)', 'Mind, Species, Weather Sense vs. 3',                'Explosion: Reach / Resist with Body, Endurance, 2d6 (bonus d12 from Breath-Holding) vs. 3 / Damage +0 Penetrating & Weak / Blinded',     'Magic, Blessed, Elderberry, Air, Unholy',    'Blessed Way', 40],
        ['Hawthorn\'s Brambles','None',     'Short (Counters Magic)', 'Mind, Species, Presence vs. 3',                     'Explosion: Close / Resist with Speed, Dodge, Armor, d6 vs. 3 / Damage +1 Penetrating, Grappled',                                         'Magic, Blessed, Hawthorn, Environmental, Unholy', 'Blessed Way', 50],
        ['Hazel\'s Allure',     'None',     'Short (Counters Magic)', 'Mind, Species, Negotiation (bonus d12 from Seduction) vs. 3', 'Resist with Body, Inquiry, d6 (bonus d12 from Seduction) vs. 3 / Immobilized',                                                'Magic, Blessed, Hazel, Psychic, Unholy',     'Blessed Way', 60],
        ['Holly Spear',         'Good hand','Reach (Counters Magic)', 'Body, Melee Combat vs. defense / Bonus 2d8 Tactics', 'Damage +2 Impaling, Sweep Reach',                                                                                                       'Magic, Blessed, Holly, Spear, Unholy',       'Blessed Way', 70],
        ['Ivy\'s Tangle',       'None',     'Short (Counters Magic)', 'Mind, Species, Climbing vs. 3',                     'Resist with Speed, Jumping, d6 (bonus d12 from Contortionist) vs. 3 / Damage +0 Penetrating & Weak, Grappled',                           'Magic, Blessed, Ivy, Psychic, Unholy',       'Blessed Way', 80],
        ['Oak\'s Lightning',    'None',     'Short (Counters Magic)', 'Mind, Species, Weather Sense vs. 3',                'Explosion: Reach / Resist with Speed, Dodge, Weather Sense vs. 3 / Damage +0 Penetrating',                                                'Magic, Blessed, Air, Oak, Unholy',           'Blessed Way', 90],
        ['Reed Arrow',          'In Bow',   'As bow (Counters Magic)','Speed, Ranged Combat vs. defense / Bonus 2d8 Tactics','As bow, Push 1 / Damage is Slaying vs. supernatural creatures / Damage ignores invulnerability of inanimate objects',                   'Magic, Blessed, Reed, Unholy',               'Blessed Way', 100],
        ['Rowan Rod',           '2 hands',  'Close (Counters Magic)', 'Body, Melee Combat vs. defense / Parry d12; bonus 2d8 Tactics', 'Damage +2 Critical, Push 1 / Sweep Close',                                                                                  'Magic, Blessed, Rowan, Staff, Unholy',       'Blessed Way', 110],
        ['Vine\'s Inebriation', 'None',     'Short (Counters Magic)', 'Mind, Species, Endurance vs. 3',                   'Resist with Body, Endurance, d6 (bonus d12 from Carousing) vs. 3 / Damage +0 Penetrating & Weak, Confused',                               'Magic, Blessed, Vine, Unholy',               'Blessed Way', 120],
        ['Willow\'s Despair',   'None',     'Short (Counters Magic)', 'Mind, Species, Swimming vs. 3',                    'Resist with Will, Presence, d6 vs. 3 / Confused, Afraid',                                                                                  'Magic, Blessed, Willow, Unholy',             'Blessed Way', 130],

        // ── Interdictions (each is its own gift; ct_name matches gift name) ─
        ['Culture\'s Interdiction',    'None, Rod, or Wand', 'Medium / Counter Short', 'Mind, Academics',    'Vs. Mind, Academics, 2d12 / Damage +0 Penetrating / Target Hurt? Choose one skill, apply d8 penalty', 'Magic, Virtue, Interdiction, Culture, Mystic',      'Culture\'s Interdiction',    10],
        ['Discipline\'s Interdiction', 'None, Rod, or Wand', 'Medium / Counter Short', 'Mind, Endurance',    'Vs. Mind, Endurance, 2d12 / Damage +0 Penetrating / Target Hurt? Target is Slowed, too',               'Magic, Virtue, Interdiction, Discipline, Mystic',   'Discipline\'s Interdiction', 20],
        ['Enigma\'s Interdiction',     'None, Rod, or Wand', 'Medium / Counter Short', 'Mind, Stealth',      'Vs. Mind, Stealth, 2d12 / Damage +0 Penetrating / Target Hurt? You gain concealment',                  'Magic, Virtue, Interdiction, Enigma, Mystic',       'Enigma\'s Interdiction',     30],
        ['Harmony\'s Interdiction',    'None, Rod, or Wand', 'Medium / Counter Short', 'Mind, Negotiation',  'Vs. Mind, Negotiation, 2d12 / Damage +0 Penetrating / Target Hurt? Target is Enraged, too',            'Magic, Virtue, Interdiction, Harmony, Mystic',      'Harmony\'s Interdiction',    40],
        ['Intuition\'s Interdiction',  'None, Rod, or Wand', 'Medium / Counter Short', 'Mind, Observation',  'Vs. Mind, Observation, 2d12 / Damage +0 Penetrating / Target Hurt? You are now Guarding',              'Magic, Virtue, Interdiction, Intuition, Mystic',    'Intuition\'s Interdiction',  50],
        ['Ken\'s Interdiction',        'None, Rod, or Wand', 'Medium / Counter Short', 'Mind, Inquiry',      'Vs. Mind, Inquiry, 2d12 / Damage +0 Penetrating / Target Hurt? Learn about target\'s Magic',           'Magic, Virtue, Interdiction, Ken, Mystic',          'Ken\'s Interdiction',        60],
        ['Transcendence\'s Interdiction','None, Rod, or Wand','Medium / Counter Short', 'Mind, Supernatural', 'Vs. Mind, Supernatural, 2d12 / Damage +0 Penetrating / Target Hurt? You gain synecdoche',             'Magic, Virtue, Interdiction, Transcendence, Mystic','Transcendence\'s Interdiction',70],

        // ── Holy Indirect Spells (Antiñgero, Pawang, Dukun) ─────────────────
        ['Gendam',  'Off hand, Wand, or Rod', 'Medium', 'Mind, Supernatural vs. target\'s Body, Mind, Supernatural',      'Damage +0 Weak, Penetrating / Target Hurt? Also Silenced! / Target Injured? Also Sick! / Counters Magic',                                                        'Magic, Holy, Indirect, Antiñgero', 'Antiñgero Apprentice', 10],
        ['Tenung',  'Off hand, Wand, or Rod', 'Medium', 'Mind, Observation vs. target\'s Mind, Will, Supernatural',       'Damage +0 Weak, Penetrating / Target Hurt? Also Confused and Disarmed! / Target Injured? Also Sick! / Counters Magic',                                          'Magic, Holy, Indirect, Antiñgero', 'Antiñgero Apprentice', 20],
        ['Jengges', 'Off hand, Wand, or Rod', 'Medium', 'Mind, Academics vs. defense / Negates Cover',                    'Damage +0 Critical, Penetrating / Counters Pawang',                                                                                                              'Magic, Holy, Indirect, Pawang',    'Pawang Apprentice', 10],
        ['Naruga',  'Off hand, Wand, or Rod', 'Medium', 'Will, Supernatural vs. defense / Negates Cover',                 'Damage +0 Weak, Penetrating / Target Hurt? Caster chooses one: Confused or Enraged / Target Afraid? Instead, caster chooses one: Marionette or Berserk / Counters Pawang', 'Magic, Holy, Indirect, Pawang', 'Pawang Apprentice', 20],
        ['Santet',  'Off hand, Wand, or Rod', 'Short',  'Will, Leadership vs. target\'s Mind, Will, Presence',            'Damage flat 3 Weak, Penetrating / Target is Unholy? +3 Damage / Sweep Near / Counters Unholy',                                                                  'Magic, Holy, Indirect, Dukun',     'Dukun Apprentice', 10],
        ['Sirep',   'Off hand, Wand, or Rod', 'Short',  'Will, Academics vs. target\'s Mind, Will, Endurance',            'Damage flat 4 Weak, Penetrating / Target is Unholy? +4 Damage / Target Hurt? Also Asleep! / Target Afraid? Also Unconscious! / Counters Unholy',                'Magic, Holy, Indirect, Dukun',     'Dukun Apprentice', 20],

        // ── Spells as Weapons (Budjaduya / Tazekar) ─────────────────────────
        ['Clysmian Foul', 'Off hand', 'Medium',              'Mind, Swimming vs. defense',             'Damage +0 Critical, Penetrating / Sweep Short', 'Magic, Water, Budjaduya, Loud', 'Clysmian Foul',  10],
        ['Faint Mirage',  'Off hand', 'Short (Counters Magic)','Species, Academics vs. target\'s Will, Inquiry', 'Confused / Sweep Short',          'Magic, Tazekar, Unholy',           'Tazekar\'s Path', 10],
        ['Sand Geyser',   'Off hand', 'Medium',              'Body, Digging vs. defense',              'Damage +4 Weak Critical / Sweep Short',         'Magic, Earth, Budjaduya, Loud', 'Sand Geyser',    10],
        ['Scathefire',    'Off hand', 'Medium',              'Will, Presence vs. defense',             'Damage +1 Critical, On Fire / Sweep Short',     'Magic, Fire, Budjaduya, Loud',  'Scathefire',     10],
        ['Sirocco Blast', 'Off hand', 'Medium',              'Speed, Weather Sense vs. defense',       'Damage +2 Critical, Knockdown / Sweep Short',   'Magic, Air, Budjaduya, Loud',   'Sirocco Blast',  10],

        // ── Changes Magic (basic spells) ────────────────────────────────────
        ['Collapsing the Weakest Column', 'Rod, Wand or Off-Hand', 'Medium',              'Mind, Inquiry vs. group\'s Body, Will, Endurance',             'Lowest Roller: Damage of 1 point for each target who failed to resist, Penetrating', 'Magic, Changes, Mountain',       'The Way of Changes', 10],
        ['Permeation of Wind',            'Rod, Wand or Off-Hand', 'Medium',              'Mind, Weather Sense vs. group\'s Body, Mind, Weather Sense',    'Lowest Roller: Reeling, Damage flat 2, Push 1 per success',                        'Magic, Changes, Air',            'The Way of Changes', 20],
        ['Quagmire of Doubt',             'Rod, Wand or Off-Hand', 'Medium',              'Mind, Negotiation vs. group\'s Mind, Will, Negotiation',        'Lowest Roller: Reeling, Confused',                                                  'Magic, Changes, Marsh',          'The Way of Changes', 30],
        ['The Turmoil of Silence',        'Rod, Wand or Off-Hand', 'Medium (Counter Magic)','Mind, Supernatural vs. group\'s Mind, Will, Supernatural',   'Lowest Roller: Reeling, Silenced',                                                  'Magic, Changes, Thunder, Loud',  'The Way of Changes', 40],
        ['Display of Propriety',          'Rod, Wand or Off-Hand', 'Medium',              'Group of targets / Mind, Will, Inquiry vs. 3 / Group of targets / Mind, Will, Deceit vs. 3', 'Unreal or Unholy Targets Only: Reeling, Damage +0 Slaying, Penetrating / Other targets: Reeling', 'Magic, Changes, Heaven', 'The Way of Changes', 50],

        // ── Changes Hexagram Magic ───────────────────────────────────────────
        ['Chase the Wind and Clutch at Shadows', 'Off hand', 'Medium', 'Crowd of all allies and foes / Allies roll Body, Will vs. 3 / Foes roll Body, Will vs. 3',                                 'All allies: Rallied / All foes: Confused, Knockdown',                                                                                                  'Magic, Changes, Hexagram, Air, Thunder',     'Secrets of Changes Magic', 10],
        ['Coiling Storm',                        'Off hand', 'Medium (Counter Magic)', 'Mind, Supernatural, Weather Sense vs. group\'s Body, Mind, Supernatural, Weather Sense',                   'Lowest Roller: Reeling, Damage flat 3 Penetrating, Push 2 per success / All other targets: Reeling, Damage flat 2, Push 2',                           'Magic, Changes, Hexagram, Air, Proscribed',  'Secrets of Changes Magic', 20],
        ['Crush the Mountain Rebels',            'Off hand', 'Medium (Counter Magic)', 'Mind, Inquiry, Supernatural vs. group\'s Body, Will, Endurance, Supernatural',                             'Lowest Roller: Damage of 1 plus 1 per target who failed to resist, Penetrating / All other targets: Reeling, Damage flat 2 Penetrating',              'Magic, Changes, Hexagram, Mountain',         'Secrets of Changes Magic', 30],
        ['Entice the Foes to Leave the Mountain Lair', 'Off hand', 'Medium',           'Crowd of all allies and foes / Allies roll Body, Will vs. 3 / Foes roll Body, Will vs. 3',                'All allies: Rallied / All foes: Confused, Push 1',                                                                                                      'Magic, Changes, Hexagram, Mountain, Thunder','Secrets of Changes Magic', 40],
        ['Force of a Thunderbolt',               'Off hand', 'Medium (Counter Magic)', 'Mind, Supernatural vs. group\'s Mind, Will, Supernatural',                                                 'Lowest Roller: Damage flat 3 Penetrating, Reeling, Silenced / All other targets: Silenced',                                                           'Magic, Changes, Hexagram, Thunder, Loud',    'Secrets of Changes Magic', 50],
        ['Lofty Path of Precipitousness',        'Off hand', 'Medium',                 'Crowd of all allies and foes / Allies roll Body, Mind vs. 3 / Foes roll Body, Mind vs. 3',                'All allies: Rallied / All foes: Reeling, Hurt, Knockdown',                                                                                              'Magic, Changes, Hexagram, Mountain, Marsh',  'Secrets of Changes Magic', 60],
        ['Sound in the East, Strike in the West','Off hand', 'Medium',                 'Crowd of all allies and foes / Allies roll Mind, Will vs. 3 / Foes roll Mind, Will vs. 3',                'All allies: Confused / All foes: Knockdown',                                                                                                           'Magic, Changes, Hexagram, Marsh, Thunder',   'Secrets of Changes Magic', 70],
        ['Uncertainty of Mortality',             'Off hand', 'Medium (Counter Magic)', 'Mind, Negotiation, Supernatural vs. group\'s Mind, Will, Negotiation, Supernatural',                       'Lowest Roller: Reeling, Confused, Damage flat 3 Penetrating / All other targets: Confused',                                                           'Magic, Changes, Hexagram, Marsh',            'Secrets of Changes Magic', 80],
        ['Wind Blows and Rain Falls',            'Off hand', 'Medium',                 'Crowd of all allies and foes / Allies roll Speed, Mind vs. 3 / Foes roll Speed, Mind vs. 3',               'All allies: Push 1, Knockdown / All foes: Push 1, Knockdown',                                                                                           'Magic, Changes, Hexagram, Air, Marsh',       'Secrets of Changes Magic', 90],
        ['A World of Ice and Hail',              'Off hand', 'Medium',                 'Crowd of all allies and foes / Allies roll Speed, Body vs. 3 / Foes roll Speed, Body vs. 3',               'All allies: Push 1, Reeling, Hurt / All foes: Push 1, Reeling, Hurt',                                                                                   'Magic, Changes, Hexagram, Air, Mountain',    'Secrets of Changes Magic', 100],

        // ── Tàoist Base Spells ───────────────────────────────────────────────
        ['Clod of Earth',   'Rod, Wand or Off-Hand', 'Medium / Counter Water', 'Body, Speed, Digging',   'Damage +1 Critical',               'Magic, Tàoist, Earth', 'Taoist Apprentice', 10],
        ['Spark of Fire',   'Rod, Wand or Off-Hand', 'Medium / Counter Metal', 'Speed, Will, Presence',  'Damage +0 Critical, On Fire',      'Magic, Tàoist, Fire',  'Taoist Apprentice', 20],
        ['Sliver of Metal', 'Rod, Wand or Off-Hand', 'Medium / Counter Wood',  'Body, Mind, Craft',      'Damage +0 Critical, Penetrating',  'Magic, Tàoist, Metal', 'Taoist Apprentice', 30],
        ['Splinter of Wood','Rod, Wand or Off-Hand', 'Medium / Counter Earth', 'Mind, Will, Endurance',  'Damage +3 Critical, Weak',         'Magic, Tàoist, Wood',  'Taoist Apprentice', 40],
        ['Spray of Water',  'Rod, Wand or Off-Hand', 'Medium / Counter Fire',  'Speed, Mind, Swimming',  'Damage +0 Critical, Push 1',       'Magic, Tàoist, Water', 'Taoist Apprentice', 50],

        // ── Tàoist Hexagrams ─────────────────────────────────────────────────
        ['Bursting of the Dam',               'Off hand', 'Medium', 'Explosion: Reach or Near / Mind, Will, Endurance, Swimming vs. defense',              'Damage flat 4 Weak, Push 2',                                    'Magic, Hexagram, Tàoist, Water, Wood, Loud',     'Secrets of Wood Taoist Magic',  10],
        ['Cacophony of Instruments',          'Off hand', 'Medium', 'Explosion: Reach or Near / Body, Mind, Will, Craft, Endurance vs. defense',            'Damage flat 3 Weak Penetrating, Confused',                      'Magic, Hexagram, Tàoist, Metal, Wood, Loud',     'Secrets of Metal Taoist Magic', 10],
        ['Cascade of Fire',                   'Off hand', 'Medium / Counter Metal', 'Speed, Will, Presence, Supernatural vs. defense',                      'Damage +1, Penetrating, On Fire / Sweep Medium',               'Magic, Hexagram, Tàoist, Fire, Loud',            'Secrets of Fire Taoist Magic',  10],
        ['Cedar Mattock',                     'Off hand', 'Medium / Counter Earth', 'Mind, Will, Endurance, Supernatural vs. defense',                      'Damage +3 Weak, Push 2 / Sweep Medium',                        'Magic, Hexagram, Tàoist, Wood, Loud',            'Secrets of Wood Taoist Magic',  20],
        ['Cleaver of the Gods',               'Off hand', 'Medium / Counter Wood',  'Body, Mind, Craft, Supernatural vs. defense',                          'Damage +3 / Sweep Medium',                                     'Magic, Hexagram, Tàoist, Metal, Loud',           'Secrets of Metal Taoist Magic', 20],
        ['Dodge a Pit Only to Fall in a Well','Off hand', 'Medium', 'Explosion: Reach / Body, Speed, Mind, Will, Digging, Swimming vs. defense',            'Damage flat 5 Weak, Knockdown',                                'Magic, Hexagram, Tàoist, Earth, Water, Loud',    'Secrets of Earth Taoist Magic', 10],
        ['Fight Inferno with a Cup of Water', 'Off hand', 'Medium', 'Explosion: Reach or Near / Speed, Mind, Will, Presence, Swimming vs. defense',         'Damage flat 4 Weak, On Fire',                                  'Magic, Hexagram, Tàoist, Fire, Water, Loud',     'Secrets of Water Taoist Magic', 10],
        ['Leave Not Even a Blade of Grass',   'Off hand', 'Medium', 'Explosion: Reach / Body, Speed, Mind, Will, Digging, Endurance vs. defense',           'Damage flat 3, Knockdown',                                     'Magic, Hexagram, Tàoist, Earth, Wood, Loud',     'Secrets of Earth Taoist Magic', 20],
        ['Momentum of an Avalanche',          'Off hand', 'Medium / Counter Water', 'Body, Speed, Digging, Supernatural vs. defense',                        'Damage +2, Knockdown / Sweep Medium',                          'Magic, Hexagram, Tàoist, Earth, Loud',           'Secrets of Earth Taoist Magic', 30],
        ['More Tinder Does Not Put Out the Fire','Off hand','Medium','Explosion: Reach or Near / Speed, Mind, Will, Endurance, Presence vs. defense',        'Damage flat 3, On Fire',                                       'Magic, Hexagram, Tàoist, Fire, Wood, Loud',      'Secrets of Fire Taoist Magic',  20],
        ['A Night in the Graveyard of Swords','Off hand', 'Medium', 'Explosion: Reach or Near / Body, Speed, Mind, Will, Craft, Digging vs. defense',       'Damage flat 2 Penetrating, Afraid',                            'Magic, Hexagram, Tàoist, Earth, Metal, Loud',    'Secrets of Earth Taoist Magic', 40],
        ['Scald with Irons from the Forge',   'Off hand', 'Medium', 'Explosion: Reach / Body, Speed, Mind, Will, Craft, Presence vs. defense',              'Damage flat 2 Penetrating, On Fire',                           'Magic, Hexagram, Tàoist, Fire, Metal, Loud',     'Secrets of Fire Taoist Magic',  30],
        ['Stir Up the Flames and Get Burnt',  'Off hand', 'Medium', 'Explosion: Reach or Near / Body, Speed, Mind, Will, Digging, Presence vs. defense',    'Damage flat 3, On Fire',                                       'Magic, Hexagram, Tàoist, Earth, Fire, Loud',     'Secrets of Earth Taoist Magic', 50],
        ['Suffer the Rain and Rust Away',     'Off hand', 'Medium', 'Explosion: Reach or Near / Body, Mind, Will, Craft, Swimming vs. defense',             'Damage flat 3 Weak Penetrating, Slowed',                       'Magic, Hexagram, Tàoist, Metal, Water, Loud',    'Secrets of Metal Taoist Magic', 30],
        ['Violent Aegir',                     'Off hand', 'Medium / Counter Fire',  'Mind, Will, Supernatural, Swimming vs. defense',                        'Damage flat 4, Push 1 per success / Sweep Medium',             'Magic, Hexagram, Tàoist, Water, Loud',           'Secrets of Water Taoist Magic', 20],

        // ── Purity Magic ─────────────────────────────────────────────────────
        ['Call Down the Lightning', 'Rod, Wand or Off-Hand', 'Medium', 'Speed, Mind, Weather Sense vs. 3 / Target\'s Speed, Dodge vs. 3', 'Damage +0 Penetrating', 'Magic, Purity, Proscribed, Indirect', 'Purity Apprentice', 10],
        ['Righteous Arrow',         'Rod, Wand or Off-Hand', 'Medium', 'Mind, Will, Presence vs. 3 / Target\'s Speed, Dodge vs. 3',       'Damage +1',             'Magic, Purity, Holy',                  'Purity Apprentice', 20],
    ];
}

// ---------------------------------------------------------------------------
// cg_install_spells — idempotent: create table + upsert all rows
// ---------------------------------------------------------------------------

function cg_install_spells(): void {
    cg_ensure_spells_table();

    $t    = cg_spells_table();
    $rows = cg_spell_rows();

    // Truncate first so re-running is always idempotent
    cg_exec("TRUNCATE TABLE {$t}");

    $sql = "INSERT INTO {$t}
                (ct_name, ct_equip, ct_range, ct_attack_dice, ct_effect, ct_descriptors, ct_gift_name, ct_sort)
            VALUES (?,?,?,?,?,?,?,?)";

    $inserted = 0;
    foreach ($rows as $r) {
        try {
            cg_exec($sql, $r);
            $inserted++;
        } catch (Throwable $e) {
            error_log("[CG spells] insert failed for '{$r[0]}': " . $e->getMessage());
        }
    }

    cg_json(['success' => true, 'data' => "Installed {$inserted} spells."]);
}

// ---------------------------------------------------------------------------
// cg_get_spells_for_gifts
//
// POST params:
//   gift_ids[]  — array of gift IDs (ints) from the character's full gift set
//                 (free choices + career + species + xp gifts).
//
// Matching strategy (OR of all conditions):
//   1. ct_name matches a gift name exactly — each spell is its own gift
//      (e.g. gift "Fire Ball" → returns the "Fire Ball" spell row).
//   2. ct_gift_name matches a gift name — school-level gift grants all spells
//      in that school (e.g. a gift literally named "Journeyman Fire Magic").
//   3. ct_gift_name = 'Common Magic' when any gift contains the word "Magic".
// ---------------------------------------------------------------------------

function cg_get_spells_for_gifts(): void {
    $p = cg_prefix();
    $t = cg_spells_table();

    // Ensure the table exists — silently create it if this is the first call
    cg_ensure_spells_table();

    // Parse gift IDs from POST
    $raw = $_POST['gift_ids'] ?? $_POST['gift_ids[]'] ?? [];
    if (is_string($raw)) {
        $decoded = json_decode($raw, true);
        $raw = is_array($decoded) ? $decoded : [];
    }
    if (!is_array($raw)) $raw = [];

    $giftIds = array_values(array_filter(array_map('intval', $raw)));

    if (empty($giftIds)) {
        cg_json(['success' => true, 'data' => []]);
        return;
    }

    // Fetch gift names for all provided IDs
    $inPlaceholders = implode(',', array_fill(0, count($giftIds), '?'));
    $giftRows = cg_query(
        "SELECT ct_id AS id, ct_gifts_name AS name
         FROM {$p}customtables_table_gifts
         WHERE ct_id IN ({$inPlaceholders})",
        $giftIds
    );

    $giftNames = array_values(array_filter(array_column($giftRows, 'name')));
    $hasMagic  = (bool) array_filter($giftNames, fn($n) => stripos((string)$n, 'magic') !== false);

    if (empty($giftNames)) {
        cg_json(['success' => true, 'data' => []]);
        return;
    }

    $conditions = [];
    $params     = [];
    $nPh        = implode(',', array_fill(0, count($giftNames), '?'));

    // 1. Spell name matches a gift name (each spell is its own gift in the DB)
    $conditions[] = "ct_name IN ({$nPh})";
    $params       = array_merge($params, $giftNames);

    // 2. School-level gift: ct_gift_name matches a gift name
    $conditions[] = "ct_gift_name IN ({$nPh})";
    $params       = array_merge($params, $giftNames);

    // 3. Common Magic spells whenever the character has any Magic gift
    if ($hasMagic) {
        $conditions[] = "ct_gift_name = 'Common Magic'";
    }

    $where  = implode(' OR ', $conditions);
    $spells = cg_query(
        "SELECT ct_id AS id, ct_name AS name, ct_equip AS equip, ct_range AS `range`,
                ct_attack_dice AS attack_dice, ct_effect AS effect,
                ct_descriptors AS descriptors, ct_gift_name AS gift_name, ct_sort AS sort
         FROM {$t}
         WHERE {$where}
         ORDER BY ct_gift_name = 'Common Magic' DESC, ct_gift_name, ct_sort, ct_name",
        $params
    );

    cg_json(['success' => true, 'data' => $spells]);
}
