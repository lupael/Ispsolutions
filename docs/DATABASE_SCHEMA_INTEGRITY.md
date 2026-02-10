# Database Schema & Integrity Plan

Goals:
- Add foreign key constraints and unique indexes
- Run migrations to remove deprecated fields
- Document schema with ERD

Checklist:
- [ ] Review `customer`, `bill`, `payment` models and add FK constraints
- [ ] Add unique indexes for usernames, MAC addresses, and IPs
- [ ] Create migrations to add constraints with `->constrained()` where possible
- [ ] Add checks to prevent duplicate bills/payments (unique composite indexes)
- [ ] Generate ERD using `laravel-erd` or draw.io and save in `docs/`
- [ ] Run migrations in staging and verify data integrity

Notes:
- Use `validate()` scripts to detect orphaned records before applying FKs.
- Back up DB prior to structural changes.