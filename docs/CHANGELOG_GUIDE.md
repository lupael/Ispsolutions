# Changelog Generation Guide

This document describes how to generate and maintain beautiful changelogs for the ISP Solution project.

## Overview

The ISP Solution project uses an automated changelog generation system that creates structured, beautiful changelogs from commit messages and pull request information. The system is based on:

- **[Conventional Commits](https://www.conventionalcommits.org/)**: A standardized commit message format
- **[Keep a Changelog](https://keepachangelog.com/)**: A changelog format specification
- **[Semantic Versioning](https://semver.org/)**: Version numbering system

## Commit Message Format

To generate meaningful changelogs, commits should follow the Conventional Commits format:

```
<type>(<scope>): <description>

[optional body]

[optional footer]
```

### Types

The following commit types are supported:

| Type | Emoji | Description | Included in Changelog |
|------|-------|-------------|----------------------|
| `feat` or `feature` | ✨ | New feature | ✅ Yes |
| `fix` | 🐛 | Bug fix | ✅ Yes |
| `perf` | ⚡ | Performance improvement | ✅ Yes |
| `revert` | ⏪ | Revert previous change | ✅ Yes |
| `docs` | 📚 | Documentation update | ✅ Yes |
| `style` | 💄 | Code style/formatting | ✅ Yes |
| `refactor` | ♻️ | Code refactoring | ✅ Yes |
| `test` | ✅ | Test addition/modification | ✅ Yes |
| `build` | 🏗️ | Build system changes | ✅ Yes |
| `ci` | 👷 | CI/CD changes | ✅ Yes |
| `chore` | 🔧 | Other changes | ✅ Yes |

### Examples

**Feature:**
```
feat(billing): add support for recurring payments

Implemented automatic recurring billing for subscription packages.
This allows customers to set up auto-pay for their services.

Closes #123
```

**Bug Fix:**
```
fix(auth): resolve session timeout issue

Fixed an issue where user sessions were timing out prematurely.
Updated session configuration to use correct timeout values.

Fixes #456
```

**Breaking Change:**
```
feat(api)!: change authentication endpoint format

BREAKING CHANGE: The authentication endpoint now requires API version in the URL path.
Update your API clients from `/auth/login` to `/v1/auth/login`.
```

**Scope Examples:**
- `auth`: Authentication/authorization
- `billing`: Billing and invoicing
- `api`: API changes
- `ui`: User interface
- `db`: Database changes
- `docs`: Documentation
- `tests`: Testing

## Automatic Changelog Generation

### On Every Commit (Automatic)

The changelog is automatically updated on every push to the main/master branch:

```bash
# Simply commit and push your changes using conventional commits
git commit -m "feat(billing): add PayPal integration"
git push origin main
```

The GitHub Actions workflow will:
1. Detect the new commit
2. Parse conventional commit messages
3. Add entries to the [Unreleased] section
4. Commit and push the updated CHANGELOG.md back to the repository

**Note:** The auto-update skips documentation-only commits and commits with `[skip ci]` to avoid noise.

### GitHub Actions Workflow

A GitHub Actions workflow automatically generates changelog entries when:
- New commits are pushed to main/master branch
- Manually triggered via workflow dispatch

The workflow:
1. Fetches all git history
2. Identifies new commits since last tag (or all commits if no tags)
3. Parses conventional commit messages
4. Updates the [Unreleased] section in CHANGELOG.md
5. Commits and pushes changes back (with [skip ci] to avoid loops)

### Manual Trigger

You can manually trigger the changelog generation workflow from GitHub:

1. Go to the "Actions" tab in the repository
2. Select "Auto-Update Changelog" workflow
3. Click "Run workflow"
4. Click "Run workflow" button

## Manual Changelog Generation

You can also generate changelogs manually using npm scripts:

### Auto-Update Unreleased Section

```bash
npm run changelog:auto
```

This updates the [Unreleased] section with new commits since the last tag, preserving all existing changelog content.

### Generate Changelog for Recent Changes

```bash
npm run changelog
```

This generates changelog entries for commits since the last tag and appends them to CHANGELOG.md.

### Generate Complete Changelog

```bash
npm run changelog:all
```

This regenerates the entire changelog from all commits in the repository history.

**⚠️ Warning:** This will overwrite the entire changelog. Use with caution!

### Using the Custom Script

For more control, use the custom changelog generation script:

```bash
# Generate changelog from last tag to HEAD
node scripts/generate-changelog.js

# Generate changelog between specific tags
node scripts/generate-changelog.js --from v1.0.0 --to v2.0.0

# Append to existing changelog
node scripts/generate-changelog.js --append

# Specify custom output file
node scripts/generate-changelog.js --output CHANGES.md

# Use GitHub token for API access
node scripts/generate-changelog.js --github-token YOUR_TOKEN
```

## Version Releases

The project uses `standard-version` for automated version bumping and changelog generation:

### Automatic Version Bump

```bash
npm run release
```

This command:
1. Analyzes commits to determine version bump (patch, minor, or major)
2. Updates version in package.json
3. Generates changelog entry
4. Creates a git tag
5. Commits changes

### Specific Version Bumps

```bash
# Patch release (1.0.0 -> 1.0.1)
npm run release:patch

# Minor release (1.0.0 -> 1.1.0)
npm run release:minor

# Major release (1.0.0 -> 2.0.0)
npm run release:major
```

### Release Process

1. Make sure all changes are committed
2. Run the appropriate release command:
   ```bash
   npm run release
   ```
3. Review the generated changelog and version bump
4. Push the changes and tags:
   ```bash
   git push --follow-tags origin main
   ```
5. Create a GitHub release from the tag

## Best Practices

### Writing Commit Messages

1. **Use descriptive messages**: Clearly explain what changed and why
2. **Include issue/PR numbers**: Reference related issues using `#123` format
3. **Follow the format**: Use conventional commit format consistently
4. **Write in imperative mood**: "Add feature" not "Added feature"
5. **Keep subject line under 72 characters**: Be concise but meaningful

### Maintaining Changelog

1. **Review before release**: Always review auto-generated changelog before releasing
2. **Add context when needed**: Manual entries can provide additional context
3. **Document breaking changes**: Always clearly document breaking changes
4. **Group related changes**: Organize changes logically
5. **Keep it user-focused**: Write for users, not developers

### Breaking Changes

Mark breaking changes using one of these methods:

1. Add `!` after type/scope:
   ```
   feat(api)!: change response format
   ```

2. Add `BREAKING CHANGE:` in commit body:
   ```
   feat(api): change response format
   
   BREAKING CHANGE: API responses now include metadata wrapper
   ```

## Configuration

### .versionrc.json

The changelog generation is configured in `.versionrc.json`:

```json
{
  "header": "# Changelog\n\n...",
  "types": [...],
  "commitUrlFormat": "...",
  "compareUrlFormat": "...",
  "issueUrlFormat": "...",
  "prependChangelog": true
}
```

### GitHub Actions

The workflow is defined in `.github/workflows/changelog.yml`. Key configuration:

- **Trigger**: On release creation or manual dispatch
- **Permissions**: Requires write access to contents and read access to PRs
- **Node version**: 20.x
- **Git config**: Uses github-actions[bot] for commits

## Troubleshooting

### Changelog not generating

1. Check that commits follow conventional format
2. Verify GitHub token has correct permissions
3. Check workflow logs in GitHub Actions

### Missing pull request information

1. Ensure GITHUB_TOKEN has read access to pull requests
2. Check that PRs are properly linked in commit messages
3. Verify PR numbers are in correct format (#123)

### Incorrect version bump

1. Review commit messages for correct types
2. Check for breaking change indicators
3. Manually specify version with `--release-as` flag

## Examples

### Complete Release Flow

```bash
# 1. Develop features with conventional commits
git commit -m "feat(billing): add PayPal integration"
git commit -m "fix(auth): resolve token refresh issue"
git commit -m "docs: update API documentation"

# 2. Generate release
npm run release

# 3. Review generated CHANGELOG.md
cat CHANGELOG.md

# 4. Push changes and tags
git push --follow-tags origin main

# 5. Create GitHub release
gh release create v1.2.0 --generate-notes
```

### Viewing Changelog

```bash
# View latest changes
head -n 50 CHANGELOG.md

# View specific version
grep -A 30 "## \[1.2.0\]" CHANGELOG.md
```

## Resources

- [Conventional Commits Specification](https://www.conventionalcommits.org/)
- [Keep a Changelog](https://keepachangelog.com/)
- [Semantic Versioning](https://semver.org/)
- [standard-version Documentation](https://github.com/conventional-changelog/standard-version)
- [GitHub CLI Release Documentation](https://cli.github.com/manual/gh_release)

## Contributing

When contributing to this project:

1. Follow the conventional commit format
2. Include issue/PR references in commits
3. Review the generated changelog before submitting PRs
4. Update documentation if adding new commit types

---

For questions or issues with changelog generation, please open an issue on GitHub.
