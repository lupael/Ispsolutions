# Authentication & AAA Plan

Goals:
- Validate FreeRADIUS integration for PPPoE and Hotspot
- Implement WebAuthn passwordless login
- Enforce MAC binding and duplicate session prevention

Checklist:
- [ ] Document current RADIUS flow and NAS integration points
- [ ] Add tests/mocks for RADIUS responses
- [ ] Add WebAuthn routes, controllers, and a test registration/login page
- [ ] Add DB tables for WebAuthn credentials (migration)
- [ ] Implement MAC binding enforcement in provisioning and login flows
- [ ] Add middleware/check to prevent duplicate sessions for the same customer
- [ ] Test router -> RADIUS -> Laravel end-to-end in staging

Notes:
- WebAuthn libraries: `web-auth/webauthn-framework` or `laravel/webauthn` wrappers.
- FreeRADIUS configuration must be reviewed by the network team.