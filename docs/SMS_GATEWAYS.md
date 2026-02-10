# SMS Gateways & API Wrapper Plan

Goals:
- Integrate multiple SMS providers with a unified wrapper
- Add fallback ordering and audit logging
- Ensure customer notifications before expiry

Checklist:
- [ ] Create `SmsProviderInterface` and driver implementations per provider
- [ ] Central `SmsService` to choose provider and fallback
- [ ] Log all SMS requests/responses in `sms_logs` table
- [ ] Add retry and exponential backoff for provider failures
- [ ] Create configuration for provider priority and credentials
- [ ] Add unit tests and integration tests with mocked providers

Initial provider list (from `todo.md`): Maestro, Robi, M2M, BDBangladesh SMS, Bulk SMS BD, BTS SMS, 880 SMS, BD Smart Pay, ElitBuzz, SSL Wireless, ADN, SMS24, SMS BDSMS NetBrand, SMSMetrotel, DianaHostSMS, Dhaka Soft BD
