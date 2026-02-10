# Payment Gateways Plan

Goals:
- Integrate local and international gateways via a unified interface
- Handle webhooks, refunds, partial payments, and reconciliation

Checklist:
- [ ] Create `PaymentGatewayInterface` and adapters for: bKash, Nagad, Rocket, SSLCommerz, aamarPay, shurjoPay, Razorpay, EasyPayWay, Walletmix, BD Smart Pay
- [ ] Add webhook endpoints and signature verification
- [ ] Implement idempotency for payment callbacks
- [ ] Add reconciliation jobs for nightly reconciliation
- [ ] Add tests for success/failure/refund flows
- [ ] Ensure PCI compliance and do not log sensitive card data

Notes:
- Prefer tokenized flows for local wallets when supported.
- Add `payment_methods` management UI for admins.