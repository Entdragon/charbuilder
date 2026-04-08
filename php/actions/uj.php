<?php
/**
 * Urban Jungle — data layer.
 *
 * Provides:
 *   uj_install_tables — CREATE TABLE IF NOT EXISTS for all trait tables.
 *   uj_install_data   — INSERT/UPSERT all Species, Type, Career, and Skill rows.
 *   uj_get_species    — list all published species (alphabetical).
 *   uj_get_types      — list all published type traits (alphabetical).
 *   uj_get_careers    — list all published career traits (alphabetical).
 *   uj_get_skills     — list all published skills (alphabetical).
 *
 * Table prefix follows the same cg_prefix() / CG_DB_PREFIX convention used
 * throughout the Ironclaw generator.
 */

declare(strict_types=1);

// ── Auth guard (admin-only write operations) ─────────────────────────────────

function uj_admin_require(): void {
    if (!cg_is_admin()) {
        cg_json(['success' => false, 'data' => 'Unauthorized.']);
        exit;
    }
}

// ── Table name helpers ────────────────────────────────────────────────────────

function uj_tbl(string $suffix): string {
    return cg_prefix() . 'uj_' . $suffix;
}

// ── CREATE TABLES ─────────────────────────────────────────────────────────────

// Internal — runs all CREATE TABLE IF NOT EXISTS statements; no auth, no JSON output.
function uj_create_tables_internal(): array {
    $p = cg_prefix();

    $sqls = [
        // Species traits
        "CREATE TABLE IF NOT EXISTS `{$p}uj_species` (
            `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `name`        VARCHAR(100) NOT NULL DEFAULT '',
            `slug`        VARCHAR(100) NOT NULL DEFAULT '',
            `description` TEXT,
            `skill_1`     VARCHAR(60)  NOT NULL DEFAULT '',
            `skill_2`     VARCHAR(60)  NOT NULL DEFAULT '',
            `skill_3`     VARCHAR(60)  NOT NULL DEFAULT '',
            `gift_1`      VARCHAR(100) NOT NULL DEFAULT '',
            `gift_2`      VARCHAR(100) NOT NULL DEFAULT '',
            `published`   TINYINT(1)   NOT NULL DEFAULT 1,
            `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        // Type traits
        "CREATE TABLE IF NOT EXISTS `{$p}uj_types` (
            `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `name`        VARCHAR(100) NOT NULL DEFAULT '',
            `slug`        VARCHAR(100) NOT NULL DEFAULT '',
            `description` TEXT,
            `skill_1`     VARCHAR(60)  NOT NULL DEFAULT '',
            `skill_2`     VARCHAR(60)  NOT NULL DEFAULT '',
            `skill_3`     VARCHAR(60)  NOT NULL DEFAULT '',
            `gift_1`      VARCHAR(100) NOT NULL DEFAULT '',
            `soak_1`      VARCHAR(100) NOT NULL DEFAULT '',
            `soak_2`      VARCHAR(100) NOT NULL DEFAULT '',
            `gear`        TEXT,
            `published`   TINYINT(1)   NOT NULL DEFAULT 1,
            `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        // Career traits
        "CREATE TABLE IF NOT EXISTS `{$p}uj_careers` (
            `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `name`        VARCHAR(100) NOT NULL DEFAULT '',
            `slug`        VARCHAR(100) NOT NULL DEFAULT '',
            `description` TEXT,
            `skill_1`     VARCHAR(60)  NOT NULL DEFAULT '',
            `skill_2`     VARCHAR(60)  NOT NULL DEFAULT '',
            `skill_3`     VARCHAR(60)  NOT NULL DEFAULT '',
            `gift_1`      VARCHAR(100) NOT NULL DEFAULT '',
            `gift_2`      VARCHAR(100) NOT NULL DEFAULT '',
            `gear`        TEXT,
            `published`   TINYINT(1)   NOT NULL DEFAULT 1,
            `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        // Gifts
        "CREATE TABLE IF NOT EXISTS `{$p}uj_gifts` (
            `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `name`          VARCHAR(100) NOT NULL DEFAULT '',
            `slug`          VARCHAR(100) NOT NULL DEFAULT '',
            `subtitle`      VARCHAR(200) NOT NULL DEFAULT '',
            `description`   TEXT,
            `gift_type`     ENUM('basic','advanced') NOT NULL DEFAULT 'basic',
            `recharge`      VARCHAR(60)  NOT NULL DEFAULT '',
            `requires_text` TEXT,
            `published`     TINYINT(1)   NOT NULL DEFAULT 1,
            `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        // Soaks
        "CREATE TABLE IF NOT EXISTS `{$p}uj_soaks` (
            `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `name`           VARCHAR(100) NOT NULL DEFAULT '',
            `slug`           VARCHAR(100) NOT NULL DEFAULT '',
            `damage_negated` TINYINT      NOT NULL DEFAULT 0,
            `recharge`       VARCHAR(60)  NOT NULL DEFAULT '',
            `side_effect`    VARCHAR(200) NOT NULL DEFAULT '',
            `description`    TEXT,
            `soak_type`      ENUM('basic','advanced') NOT NULL DEFAULT 'basic',
            `published`      TINYINT(1)   NOT NULL DEFAULT 1,
            `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        // Skills
        "CREATE TABLE IF NOT EXISTS `{$p}uj_skills` (
            `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `name`             VARCHAR(100) NOT NULL DEFAULT '',
            `slug`             VARCHAR(100) NOT NULL DEFAULT '',
            `description`      TEXT,
            `paired_trait`     VARCHAR(60)  NOT NULL DEFAULT '',
            `sample_favorites` TEXT,
            `gift_notes`       TEXT,
            `published`        TINYINT(1)   NOT NULL DEFAULT 1,
            `created_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    ];

    $created = [];
    foreach ($sqls as $sql) {
        cg_exec($sql);
        preg_match('/CREATE TABLE IF NOT EXISTS `([^`]+)`/', $sql, $m);
        $created[] = $m[1] ?? '?';
    }
    return $created;
}

// AJAX handler — auth-gated, returns JSON.
function uj_install_tables(): void {
    uj_admin_require();
    $created = uj_create_tables_internal();
    cg_json(['success' => true, 'data' => 'Tables ensured: ' . implode(', ', $created)]);
}

// ── INSERT / UPSERT DATA ──────────────────────────────────────────────────────

function uj_install_data(): void {
    uj_admin_require();

    // Always ensure tables exist before inserting — safe to run repeatedly.
    uj_create_tables_internal();

    $counts = [];
    $counts['species']  = uj_install_species();
    $counts['types']    = uj_install_types();
    $counts['careers']  = uj_install_careers();
    $counts['skills']   = uj_install_skills();
    $counts['gifts']    = uj_install_gifts();
    $counts['soaks']    = uj_install_soaks();

    $msg = "Upserted: {$counts['species']} species, {$counts['types']} types, {$counts['careers']} careers, {$counts['skills']} skills, {$counts['gifts']} gifts, {$counts['soaks']} soaks.";
    cg_json(['success' => true, 'data' => $msg]);
}

// ── Species data ──────────────────────────────────────────────────────────────

function uj_install_species(): int {
    $t = uj_tbl('species');

    $rows = [
        ['Alligator', 'alligator',
         'Big jaws and a lot of scales, you can be one tough customer. You\'re much more at home in the swamps and by the shore.',
         'Endurance', 'Fighting', 'Observation', 'Brawling', 'Swimming'],
        ['Anteater', 'anteater',
         'If another mug turns to you with an ever-lovin\' smile and says "Why the long face?", you are well within your rights to slug them.',
         'Athletics', 'Fighting', 'Observation', 'Brawling', 'Coward'],
        ['Armadillo', 'armadillo',
         'Most people recognize you by your hard shell. What they don\'t always know is what a fantastic jumper you are.',
         'Athletics', 'Endurance', 'Presence', 'Coward', 'Jumping'],
        ['Badger', 'badger',
         'Short, bristly, and powerful, you and your kind have a reputation for being mainly active at night and for not giving two bits about what other people think.',
         'Endurance', 'Fighting', 'Presence', 'Brawling', 'Stealth'],
        ['Bat', 'bat',
         'When most people think of bats, they think of the micro-bat, with the fancy squeaks and the flying around at night. The vampire bat is actually a rarity, but you can talk in the funny Transylvanian accent if you think it will get you anywhere.',
         'Athletics', 'Evasion', 'Observation', 'Flight', 'Stealth'],
        ['Bear', 'bear',
         'Largest land carnivore and notoriously poor sport.',
         'Endurance', 'Fighting', 'Presence', 'Giant', 'Wrestling'],
        ['Boar', 'boar',
         'Some of that weight that you carry is muscle. The rest of it is all attitude.',
         'Endurance', 'Fighting', 'Presence', 'Brawling', 'Tracking'],
        ['Cat', 'cat',
         'Smooth and sophisticated, with just a touch of aloofness, you have a light touch and you always land on your feet. When you\'re not delicate and graceful, you have the look of someone who just willfully intended to not be delicate and graceful.',
         'Athletics', 'Evasion', 'Observation', 'Acrobat', 'Brawling'],
        ['Cattle', 'cattle',
         'They think the country folk are big, dumb, and simple. They underestimate you a lot. You can use that to your advantage.',
         'Athletics', 'Endurance', 'Observation', 'Brawling', 'Giant'],
        ['Cheetah', 'cheetah',
         'When people say that you\'re the fastest land mammal, that puts a lot of pressure on you. Let\'s just say that you\'re half-fast and move on.',
         'Athletics', 'Fighting', 'Observation', 'Brawling', 'Running'],
        ['Coyote', 'coyote',
         'One man\'s trash is another man\'s treasure. You are an expert at finding value in the things other people leave behind.',
         'Athletics', 'Fighting', 'Observation', 'Brawling', 'Tracking'],
        ['Crocodile', 'crocodile',
         'Those tears are just for show.',
         'Deceit', 'Endurance', 'Fighting', 'Brawling', 'Swimming'],
        ['Deer', 'deer',
         'Sometimes, you can just bat those big eyelashes of yours, and people will believe that you\'re just an innocent victim of all this sordid wickedness.',
         'Athletics', 'Evasion', 'Observation', 'Coward', 'Running'],
        ['Dog', 'dog',
         'Anyone can sleep, but few can die. When it all hits the fan and the bullets are flying, it\'s time to show them what we can do when we stand together.',
         'Athletics', 'Observation', 'Tactics', 'Brawling', 'Tracking'],
        ['Donkey', 'donkey',
         'The same stubbornness that makes you humbly work an honest living is the same stubbornness that can make you dislodge the middle pillars and bring this whole house crashing down.',
         'Endurance', 'Fighting', 'Presence', 'Brawling', 'Coward'],
        ['Elephant', 'elephant',
         'The problem with being this big and strong is that everyone is always asking you to help them move.',
         'Athletics', 'Endurance', 'Presence', 'Brawling', 'Giant'],
        ['Ferret', 'ferret',
         'Being lithe, quick, and flexible gives you a serious knack for larceny. Not saying you have to do such things, just saying you\'d be good at it.',
         'Athletics', 'Evasion', 'Observation', 'Contortionist', 'Coward'],
        ['Fox', 'fox',
         'When they try to catch you, smile. Because if by some miracle they do catch you, that\'s no excuse for not looking your best in front of your admirers.',
         'Athletics', 'Evasion', 'Observation', 'Coward', 'Danger Sense'],
        ['Gecko', 'gecko',
         'Wide eyes mean you can see a lot of trouble. Sticky fingers mean you can get into some, too.',
         'Athletics', 'Deceit', 'Evasion', 'Climbing', 'Coward'],
        ['Goat', 'goat',
         'Why choose a hill to die on, when you can choose a mountain?',
         'Endurance', 'Fighting', 'Presence', 'Brawling', 'Climbing'],
        ['Horse', 'horse',
         'Strength, speed, grace... if only those were enough to make a living in this world.',
         'Athletics', 'Endurance', 'Tactics', 'Giant', 'Running'],
        ['Jackal', 'jackal',
         'Look, it\'s a dog-eat-dog world out there. You know how it is, and you\'re not afraid to do what\'s got to be done.',
         'Athletics', 'Endurance', 'Observation', 'Brawling', 'Stealth'],
        ['Lion', 'lion',
         'Someone\'s got to be king of the urban jungle. Who\'s a better candidate for the job than you?',
         'Fighting', 'Presence', 'Tactics', 'Brawling', 'Stealth'],
        ['Monkey', 'monkey',
         'In this modern age, it\'s always nice to meet somebody who can work with their hands.',
         'Craft', 'Observation', 'Tactics', 'Climbing', 'Dexterity'],
        ['Mouse', 'mouse',
         'The nail that sticks out is the one that gets pounded. Sometimes it\'s best to make yourself quiet and small, and wait until all this blows over.',
         'Athletics', 'Evasion', 'Observation', 'Contortionist', 'Coward'],
        ['Otter', 'otter',
         'As graceful in the water as out of it, it doesn\'t hurt that you can dazzle them with your big eyes and your winning smile, neither.',
         'Athletics', 'Evasion', 'Observation', 'Contortionist', 'Swimming'],
        ['Panther', 'panther',
         'There\'s nothing like a little apex predation to really give you a swell feeling, right here in the chest.',
         'Evasion', 'Fighting', 'Observation', 'Brawling', 'Tracking'],
        ['Pigeon', 'pigeon',
         'Say, what did you do before you came to the big city?',
         'Athletics', 'Evasion', 'Observation', 'Coward', 'Flight'],
        ['Porcupine', 'porcupine',
         'It continues to amaze you that people keep starting trouble with you, when you couldn\'t make it any clearer that you are not one to start trouble with.',
         'Evasion', 'Observation', 'Presence', 'Coward', 'Quills'],
        ['Possum', 'possum',
         'Sometimes the best strategy is to just play dead and wait the whole thing out.',
         'Athletics', 'Deceit', 'Evasion', 'Climbing', 'Coward'],
        ['Rabbit', 'rabbit',
         'A quick-witted and fleet-footed sort like yourself has nothing to fear, as long as no one throws you in the briar patch.',
         'Athletics', 'Evasion', 'Observation', 'Coward', 'Jumping'],
        ['Raccoon', 'raccoon',
         'Just because you\'re wearing a mask is no reason to start a rumor that you\'re some kind of thief. That\'s the worst kind of rumor, especially when it\'s true.',
         'Athletics', 'Evasion', 'Observation', 'Climbing', 'Dexterity'],
        ['Rat', 'rat',
         'It\'s when they push you into a corner, and you\'ve got nothing left to lose, that they finally learn just how dangerous you really are.',
         'Athletics', 'Evasion', 'Observation', 'Brawling', 'Contortionist'],
        ['Rhinoceros', 'rhinoceros',
         'If they just took the time to get to know you, they\'d figure out you\'re not so bad.',
         'Endurance', 'Fighting', 'Observation', 'Brawling', 'Giant'],
        ['Shrew', 'shrew',
         'They say, "the bigger they are, the harder they fall." Maybe that\'s true for you, too, because you are a hell of a lot tougher than you look, small fry.',
         'Athletics', 'Fighting', 'Presence', 'Brawling', 'Stealth'],
        ['Skunk', 'skunk',
         'You\'re a walking contradiction, as others admire both your sleek and silky grace while they dread your vitriol and scorn. Is it better to be feared or to be loved?',
         'Athletics', 'Evasion', 'Presence', 'Acrobat', 'Spray'],
        ['Sloth', 'sloth',
         'People tend to assume that the quiet, slow people aren\'t that smart. You can really use that to your advantage.',
         'Athletics', 'Deceit', 'Presence', 'Climbing', 'Stealth'],
        ['Snake', 'snake',
         'We\'re not sure about the hands thing, either.',
         'Athletics', 'Deceit', 'Evasion', 'Contortionist', 'Wrestling'],
        ['Sparrow', 'sparrow',
         'So what\'s the greatest: your melodious voice, your magnificent plumage, or your gentle humility?',
         'Athletics', 'Evasion', 'Observation', 'Flight', 'Singing'],
        ['Tiger', 'tiger',
         'You just come over here and try to tame my fearful symmetry, pal.',
         'Evasion', 'Fighting', 'Observation', 'Brawling', 'Climbing'],
        ['Weasel', 'weasel',
         'Say, how come everyone thinks the ferrets are so cute, and that the otters are so adorable? You\'re a mustelid too, you\'re one of the gang. It\'s not fair.',
         'Athletics', 'Evasion', 'Observation', 'Brawling', 'Contortionist'],
        ['Wolf', 'wolf',
         'The city might have the neon lights and the grand spotlights, but nothing is more radiant than the full moon on a clear night.',
         'Athletics', 'Observation', 'Tactics', 'Brawling', 'Tracking'],
    ];

    $count = 0;
    foreach ($rows as [$name, $slug, $desc, $s1, $s2, $s3, $g1, $g2]) {
        cg_exec(
            "INSERT INTO `$t`
                (name, slug, description, skill_1, skill_2, skill_3, gift_1, gift_2)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                name=VALUES(name), description=VALUES(description),
                skill_1=VALUES(skill_1), skill_2=VALUES(skill_2), skill_3=VALUES(skill_3),
                gift_1=VALUES(gift_1), gift_2=VALUES(gift_2)",
            [$name, $slug, $desc, $s1, $s2, $s3, $g1, $g2]
        );
        $count++;
    }
    return $count;
}

// ── Type data ─────────────────────────────────────────────────────────────────

function uj_install_types(): int {
    $t = uj_tbl('types');

    // [name, slug, description, skill_1, skill_2, skill_3, gift_1, soak_1, soak_2, gear]
    $rows = [
        ['Angel', 'angel',
         'You might be the wide-eyed innocent, blissfully ignorant of the way we do things outside of Kansas. Or you might be a modern-day saint, pure of heart in a way that\'s too good to believe. Play an Angel type if you want a character whose inner purity is a light in this dark, sordid place.',
         'Endurance', 'Negotiation', 'Questioning',
         'Noncombatant', 'Distress Soak −4', '',
         'A letter from home, from a parent or guardian, who says they love you and that they wish you the best, and that you can do anything if you just believe in yourself.'],
        ['Authority', 'authority',
         'From the government agent to the beat cop, from the district attorney to the police detective, from the forensic investigator to the top-secret scientist, this type is a catch-all for all kinds of characters who have some form of legal authority from the U.S. Government.',
         'Observation', 'Presence', 'Questioning',
         'Legal Authority', 'Injury Soak −4', '',
         'A badge from your government agency.'],
        ['Boss', 'boss',
         'People depend on you. Maybe you\'re the toughest in a gang of street thugs. Maybe it\'s your family who need you to provide for them. Or maybe you\'ve just got the natural charisma that makes people do what you say. Play a Boss type if you want to have followers — that is, minor characters controlled by the Game Host — who help you get things done.',
         'Negotiation', 'Presence', 'Tactics',
         'Entourage', 'Distress Soak −4', '',
         'A fancy scarf, cufflink, hat, or other article that you\'ve never seen without. If any of your entourage finds this, they would instantly know that you must be in trouble.'],
        ['Broken', 'broken',
         'Some can walk through fire and not singe a hair on their heads. That someone is not you. Maybe you lost your true love in some tragedy. Or maybe you ripped your beating heart right out of your chest, leaving you an empty husk. Play a Broken type if you want a character who is damaged goods but still wants to do right.',
         'Endurance', 'Evasion', 'Presence',
         'Noncombatant', 'Frenzy Soak −2', '',
         'A good-luck charm or keepsake that you sleep with. Maybe one of these nights, it will keep away the bad dreams.'],
        ['Crooked', 'crooked',
         'You might be a con artist who enjoys the challenge of fleecing the rubes out of their nickels and dimes. Or you could be the career criminal, willing to break and to enter to get yet another big score. Play a Crooked type if you want a character who lies, cheats, and steals to get things done.',
         'Deceit', 'Negotiation', 'Questioning',
         'Leadership', 'Sneaky Soak −2', '',
         'Three bobby pins, a pocket knife, and a soft handkerchief.'],
        ['Drifter', 'drifter',
         'You had your reasons to leave home. Maybe there was trouble with your family. Maybe it was trouble in the romance. Maybe it\'s not anything at all. You know that no one can ever go home again. Play a Drifter type if you want to be a wanderer who calls anywhere they lay their head, their home.',
         'Evasion', 'Observation', 'Transport',
         '', 'Winded Soak −1', 'Injury Soak −4',
         'A blurry picture of your former home. There\'s no one in it, just a building or two and the unforgiving sky.'],
        ['Egghead', 'egghead',
         'The dawn of the 20th Century is the modern age. Electricity! Radio! Atomic power! You know the first hundred digits of π, you\'ve memorized the periodic table. You\'ll drag this world out of superstitious mumbo-jumbo and into the future. Play an Egghead type if you want a character who has a head for science and a hand for machines.',
         'Academics', 'Craft', 'Transport',
         'Noncombatant', 'Injury Soak −4', '',
         'A slide rule.'],
        ['Famous', 'famous',
         'It\'s strange, isn\'t it? The way some people are well-known just for being well-known? Whatever "it" is, you\'ve got it: charisma, charm, looks, wit, you name it. You\'re going places, you\'re going to be a star. Play a Famous character if you want your character to be larger than life and the subject of constant attention.',
         'Deceit', 'Presence', 'Tactics',
         'Leadership', 'Injury Soak −4', '',
         'A nice comb, purse, or compact.'],
        ['Hard-Boiled', 'hard-boiled',
         'This isn\'t your first rodeo. You\'ve seen things. You\'ve done things. You\'re not afraid to get your hands dirty or to bust a few heads to get the goods. This world is tough, but you\'re tougher. You\'re everybody\'s nightmare and you\'re nobody\'s fool. Play a Hard-Boiled type if you want to be a tough-as-nails character who doesn\'t spook easily.',
         'Endurance', 'Presence', 'Shooting',
         '', 'Winded Soak −1', 'Hurt Soak −3',
         'A hip flask.'],
        ['Heart-of-Gold', 'heart-of-gold',
         'What happened to you? The world is tough, and you used to be tougher. You can\'t let other people get to you, they\'ll use you up and throw you away like yesterday\'s newspaper. Play a Heart-of-Gold type if you want your character to be ready to turn their life around.',
         'Observation', 'Presence', 'Tactics',
         'Bodyguard', 'Distress Soak −4', '',
         'A diary where you write your most personal confessions.'],
        ['Knight', 'knight',
         'They tell you that you\'ve got to play ball. They say that the nail that sticks out gets pounded. They tell you to go with the flow, don\'t make any waves, that you can\'t fight the system. You don\'t listen so good. Play a Knight type for a character with a personal code of ethics who will make a stand for what\'s right.',
         'Endurance', 'Presence', 'Tactics',
         'Bodyguard', 'Injury Soak −4', '',
         'A good book, that you read before you go to sleep at night, to remind yourself of how much better things could be.'],
        ['Loser', 'loser',
         'Other people tell you that you\'ll never amount to anything. Some of them pick on you. You don\'t remember doing anything to deserve all this trouble, but it still comes your way. Best not to be too bitter about it. You play the cards you\'re dealt. Play a Loser type if your character is a misunderstood stranger who takes more lumps than they deserve.',
         'Deceit', 'Evasion', 'Observation',
         '', 'Hurt Soak −3', 'Injury Soak −4',
         'Nothing, loser.'],
        ['Lucky', 'lucky',
         'Somebody upstairs must sure like you. The number of times you got away with it all… the number of times you almost got killed… You perform death-defying stunts with ease. But can your lucky streak last forever? Play a Lucky type if your character is a thrill-seeker who just can\'t leave well enough alone.',
         'Athletics', 'Craft', 'Evasion',
         'Luck', 'Hurt Soak −3', '',
         'A pocket Bible with a bullet stuck in it.'],
        ['Old', 'old',
         'You can remember a time before the talking pictures, before radio, before electricity. Sometimes, you wonder if it was better. Other times, you know it was worse. The times, they are a\'changin\', and you do your best to keep up. Play an Old type if you want a character with life experience who shows these whippersnappers how we used to do it back in your day.',
         'Academics', 'Craft', 'Tactics',
         'Leadership', 'Distress Soak −4', '',
         'An old, ragged photo from twenty years ago or more, depicting a dozen people, at least one of whom are still alive.'],
        ['Partner', 'partner',
         'You\'re one half of a set. You have an Ally who hangs around with you. Your ally could be a good friend, like a buddy from the war. Or your ally could be more than just a good friend, like a spouse or something. Play a Partner type if you want your character to start the game with a Minor Character as a friend.',
         'Observation', 'Presence', 'Tactics',
         'Ally [of choice]', 'Injury Soak −4', '',
         'A photograph of your Ally, with the words "best friends forever" or something like that, written on the back.'],
        ['Quiet', 'quiet',
         'They say it\'s the quiet ones you really have to watch out for. You\'re not much for words. Either you prefer to let your actions speak for you, or you\'re content to just sit back and watch the world go by. Play a Quiet type if you want your character to be a methodical, patient type who lets their actions speak for them.',
         'Evasion', 'Observation', 'Presence',
         'Noncombatant', 'Injury Soak −4', '',
         'A personal keepsake, that, while useless and of no monetary value, there are no known limits to how much effort you would expend to get it back.'],
        ['Rebel', 'rebel',
         'You\'re not one to play by the rules. You might be a young punk who doesn\'t need a cause or a reason … or maybe you\'re an old hand who\'ll let nobody kick them around. Play a Rebel type if you want your character to fight the system by any means necessary.',
         'Fighting', 'Presence', 'Shooting',
         '', 'Frenzy Soak −2', 'Hurt Soak −3',
         'Your personal manifesto.'],
        ['Rich', 'rich',
         'Maybe you were born with a silver spoon, old money from a long line of blue-bloods. Or maybe you\'re an enterprising sort, with fat stacks of cash from some shady operation. Play a Rich type if your character is an adventurer of means, more interested in thrills than money.',
         'Academics', 'Presence', 'Transport',
         'Wealth', 'Injury Soak −4', '',
         'An Expensive or Extravagant item of choice.'],
        ['Sultry', 'sultry',
         'Femmes fatale, lady-killers, lotharios, and vamps… this bunch are the ones who hit below the belt, if you know what I mean. Your walk makes grown men feel like little kids. Play a Sultry Type if you want to be a smooth talker who likes to take other characters for a ride.',
         'Deceit', 'Negotiation', 'Presence',
         'Leadership', 'Distress Soak −4', '',
         'A necklace, a ring, or some other jewelry given to you by a "good friend".'],
        ['Young', 'young',
         'How can they keep you back on the farm, when you\'ve seen the lights of the big city? Everyone keeps calling you a little bit, but no one tells you anything. Well, nuts to that. You\'re in a big hurry to grow up and to see the world. Play a Young type if you want to be a spunky sidekick, a runaway, or some other urchin with something to prove.',
         'Athletics', 'Evasion', 'Observation',
         '', 'Sneaky Soak −2', 'Winded Soak −1',
         'Three aggies.'],
    ];

    $count = 0;
    foreach ($rows as [$name, $slug, $desc, $s1, $s2, $s3, $g1, $soak1, $soak2, $gear]) {
        cg_exec(
            "INSERT INTO `$t`
                (name, slug, description, skill_1, skill_2, skill_3, gift_1, soak_1, soak_2, gear)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                name=VALUES(name), description=VALUES(description),
                skill_1=VALUES(skill_1), skill_2=VALUES(skill_2), skill_3=VALUES(skill_3),
                gift_1=VALUES(gift_1), soak_1=VALUES(soak_1), soak_2=VALUES(soak_2), gear=VALUES(gear)",
            [$name, $slug, $desc, $s1, $s2, $s3, $g1, $soak1, $soak2, $gear]
        );
        $count++;
    }
    return $count;
}

// ── Career data ───────────────────────────────────────────────────────────────

function uj_install_careers(): int {
    $t = uj_tbl('careers');

    // [name, slug, description, skill_1, skill_2, skill_3, gift_1, gift_2, gear]
    $rows = [
        ['Actor', 'actor',
         'A performer of stage or screen. Or of radio dramas, for that matter. (Television? Stay away from that, it\'s just a fad.) You\'re always eager for another big break. Watch out for the casting couch.',
         'Deceit', 'Observation', 'Presence',
         'Performance', 'Team Player',
         "Fancy Outfit.\nPocket Pistol.\nA copy of a Shakespeare play, bookmarked to your favorite soliloquy."],
        ['Agitator', 'agitator',
         'An exhorter of political action against the establishment. You might be an anarchist, a communist, a socialist, or an activist. And you\'re probably on a blacklist, if not a most-wanted list.',
         'Academics', 'Presence', 'Tactics',
         'Guts', 'Leadership',
         "Rough Outfit.\nA picket sign."],
        ['Artisan', 'artisan',
         'Those fancy skyscrapers don\'t put themselves up, you know. Someone\'s got to put the gilding, the neon, the frescos and the big signs. You get the big bucks because you have the rare skills.',
         'Academics', 'Craft', 'Negotiation',
         'Craft Specialty', 'Team Player',
         "Handy Outfit.\nUnion or Guild card."],
        ['Athlete', 'athlete',
         'When they first started the Olympics, it was mostly enthusiastic amateurs going to compete. But over time, people started to take this kind of thing way more seriously. Oh, and you might be good at sports, too.',
         'Athletics', 'Endurance', 'Fighting',
         'Team Player', 'Wrestling',
         "Rough Outfit.\nTowel."],
        ['Biker', 'biker',
         'You\'re quick to remind people that it\'s only 1% of all motorcyclists actually participate in criminal activity. You\'re not so quick to tell people what it is that you actually do all day, though.',
         'Endurance', 'Observation', 'Transport',
         'Motorcycling', 'Streetwise',
         "Rough Outfit.\nLast year's motorcycle."],
        ['Bootlegger', 'bootlegger',
         'Get yourself some pipes for a still. Gather up the stuff they just throw away at the farm. A few weeks later, and you\'ve got liquid gold. Shucks, it\'s almost like printing money.',
         'Deceit', 'Evasion', 'Transport',
         'Chemistry', 'Streetwise',
         "Rough Outfit.\nHoldout Shotgun."],
        ['Brute', 'brute',
         'They say that thinking with your fists will get you into trouble. But sometimes, your fists can get out of it, too. And let\'s face it, if you were the thinking type, you probably could\'ve gotten a nicer Career than this one.',
         'Endurance', 'Fighting', 'Presence',
         'Boxing', 'Streetwise',
         "Rough Outfit.\nPocket Pistol."],
        ['Bureaucrat', 'bureaucrat',
         'Look, nobody likes to be told by some pasty-faced paper-pusher that they got to pay overdue fines for some reason or another, but this is the price we pay to live in a civilized society.',
         'Academics', 'Negotiation', 'Observation',
         'Bribery', 'Research',
         "Fancy Outfit.\nPocket Pistol.\nInkpad and date-stamp."],
        ['Burglar', 'burglar',
         'If anyone asks, tell them you\'re here to fix the roof.',
         'Athletics', 'Craft', 'Evasion',
         'Sabotage', 'Streetwise',
         "Sneaky Outfit.\nPocket Pistol.\nLarge sack."],
        ['Celebrity', 'celebrity',
         'The only thing worse than having your name in all the papers is not having your name in all the papers.',
         'Deceit', 'Negotiation', 'Presence',
         'Entourage', 'Leadership',
         "Fancy Outfit.\nPocket Pistol.\nCigarette holder."],
        ['Clergy', 'clergy',
         'When will all this wickedness end? Thank heavens that someone\'s doing the good work down here.',
         'Academics', 'Negotiation', 'Questioning',
         'Diplomacy', 'Leadership',
         "Fancy Outfit.\nGood Book."],
        ['Con Artist', 'con-artist',
         'Never give a sucker an even break. The only thing you change faster than your name is your sales pitch.',
         'Deceit', 'Negotiation', 'Questioning',
         'Disguise', 'Fast-Talk',
         "Fancy Outfit.\nPocket Pistol.\nThree out-of-state Driver's Licenses."],
        ['Daredevil', 'daredevil',
         'From sitting on a pole for three months straight, to walking over Niagara Falls on a tightrope, to riding on the wing of a flying airplane… you can make a lot of money by trying to kill yourself in front of a paying audience.',
         'Athletics', 'Endurance', 'Transport',
         'Guts', 'Performance',
         "Fancy Outfit.\nPocket Pistol.\n50 flyers for the show."],
        ['Detective', 'detective',
         'You might be police who works for the city, or you might be a private eye. Either way, you\'ve seen the worst this city has to offer, so nothing surprises you any more. At least, let\'s hope it doesn\'t.',
         'Deceit', 'Observation', 'Questioning',
         'Gossip', 'Streetwise',
         "Rough Outfit.\nService Pistol.\nMagnifying glass."],
        ['Dilettante', 'dilettante',
         'Ain\'t this the life? Born with a silver spoon in your mouth and never having to worry about where your next meal is coming from. There is a long tradition of rich folks who solve crimes. I\'m told it\'s way more interesting than opera.',
         'Academics', 'Observation', 'Questioning',
         'High Society', 'Wealth',
         "Fancy Outfit.\nImported cigarettes.\nExpensive lighter."],
        ['Doctor', 'doctor',
         'From the country sawbones to the city surgeon, from the spaghetti surgeon to the surgeon general, you hold the power of life and death in your hands. No pressure, now.',
         'Academics', 'Observation', 'Questioning',
         'Medicine', 'Research',
         "Medical Outfit.\nHead mirror."],
        ['Explorer', 'explorer',
         'The map of the world gets filled in a little more every day, so if you want to go chart some wilderness, you\'d better get going. There\'s a thriving market for natural research, too.',
         'Academics', 'Athletics', 'Endurance',
         'Geography', 'Survival',
         "Rough Outfit.\nMagnum Pistol.\nBowie Knife."],
        ['Farmer', 'farmer',
         'It\'s not glamorous, but it\'s good honest work. For a laugh, make your Farmer trait your lowest Trait, and then listen to people make jokes about why you left the family farm after it went broke.',
         'Craft', 'Endurance', 'Observation',
         'Survival', 'Team Player',
         "Rough Outfit.\nVarmint Rifle.\nCorncob pipe."],
        ['Firefighter', 'firefighter',
         'Ah, the early 20th century, with buildings taller than our ladders can reach, and without exits to get everyone to safety. What starts out as just volunteers will turn into an entire service industry by 1950.',
         'Athletics', 'Endurance', 'Observation',
         'Firefighting', 'Team Player',
         "Rough Outfit.\nFireman's Ax."],
        ['Gambler', 'gambler',
         'All the necessary skills to bluff, to count cards, and to read people\'s poker faces. We advise sticking to card games, you can never trust dice.',
         'Deceit', 'Observation', 'Questioning',
         'Gossip', 'Streetwise',
         "Fancy Outfit.\nPocket Pistol.\nNothing (in your sleeves)."],
        ['Gangster', 'gangster',
         'From your first cigarette to your last dying day, you\'ve got family around to protect you. Of course, they don\'t call you "dead end kids" for nothin\'.',
         'Presence', 'Shooting', 'Tactics',
         'Bullet Conservation', 'Streetwise',
         "Rough Outfit.\nPocket Pistol.\nHandkerchief."],
        ['Hoodlum', 'hoodlum',
         'It\'s not that you particularly enjoy hurting people. It\'s that there\'s nothing you enjoy more.',
         'Fighting', 'Presence', 'Tactics',
         'Guts', 'Streetwise',
         "Rough Outfit.\nSwitchblade."],
        ['Hooker', 'hooker',
         'They say it\'s the world\'s oldest profession. How you wound up with this Career is your own business. You probably don\'t want to keep doing this, too long. This kind of profession, it ain\'t so kind.',
         'Deceit', 'Negotiation', 'Presence',
         'Streetwise', 'Team Player',
         "Fancy Outfit.\nSwitchblade."],
        ['Laborer', 'laborer',
         'You just want an honest day\'s pay for an honest day\'s work. Maybe you have a steady job, or maybe you travel to wherever the work is.',
         'Craft', 'Endurance', 'Observation',
         'Carousing', 'Team Player',
         "Handy Outfit.\nPocket knife."],
        ['Libertine', 'libertine',
         'Guided by shameless music and by animal instinct, you walk a path of degradation. Some people say the body is a temple, but yours is a carnival. Life\'s too short, so why not enjoy it? Just try not to lose too many weekends to the reefer madness.',
         'Deceit', 'Presence', 'Tactics',
         'Carousing', 'High Society',
         "Fancy Outfit.\nPocket Pistol.\nHeadache powder."],
        ['Magician', 'magician',
         'From private parties to theater houses, you can dazzle them with your parlor-tricks and your legerdemain. There\'s a spiritualism craze that you can capitalize on, too. Maybe throw in some foreign words, while you\'re at it. Abra-cadabra!',
         'Deceit', 'Observation', 'Presence',
         'Performance', 'Sleight of Hand',
         "Fancy Outfit.\nCollapsing cane."],
        ['Masked Vigilante', 'masked-vigilante',
         'Criminals are a cowardly and superstitious lot, so your disguise must be able to strike terror into their hearts. Striking your fist into their face works, too. That\'s the simple language that all of these law-breakers can understand.',
         'Athletics', 'Evasion', 'Fighting',
         'Disguise', 'Guts',
         "Sneaky Outfit.\nPocket Pistol.\n50 calling cards."],
        ['Mobster', 'mobster',
         'Look, pal, I don\'t know how they do things down out in the sticks, but this is the big city. This is the big time! You\'re made, now, so you got to dress the part, you got to show respect to those what need the respect, and you got to do what you got to do. Strictly business.',
         'Negotiation', 'Presence', 'Shooting',
         'Bullet Conservation', 'Streetwise',
         "Fancy Outfit.\nPocket Pistol.\nChallenge pin."],
        ['Motorist', 'motorist',
         'Is there any better invention that the automobile? Maybe you\'re a cab driver, busking fares in the big city. Or you might be a private chauffeur, that\'ll get you the big bucks. Auto racing is big now, too, in both the yards and on the strips. Whatever spins your wheels, pal.',
         'Evasion', 'Observation', 'Transport',
         'Driving', 'Team Player',
         "Rough Outfit.\nHoldout Shotgun.\nAn automobile that's only got 33 monthly payments left on it."],
        ['Musician', 'musician',
         'You sure meet lots of interesting people in the speakeasies, clubs, and big-band shows. If you\'re lucky, maybe you\'ll get some interesting money from them. My advice? Make "with my instrument" your Favorite for Presence skill, you\'ll thank me the next time you almost die on stage.',
         'Academics', 'Observation', 'Presence',
         'Gossip', 'Performance',
         "Fancy Outfit.\nYour instrument of choice."],

        // ── UJ20 ────────────────────────────────────────────────────────────
        ['Nurse', 'nurse',
         'I hear there\'s both female nurses and male nurses, did you know? This modern world, it never ceases to amaze. I can\'t think of a calling more noble than helping the sick get better. I\'m glad somebody \'round here has come down with a case of the nobility.',
         'Academics', 'Observation', 'Questioning',
         'Medicine', 'Team Player',
         "Medical Outfit.\nTongue depressors."],
        ['Outlaw', 'outlaw',
         'No one is above the law. Look at you, you\'re outside the law. There\'s a difference. It\'s a subtle difference, true, and it\'s one that\'s lost on the cops, but it\'s a difference nonetheless. You\'re nobody\'s fool… in that you\'ve not been adopted by any gang, mob, syndicate, mafia, subversive group, or Communist cell.',
         'Evasion', 'Fighting', 'Shooting',
         'Stealth', 'Streetwise',
         "Rough Outfit.\nService Pistol."],
        ['Patrol', 'patrol',
         'You could be a beat cop, if they have legal authority. Or you could be private, like maybe the Pinkertons or the hotel security or the strike-breakers. A Patrol has two jobs. The first is to say that they don\'t want any trouble. The second is to put a swift end to any trouble.',
         'Endurance', 'Presence', 'Tactics',
         'Boxing', 'Bullet Conservation',
         "Uniform Outfit.\nService Pistol.\nTruncheon."],
        ['Politician', 'politician',
         'The very essence of equality of opportunity and of American individualism is that there shall be no domination of any group or monopoly in this republic. The initiative and enterprise of this great nation is at stake, and if you are still reading this, then you might be ready to play as a Politician. Vote early and often!',
         'Academics', 'Deceit', 'Negotiation',
         'Bribery', 'Diplomacy',
         "Fancy Outfit.\nCampaign buttons."],
        ['Prize Fighter', 'prize-fighter',
         'Some people like to call the fine art of boxing to be "the sweet science," so you might consider this Career if you\'re the type who likes science. And by "science", we mean "turn some sap\'s face inside-out by punching it repeatedly."',
         'Endurance', 'Fighting', 'Presence',
         'Boxing', 'Guts',
         "Rough Outfit.\nBoxing gloves."],
        ['Professor', 'professor',
         'Early in the century, only the seriously well-to-do can afford to send their kids to college. After New Deal and World War Two, though, the government\'s really eager to get more alumni out there, so as how to figure out how to build new weapons to fight off the Commies before they ship us off to gulags on the moon.',
         'Academics', 'Observation', 'Questioning',
         'Geography', 'Research',
         "Fancy Outfit.\nBook with a title that's longer than your arm, 500 pages and no pictures."],

        // ── UJ21 ────────────────────────────────────────────────────────────
        ['Reporter', 'reporter',
         'Are you digging up the dirt on the Tinseltown set, to tell us who\'s sleeping with who? Or do you walk the night beat, so you can be the first to get photos of the latest dismembering by the South-Side Slasher? You could be a war correspondent, reporting on our brave soldiers on the front while shells burst overhead.',
         'Observation', 'Questioning', 'Transport',
         'Gossip', 'Research',
         "Fancy Outfit.\nPocket Pistol.\nPress Pass."],
        ['Safecracker', 'safecracker',
         'In the early days, we had the "yeggs". They would put some putty around the lock, pour in some nitroglycerin, and then hit it with a sledgehammer. Later, as the safes get tougher and as people pass laws regulating the sale of nitro to just anybody, it takes a little more finesse.',
         'Craft', 'Evasion', 'Observation',
         'Demolitions', 'Sabotage',
         "Rough Outfit.\nPocket Pistol.\nStethoscope."],
        ['Salesperson', 'salesperson',
         'Friend, I know you\'ve looked at a lot of other Careers, and sure, they all have their positive qualities, but have you considered a career as a Salesperson? You get lots of good skills and gifts that will serve you well on both your busy days and your nights on the town. Don\'t settle for inferior builds: tell your Host that you deserve nothing less than the best. Salesperson!',
         'Deceit', 'Negotiation', 'Questioning',
         'Diplomacy', 'Team Player',
         "Fancy Outfit.\nThis year's sales catalog.\nLast year's sales catalog."],
        ['Servant', 'servant',
         'What\'s the point of being rich if you don\'t have anyone to do your dirty jobs for you? This Career covers a lot of service industries, such as butlers, gardeners, groundskeepers, restaurant servers, and most everyone else who works in a service industry and dreams of something better.',
         'Craft', 'Observation', 'Transport',
         'Gossip', 'Team Player',
         "Handy Outfit, for the day-to-day.\nFancy Outfit, for special occasions."],
        ['Singer', 'singer',
         'The dulcet tones, the velvet fog, the siren song of your lovely voice. Early on, you\'ll want to be a big draw to fill up the clubs and the speakeasies. Later on, you\'ll be heard on the radio or onto the records. (But skip television, that\'s just a fad.) Watch out for sleazy promoters and crooked record labels!',
         'Deceit', 'Observation', 'Presence',
         'Singing', 'Team Player',
         "Fancy Outfit.\nPocket Pistol."],
        ['Soldier', 'soldier',
         'A lot of you might have been drafted for the War to End All Wars… or for the War we had after that. Some of you might enlist because you can\'t think of any other job. A few of you just want to serve your country, and god bless you. We assume that anyone with this career is an ex-Soldier who doesn\'t have to be back in the barracks at 18:00.',
         'Fighting', 'Shooting', 'Tactics',
         'Bullet Conservation', 'Team Player',
         "Rough Outfit.\nService Pistol."],

        // ── UJ22 ────────────────────────────────────────────────────────────
        ['Spy', 'spy',
         'You could be an undercover cop, trying to infiltrate the mob. Or maybe it\'s the other way around, and you\'re a plant in City Hall who feeds inside info to your boss. You could even be an agent of the foreign nationals, sent here to steal our military secrets, or to fluoridate our water, or to seduce our innocent, or whatever it is that the foreign nationals are up to.',
         'Deceit', 'Evasion', 'Observation',
         'Disguise', 'Gossip',
         "Rough Outfit.\nSilenced Pistol."],
        ['Thief', 'thief',
         'Anyone can smash a window, grab a necklace, and run off with it. True thievery, that takes style. You\'ve got to be able to scale sheer skyscrapers, to disable alarms, and to palm objects right under the noses of the finest guards that the insurance companies have to offer. The only downside to this job is that you can\'t brag about your exploits.',
         'Athletics', 'Evasion', 'Observation',
         'Sabotage', 'Sleight of Hand',
         "Sneaky Outfit, for business.\nFancy Outfit, for pleasure."],
        ['Torpedo', 'torpedo',
         'Hit man, assassin, contract killer… Look, I\'m not going to sugar-coat this one. This Career is about killing people for money. Strictly business, mind you. It\'s not looking to consider being a Salesperson, instead.',
         'Evasion', 'Observation', 'Shooting',
         'Sneak Attack', 'Streetwise',
         "Rough Outfit.\nSilenced Pistol.\nGarrote."],
        ['Tycoon', 'tycoon',
         'What\'s better than spending money? Why, spending other people\'s money, of course! Enjoy the Roaring Twenties while stocks just go up, up, up! Short-sell to profit off misery in the Great Depression! Invest in bonds as Uncle Sam goes to war! Light your cigars with hundred-dollar bills as you throw the biggest parties this city has ever seen! The world is yours, kid.',
         'Academics', 'Negotiation', 'Questioning',
         'Diplomacy', 'High Society',
         "Fancy Outfit.\nFountain pen.\nCheckbook."],
        ['Vagrant', 'vagrant',
         'We thought "Vagrant" was a better name than "bum" or "hobo". We\'re not here to judge. Times are tough all over, pal. We\'re not going to ask how you got to this state of affairs: riding the rails, sleeping on doorsteps, selling pencils for dimes, or whatever it is you do to make ends meet.',
         'Endurance', 'Negotiation', 'Observation',
         'Streetwise', 'Survival',
         "Rough Outfit.\nBindle on a stick."],
        ['Writer', 'writer',
         'It\'s the dawn of the 20th century, and media is big, baby! There\'s all those magazines that need you to make up true-to-life stories! There\'s the radio, the movies, and the teevee, what need you to crank out the same old stuff week after week. And there\'s always some rich person who wants to sign their name to the auto-biography that you ghost-wrote.',
         'Academics', 'Observation', 'Questioning',
         'Gossip', 'Research',
         "Fancy Outfit.\nPortable typewriter."],
    ];

    $count = 0;
    foreach ($rows as [$name, $slug, $desc, $s1, $s2, $s3, $g1, $g2, $gear]) {
        cg_exec(
            "INSERT INTO `$t`
                (name, slug, description, skill_1, skill_2, skill_3, gift_1, gift_2, gear)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                name=VALUES(name), description=VALUES(description),
                skill_1=VALUES(skill_1), skill_2=VALUES(skill_2), skill_3=VALUES(skill_3),
                gift_1=VALUES(gift_1), gift_2=VALUES(gift_2), gear=VALUES(gear)",
            [$name, $slug, $desc, $s1, $s2, $s3, $g1, $g2, $gear]
        );
        $count++;
    }
    return $count;
}

// ── Read endpoints ────────────────────────────────────────────────────────────

function uj_get_species(): void {
    $rows = cg_query(
        "SELECT id, name, slug, description, skill_1, skill_2, skill_3, gift_1, gift_2
           FROM `" . uj_tbl('species') . "`
          WHERE published = 1
       ORDER BY name ASC"
    );
    cg_json(['success' => true, 'data' => $rows]);
}

function uj_get_types(): void {
    $rows = cg_query(
        "SELECT id, name, slug, description, skill_1, skill_2, skill_3,
                gift_1, soak_1, soak_2, gear
           FROM `" . uj_tbl('types') . "`
          WHERE published = 1
       ORDER BY name ASC"
    );
    cg_json(['success' => true, 'data' => $rows]);
}

function uj_get_careers(): void {
    $rows = cg_query(
        "SELECT id, name, slug, description, skill_1, skill_2, skill_3,
                gift_1, gift_2, gear
           FROM `" . uj_tbl('careers') . "`
          WHERE published = 1
       ORDER BY name ASC"
    );
    cg_json(['success' => true, 'data' => $rows]);
}

function uj_get_skills(): void {
    $rows = cg_query(
        "SELECT id, name, slug, description, paired_trait, sample_favorites, gift_notes
           FROM `" . uj_tbl('skills') . "`
          WHERE published = 1
       ORDER BY name ASC"
    );
    cg_json(['success' => true, 'data' => $rows]);
}

function uj_get_gifts(): void {
    $rows = cg_query(
        "SELECT id, name, slug, subtitle, description, gift_type, recharge, requires_text
           FROM `" . uj_tbl('gifts') . "`
          WHERE published = 1
       ORDER BY gift_type ASC, name ASC"
    );
    cg_json(['success' => true, 'data' => $rows]);
}

function uj_get_soaks(): void {
    $rows = cg_query(
        "SELECT id, name, slug, damage_negated, recharge, side_effect, description, soak_type
           FROM `" . uj_tbl('soaks') . "`
          WHERE published = 1
       ORDER BY soak_type ASC, name ASC"
    );
    cg_json(['success' => true, 'data' => $rows]);
}

function uj_get_all_traits(): void {
    // Convenience: fetch all six tables in one round-trip.
    $sp = cg_query("SELECT id, name, slug, description, skill_1, skill_2, skill_3, gift_1, gift_2 FROM `" . uj_tbl('species') . "` WHERE published = 1 ORDER BY name ASC");
    $ty = cg_query("SELECT id, name, slug, description, skill_1, skill_2, skill_3, gift_1, soak_1, soak_2, gear FROM `" . uj_tbl('types') . "` WHERE published = 1 ORDER BY name ASC");
    $ca = cg_query("SELECT id, name, slug, description, skill_1, skill_2, skill_3, gift_1, gift_2, gear FROM `" . uj_tbl('careers') . "` WHERE published = 1 ORDER BY name ASC");
    $sk = cg_query("SELECT id, name, slug, description, paired_trait, sample_favorites, gift_notes FROM `" . uj_tbl('skills') . "` WHERE published = 1 ORDER BY name ASC");
    $gi = cg_query("SELECT id, name, slug, subtitle, description, gift_type, recharge, requires_text FROM `" . uj_tbl('gifts') . "` WHERE published = 1 ORDER BY gift_type ASC, name ASC");
    $so = cg_query("SELECT id, name, slug, damage_negated, recharge, side_effect, description, soak_type FROM `" . uj_tbl('soaks') . "` WHERE published = 1 ORDER BY soak_type ASC, name ASC");
    cg_json(['success' => true, 'data' => ['species' => $sp, 'types' => $ty, 'careers' => $ca, 'skills' => $sk, 'gifts' => $gi, 'soaks' => $so]]);
}

// ── Skills data ───────────────────────────────────────────────────────────────

function uj_install_skills(): int {
    $t = uj_tbl('skills');

    // Each row: [name, slug, description, paired_trait, sample_favorites, gift_notes]
    $rows = [
        ['Academics', 'academics',
         'The Academics skill covers mathematics, history, geography, medicine, and all kinds of fancy book learning. It is almost always combined with the Mind trait. One Academics success will be enough for general knowledge — the types of things people would pick up just by being smart. Two Academics successes means your character can figure out some hard math problem or solve some science issue. Three or more Academics successes will let your character know some really obscure historical fact or science principle that can apply to your current situation.',
         'Mind',
         "Chemistry\nGeography\nHistory\nMedicine\nPhysics",
         ''],
        ['Athletics', 'athletics',
         'Athletics skill helps with climbing, jumping, riding, swimming, throwing, and all kinds of outdoor sports. Athletics is often combined with Body for feats of physical strength and coordination, but when finishing first is more important than finishing well, Speed might be used instead. One Athletics success will be enough for typical physical feats, such as jumping a small gap or climbing a tree — something anyone with a Body trait could pull off. Two or more Athletics successes would be needed for difficult gymnastics and other physical feats. Athletics is a skill to perform a physical activity. To keep up the same physical activity for a long time, try Endurance skill, instead.',
         'Body',
         "Climbing\nJumping\nSwimming",
         "The Gifts of Climbing, Jumping, and Swimming each give a bonus d12 to a specialty use of Athletics. Consult the Gift's descriptions for more details."],
        ['Craft', 'craft',
         'Craft skill is a catch-all for working with your hands. This skill can be used to repair things, to build things, to make new things, and to know about how things are made. Anyone can roll their Mind to try to make something… but only those with Mind and at least one die in Craft skill can score two successes. Crafts that need strength or brawn might include Body; crafts that need precision and hand-to-eye coordination might include Speed. One Craft success will be enough for unskilled labor — tying knots, replacing wheels, and simple repairs. Two successes will be enough for skilled labor and the more difficult repairs. Three Craft successes or more are only possible by master craftsmen.',
         'Mind',
         "Carpentry\nLeatherworking\nMechanics\nMetalworking\nPainting\nWhen wearing my Handy Outfit",
         'The Gift of Craft Specialty gives a bonus d12 when using Craft to work on your Favorite thing.'],
        ['Deceit', 'deceit',
         'The Deceit skill covers all lying, cheating, disguise, pilfering, and anything else that uses falsehood to get what you want. For clever deception, Mind may be included. For simple bald-faced lying, told with conviction and without any tells to give it away, try including Will. To pick up unattended objects without anyone noticing, use Speed & Deceit. To see through deceit, your opponent may use their Mind Dice, and either their Questioning dice (for seeing through lies) or Observation dice (for seeing things they shouldn\'t). One success will be enough to fool most people who suspect nothing. Two successes or more will be necessary for targets with good Skills or who have a strong reason to be suspicious. Deceit is the skill used to distract people from the truth. If you want to hide, or to sneak past people without being seen, use Evasion, instead.',
         'Mind',
         "Cheating\nDisguise\nvs. authority figures\nStealing",
         "The Gift of Gambling gives a bonus d12 when cheating at games of chance.\nThe Gift of Sleight of Hand makes it easier for you to pick pockets or to palm small items.\nThe Gift of Disguise gives a bonus d12 to pretend to be someone else."],
        ['Endurance', 'endurance',
         'The Endurance skill represents stamina, self-discipline, and the ability to work through physical hardship. When slow and steady wins the race, it\'s Endurance. Endurance usually pairs with the Body Trait. For a marathon run or a chase, Endurance may pair with Speed instead. Every character has an instant "Endurance Soak" that lets you roll your dice vs. 3, with each success removing a point of damage. One success will be enough for any long-term activity, such as walking several miles in good weather. Two successes or more can let a character work longer… or maybe "slow and sure" becomes "fast and sure", allowing you to work both faster and longer.',
         'Body',
         "Hiking\nWhen Soaking a Fighting attack\nWhen Soaking a Shooting attack\nWhen wearing my Rough Outfit",
         'The Gift of Local Knowledge gives a bonus d12 to hiking, but only if you\'re in the right landscape. Consult the Local Knowledge\'s description for the details.'],
        ['Evasion', 'evasion',
         'A very popular skill with adventurers, Evasion is used to avoiding detection and for dodging attacks. Evasion pairs with your Speed Trait for those all-important dodge rolls, and for sneaking rolls. For staying very still in hiding spots, Will & Evasion may be used. For clever hiding spots, Mind & Evasion might come into play. In combat, a dodge is a roll of your Speed & Evasion vs. your attacker\'s dice. If your dodge dice roll higher, you avoid the attack. Also in dangerous combat situations, you may need to hide, which is a dangerous stunt that uses your Speed and Evasion dice.',
         'Speed',
         "Hiding and infiltrating\nvs. Fighting\nvs. Shooting\nWhen wearing my Sneaky Outfit",
         "Another popular Gift, Stealth gives a bonus d12 on rolls of Evasion to sneak.\nAnother popular Gift with adventurers, Veteran lets you take a \"guard\" action to claim a bonus d12 to dodges."],
        ['Fighting', 'fighting',
         'An essential Skill for the adventurer, Fighting covers punching, kicking, clubbing, stabbing, and all hand-to-hand combat. Fighting always pairs with your Body Trait. Different weapons include more Traits — some weapons require fast strikes, precise moves, or unchecked savagery. Fighting dice may be limited. If your character is climbing, swimming, or otherwise distracted with some physical feat, none of your Fighting dice may be larger than your best Athletics die (or d4, whichever is better). If your character is in a moving vehicle, none of your Fighting dice may be larger than your best Vehicle die (or d4, whichever is better). When attacking, your Fighting dice go up against your opponent\'s defense dice. To hit your target, you\'ll have to roll higher than they did. Fighting is used for hand-to-hand combat. For bows and guns, use Shooting skill, instead.',
         'Body',
         "With my favorite weapon\nWith my fists\nWith grabs\nWith escapes",
         "A very popular Gift with adventurers, Veteran lets you take a \"guard\" action to claim a bonus d12 to counters made with Fighting weapons, and it lets you take an \"aim\" action to claim a bonus d12 to attacks made with fighting weapons against a single foe."],
        ['Negotiation', 'negotiation',
         'When you want other people to give you something, to help you with something, or to not do something, it\'s time to use Negotiation Skill. Negotiation is all about getting along with others, and getting them to do things for you. For many negotiations, you won\'t need to roll — asking the police to help you against a mugger, asking a merchant to sell you an item at a standard price, asking a porter to take your train ticket and let you aboard, etc. Use Negotiation when you want a minor advantage in a transaction or when your request is dubious or when you want someone to break the rules to help you. Negotiation almost always pairs with the Mind trait. You might try negotiating dishonestly by including your Deceit dice. If the target doesn\'t have any Deceit dice, you\'re probably better off with the truth! And if the target doesn\'t have any Questioning dice, they\'re an easy target for such trickery.',
         'Mind',
         "With criminals\nWith royalty\nWith merchants\nWith the authorities",
         "The gift of Fast Talk can give you a bonus d12 to a Negotiation that takes less than five minutes (and assuming they don\'t already dislike you).\nThe gift of Diplomacy gives you a bonus d12 to any Negotiation that lasts more than five minutes, with people who are willing to hear you out.\nMany social gifts give a d12 bonus to negotiate in certain social situations."],
        ['Observation', 'observation',
         'A very popular skill with adventurers, Observation is the skill of knowing what\'s in your environment that\'s useful to you… and what isn\'t useful. Seeing things in plain sight, or hearing loud noises, don\'t require rolls of Observation. It\'s the hidden things, or the things lost in noise and clutter, that you have to make rolls to find. You can also use Observation to search for clues, such as tracks. Observation pairs with your Mind Trait for those all-important rolls to find out useful information. If you\'re in a hurry, you might pair Observation with Speed to quickly toss a room for clues. In a combat situation, you will roll Mind and Observation to see how ready you are, when a fight starts. Observation is used to resist Deceit when someone attempts to pick a pocket or palm an object while you\'re around.',
         'Mind',
         "Tracking\nSearching for clues\nInitiative\non my home turf",
         "The Gift of Danger Sense gives a bonus d12 to your Initiative rolls.\nThe Gift of Local Knowledge gives a bonus d12 to spot if anyone is sneaking up on you, but only if you\'re in the right landscape. Consult the Local Knowledge\'s description for the details."],
        ['Presence', 'presence',
         'The Presence skill is for making an impression on others — to make them remember you, to make them respect you, to make them fear you, to make them take you seriously. Presence is popular with actors, politicians, and crime lords. When trying to scare people, you use your Body & Will Traits with your Presence dice. When giving a public speech, use Mind & Presence to make people pay attention to what you\'re talking about. For a performance, roll Will & Presence vs. 3. If you score one success, your performance is good. More successes will give a better performance and a stronger impression. You may attempt to scare someone by contesting your Body, Will, & Presence vs. their Body, Will & Presence.',
         'Body & Will',
         "on my home turf\nwhen I have a gun\non stage\non screen\nwith people who have never heard of me before\nwith anyone who already has a negative opinion of me\nwith anyone who already has a positive opinion of me\nWhen wearing my Uniform Outfit",
         'The gift of Guts gives a bonus d12 on rolls to use Presence to Frighten people and to resist being Frightened.'],
        ['Questioning', 'questioning',
         'Questioning is the skill of gossiping to find rumors, to separate rumor from fact, to interrogate people for correct answers, and to piece together multiple stories to find the big picture. One success on a Questioning roll will get you the same rumors and information that the locals would know. Two successes would get you information that only people "in the know" would be able to figure out. Three successes or more will dig up some serious secrets.',
         'Mind',
         "with criminals\nwith aristocrats\nwith intellectuals\nwith the working class",
         "The Gift of Gossip gives you a bonus d12 to gossip — that is, asking people informal questions in social situations. Gossip takes a long time — at least an hour to get maybe five minutes of useful information.\nThe gift of Local Knowledge gives you a bonus d12 when gossiping inside a specific area.\nMany social gifts give a d12 bonus to gossip with people in certain social situations: Carousing works in bars and at parties, Romance helps with amorous partners, High Society is for the upper crust, Streetwise assists with criminals and the underclass, and an Insider gift gives a d12 bonus, but only with a specific crowd."],
        ['Shooting', 'shooting',
         'An essential Skill for the adventurer, Shooting is used with bows, crossbows, guns, slings, and all ranged weapons. Shooting always pairs with your Speed Trait. Different weapons include more Traits — some weapons require a strong grip, keen awareness, or unflinching violence. Consult the Equipment chapter to see what other Traits might be used. Shooting dice may be limited. If your character is climbing, swimming, or otherwise distracted with some physical feat, none of your Shooting dice may be larger than your best Athletics die (or d4, whichever is better). If your character is in a moving vehicle, none of your Shooting dice may be larger than your best Vehicle die (or d4, whichever is better). Shooting is used for ranged combat. For thrown weapons, use Fighting skill, instead.',
         'Speed',
         "With my favorite gun\nWith aimed shots only",
         "Gifts such as Pistol Reflex, Shotgun Blast, and Rifle Accuracy improve your damage output with key weapons.\nA very popular Gift with adventurers, Veteran lets you take a \"guard\" action to claim a bonus d12 to counters made with Fighting weapons, and it lets you take an \"aim\" action to claim a bonus d12 to attacks made with fighting weapons against a single foe."],
        ['Tactics', 'tactics',
         'When a mob fights, they are an uncoordinated mess, tripping over each other and getting in one another\'s way. When trained warriors fight, they use skill in Tactics. When you attack a target that is threatened by one of your allies, you may claim your Tactics Dice as extra dice with your Fighting or Shooting to hit the target. When a nearby ally is Dazed or Panicked, you may attempt to help them with a Rally action. Roll your Will & Tactics dice vs. 3. For each success you score, you can remove one bad effect.',
         'Will',
         "Rallying\nwith Fighting\nwith Shooting\nwhen outnumbered",
         "The gift of Leadership gives you a bonus d12 when using Tactics to rally others.\nThe gift of Counter-Tactics gives you a bonus d12 to counter or any dodge when others try to claim Tactics dice as bonus blindside dice, against you."],
        ['Transport', 'transport',
         'The Transport skill is used to operate any vehicle — automobile, boat, locomotive, steamship, airplane, zeppelin, etc. Transport skill usually pairs with your Speed Trait. Muscle-powered Transport, such as rowboats, may use Body & Transport instead. Having Transport skill will let you attack from a moving vehicle better. Your Fighting & Shooting skills are limited to the size of your best Transport die. For example, if your best Transport die is d6, all your combat dice that are d8, d10, or d12 become d6.',
         'Speed',
         "With my favorite vehicle\nWhen driving on my home turf",
         "Driving gives a bonus d12 to operate automobiles (4 or 6 wheels).\nMotorcycling gives a bonus d12 for cycles (2 or 3 wheels)."],
    ];

    $count = 0;
    foreach ($rows as [$name, $slug, $desc, $trait, $favs, $gifts]) {
        cg_exec(
            "INSERT INTO `$t`
                (name, slug, description, paired_trait, sample_favorites, gift_notes)
             VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                name=VALUES(name), description=VALUES(description),
                paired_trait=VALUES(paired_trait),
                sample_favorites=VALUES(sample_favorites),
                gift_notes=VALUES(gift_notes)",
            [$name, $slug, $desc, $trait, $favs, $gifts]
        );
        $count++;
    }
    return $count;
}

// ── Gifts data ────────────────────────────────────────────────────────────────

function uj_install_gifts(): int {
    $t = uj_tbl('gifts');

    // Each row: [name, slug, subtitle, description, gift_type, recharge, requires_text]
    $rows = [
        // ── Basic Gifts (alphabetical) ────────────────────────────────────────
        ['Acrobat', 'acrobat',
         "extra 'stand-up' action; \u{2212}2 falling damage",
         "On your turn, you may claim an extra \"stand up\" action. (That means you can stand up and still do two other things. Characters without this Gift have to use one of their two standard actions to stand up.) If you suffer damage from falling, and you're still awake, alert, etc., you'll take 2 points less of damage, as you nimbly land on your feet. You can stand on your hands, walk on tightropes, and balance on a flagpole. You may claim a d8 assist bonus to any roll where being acrobatic might help you.",
         'basic', '', ''],
        ['Ally', 'ally',
         'friend with Species, Career, Distress Soak \u{2212}4',
         "You have a friend! Your friend is a Minor Typical character, with a Species and a Career, a d6 in all six Traits, and the four gifts they get from those two choices. Choose a Species and a Career for them to have. (Your Ally does not have a Type Trait.) Your Ally also has the Soak of Distress Soak \u{2212}4. Your friend is normally controlled by the Game Host, but the Host may let you \"take control\" and use the Ally as if it were your own character. Your Ally always has your best interest in mind. They would never betray you, but they might be deceived by villains. Or they might be captured and held hostage. If your Ally is killed, or otherwise leaves the game, you will have to retrain this gift.",
         'basic', '', ''],
        ['Bodyguard', 'bodyguard',
         'defend for Near friend, 1/recover',
         "You are closely vigilant for danger. When an enemy declares an attack on one of your friends that's Near you (within 3m), you can declare you will use this Bodyguard ability. You immediately swap places with your friend. You are now the target, not them. You defend normally, and you must immediately declare if you will counter or if you will dodge. You recharge this gift with a \"recover\" action. You can't use Bodyguard to swap places again until you recharge this gift.",
         'basic', '1/recover', ''],
        ['Boxing', 'boxing',
         'can use Body Blow, Jab, Knockout, Uppercut',
         "You can use the Boxing Attacks of \"Body Blow\", \"Jab\", \"Knockout\", and \"Uppercut\". (Note that the Jab can Counter.) Boxing Methods are described on page 87. (Characters without this Gift cannot use Boxing Attacks. They must make do with the inferior Unarmed Attacks.)",
         'basic', '', ''],
        ['Brawling', 'brawling',
         'can use Grapple, Pummel, Overbear',
         "You can use the Brawling Attacks of \"Grapple\", \"Pummel\", and \"Overbear\". Brawling Methods are described on page 87. (Characters without this Gift cannot use Brawling Attacks. They must make do with the inferior Unarmed Attacks.)",
         'basic', '', ''],
        ['Bribery', 'bribery',
         'bonus d12 with incentives, no offense',
         "You know how to grease the wheels. When convincing someone to take a monetary incentive (that is, a bribe), using Deceit, Negotiation, or Presence, etc., you may claim a d12 bonus to your roll. When offering someone a bribe that's illegal, if you fail your roll, you don't automatically offend the target (and thus gain a bad Opinion). You still suffer a bad Opinion for repeated attempts, though. (Characters without this gift must automatically offend a target when failing to make an illegal bribe.)",
         'basic', '', ''],
        ['Bullet Conservation', 'bullet-conservation',
         'Increase Ammo die',
         "You know how to fire for best effect. When you equip a fully-loaded gun, raise its Ammo die by one size. For example, where other people would have \"Ammo d4\", you would have \"Ammo d6\". Your Ammo die is reduced normally by shooting. (That is, when that die rolls a 1, it Dwindles by one size.) If you reload, the Ammo die goes back up to that bigger size. If your gun gets passed to someone who doesn't have Bullet Conservation, the Ammo die goes back to normal.",
         'basic', '', ''],
        ['Carousing', 'carousing',
         'bonus d12 with intoxication',
         "You may claim a bonus d12 to any rolls of Deceit, Negotiation, or Presence when you're in a place where intoxicants flow freely. (Such as social-drinking parties, reefer dens, speakeasies, etc.) The Game Host may give you a d12 bonus to other rolls as well, if intoxication somehow would help. You do not have to be blind drunk to use this gift to work. Social drinking is fine.",
         'basic', '', ''],
        ['Chemistry', 'chemistry',
         'bonus d12 with chemicals',
         "You may claim a bonus d12 to rolls with Academics, Craft, or Observation where chemicals are involved. The Game Host may give you a d12 bonus to other rolls as well, if your knowledge of chemical transformations somehow would help.",
         'basic', '', ''],
        ['Climbing', 'climbing',
         'bonus d12 to climb',
         "You may claim a bonus d12 to all Athletics rolls to climb or to grab onto surfaces to avoid a fall. While climbing, none of your dice are limited in any way. (Characters without this gift have their dice reduced to either their highest Athletics die or to d4.)",
         'basic', '', ''],
        ['Contortionist', 'contortionist',
         'can Squirm or Wriggle; d4 cover',
         "You may use the Contortionist Attacks of \"Squirm\" and \"Wriggle\". (These Attacks can break free of holds or restraints.) Contortionist methods are described on page 88. When dodging, you can claim a d4 Cover bonus just by contorting your body, even if there's nothing to hide behind. (Cover boosts your dodge defense. See page 97.)",
         'basic', '', ''],
        ['Coward', 'coward',
         'Panicked? Bonus d12 to dodge and scramble!',
         "Whenever you suffer the \"Panicked\" status, you may claim a bonus d12 to all dodge rolls. (There is no bonus to counter-attacks.) Also, while Panicked, you may claim a d12 bonus to any rolls to Scramble\u{2026} but only if you are moving away from danger. (That is, you must be moving away from hostiles, and you must not be moving towards something hazardous to your health.) You can become Panicked by using your \"Panic Save \u{2212}2\". You can also choose to become Panicked at any time during a combat.",
         'basic', '', ''],
        ['Craft Specialty', 'craft-specialty',
         'bonus d12 to Favorite Craft',
         "When you get this gift, choose a Favorite thing from this list: Carpentry, Electronics, Masonry, Mechanics, Painting. Whenever you use your Craft skill to work on your Favorite thing, you may claim a bonus d12. (You will want to roll this bonus d12 before you re-roll one 1 for Favorite use.) If your Game Host permits it, you can make up a different specialty from what we have listed here.",
         'basic', '', ''],
        ['Danger Sense', 'danger-sense',
         'bonus d12 to initiative & hazards',
         "You may claim a bonus d12 to any Initiative roll. (Write this d12 into your Initiative box.) You may claim a d12 bonus to Athletics, Evasion, and Observation rolls to avoid traps and hazards. (Sorry, no bonus to Craft! That's covered by the Sabotage gift.) You don't get a bonus to any rolls to see other people sneaking up on you. (That's what the Initiative bonus is for.)",
         'basic', '', ''],
        ['Demolitions', 'demolitions',
         'bonus d12 with explosives',
         "You may claim a bonus d12 to any rolls of Academics, Craft, or Observation to identify, to build, to spot, or to prepare explosives.",
         'basic', '', ''],
        ['Dexterity', 'dexterity',
         'off-hand is good hand; Dual-Wield action',
         "You're ambidextrous. Your off-hand can be used as a good hand. You can equip \"Good hand\" weapons in your off-hand, no problem. You can \"Dual-Wield\" to attack a second time, but not with the same weapon you attacked with earlier. Your target defends normally. You can only Dual-Wield if your second weapon has a Counter range. You can't \"Dual-Wield\" if you're Panicked, restrained, or otherwise unable to attack.",
         'basic', '', ''],
        ['Diplomacy', 'diplomacy',
         'bonus d12 when being diplomatic',
         "You may claim a d12 bonus to any Deceit, Negotiation or Presence when you're in any diplomatic setting where you have at least one hour (in game time) to talk with your targets. If you fail your roll in this diplomatic setting, you do not automatically offend your target. (Other characters without this gift would suffer a negative Opinion.) If you're in a hurry, consider the Fast-Talk gift, instead.",
         'basic', '', ''],
        ['Disguise', 'disguise',
         'bonus d12 for imposture',
         "You may claim a bonus d12 to any roll to Deceit to pretend to be someone that you're not. You know the finer points of dress, makeup, gesture, etc. for disguising yourself.",
         'basic', '', ''],
        ['Driving', 'driving',
         'bonus d12 to operate automobiles',
         "You may claim a bonus d12 to operate any automobile (with four or six wheels), using the Transport skill. You suffer no limits on your skills while in a moving automobile. (Characters without this gift have their dice limited to either their highest Transport die or to d4.)",
         'basic', '', ''],
        ['Entourage', 'entourage',
         'roll for hangers-on, 1/episode',
         "You have a circle of admirers, or extended family, or gang of underlings. Your entourage are minor non-player characters who follow you around, trying to help out. You can use this gift any time you're in a place where you can recruit new friends. Roll your dice vs. 3 and count the successes. You can have up to one follower, plus one for each success that you roll. This result is the maximum number of Entourage friends you can have, until the start of the next episode.",
         'basic', '1/episode', ''],
        ['Fast-Talk', 'fast-talk',
         'bonus d12 with a rube for five minutes',
         "You may claim a bonus d12 to any roll of Deceit, Negotiation, or Presence if and only if: You can get what you want in the next five minutes or less (in game time), and the target doesn't already have a negative Opinion of you. After five minutes, if the target of your Fast-Talk has reasons to think you were less than honest with them, they are automatically offended, gaining a bad Opinion of you.",
         'basic', '', ''],
        ['Firefighting', 'firefighting',
         'bonus d12 with fires',
         "You may claim a bonus d12 to any rolls of Academics, Craft, or Observation to identify the source of fires, or to attempt to put fires out. (Oh, and it's a bonus d12 to any rolls to burn a place down and make it look like an accident, if arson is your bag.)",
         'basic', '', ''],
        ['Flight', 'flight',
         'you can fly while doing a Scramble stunt',
         "You can fly. As part of any Scramble stunt, you can also move vertically, or stay in the air. (Characters without this gift do not fly so much as plummet.) The Scramble stunt is described on page 78.",
         'basic', '', ''],
        ['Geography', 'geography',
         'bonus d12 to know places',
         "You may claim a bonus d12 to rolls of Academics and Questioning when dealing with issues like state capitals, foreign countries, lists of natural resources, population censuses, and other geographical things. You can claim a bonus d12 to any Transport rolls to plan long-distance travel over such geography. The Game Host may give you a d12 bonus to other rolls as well, if your encyclopedic knowledge of the world somehow would help.",
         'basic', '', ''],
        ['Giant', 'giant',
         'extend Close Attacks to Near',
         "You buy your clothes from the big-and-tall stores. Your reach is amazing. If you have an Attack or a Counter that only works at \"Close\", you may extend that range to \"Near\". (Sadly, there's no change to any attacks or counters that have a range other than \"Close\".) You may claim a d8 assist bonus to any roll where being a giant might help you. You can't claim non-giant people as cover.",
         'basic', '', ''],
        ['Gossip', 'gossip',
         'bonus d12 to gather information',
         "You may claim a bonus d12 to Questioning when you are gossiping. Gossiping takes at least one hour and requires you to talk to lots of people. (When role-playing, gossiping often assumes 55 game-minutes of useless jabber and walking for every 5 minutes of useful information.)",
         'basic', '', ''],
        ['Guts', 'guts',
         'bonus d12 to cause/resist Fright',
         "You're scary. You may claim a bonus d12 when performing a Frighten stunt. (That is, when you roll Body, Will, & Presence to Frighten others.) And you don't scare easy. You may claim a bonus d12 to resist being Frightened. The Game Host may give you a d12 bonus to other rolls as well, if your steely-eyed determination somehow would help.",
         'basic', '', ''],
        ['High Society', 'high-society',
         'd12 with upper class; extravagance',
         "You may claim a bonus d12 to any rolls of Deceit, Negotiation, or Presence when you're among the jet set, the blue-bloods, the glitterati, and the upper class. Also, you can buy Extravagant goods at 50% of their listed price, and you can sell Extravagant goods at 20% of their listed price. (Characters without this gift must buy at 100% and sell at only 10%.)",
         'basic', '', ''],
        ['Jumping', 'jumping',
         'bonus d12 to jump; use Vault attack',
         "You may claim a bonus d12 to all Athletics rolls to jump high or long. You can use the Jumping Attack of \"Vault\". This attack lets you move through other people's spaces, even as a counter. Jumping methods are described on page 88. (Characters without this gift can't vault over others, especially when it's not their turn.)",
         'basic', '', ''],
        ['Leadership', 'leadership',
         'bonus d12 to rally & oratory',
         "You may claim a bonus d12 to any Rally action (that is, when you roll Will & Tactics to help your friends). You may claim a d12 to any Presence roll when you give a public speech to exhort a crowd to action. The Game Host may give you a d12 bonus to other rolls as well, if your superior public-speaking voice somehow would help.",
         'basic', '', ''],
        ['Legal Authority', 'legal-authority',
         'power of the law',
         "You have a badge, and that authority is recognized in a significant part of the state. You may use Proscribed items appropriate to your authority. The Game Host may give you a d12 bonus to other rolls as well, if being an officer of the law somehow would help. The Game Host may force you to retrain this gift if you become stripped of your Legal Authority, so be careful.",
         'basic', '', ''],
        ['Luck', 'luck',
         're-roll any and all dice, 1/chapter',
         "After you've made a roll of any kind, if you decide you don't like it, declare you will use this gift. Choose which of your dice you want to re-roll. Any dice that you re-roll, the new result stands\u{2026} even if it's worse. If your roll was a challenge, you may also choose that your opponent re-rolls none, one, or more of their dice, too. You must choose what dice your opponent re-rolls before you re-roll, and the new results stand, even if they're worse for you. After the re-rolling, you can still claim other bonuses\u{2026} but you'll have already tapped your Luck, so you can't re-roll any of those dice. This ability recharges at the start of the next episode.",
         'basic', '1/chapter', ''],
        ['Medicine', 'medicine',
         'bonus d12; treat illness & injury',
         "You may claim a bonus d12 to any rolls of Academics, Observation, or Questioning when making rolls about medical issues. The Game Host may give you a d12 bonus to other rolls as well, if being a physician somehow would help. You can treat long-term illness and injury. If you can spend five in-game minutes with a patient, you can reduce the effects of some statuses. See the Aftermath chapter for more details.",
         'basic', '', ''],
        ['Motorcycling', 'motorcycling',
         'bonus d12 to operate cycles',
         "You may claim a bonus d12 to operate any motorcycle (two or three wheels, with or without sidecar), using the Transport skill. You suffer no limits on your skills while in a moving motorcycle. (Characters without this gift have their dice limited to either their highest Transport die or to d4.)",
         'basic', '', ''],
        ['Noncombatant', 'noncombatant',
         'passive d12 to dodge and flee; violence uses it up; 1/peace',
         "You aren't a fighter at heart, and other people believe you. As long as you haven't been violent, you may claim a bonus d12 to all Dodge rolls. (There is no bonus to Counters.) You may also claim a d12 bonus to any Scramble rolls\u{2026} but only when you are fleeing from a combat situation. If you engage in an act of violence \u{2014} that is, if you attack or counter someone \u{2014} you immediately use this gift up. The bonus d12 goes away. After using up this gift, you have to wait at least 24 in-game hours before you can recharge it, and claim the d12 bonus again. If you engage in any violence before then, the timer resets.",
         'basic', '1/peace', ''],
        ['Performance', 'performance',
         'bonus d12 on stage and screen',
         "You may claim a bonus d12 to any rolls of Athletics, Deceit, or Presence to impress a crowd with your acting, music, or other public performance. The Game Host may give you a d12 bonus to other rolls as well, if your theatricality somehow would help.",
         'basic', '', ''],
        ['Personality [of choice]', 'personality',
         'bonus d12, 1/rest',
         "Your sense of self is so strong that you can succeed where other people would have given up. All Player Characters start with this gift. You must choose a Personality for your character to have: a one-word or short description that explains your personality. You can use your Personality ability to claim a d12 bonus to a roll you've just made. (Yes, because this is a claimed bonus, you can roll your dice first, decide if you like how it came out, and then say you'll use your d12.) You can't use this gift again until your character gets a rest. (That's 8 hours of sleep, in game time, and at least one square meal.)",
         'basic', '1/rest', ''],
        ['Quills', 'quills',
         'can use Quills',
         "You can use the Quills Attack of \"Quills\". This attack and counter will deliver sharp pains to people in close quarters. Quills methods are described on page 88.",
         'basic', '', ''],
        ['Research', 'research',
         'bonus d12 with libraries and data',
         "You may claim a bonus d12 to any rolls of Academics, Observation, or Questioning when you have a few hours to collate data by having access to a large library, dossier, or other enormous database of information. The Game Host may give you a d12 bonus to other rolls as well, if methodical investigation somehow would help.",
         'basic', '', ''],
        ['Romance', 'romance',
         'bonus d12 with love & desire',
         "You may claim a bonus d12 to any rolls of Deceit, Negotiation, or Presence against characters who have romantic intentions against you. The Game Host may give you a d12 bonus to other rolls as well, if your sly seduction somehow would help.",
         'basic', '', ''],
        ['Running', 'running',
         'bonus d12 to run; use Trample attack',
         "You may claim a bonus d12 to Athletics rolls to run at high speeds. You can use the Running Attack of \"Rush\" and \"Trample\". These attacks let you move and attack, possibly knocking people over. Running methods are described on page 88. (Characters without this gift can't move and attack in one action.)",
         'basic', '', ''],
        ['Sabotage', 'sabotage',
         'bonus d12 to break down, in, or out',
         "You may claim a bonus d12 to any Craft rolls to pick a lock, to disable an alarm, to jimmy open a window, to neutralize a trap, to cut brake lines, to crack a safe, to disarm a bomb, to attack an inanimate object, or otherwise work around some contrivance or contraption. The Game Host may give you a d12 bonus to other rolls as well, if your extensive knowledge of breaking things somehow would help.",
         'basic', '', ''],
        ['Singing', 'singing',
         'bonus d12 with vocal music',
         "You may claim a bonus d12 to any rolls of Academics or Presence when singing, either in private or on stage. The Game Host may give you a d12 bonus to other rolls as well, if your magic pipes somehow would help. (No, you can't get a d12 bonus to rally people by singing at them. This ain't West Side Story, for crying out loud.)",
         'basic', '', ''],
        ['Sleight of Hand', 'sleight-of-hand',
         'use Legerdemain',
         "You can palm small objects, picking things up while people are still watching you. Roll Speed & Deceit vs. 3. The more people watching you, and the more unusual the object, the more successes you would need. In the heat of battle, you can use the Sleight-of-Hand Attack of \"Legerdemain\". This action lets you take items from people in the middle of combat. Sleight-of-Hand methods are described on page 88. (Characters without this gift must resort to brutal methods to wrest items away from people.)",
         'basic', '', ''],
        ['Sneak Attack', 'sneak-attack',
         'declare bonus 2d8 to attack, 1/hide',
         "Before you attack, you may declare an attack to be a Sneak Attack. Declare you will use this gift. When you make your attack roll, you may roll a bonus 2d8. You may recharge this gift with a hide action. (That is, you must roll Speed, Evasion, & Stealth's d12 vs. 3 and score at least one success \u{2014} more if people are watching for you.) You can't declare this 2d8 bonus again before you recharge the gift. No, you don't actually have be hidden or in darkness to declare this bonus. You do have to hide before you can use this again.",
         'basic', '1/hide', ''],
        ['Spray', 'spray',
         'declare Spray attack, 1/rest',
         "You have a noxious spray. In a combat situation, you can declare an Attack action to use a \"Spray\" attack. Unlike other attacks, you have to use this gift up. Whether you hit or you miss, you won't be able to spray again until you can recharge this gift, which takes time and a fair amount of hydration. Spray methods are described on page 89.",
         'basic', '1/rest', ''],
        ['Stealth', 'stealth',
         'bonus d12 to hide and sneak',
         "You may claim a bonus d12 to Evasion rolls to hide and to sneak. (Sorry, there's no bonus to dodge\u{2026} but successful hiding and sneaking does make you harder to hit.) When you suffer penalties to observe things due to the concealing darkness all about, you may claim a bonus d12 to Observation to perceive what's around you. No bonuses to attack or to defend, though. The Game Host may give you a d12 bonus to other rolls as well, if being stealthy somehow would help.",
         'basic', '', ''],
        ['Streetwise', 'streetwise',
         'bonus d12 with crime, fencing',
         "You may claim a bonus d12 to any rolls of Academics or Observation, to know or to recognize the criminal element. You may claim a d12 bonus to any gossip rolls to gather information about criminals. Also, you can buy Proscribed goods at 50% of their listed price, and you can sell Proscribed goods at 10% of their listed price. (Characters without this gift must buy at 100% and sell at only 5%.)",
         'basic', '', ''],
        ['Survival', 'survival',
         'bonus d12 in the wilderness',
         "You may claim a bonus d12 to any rolls of Academics, Endurance, or Observation to make your way in the untamed wilderness. You get this bonus to forage for food, to find drinkable water, and to create shelter from the elements, among other things.",
         'basic', '', ''],
        ['Swimming', 'swimming',
         'bonus d12 to swim',
         "You may claim a bonus d12 to all Athletics rolls to swim\u{2026} or to not drown, which is the basic premise of swimming in the first place. While swimming, none of your dice are limited in any way. (Characters without this gift have their dice reduced to either their highest Athletics die or to d4.)",
         'basic', '', ''],
        ['Team Player', 'team-player',
         'you assist better, and for d12 bonus',
         "You work well with others. When you successfully assist someone else, the bonus you provide is d12. (Characters without this gift can only give a d8 bonus.) You keep your mistakes to yourself. If you botch on an assist attempt, something bad happens to you, but the task-master doesn't automatically fail, too. (Characters without this gift ruin any task that they assist, if they roll all ones and botch.)",
         'basic', '', ''],
        ['Tracking', 'tracking',
         'bonus d12 to follow or not be followed',
         "You may claim a bonus d12 to any rolls of Observation to follow someone else, or to Evasion to avoid being followed by someone else. If there's witnesses, you can gain a bonus d12 to follow somebody who was just here, by asking which way that he went (or just seeing which way people are looking).",
         'basic', '', ''],
        ['Wealth', 'wealth',
         'produce lots of money, 1/episode',
         "You're stinking rich. Use this gift to produce lots of money, as you use your checkbook, bank accounts, and good credit to just pay for stuff. Among other things, Wealth can let you buy your way out of trouble, or maybe you can pick up an Extravagant item without counting the cost. You can only use this gift once per episode. This gift recharges at the start of the next episode.",
         'basic', '1/episode', ''],
        ['Wrestling', 'wrestling',
         'use Wrestling Attacks',
         "You can use the Wrestling Attacks of \"Crush\", \"Suplex\", \"Throw\", and \"Wrestle\". (Characters without this gift must make do with the second-rate Unarmed attacks.) Wrestling methods are described on page 89.",
         'basic', '', ''],

        // ── Advanced Gifts (alphabetical) ─────────────────────────────────────
        ['Counter-Tactics', 'counter-tactics',
         'bonus d12 vs. Tactics',
         "If you have the gift of Counter-Tactics, you may claim a bonus d12 to any counter or dodge when other people try to use Tactics dice to blindside you. (When you're outnumbered or attacked from surprise, you are blindsided. Blindsiding attackers can claim any Tactics dice they have as bonus attack dice\u{2026}. But if your attackers claim that gift's d12 to oppose them.)",
         'advanced', '', ''],
        ['Expert [of Choice]', 'expert',
         'extra d8',
         "Skill dice are like real estate. You want as much and as big as you can get. When you get this gift, there has to be a skill choice to go with it. If the Host gives you this gift as a reward, the Host chooses the skill. If you buy this with your own Experience, then you choose the skill. You gain a d8 in your Skill of choice. If you already have the skill, this is another d8. This d8 from Expert is a Skill die in every way, just like the ones you get from Traits. You may have this gift multiple times, choosing a different Skill each time. You can only buy Expert once per Skill.",
         'advanced', '', ''],
        ['Extra Career [of choice]', 'extra-career',
         'gain d4 in a new Career Trait',
         "You can buy a second Career Trait. Before you can get this gift, you must have all the gifts that Career starts with. Your new Career Trait starts at d4. You get a brand-spanking new column to use, to boost three more skills. You can improve this Trait with the \"Improved Trait\" gift\u{2026} but now that you have two Career Traits, you have to improve each one separately.",
         'advanced', '', 'Requires the gifts that Career starts with'],
        ['Extra Type [of choice]', 'extra-type',
         'gain d4 in a new Type Trait',
         "You can buy a second Type Trait. Before you can get this gift, you must have all the gifts and/or soaks that Type starts with. Your new Type Trait starts at d4. You get a factory-fresh new column to use, to boost three more skills. You can improve this Trait with the \"Improved Trait\" gift\u{2026} but now that you have two Type Traits, you have to improve each one separately.",
         'advanced', '', 'Requires the gifts and soaks that Type starts with'],
        ['Favored Hit', 'favored-hit',
         'Have Favor? +2 damage!',
         "You can only use this gift when you have Favor \u{2014} that is, when you get to re-roll one 1. The easiest way to get Favor is to have a Favorite use of a skill. If you roll one 1 with your attack or your counter, then if you hit your target, you may claim +2 damage. (It doesn't matter if the die you re-roll scores a success or not. If you rolled a 1 and thus can claim favor, you can also claim the +2 damage.) You don't get the +2 damage if you don't roll any ones (because you don't have any Favor).",
         'advanced', '', ''],
        ['Hail of Bullets', 'hail-of-bullets',
         'burn Ammo for +2 damage',
         "If you have this gift, you may declare the Hail of Bullets ability with any weapon that has an Ammo die. A Hail of Bullets changes your attack or counter: Don't roll your Ammo die. Your Ammo die automatically dwindles, dropping one size. (If it dwindles away, you'll have to reload to shoot again.) If your attack or counter hits, it does +2 damage, above and beyond all other damage.",
         'advanced', '', ''],
        ['Improved Ally [Gift or Soak of Choice]', 'improved-ally',
         'add one gift to your Ally',
         "When you buy this Gift, choose a different Gift or Soak to add to your Ally. Your Ally gains that Gift or Soak. Note that the new ability is for the Ally's use, not yours. As always, the Game Host has final say over what Gifts you can and cannot buy for your Ally. If your Ally is killed, you will have to retrain this Improved Ally gift. You may have this gift multiple times, choosing different improvements each time.",
         'advanced', '', ''],
        ['Improved Trait [of choice]', 'improved-trait',
         'Increase chosen trait by one die size',
         "When you first gain this gift, it has to be assigned to a Trait. You can choose one of your basic Traits (Body, Speed, Mind, or Will). You can also choose your Species, Type, or Career. Increase your Improved Trait by one size. For example, if you had a Body of d8, then Improved Trait increases it to d10. Traits have a maximum of d12. You can get this gift more than once, improving the same trait over and over. You can also get it more than once, choosing different Traits to improve.",
         'advanced', '', ''],
        ['Insider [with crowd of choice]', 'insider',
         'bonus d12 with a certain crowd',
         "When you first gain this gift, it has to have a group of people assigned to it. This could be a social organization, such as \"the Labrizio Gang\" or \"The Sunshine City Yacht Club\". You may claim a d12 bonus to rolls to Academics and Observation to know things or to recognize members of this crowd. You may claim a d12 bonus to any gossip rolls to gather information or to fence goods, but only with people in this crowd. You may claim a d12 bonus to any rolls to change the opinion that a member of this crowd has of you. You may have this gift multiple times, choosing a different crowd each time.",
         'advanced', '', ''],
        ['Local Knowledge [of choice]', 'local-knowledge',
         'bonus d12 when in local area of choice',
         "When you first gain this gift, it has to have a small geographical area of choice assigned to it. This area could be a borough of a big city, a redneck wilderness just outside of town, the Pacific Coast Highway, etc. You may claim a d12 bonus to rolls to Academics and Transport to know things or to navigate around this area. You may claim a bonus d12 to any gossip rolls to gather information or to fence goods, but only in this area. You may claim a bonus d12 when pursuing or fleeing other people in a long-distance chase, but only in this area. You may have this gift multiple times, choosing a different area each time.",
         'advanced', '', ''],
        ['Pistol Reflex', 'pistol-reflex',
         '+2 Dmg w/ Pistol Counter + Guard',
         "You may claim +2 damage vs. a target, when you counter-attack with a Pistol and if and only if you are Guarding. A Pistol is a weapon that has the \"Pistol\" descriptor. This +2 damage is only for counter-attacks. It's never for attacks. You must be Guarding to get this +2 bonus. (You can gain Guarding by taking the \"Guard\" action on your turn.)",
         'advanced', '', ''],
        ['Rifle Accuracy', 'rifle-accuracy',
         '+2 Dmg with aimed Rifle attacks',
         "You may claim +2 damage vs. a target, when you attack with a Rifle and if you have an Aiming bonus vs. that target. A Rifle is a weapon that has the \"Rifle\" descriptor. Aim is an action that gives you an aiming bonus to hit a target, when you attack in the same round that you aimed.",
         'advanced', '', ''],
        ['Savant', 'savant',
         'No Skill? Claim Favor!',
         "You're a regular Jack-of-all-Trades. If you have zero skill dice \u{2014} that is, you are just rolling a Basic Trait and maybe some bonus dice from a gift or an assist or whatnot \u{2014} then you can claim Favor on your roll. (That is, you can re-roll one \"1\".) You can't claim this ability if you have even a single d4 in the Skill. (Expert is a skill die.)",
         'advanced', '', ''],
        ['Shotgun Blast', 'shotgun-blast',
         '+2 Dmg with Shotguns @Near',
         "You may claim +2 damage vs. a target, when you attack or counter with a Shotgun and your target is at Near range (3m away from you, or closer). A Shotgun is a weapon that has the \"Shotgun\" descriptor.",
         'advanced', '', ''],
        ['Sniper', 'sniper',
         'Attack with Shooting at increased range',
         "You may extend the attack range of any Shooting weapon by one band. For example, if your Pocket Pistol only attacks up to Medium range, then in your practiced hands, you can use it up to Long range. Your Counter ranges are unaffected. Ranges with attacks that don't use Shooting Skill are not affected.",
         'advanced', '', ''],
        ['Veteran', 'veteran',
         'Aim/Guard bonus is d12 (not d8)',
         "When you Aim at a target, you may claim a d12 bonus to hit, instead of d8. When you are Guarding, you may claim a bonus d12 to all defenses, instead of d8. (Characters without this gift must make do with an aim or guard bonus of merely d8.)",
         'advanced', '', ''],
    ];

    $count = 0;
    foreach ($rows as [$name, $slug, $subtitle, $desc, $type, $recharge, $requires]) {
        cg_exec(
            "INSERT INTO `$t`
                (name, slug, subtitle, description, gift_type, recharge, requires_text)
             VALUES (?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                name=VALUES(name), subtitle=VALUES(subtitle),
                description=VALUES(description), gift_type=VALUES(gift_type),
                recharge=VALUES(recharge), requires_text=VALUES(requires_text)",
            [$name, $slug, $subtitle, $desc, $type, $recharge, $requires]
        );
        $count++;
    }
    return $count;
}

// ── Soaks data ────────────────────────────────────────────────────────────────

function uj_install_soaks(): int {
    $t = uj_tbl('soaks');

    // Each row: [name, slug, damage_negated, recharge, side_effect, description, soak_type]
    $rows = [
        // ── Basic Soaks ───────────────────────────────────────────────────────
        ['Distress Soak', 'distress-soak',
         4, '1/episode', '',
         "You may use this Soak to negate 4 points of damage. (And you can't negate 1 point now and 3 points later, or something weird like that. It's all or nothing.) Immediately after using this, all your friends who see you take the hit or who can hear your voice are rallied with 1 success. Your personal tragedy spurs your friends onward to rescue you\u{2026} or you can beg them to just leave you, if you want to be dramatic about it. If your friends can't see or hear your distress\u{2026} well, you can still negate 4 points of damage, but there's no rally. You can use this ability once per episode. It recharges at the start of the next episode.",
         'basic'],
        ['Frenzy Soak', 'frenzy-soak',
         2, '1/hit', '',
         "You may use this Soak to negate 2 points of damage. (And you can't negate 1 point now and 1 point later, or something weird like that. It's all or nothing.) You recharge this ability if you can hit an enemy with an attack or a counter-attack. When your attack or counter is successful, immediately recharge this gift. (What does not kill you, makes you stronger.)",
         'basic'],
        ['Hurt Soak', 'hurt-soak',
         3, '1/scene', '',
         "You may use this Soak to negate 3 points of damage. (And you can't negate 1 point now and 2 points later, or something weird like that. It's all or nothing.) You can use this ability once per scene. (That is, once about every 5 game minutes.) It recharges at the start of the next scene, when you've had some time to clean yourself up. You're still battered and bruised, but now it only hurts when you laugh.",
         'basic'],
        ['Injury Soak', 'injury-soak',
         4, '1/rest', '',
         "You may use this Soak to negate 4 points of damage. (And you can't negate 1 point now and 3 points later, or something weird like that. It's all or nothing.) You can use this ability once per rest. (That is, after you get 8 hours of sleep and at least one square meal.) After the rest, you still look terrible, and other people will comment on it, until the start of the next episode. To feel better, tell people they should've seen what happened to the other guy.",
         'basic'],
        ['Panic Soak', 'panic-soak',
         2, '1/rally', 'become Panicked',
         "You may use this Soak to negate 2 points of damage. (And you can't negate 1 point now and 1 point later, or something weird like that. It's all or nothing.) Immediately after using Panic Soak, you become Panicked. Panicked is a status debuff that limits your actions. While Panicked, you cannot Attack (but you can still counter). You also cannot Rally other friends. You recharge this gift by being rallied. In game terms, a friend can rally you by using a Rally action and by succeeding on a roll of Will & Tactics vs. 3. (That Rally can also remove your Panic.) You can rally yourself if and only if you can get out of line of sight of all hostiles.",
         'basic'],
        ['Sneaky Soak', 'sneaky-soak',
         2, '1/hide', '',
         "You may use this Soak to negate 2 points of damage. (And you can't negate 1 point now and 1 point later, or something weird like that. It's all or nothing.) You may recharge this gift with a hide stunt. (That is, you must roll Speed, Evasion, & Stealth's d12 vs. 3 and score at least one success \u{2014} more if more people are watching for you.)",
         'basic'],
        ['Winded Soak', 'winded-soak',
         1, '1/recover', '',
         "You may use this Soak to negate 1 point of damage. You recover this gift by simply taking a Recover action in combat.",
         'basic'],

        // ── Advanced Soaks ────────────────────────────────────────────────────
        ['Dazed Soak', 'dazed-soak',
         2, '1/recover', 'become Dazed',
         "You may use this Soak to negate 2 points of damage. (And you can't negate 1 point now and 1 point later, or something weird like that. It's all or nothing.) Immediately after using this Soak, you become Dazed. Until you can get rid of the Dazed condition, you can't counter any attacks, and your next action must be the Recover action. As soon as you take a Recover action, you may recharge this gift. (However, you can use a single Recover action to both remove Dazed and recharge this gift.)",
         'advanced'],
        ['Fumble Soak', 'fumble-soak',
         4, '1/scene', 'become Disarmed',
         "You can use Fumble Soak if you failed to counter (and thus you took damage). You can also use it if you attacked your target, but they successfully countered you (and thus you took damage.) You can also use it if you tied on an attack-vs.-counter contest. You cannot use Fumble Soak if you attempted to dodge your attacker and failed. You can only use Fumble Soak if you are using a weapon \u{2014} not if you are unarmed. If you meet all the above conditions, you may use this Soak to negate 4 points of damage. Immediately after using this Soak, you are Disarmed. The weapon you were using flies out of your hand. You may not recharge this gift until the start of the next scene.",
         'advanced'],
        ['Rampage Soak', 'rampage-soak',
         2, '1/rest', 'bonus d12 to all Counters until recharged',
         "You may use this Soak to negate 2 points of damage. (And you can't negate 1 point now and 1 point later, or something weird like that. It's all or nothing.) After you use up this Soak, you may claim a bonus d12 to all Counters until you recharge this Gift (at your next rest). You cannot claim the d12 bonus to Counters before you've used it. If it's still ready to use, there's no bonus. This Soak never gives you a bonus to Attacks. The bonus is only for Counters.",
         'advanced'],
    ];

    $count = 0;
    foreach ($rows as [$name, $slug, $dmg, $recharge, $side, $desc, $type]) {
        cg_exec(
            "INSERT INTO `$t`
                (name, slug, damage_negated, recharge, side_effect, description, soak_type)
             VALUES (?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                name=VALUES(name), damage_negated=VALUES(damage_negated),
                recharge=VALUES(recharge), side_effect=VALUES(side_effect),
                description=VALUES(description), soak_type=VALUES(soak_type)",
            [$name, $slug, $dmg, $recharge, $side, $desc, $type]
        );
        $count++;
    }
    return $count;
}
