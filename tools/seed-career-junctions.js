#!/usr/bin/env node
/**
 * tools/seed-career-junctions.js
 *
 * One-shot migration: populate career_skills and career_gifts junction
 * tables from the michae20_ironclaw_table_careers SQL dump.
 *
 * Source column order in the dump:
 *   id, name, type, skill_1, skill_2, skill_3,
 *   gift_1, gift_1_choice, gift_2, gift_2_choice, gift_3, gift_3_choice, ...
 *
 * Run with:  node tools/seed-career-junctions.js
 */

const { query, prefix } = require('../server/db');

// [id, skill_1, skill_2, skill_3, gift_1, gift_2, gift_3]
const RAW = [
  [1,   1,  12,  21,   14,  15,  16],
  [2,   1,   9,  10,   46,  15,  31],
  [3,   1,   9,  10,   47,  32,  15],
  [4,  13,  20,  23,   21,  19,  27],
  [5,   1,   9,  10,   48,  46,  15],
  [6,   5,   9,  14,   17,  18,  19],
  [7,  14,  15,  20,   49,  19,  94],
  [8,   2,  10,  15,   20,  98,  21],
  [9,   2,  13,  19,   21,  64,  96],
  [10,  3,  19,  20,  107,  64,  19],
  [11, 12,  13,  18,   50,  26,  51],
  [12,  2,   7,  13,   34,  52,  27],
  [13,  5,  14,  16,   23,  24,  19],
  [14,  7,  11,  16,   77, 113, 112],
  [15, 15,  18,  25,   53,  36,  54],
  [16,  4,  10,  14,   55,  33,   3],
  [17,  9,  10,  14,   25,  15,  26],
  [18, 10,  12,  14,   56,  15,  26],
  [19,  7,   8,  13,   21,  81,  27],
  [20,  1,  15,  21,   28,  29,  15],
  [21,  1,   4,   6,   30,  15,  57],
  [22,  5,   9,  16,   17,  23, 110],
  [23,  1,   8,  19,   30,  15,  31],
  [24,  4,   8,  26,   55,  53,   3],
  [25,  5,  10,  14,   24,  33,  19],
  [26,  1,  10,  14,   58,  70,  15],
  [27,  9,  10,  15,   17,  71,  19],
  [28,  9,  10,  16,   77,  68, 112],
  [29,  1,   6,  19,   15,  72,  96],
  [30, 13,  16,  18,   25,  51,  19],
  [31,  5,  16,  24,   77,  63, 112],
  [32,  8,  17,  19,   97,  94,  96],
  [33,  7,   8,  16,   77, 112,  44],
  [34,  2,  13,  15,   59,   9,  21],
  [35,  9,  10,  16,   77, 112,  32],
  [36,  7,  16,  21,   77, 112,  15],
  [37,  7,  13,  23,   32,  26,  27],
  [38, 13,  17,  18,   51,  21,  27],
  [39, 13,  17,  22,   21,  60,  27],
  [40,  7,  13,  17,   33,  21,  27],
  [41,  9,  10,  14,   56,  37,  33],
  [42,  8,   9,  15,   35,  36,  97],
  [43,  4,  13,  24,   55,  21,  27],
  [44,  7,  16,  20,   77, 112,  49],
  [45,  1,  15,  26,   61,  30,  15],
  [46,  7,  15,  16,   77, 112,  98],
  [47, 18,  25,  26,   53,   3,  91],
  [48,  7,  17,  20,   21,  19,  27],
  [49,  1,  13,  21,   38,  15,  16],
  [50,  7,  12,  16,   77, 112,   7],
  [51,  4,   8,  14,   55,  33,  62],
  [52,  1,  10,  15,   48,  15,  72],
  [53,  7,  15,  16,   77, 112,  19],
  [54,  5,   9,  20,   63,  64,  19],
  [55,  2,   7,  13,   34,  21,  27],
  [56,  7,  13,  16,   77, 112,  27],
  [57,  1,   4,   8,   46,  15,  65],
  [58,  7,   9,  14,   17,  37,  19],
  [59,  7,   8,  17,   34,  21,  27],
  [60,  2,  13,  20,   21,  19,  27],
  [61, 22,  25,  26,   17,  60,   3],
  [62,  1,  10,  21,  212,  15,  31],
  [63,  8,  15,  20,   35,  97,  21],
  [64,  1,  16,  21,   15,  68,  31],
  [65,  9,  10,  14,   32,  49,   3],
  [66,  2,  13,  23,   66,  21,  27],
  [67,  9,  10,  15,   49,  64,  19],
  [68, 13,  17,  23,   97,  21,  27],
  [69,  1,  10,  21,   39,  40,  15],
  [70,  1,  14,  21,   41,  40,  15],
  [71,  2,  13,  23,   23,  49,  67],
  [72,  7,  10,  16,   77, 112,  76],
  [73,  1,  15,  21,   42,  43,  15],
  [74,  9,  16,  19,   77, 112,  17],
  [75,  8,   9,  15,   17,  44,  19],
  [76,  7,  13,  21,   45,  29,  15],
  [77, 13,  15,  19,   69,  21,  27],
  [78, 13,  19,  21,   15,  42,  43],
  [79,  2,   4,   8,   17,  81,   3],
  [80,  1,  12,  25,   26,   7,  27],
  [81,  1,  12,  21,  381, 273, 176],
  [82,  4,  15,  25,   33,  60,   3],
  [83, 15,  23,  25,   60,   3,   7],
  [84,  1,  14,  21,  273, 282, 385],
  [85,  1,  21,  26,  273, 282, 385],
  [86,  8,  15,  22,   34, 155, 103],
  [87,  1,   9,  10,  146, 276,  60],
  [88,  4,  15,  25,  232,  60,   3],
  [89, 10,  15,  21,  130, 131, 273],
  [90,  4,  13,  24,  389,  21,  27],
  [91,  4,   7,  13,  388,  21,  27],
  [92, 22,  25,  26,   29,  45,  15],
  [93,  9,  14,  25,   33,  15, 375],
  [94,  1,  12,  25,   15,  16,  38],
  [95,  1,  12,  25,   19,   7,  27],
  [96,  1,  15,  25,   33,  19,   3],
  [97,  4,  15,  25,   21,   3,  27],
  [98,  2,   7,  21,  130, 131, 273],
  [99,  2,   7,  11,   59,  21,  27],
  [100, 7,  13,  22,  383,  21,  27],
  [101, 1,   4,  14,   55,  33,  15],
  [102, 1,   2,  21,   44,  15, 285],
  [103, 2,  13,  20,   59,  23,  49],
  [104,14,  15,  20,   49,  19,  94],
  [105, 1,   2,   7,   59,  15,  27],
  [106, 2,   7,  16,   21, 309,  27],
  [107, 7,  13,  23,   59,  21,  27],
  [108, 2,   7,  16,   59,  81,  27],
  [109, 1,   9,  10,  237,  15,  57],
  [110, 1,  10,  21,  215, 216,  15],
  [111, 2,   7,  21,  273,  15,  27],
  [112, 8,  14,  19,   62,  49,  19],
  [113, 1,  10,  15,   48,  15,  72],
  [114, 2,  14,  15,   20,  59,  98],
  [115, 2,   7,  16,   34,  59,  27],
  [116, 1,  12,  21,  228,  15, 273],
  [117, 4,   7,  23,   21,   3,  27],
  [118, 7,  13,  25,   88,  21,  27],
  [119, 1,  10,  14,   56,  15,  68],
  [120, 7,  15,  17,   15, 211,  27],
  [121, 1,  14,  16,   58,  15,  26],
  [122, 1,   4,  14,   33, 237,  15],
  [123, 2,   9,  16,   59,  21,  27],
  [124, 4,   8,  15,   97,  94,   3],
  [125, 4,   8,  15,   55,  49,   3],
  [126, 1,   7,  14,  155, 110,  27],
  [127, 5,   9,  10,   37,  33,  19],
  [128, 1,  15,  21,   15, 209, 208],
  [129, 2,   7,  16,   59,  33,  19],
  [130, 1,   4,  15,   55,  15,  31],
  [131, 1,  19,  21,   98,  15,  31],
  [132, 2,   7,  21,   59,  21,  27],
  [133, 1,   9,  16,   15,  68, 110],
  [134, 7,  13,  16,  188, 110, 182],
  [135, 1,   7,  21,  108,  15, 205],
  [136, 1,  10,  16,   33,  15,  57],
  [137, 1,  10,  14,   58,  33,  15],
  [138, 2,   6,   7,   59,  90,  27],
  [139, 7,  13,  23,    9, 228,  27],
  [140, 1,   2,  21,   59,  15,  81],
  [141, 1,  10,  21,  146,  48, 276],
  [142, 1,  14,  21,   56, 397, 398],
  [143, 7,  13,  17,   15, 285,  27],
  [144, 1,  13,  21,  397, 398,  27],
  [145, 1,  15,  21,   15, 273,  31],
  [146, 4,   8,  21,   49, 276, 285],
  [147, 1,   8,  21,  276, 285,  94],
  [148, 7,  13,  17,  146, 276,  27],
  [149, 1,   8,  21,  228, 276, 285],
  [150,10,  19,  21,   69,  15,  27],
  [151, 1,  10,  12,   46, 276,   3],
  [152, 1,  15,  21,  146,  98, 276],
  [153, 1,  12,  21,  236,  15,  65],
  [154, 1,  15,  21,   98, 397, 398],
  [155, 1,   8,   9,   44,  97, 285],
  [156, 1,   8,  21,   14, 236,  15],
  [157, 1,  16,  21,   97,  68, 285],
  [158, 1,   8,  14,   14,  15,  57],
  [159, 4,   8,  21,   55, 276, 285],
  [160, 1,   5,  21,   15,  49, 285],
  [161, 1,   4,  21,   14,  55,   3],
  [162, 8,  19,  21,  146, 245, 276],
  [163, 7,  13,  21,   20,  98,  21],
  [164, 1,   4,  24,  123,  15,  31],
  [165,10,  12,  14,  401,  39,  15],
  [166, 1,  15,  21,   29,  45,  15],
  [167,10,  19,  21,   49,  19,  94],
  [168, 1,  15,  19,   61, 364,  94],
  [169, 8,  16,  21,  110, 285,  94],
  [170, 5,  16,  21,  399,  15, 387],
  [171, 1,  13,  25,   72, 285,  27],
  [172, 8,  17,  23,  391,  21,  27],
  [173, 7,  13,  23,  386,  21,  27],
  [174,10,  17,  19,  391,  21,  27],
  [175, 1,  12,  21,   14,  15, 400],
  [176, 2,   8,  24,   17,  91,   3],
  [177, 1,  15,  21,  146, 276, 394],
  [178, 1,  15,  23,   26,  68,   7],
  [179, 7,  12,  16,   26,  68,   7],
  [180, 7,  13,  21,  399,  29,  15],
  [181, 7,  13,  21,  400,  29,  15],
  [182, 7,  13,  21,  401,  29,  15],
];

async function run() {
  const p = prefix();
  const csTable = `${p}customtables_table_career_skills`;
  const cgTable = `${p}customtables_table_career_gifts`;

  // Verify tables exist and are empty
  const csCount = (await query(`SELECT COUNT(*) AS n FROM ${csTable}`))[0].n;
  const cgCount = (await query(`SELECT COUNT(*) AS n FROM ${cgTable}`))[0].n;

  if (Number(csCount) > 0 || Number(cgCount) > 0) {
    console.log(`Tables already have data — career_skills: ${csCount}, career_gifts: ${cgCount}`);
    console.log('Truncating before re-seed...');
    await query(`TRUNCATE TABLE ${csTable}`);
    await query(`TRUNCATE TABLE ${cgTable}`);
  }

  // Verify the WP careers table ct_id alignment
  const sample = await query(`SELECT ct_id, ct_career_name FROM ${p}customtables_table_careers ORDER BY ct_id LIMIT 5`);
  console.log('WP careers sample (check alignment):', sample.map(r => `${r.ct_id}=${r.ct_career_name}`).join(', '));

  const skillRows = [];
  const giftRows  = [];

  for (const [id, s1, s2, s3, g1, g2, g3] of RAW) {
    if (s1) skillRows.push([id, s1, 1]);
    if (s2) skillRows.push([id, s2, 2]);
    if (s3) skillRows.push([id, s3, 3]);
    if (g1) giftRows.push([id, g1, 1]);
    if (g2) giftRows.push([id, g2, 2]);
    if (g3) giftRows.push([id, g3, 3]);
  }

  // Batch insert career_skills
  const skPlaceholders = skillRows.map(() => '(?,?,?)').join(',');
  const skFlat = skillRows.flat();
  await query(`INSERT INTO ${csTable} (career_id, skill_id, sort) VALUES ${skPlaceholders}`, skFlat);
  console.log(`Inserted ${skillRows.length} career_skills rows`);

  // Batch insert career_gifts
  const cgPlaceholders = giftRows.map(() => '(?,?,?)').join(',');
  const cgFlat = giftRows.flat();
  await query(`INSERT INTO ${cgTable} (career_id, gift_id, sort) VALUES ${cgPlaceholders}`, cgFlat);
  console.log(`Inserted ${giftRows.length} career_gifts rows`);

  // Spot-check: Almoner (id=1) should have Academics, Leadership, Supernatural
  const check = await query(`
    SELECT c.ct_career_name, sk.ct_skill_name, cs.sort
    FROM ${csTable} cs
    JOIN ${p}customtables_table_careers c ON c.ct_id = cs.career_id
    JOIN ${p}customtables_table_skills sk ON sk.ct_id = cs.skill_id
    WHERE cs.career_id = 1
    ORDER BY cs.sort
  `);
  console.log('Almoner skills (expect Academics, Leadership, Supernatural):',
    check.map(r => r.ct_skill_name).join(', '));

  const checkG = await query(`
    SELECT g.ct_gifts_name, cg.sort
    FROM ${cgTable} cg
    JOIN ${p}customtables_table_gifts g ON g.ct_id = cg.gift_id
    WHERE cg.career_id = 1
    ORDER BY cg.sort
  `);
  console.log('Almoner gifts:', checkG.map(r => r.ct_gifts_name).join(', '));

  process.exit(0);
}

run().catch(err => { console.error(err); process.exit(1); });
