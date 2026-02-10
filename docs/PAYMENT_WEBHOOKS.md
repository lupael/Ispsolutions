# Payment Webhooks & Reconciliation Plan

Goals:
- Add secure webhook handling, idempotency, and reconciliation

Checklist:
- [ ] Create webhook endpoints with signature verification per gateway
- [ ] Persist raw webhook payloads to `payment_callbacks` for auditing
- [ ] Implement idempotency keys and ignore duplicate callbacks
- [ ] Create nightly reconciliation job that compares gateway reports with DB
- [ ] Add manual reconciliation UI for admins to resolve discrepancies
- [ ] Add tests for signature verification and idempotency

Notes:
- Rotate webhook secret keys and provide admin UI to view active keys.