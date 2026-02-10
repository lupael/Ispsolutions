# Router & Network Integration Plan

Goals:
- Refactor MikroTik API calls into modular services
- Move hardcoded IP ranges/firewall rules into configs
- Add error handling for router API failures
- Test PPPoE and Hotspot provisioning end-to-end

Checklist:
- [ ] Audit existing router API usage (`evilfreelancer/routeros-api-php`)
- [ ] Extract router calls into `App\Services\Router\MikrotikService`
- [ ] Add retry and circuit-breaker patterns for API failures
- [ ] Move IP ranges and firewall rules to `config/router.php`
- [ ] Add tests that mock Router API to validate provisioning logic
- [ ] Document NAS/RADIUS mapping and expected responses

Notes:
- Avoid changes to production router configs without network team sign-off.
- Use secure storage for router credentials and limit access.