<?php
/**
 * Urban Jungle — data layer.
 *
 * Provides:
 *   uj_install_tables — CREATE TABLE IF NOT EXISTS for the three trait tables.
 *   uj_install_data   — INSERT/UPSERT all Species, Type, and Career trait rows.
 *   uj_get_species    — list all published species (alphabetical).
 *   uj_get_types      — list all published type traits (alphabetical).
 *   uj_get_careers    — list all published career traits (alphabetical).
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

function uj_install_tables(): void {
    uj_admin_require();
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
    ];

    $created = [];
    foreach ($sqls as $sql) {
        cg_exec($sql);
        preg_match('/CREATE TABLE IF NOT EXISTS `([^`]+)`/', $sql, $m);
        $created[] = $m[1] ?? '?';
    }

    cg_json(['success' => true, 'data' => 'Tables created: ' . implode(', ', $created)]);
}

// ── INSERT / UPSERT DATA ──────────────────────────────────────────────────────

function uj_install_data(): void {
    uj_admin_require();

    $counts = [];
    $counts['species']  = uj_install_species();
    $counts['types']    = uj_install_types();
    $counts['careers']  = uj_install_careers();

    $msg = "Upserted: {$counts['species']} species, {$counts['types']} types, {$counts['careers']} careers.";
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

function uj_get_all_traits(): void {
    // Convenience: fetch all three tables in one round-trip.
    $sp = cg_query("SELECT id, name, slug, description, skill_1, skill_2, skill_3, gift_1, gift_2 FROM `" . uj_tbl('species') . "` WHERE published = 1 ORDER BY name ASC");
    $ty = cg_query("SELECT id, name, slug, description, skill_1, skill_2, skill_3, gift_1, soak_1, soak_2, gear FROM `" . uj_tbl('types') . "` WHERE published = 1 ORDER BY name ASC");
    $ca = cg_query("SELECT id, name, slug, description, skill_1, skill_2, skill_3, gift_1, gift_2, gear FROM `" . uj_tbl('careers') . "` WHERE published = 1 ORDER BY name ASC");
    cg_json(['success' => true, 'data' => ['species' => $sp, 'types' => $ty, 'careers' => $ca]]);
}
