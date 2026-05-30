# Memory Index

- [UJ junction rebuild over proxy](uj-junction-rebuild.md) — never run uj_build_joins through the dev proxy (it truncates LIVE then dies mid-rebuild); use tools/uj-rebuild-joins.php (bulk, ~20s).
- [UJ name matching](uj-name-matching.md) — junctions resolve text gift/skill/soak names via uj_normalize_name (strips die specs & soak mods); normalize both sides; occult careers grant Powers/Soaks by name.
