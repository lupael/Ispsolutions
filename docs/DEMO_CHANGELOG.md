# Demo Changelog Output

This file demonstrates what the automated changelog generation produces.

---

## [2.1.0] - 2026-01-25

### ✨ Features

- **billing**: add PayPal payment gateway integration ([a1b2c3d](../../commit/a1b2c3d)) ([#234](../../pull/234))
- **auth**: implement two-factor authentication with SMS ([e4f5g6h](../../commit/e4f5g6h)) ([#235](../../pull/235))
- **dashboard**: add real-time customer analytics widget ([i7j8k9l](../../commit/i7j8k9l))
- **api**: add REST API for customer management ([m0n1o2p](../../commit/m0n1o2p)) ([#240](../../pull/240))

### 🐛 Bug Fixes

- **billing**: resolve invoice calculation for partial months ([q3r4s5t](../../commit/q3r4s5t)) ([#238](../../pull/238))
- **auth**: fix session timeout after password reset ([u6v7w8x](../../commit/u6v7w8x))
- **mikrotik**: correct PPPoE profile synchronization ([y9z0a1b](../../commit/y9z0a1b)) ([#241](../../pull/241))

### ⚡ Performance Improvements

- **database**: optimize customer query with proper indexing ([c2d3e4f](../../commit/c2d3e4f))
- **api**: add Redis caching for frequently accessed data ([g5h6i7j](../../commit/g5h6i7j))

### 📚 Documentation

- update API documentation with new endpoints ([k8l9m0n](../../commit/k8l9m0n))
- add deployment guide for production environments ([o1p2q3r](../../commit/o1p2q3r))

### ♻️ Code Refactoring

- **services**: extract billing logic into dedicated service ([s4t5u6v](../../commit/s4t5u6v))
- **controllers**: simplify customer controller methods ([w7x8y9z](../../commit/w7x8y9z))

### ✅ Tests

- add unit tests for PayPal payment gateway ([a1b2c3d](../../commit/a1b2c3d))
- add integration tests for customer API endpoints ([e4f5g6h](../../commit/e4f5g6h))

### 🏗️ Build System

- update Laravel to version 12.1 ([i7j8k9l](../../commit/i7j8k9l))
- upgrade Tailwind CSS to version 4.2 ([m0n1o2p](../../commit/m0n1o2p))

### 👷 CI/CD

- add automated changelog generation workflow ([q3r4s5t](../../commit/q3r4s5t))
- improve test coverage reporting ([u6v7w8x](../../commit/u6v7w8x))

---

## [2.0.0] - 2026-01-20

### ⚠️ BREAKING CHANGES

- **api**: change authentication endpoint format ([y9z0a1b](../../commit/y9z0a1b)) ([#230](../../pull/230))
  
  BREAKING CHANGE: The authentication endpoint now requires API version in the URL path.
  Update your API clients from `/auth/login` to `/v1/auth/login`.

### ✨ Features

- **api**: add v1 API versioning support ([c2d3e4f](../../commit/c2d3e4f))
- **billing**: implement subscription management ([g5h6i7j](../../commit/g5h6i7j)) ([#225](../../pull/225))
- **reports**: add customizable report builder ([k8l9m0n](../../commit/k8l9m0n))

### 🐛 Bug Fixes

- **radius**: fix authentication for special characters in passwords ([o1p2q3r](../../commit/o1p2q3r)) ([#228](../../pull/228))
- **ui**: resolve responsive layout issues on mobile ([s4t5u6v](../../commit/s4t5u6v))

### 📚 Documentation

- add migration guide from v1.x to v2.0 ([w7x8y9z](../../commit/w7x8y9z))
- update README with new features ([a1b2c3d](../../commit/a1b2c3d))

---

## [1.5.1] - 2026-01-15

### 🐛 Bug Fixes

- **billing**: fix recurring payment scheduling bug ([e4f5g6h](../../commit/e4f5g6h)) ([#220](../../pull/220))
- **auth**: resolve password reset email delivery ([i7j8k9l](../../commit/i7j8k9l))
- **mikrotik**: fix API connection timeout handling ([m0n1o2p](../../commit/m0n1o2p))

### 📚 Documentation

- add troubleshooting guide for common issues ([q3r4s5t](../../commit/q3r4s5t))

---

## [1.5.0] - 2026-01-10

### ✨ Features

- **payments**: add Stripe payment gateway ([u6v7w8x](../../commit/u6v7w8x)) ([#215](../../pull/215))
- **notifications**: implement SMS notifications for due payments ([y9z0a1b](../../commit/y9z0a1b))
- **dashboard**: add customer growth chart widget ([c2d3e4f](../../commit/c2d3e4f))

### 🐛 Bug Fixes

- **reports**: fix date range filtering in financial reports ([g5h6i7j](../../commit/g5h6i7j)) ([#218](../../pull/218))
- **ui**: correct sidebar menu highlighting on nested routes ([k8l9m0n](../../commit/k8l9m0n))

### 💄 Styles

- improve mobile responsive design for customer portal ([o1p2q3r](../../commit/o1p2q3r))
- update color scheme for better accessibility ([s4t5u6v](../../commit/s4t5u6v))

### 🔧 Chores

- update dependencies to latest versions ([w7x8y9z](../../commit/w7x8y9z))
- clean up deprecated code and comments ([a1b2c3d](../../commit/a1b2c3d))

---

## Features of This Changelog

### Beautiful Formatting
- ✨ **Emoji icons** for visual categorization
- 📦 **Grouped by type** for easy navigation
- 🔗 **Linked commits** for detailed investigation
- 🔗 **Linked PRs** for full context

### Comprehensive Information
- All significant changes documented
- Breaking changes clearly marked
- Contributors automatically credited
- Version and date clearly displayed

### Developer Friendly
- Conventional commit format
- Automated generation
- Consistent structure
- Easy to maintain

### User Friendly
- Clear section headers
- Descriptive commit messages
- Links to more information
- Chronological order

---

## How It's Generated

1. **Commits** are parsed using conventional commit format
2. **Types** are categorized (feat, fix, docs, etc.)
3. **Emojis** are added for visual appeal
4. **Links** are created to commits and PRs
5. **Sections** are organized logically
6. **Format** follows Keep a Changelog standard

## Tools Used

- **conventional-changelog**: Parses commit history
- **standard-version**: Manages versioning
- **Custom script**: Adds PR information and formatting
- **GitHub Actions**: Automates on releases

---

This changelog is automatically generated using:
- [Conventional Commits](https://www.conventionalcommits.org/)
- [Keep a Changelog](https://keepachangelog.com/)
- [Semantic Versioning](https://semver.org/)
