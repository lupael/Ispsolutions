# Frontend & UX Plan

Goals:
- Align dashboards with Metronic demo1
- Ensure role-based visibility and customer registration flow

Checklist:
- [ ] Inventory current dashboard pages and components
- [ ] Map roles to menu/feature visibility and enforce in Blade components
- [ ] Refactor Axios calls to use a standardized `api/` prefix and centralized service
- [ ] Validate Chart.js and Mapael integrations for performance
- [ ] Implement mobile phone number registration flow and validation
- [ ] Add frontend tests for critical components (Vitest/Jest)

Notes:
- Vite + Tailwind are already used; ensure `vite` build runs in CI.