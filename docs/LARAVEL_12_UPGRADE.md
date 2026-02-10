# Laravel 12.x Upgrade Plan for ispsolution

This document lists concrete steps to validate and complete the application upgrade to Laravel 12.x. `composer.json` already requires `laravel/framework: ^12.0` and PHP `^8.2` — the checklist below focuses on verification, dependency updates and manual code changes.

## Quick checks (do these locally)
- Verify PHP CLI version: `php -v` (must be >= 8.2)
- Backup code & DB: commit changes and export DB snapshot
- Ensure `composer` >= 2.4 (recommended)

## Composer & dependencies
1. Run:

```bash
composer update --with-all-dependencies
```

2. Resolve any conflicts introduced by packages (common packages in this repo: `barryvdh/laravel-dompdf`, `maatwebsite/excel`, `laravel/sanctum`, `larastan`, `pragmarx/google2fa-laravel`, `evilfreelancer/routeros-api-php`).
3. If package versions don't support Laravel 12, upgrade or replace them.

## Framework breaking-changes & code review
- Review Laravel 12 upgrade notes (official changelog) for breaking changes and deprecated features.
- Search codebase for deprecated helpers or behavior (examples): `str_*`, `array_*` helper differences, routing group signature changes, `assert` helpers in tests.
- Verify middleware signatures and route registration: confirm `RouteServiceProvider` and `routes/*.php` match Laravel 12 expectations.
- Validate `Auth` flows: `sanctum` usage, guard config, and session/cookie options.

## Config & environment
- Compare `config/session.php`, `config/auth.php`, `config/fortify.php` (if used) with a fresh Laravel 12 skeleton for new keys or changed defaults.
- Confirm `APP_URL`, `SESSION_COOKIE`, and same-site policies; verify `session` driver behavior.

## Database & migrations
- Run migrations in a staging environment:

```bash
php artisan migrate --force
```

- Ensure migration files are compatible with newer `Illuminate/Database` behavior.

## Tests & static analysis
- Run tests locally:

```bash
composer test
```

- Run `phpstan`/`larastan` and fix issues. Adjust level where necessary.

## Local dev and assets
- Rebuild front-end assets if using Vite:

```bash
npm install
npm run build
```

- Verify `php artisan vite:build` (or use dev server) works without errors.

## Runtime checks (staging)
- Deploy to staging, run smoke tests:
  - Login/Logout
  - Customer creation and billing flow
  - SMS sending (mock or sandbox)
  - Router API calls (MikroTik) in a safe mode

## Post-upgrade tasks
- Update `README` and `todo.md` with any manual changes applied.
- Pin working package versions in `composer.json` once stabilized.
- Create a CI job to run `composer install`, `php artisan test`, and `phpstan` on PRs.

## Notes specific to this repo
- `composer.json` currently sets PHP `^8.2` and Laravel `^12.0` — good starting point.
- Confirm `platform.php` in `composer.json` matches the deployment environment.
- `phpunit` is `^11` in `require-dev` — ensure tests are compatible with PHPUnit 11.

---

If you want, I can now:
- Run a quick code scan for obvious deprecated calls and list them, or
- Add a CI workflow to run `composer install` + tests on PRs, or
- Prepare a small patch updating `composer.json` platform PHP to match a target (if you tell me the deployment PHP).

Which action should I take next?