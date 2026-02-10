# SMS Fallback & Audit Plan

Goals:
- Add provider fallback and detailed audit logging for SMS

Checklist:
- [ ] Create `sms_logs` table with provider, request, response, status, attempt_count
- [ ] Implement provider priority config with fallback ordering
- [ ] On failure, try next provider with exponential backoff
- [ ] Mark final failure and notify admins if all providers fail
- [ ] Keep logs for at least 90 days and provide an export UI

Notes:
- Be mindful of provider rate limits and opt-out/unsubscribe handling.