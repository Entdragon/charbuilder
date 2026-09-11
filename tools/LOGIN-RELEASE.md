# WordPress login fix release

This is a PHP-only change. Do not rebuild or replace the Urban Jungle bundle:
the deployed bundle is known to differ from the repository.

## Verify before release

Run `php tests/auth.php` in the checkout. The tests use synthetic fixtures and
do not access the database or real accounts.

Review the GitHub changes before pushing: the workspace contains earlier,
unpublished character-builder work. Do not assume every local commit belongs
in this login release.

## Hosting deployment

After the reviewed changes have reached GitHub, check the hosting checkout is
clean and pull with `git pull --ff-only` in `~/charbuilder`.

Back up these existing files outside the public web directory:

- `~/public_html/includes/password.php`
- `~/public_html/actions/auth.php`
- `~/public_html/includes/auth-proxy.php` if it already exists

Copy exactly these files from the checkout, in this order:

1. `php/includes/auth-proxy.php` → `~/public_html/includes/auth-proxy.php`
2. `php/includes/password.php` → `~/public_html/includes/password.php`
3. `php/actions/auth.php` → `~/public_html/actions/auth.php`

The new helper must be deployed before the login handler that requires it.
No database migration, password reset, JS build, or proxy-server update is needed.
Do not copy application configuration or the Replit router.

Compare each deployed file to its source with `cmp` and PHP-lint all three.
If the host does not automatically refresh PHP OPcache, refresh it through the
hosting controls. Verify a login through the site's normal login screen;
never paste a user's password into chat or logs.

Server logs now distinguish proxy configuration, transport, HTTP, malformed
response, and rejected-password failures. Client errors remain generic.
The earlier unprefixed bcrypt failures are not explained by this fix and should
not be described as resolved without a successful login.

To roll back this release, restore the backed-up login handler and verifier.
If the helper was newly added, it can remain unused until investigated.