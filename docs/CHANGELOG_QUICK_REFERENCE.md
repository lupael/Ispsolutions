# Changelog Quick Reference

Quick reference for generating and maintaining changelogs in ISP Solution.

## Commit Message Format

```
<type>(<scope>): <subject>
```

### Common Types

| Type | When to Use | Example |
|------|-------------|---------|
| `feat` | New feature | `feat(billing): add PayPal integration` |
| `fix` | Bug fix | `fix(auth): resolve session timeout` |
| `docs` | Documentation only | `docs: update API documentation` |
| `style` | Code formatting | `style: fix indentation in user model` |
| `refactor` | Code restructure | `refactor(api): simplify response handling` |
| `test` | Adding tests | `test: add unit tests for billing module` |
| `chore` | Maintenance | `chore: update dependencies` |

### Breaking Changes

```bash
# Method 1: Add ! after type
git commit -m "feat(api)!: change response format"

# Method 2: Add BREAKING CHANGE in body
git commit -m "feat(api): change response format

BREAKING CHANGE: API responses now use metadata wrapper"
```

## Generate Changelog

### Automatic (on every commit)

The changelog is automatically updated when you push to main:

```bash
# Just commit using conventional format and push
git commit -m "feat(auth): add two-factor authentication"
git push origin main

# Changelog updates automatically via GitHub Actions
```

### Using npm scripts (manual)

```bash
# Auto-update [Unreleased] section (recommended)
npm run changelog:auto

# Generate changelog for recent changes
npm run changelog

# Generate complete changelog from scratch
npm run changelog:all

# Create a new release (bump version + changelog)
npm run release

# Create specific version
npm run release:patch   # 1.0.0 -> 1.0.1
npm run release:minor   # 1.0.0 -> 1.1.0
npm run release:major   # 1.0.0 -> 2.0.0
```

### Using custom script

```bash
# Basic usage
node scripts/generate-changelog.cjs

# Between specific tags
node scripts/generate-changelog.cjs --from v1.0.0 --to v2.0.0

# Append to existing changelog
node scripts/generate-changelog.cjs --append
```

## GitHub Actions

Changelog is automatically generated when:
- A new release is created
- Manually triggered via workflow dispatch

To manually trigger:
1. Go to Actions tab
2. Select "Generate Changelog"
3. Click "Run workflow"

## Quick Examples

### Feature Commit
```bash
git commit -m "feat(billing): add recurring payment support

Implemented automatic recurring billing for subscription packages.
This allows customers to set up auto-pay for their services.

Closes #123"
```

### Bug Fix Commit
```bash
git commit -m "fix(auth): resolve session timeout issue

Fixed an issue where sessions were expiring prematurely.
Updated session configuration to use correct timeout values.

Fixes #456"
```

### Documentation Commit
```bash
git commit -m "docs: update installation guide

Added Docker installation instructions and troubleshooting section."
```

### Breaking Change Commit
```bash
git commit -m "feat(api)!: change authentication endpoint format

BREAKING CHANGE: Authentication endpoint now requires API version.
Update clients from /auth/login to /v1/auth/login."
```

## Changelog Sections

Generated changelog includes:

- ⚠️ **BREAKING CHANGES** - Breaking changes
- ✨ **Features** - New features
- 🐛 **Bug Fixes** - Bug fixes
- ⚡ **Performance** - Performance improvements
- 📚 **Documentation** - Documentation changes
- ♻️ **Refactoring** - Code refactoring
- ✅ **Tests** - Test additions/changes
- 🏗️ **Build** - Build system changes
- 👷 **CI/CD** - CI/CD changes
- 🔧 **Chores** - Maintenance tasks

## Release Process

1. Make commits following conventional format
2. Run `npm run release`
3. Review generated changelog
4. Push changes: `git push --follow-tags origin main`
5. Create GitHub release from tag

## Resources

- [Full Changelog Guide](./CHANGELOG_GUIDE.md)
- [Contributing Guide](../CONTRIBUTING.md)
- [Conventional Commits](https://www.conventionalcommits.org/)
- [Keep a Changelog](https://keepachangelog.com/)

## Tips

✅ **DO:**
- Use imperative mood ("Add feature" not "Added feature")
- Be specific and descriptive
- Reference issues with `#123`
- Include scope when relevant
- Document breaking changes clearly

❌ **DON'T:**
- Use vague messages like "fix bug" or "update code"
- Mix multiple unrelated changes in one commit
- Forget to include type prefix
- Leave out important context

---

For detailed documentation, see [docs/CHANGELOG_GUIDE.md](./CHANGELOG_GUIDE.md)
