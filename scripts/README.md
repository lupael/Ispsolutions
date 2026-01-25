# Scripts Directory

This directory contains utility scripts for the ISP Solution project.

## Available Scripts

### auto-update-changelog.cjs

**Primary script for automatic changelog updates.** This script automatically updates the [Unreleased] section of the changelog with new commits while preserving existing content. Used by the GitHub Actions workflow.

**Features:**
- Parses conventional commit messages from recent commits
- Groups commits by type with emoji icons
- Extracts PR numbers from commit messages
- Preserves existing changelog content
- Only updates the [Unreleased] section
- Skips documentation-only commits and [skip ci] commits
- Works with or without git tags

**Usage:**

```bash
# Auto-update the changelog (recommended)
node scripts/auto-update-changelog.cjs

# Or use the npm script
npm run changelog:auto
```

**How it works:**
1. Finds commits since the last git tag (or all commits if no tags)
2. Parses conventional commit format (type, scope, description)
3. Groups commits by type
4. Removes existing [Unreleased] section if present
5. Prepends new [Unreleased] section with recent commits
6. Preserves all other changelog content

**Output:**
Updates CHANGELOG.md with an [Unreleased] section at the top containing recent changes.

**When to use:**
- Automatically via GitHub Actions on every push
- Manually when you want to update the changelog locally
- Before creating a pull request to show your changes

### generate-changelog.cjs

A Node.js script for generating beautiful changelogs from Git commit history and pull request information.

**Features:**
- Parses conventional commit messages
- Groups commits by type with emoji icons
- Extracts PR numbers from commit messages
- Links to commits and PRs in GitHub
- Supports custom version ranges
- Can append to existing changelog

**Usage:**

```bash
# Generate changelog from last tag to HEAD (default)
node scripts/generate-changelog.cjs

# Generate changelog between specific tags
node scripts/generate-changelog.cjs --from v1.0.0 --to v2.0.0

# Append to existing changelog
node scripts/generate-changelog.cjs --append

# Specify custom output file
node scripts/generate-changelog.cjs --output CHANGES.md

# Use GitHub token for API access (optional)
node scripts/generate-changelog.cjs --github-token YOUR_TOKEN
```

**Options:**
- `--from <tag>`: Start tag (default: previous tag)
- `--to <tag>`: End tag (default: HEAD)
- `--output <file>`: Output file (default: CHANGELOG.md)
- `--append`: Append to existing changelog instead of overwriting
- `--github-token`: GitHub token for API access (default: GITHUB_TOKEN env var)

**Examples:**

```bash
# Generate changelog for unreleased changes
node scripts/generate-changelog.cjs --append

# Generate complete changelog from beginning
node scripts/generate-changelog.cjs --from ""

# Generate for specific release
node scripts/generate-changelog.cjs --from v1.0.0 --to v1.1.0 --append
```

**Output Format:**

The script generates a changelog in Keep a Changelog format with sections:
- ⚠️ BREAKING CHANGES
- ✨ Features
- 🐛 Bug Fixes
- ⚡ Performance Improvements
- ⏪ Reverts
- 📚 Documentation
- 💄 Styles
- ♻️ Code Refactoring
- ✅ Tests
- 🏗️ Build System
- 👷 CI/CD
- 🔧 Chores

Each commit includes links to the commit and pull request (if applicable).

## Adding New Scripts

When adding new utility scripts:

1. Use `.cjs` extension for CommonJS scripts (since package.json uses ES modules)
2. Add execute permissions: `chmod +x scripts/your-script.cjs`
3. Include a header comment explaining the script's purpose
4. Document the script in this README
5. Add appropriate error handling
6. Test the script before committing

## Related Documentation

- [Changelog Guide](../docs/CHANGELOG_GUIDE.md) - Complete guide for changelog generation
- [Contributing Guide](../CONTRIBUTING.md) - How to contribute to the project
- [README](../README.md) - Main project documentation
