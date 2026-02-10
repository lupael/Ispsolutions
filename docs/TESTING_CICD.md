# Testing & CI/CD Plan

Goals:
- Add automated testing (PestPHP/PHPUnit) and frontend tests
- Run static analysis with PHPStan/Larastan and linting with Pint
- Integrate CI to run tests on PRs

Checklist:
- [ ] Ensure `composer test` runs PHPUnit/Pest tests
- [ ] Add `phpstan.neon` and configure `larastan` levels
- [ ] Add `vitest` or `jest` for frontend tests, configure `npm test`
- [ ] Add GitHub Actions workflows for `lint`, `test`, and `build`
- [ ] Add code coverage job (optional) and fail PRs on threshold drop
- [ ] Add `php-cs-fixer` or `pint` job for style enforcement

Notes:
- A basic CI workflow was added at `.github/workflows/ci.yml` to run tests and phpstan.