# Billing & Payments Plan

Goals:
- Implement daily vs monthly billing cycles
- Ensure prepaid/postpaid logic consistency
- Auto-generate monthly bills on the 1st
- Ensure service termination on expiry

Checklist:
- [ ] Review `Billing` and `Invoice` models and migrations
- [ ] Add `billing_cycle` column (enum: daily, monthly) if missing
- [ ] Create scheduled command `bills:generate` and schedule in `Kernel.php`
- [ ] Add SQL constraints to prevent duplicate bills/payments
- [ ] Implement immediate network access termination on package expiry
- [ ] Add invoice generation (PDF/Excel) using `maatwebsite/excel` and `barryvdh/laravel-dompdf`
- [ ] Add integration tests for billing flows

Notes:
- Ensure timezone-aware scheduling; use UTC in DB and localize in UI.