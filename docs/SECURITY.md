# Security Policy

## Supported Versions

We actively support the following versions of ISP Solution with security updates:

| Version | Supported          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |
| < 1.0   | :x:                |

## Reporting a Vulnerability

We take the security of ISP Solution seriously. If you discover a security vulnerability, please follow these steps:

### How to Report

1. **Do NOT** create a public GitHub issue for security vulnerabilities
2. Email security concerns to: **security@[your-domain].com** or create a private security advisory on GitHub
3. Include the following information:
   - Description of the vulnerability
   - Steps to reproduce the issue
   - Potential impact
   - Suggested fix (if available)

### What to Expect

- **Initial Response**: Within 48 hours
- **Status Updates**: Every 5-7 days until resolved
- **Resolution Timeline**: 
  - Critical vulnerabilities: 24-48 hours
  - High severity: 1 week
  - Medium/Low severity: 2-4 weeks

### Security Best Practices

When deploying ISP Solution in production:

1. **Keep dependencies updated**: Run `composer update` and `npm update` regularly
2. **Use strong passwords**: For database, application, and RADIUS
3. **Enable HTTPS**: Configure SSL/TLS for all connections
4. **Secure API keys**: Never commit API keys to version control
5. **Configure firewall**: Restrict access to sensitive ports
6. **Regular backups**: Implement automated backup strategy
7. **Monitor logs**: Review application and system logs regularly
8. **Two-Factor Authentication**: Enable 2FA for admin accounts

### Recent Security Audits

- **2026-01-27**: Comprehensive security audit completed (see [SECURITY_AUDIT_2026_01_27.md](SECURITY_AUDIT_2026_01_27.md))
- **Ongoing**: CodeQL security scanning enabled in CI/CD pipeline

### Security Features

ISP Solution includes these built-in security features:

- Multi-tenant data isolation
- Role-based access control (RBAC)
- Two-factor authentication (2FA)
- API key management with scopes
- Audit logging for all sensitive operations
- CSRF protection on all forms
- SQL injection prevention (Eloquent ORM)
- XSS protection (Blade templating)
- Rate limiting on API endpoints
- Password hashing (bcrypt)

For more details, see our [Security Fixes Summary](SECURITY_FIXES_SUMMARY.md).
