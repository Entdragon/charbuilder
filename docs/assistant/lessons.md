# Assistant Lessons Log

Last updated: 2026-01-19 05:07:32Z

## Copy/paste hazards (recurring)
### 1) Ctrl-S “freeze” (XON/XOFF)
- Symptom: terminal appears hung
- Fix (session): `stty -ixon`
- Unfreeze: Ctrl-Q

### 2) Heredoc waiting forever
- Symptom: paste “hangs” during `cat <<'EOF'`
- Fix: Ctrl-C, then inspect temp file + finalize
- Better: use a python atomic writer instead of heredocs for long text

### 3) Backticks inside double quotes
- Symptom: `bash: json: command not found` (or similar)
- Cause: backticks in "..." trigger command substitution
- Fix: use single quotes around grep patterns containing backticks, or escape them

## Recovery playbook
1) Press Enter once (sometimes paste ended mid-line)
2) Try Ctrl-Q (if flow control got triggered)
3) Ctrl-C to abort a stuck heredoc/command
4) If writing a file: locate temp file, check head/tail, then mv into place

## Notes (chronological)
- [2026-01-11 08:07:40Z] Installed cg-note helper; use this to record paste/hang issues and fixes.
- [2026-01-11 08:09:25Z] Installed tools/cg-write (atomic STDIN writer) — prefer this over heredocs for long content; paste then Ctrl-D.
- [2026-01-11 08:10:42Z] Installed tools/cg-write-pair: write long docs via STDIN once, auto-backup + mirror to docs/ copy. Prefer this over heredocs.
- [2026-01-11 08:18:05Z] Copy/paste hazard: avoid triple-backtick inside double quotes; bash treats backticks as command substitution and can drop you into the > continuation prompt. Fix: Ctrl-C. Prevention: write "triple-backtick" or use single quotes.
- [2026-01-11 08:20:05Z] cg-note updated: supports stdin + avoids quoting hazards.
- [2026-01-11 08:20:05Z] (multi-line)
  line one
  line two
- [2026-01-11 08:28:16Z] Installed tools/cg-stage — use: . tools/cg-stage at the start of every session to set STAGE/CG, cd into plugin, and disable Ctrl-S flow control.
- [2026-01-11 08:32:25Z] Upgraded cg-write/cg-write-pair to print a clear 'Waiting for STDIN' banner (TTY only) + remind: paste contents then Ctrl-D; Ctrl-C abort writes nothing.
- [2026-01-11 08:35:40Z] Rewrote tools/cg-stage cleanly (no embedded test commands). It now derives CG from its own location, sets STAGE, disables Ctrl-S flow control, and prints whoami/host/pwd.
- [2026-01-11 08:37:19Z] Added tools/cg-help (prints quick workflow + STDIN paste rules). Run: ./tools/cg-help anytime you forget what to paste.
- [2026-01-11 08:45:35Z] Fix: cg-write/cg-write-pair now compatible with older python3 by using typing.List (avoids list[str] TypeError). Transcript guard retained.
- [2026-01-11 08:49:52Z] Hardened cg-write transcript guard: now matches up to 3 stray characters before [user@host]$ (e.g. r[libraryo@...]$).
- [2026-01-11 08:53:51Z] Polish: cg-write/cg-write-pair banners now print to STDERR + flush (more reliable before paste). Updated cg-help with Ctrl-U line-clear tip for garbled prompt lines.

- Shell gotcha: avoid backticks in grep patterns (can trigger `unexpected EOF while looking for matching ``'`). Use single quotes or remove backticks from patterns.

- Shell gotcha: with `set -u`, avoid `${var}` in strings unless var is defined (e.g. echoing `cg-free-choice-${i}` can crash with `unbound variable`). Use single quotes or remove `$`.
- [2026-01-19 04:36:45Z] Lesson: detached npm builds must cd to an absolute path expanded BEFORE bash -lc runs. Using cd '$CG' inside bash -lc fails because inner shell lacks $CG, runs in HOME, and npm reports Missing script: build:core.
- [2026-01-19 05:07:32Z] Lesson: when reading bg build results, derive RC from the newest LOG timestamp (LOG->TS->RC). Don’t ls -t *.rc separately or you’ll accidentally read an older rc.
## 2026-01-24 21:37:12Z — core.bundle.js 404 caused by permissions

- **Cause:** `assets/js/dist/core.bundle.js` ended up with `0600` (`-rw-------`) (e.g., after an atomic write). Apache may surface this as **404**.
- **Symptom:** Builder won’t open; browser shows `core.bundle.js` **404 / ERR_ABORTED**.
- **Fix:** `chmod 0755 assets assets/js assets/js/dist` and `chmod 0644 assets/js/dist/core.bundle.js assets/js/dist/core.bundle.js.map`; confirm with `curl -I` → 200/304.
- **Prevention:** npm `postbuild:core` / `postbuild:css` chmod hooks to enforce web-readable perms after builds.

