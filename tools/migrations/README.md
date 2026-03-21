# Database Migrations

SQL migration files for the Library of Calabria production database (`libraryo_wp_ainng`).

## How migrations work

Each migration is a numbered SQL file (or directory for multi-phase work) that is applied manually via **phpMyAdmin → SQL tab**. There is no automated runner — run each file against the production database and confirm success before proceeding to the next.

## Naming convention

```
NNN-short-description.sql          ← single-file migration
NNN-short-description/             ← multi-file migration (phases)
  README.md                        ← explains the phases
  01-expand.sql
  02-migrate.sql
  03-contract.sql
```

`NNN` is a zero-padded three-digit sequence number. Always increment — never reuse a number.

## Applying a migration

1. Open **phpMyAdmin** → select database `libraryo_wp_ainng`
2. Click the **SQL** tab
3. Paste the contents of the migration file
4. Review the SQL carefully — especially any `DROP` or `ALTER` statements
5. Click **Go**
6. Verify the result (row counts, table list, spot-check data)
7. Note the date applied in the log below

## Migration log

| # | File | Description | Applied |
|---|------|-------------|---------|
| 001 | `001-cleanup-drop-tables.sql` | Drop 39 redundant/backup tables | — |
| 002 | `002-schema-normalisation/` | Full schema normalisation (expand→migrate→contract) | — |

## Tables to never touch

The following tables are live and must not be modified without a tested migration:

- `DcVnchxg4_character_records` — all saved characters
- All `DcVnchxg4_customtables_table_*` tables — game reference data
- All standard `DcVnchxg4_` WordPress tables (`options`, `posts`, `users`, etc.)
- Three live sites: `libraryofcalbria.com`, `michaeljparry.com`, `characters.libraryofcalbria.com`
