---
name: UJ trait/gift name matching for junction joins
description: How species/type/career text references resolve to skill/gift/soak IDs, and the normalizer rules.
---

# UJ name-based join resolution

Species/types/careers store their granted traits as **text** in `gift_1/gift_2`,
`skill_1..3`, `soak_1/2`. Junctions are built by name-matching those strings
against the `name` column of the skills/gifts/soaks tables via `uj_normalize_name()`.

**Rule:** normalize BOTH sides with `uj_normalize_name()`. The lookup maps in
`uj_build_joins_internal()` must be keyed by the normalized name, not the raw DB
name, or stored names with suffixes won't match references without them.

`uj_normalize_name()` strips: ` [..]` option suffixes, trailing soak modifiers
(` -4`, ` −2`), and trailing die specs (` d6`, ` 2d8`, ` 6d6`). This is why gift
`Personal Power d6` matches a career reference of `Personal Power`.
**Why:** occult careers reference gifts/soaks by their short display name.
Verify no two distinct names collapse to the same normalized key before changing
the normalizer (currently 0 collisions across gifts/skills/soaks).

## Occult careers reference Powers & Soaks by name (not gift rows)
11 Occult Horror careers list a **Power** (Mesmerism, ESP, Spiritualism, Telepathy,
PK, Vitalism) or an **advanced Soak** (Malign/Convocation/Disbelief/Reckoning/
Monstrous/Wicked Soak) in their gift slots. Those aren't gift rows, so
`uj_get_all_full` resolves them server-side into per-career `powers` and `soaks`
arrays (name-match against power meta / soaks table). The builder renders them as
labeled "Power"/"Soak" cards in the Gifts step (`collectAuxGrants`) and folds
career soaks into the summary soak list.
