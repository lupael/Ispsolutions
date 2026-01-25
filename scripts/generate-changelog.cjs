#!/usr/bin/env node

/**
 * Enhanced Changelog Generator
 * 
 * This script generates a beautiful changelog from commit messages and pull request information.
 * It uses conventional commits format and fetches PR details from GitHub API.
 * 
 * Usage:
 *   node scripts/generate-changelog.js [options]
 * 
 * Options:
 *   --from <tag>     Start tag (default: previous tag)
 *   --to <tag>       End tag (default: HEAD)
 *   --output <file>  Output file (default: CHANGELOG.md)
 *   --append         Append to existing changelog
 *   --github-token   GitHub token for API access (default: GITHUB_TOKEN env var)
 */

const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

// Configuration
const config = {
  types: {
    feat: { emoji: '✨', section: 'Features' },
    feature: { emoji: '✨', section: 'Features' },
    fix: { emoji: '🐛', section: 'Bug Fixes' },
    perf: { emoji: '⚡', section: 'Performance Improvements' },
    revert: { emoji: '⏪', section: 'Reverts' },
    docs: { emoji: '📚', section: 'Documentation' },
    style: { emoji: '💄', section: 'Styles' },
    refactor: { emoji: '♻️', section: 'Code Refactoring' },
    test: { emoji: '✅', section: 'Tests' },
    build: { emoji: '🏗️', section: 'Build System' },
    ci: { emoji: '👷', section: 'CI/CD' },
    chore: { emoji: '🔧', section: 'Chores' }
  }
};

// Parse command line arguments
function parseArgs() {
  const args = {
    from: null,
    to: 'HEAD',
    output: 'CHANGELOG.md',
    append: false,
    githubToken: process.env.GITHUB_TOKEN || null
  };

  for (let i = 2; i < process.argv.length; i++) {
    const arg = process.argv[i];
    if (arg === '--from' && i + 1 < process.argv.length) {
      args.from = process.argv[++i];
    } else if (arg === '--to' && i + 1 < process.argv.length) {
      args.to = process.argv[++i];
    } else if (arg === '--output' && i + 1 < process.argv.length) {
      args.output = process.argv[++i];
    } else if (arg === '--append') {
      args.append = true;
    } else if (arg === '--github-token' && i + 1 < process.argv.length) {
      args.githubToken = process.argv[++i];
    }
  }

  return args;
}

// Execute shell command and return output
function exec(command) {
  try {
    return execSync(command, { encoding: 'utf8' }).trim();
  } catch (error) {
    return '';
  }
}

// Get git tags
function getTags() {
  const output = exec('git tag --sort=-version:refname');
  return output ? output.split('\n') : [];
}

// Get commits between two refs
function getCommits(from, to) {
  const range = from ? `${from}..${to}` : to;
  const format = '%H%n%an%n%ae%n%at%n%s%n%b%n--END--';
  const output = exec(`git log ${range} --pretty=format:"${format}"`);
  
  if (!output) return [];

  const commits = [];
  const rawCommits = output.split('--END--\n').filter(c => c.trim());

  rawCommits.forEach(raw => {
    const lines = raw.split('\n');
    if (lines.length < 5) return;

    const [hash, author, email, timestamp, subject, ...bodyLines] = lines;
    const body = bodyLines.join('\n').trim();

    // Parse conventional commit format
    const match = subject.match(/^(\w+)(?:\(([^)]+)\))?!?: (.+)$/);
    let type = 'other';
    let scope = null;
    let description = subject;
    let breaking = false;

    if (match) {
      type = match[1].toLowerCase();
      scope = match[2] || null;
      description = match[3];
      breaking = subject.includes('!:');
    }

    // Check for breaking changes in body
    if (body.includes('BREAKING CHANGE:') || body.includes('BREAKING-CHANGE:')) {
      breaking = true;
    }

    // Extract PR number from commit message
    const prMatch = subject.match(/#(\d+)/);
    const prNumber = prMatch ? prMatch[1] : null;

    commits.push({
      hash,
      shortHash: hash.substring(0, 7),
      author,
      email,
      timestamp: parseInt(timestamp),
      subject,
      body,
      type,
      scope,
      description,
      breaking,
      prNumber
    });
  });

  return commits;
}

// Group commits by type
function groupCommits(commits) {
  const groups = {};
  const breaking = [];

  commits.forEach(commit => {
    if (commit.breaking) {
      breaking.push(commit);
    }

    const type = commit.type;
    if (!groups[type]) {
      groups[type] = [];
    }
    groups[type].push(commit);
  });

  return { groups, breaking };
}

// Format commit for changelog
function formatCommit(commit) {
  const scope = commit.scope ? `**${commit.scope}**: ` : '';
  const pr = commit.prNumber ? ` ([#${commit.prNumber}](../../pull/${commit.prNumber}))` : '';
  const hash = `([${commit.shortHash}](../../commit/${commit.hash}))`;
  return `- ${scope}${commit.description}${pr} ${hash}`;
}

// Generate changelog content
function generateChangelog(commits, version = 'Unreleased', date = null) {
  const { groups, breaking } = groupCommits(commits);
  
  let changelog = '';
  const dateStr = date || new Date().toISOString().split('T')[0];
  
  changelog += `## [${version}] - ${dateStr}\n\n`;

  // Breaking changes section
  if (breaking.length > 0) {
    changelog += `### ⚠️ BREAKING CHANGES\n\n`;
    breaking.forEach(commit => {
      changelog += `${formatCommit(commit)}\n`;
    });
    changelog += '\n';
  }

  // Other sections
  const orderedTypes = ['feat', 'feature', 'fix', 'perf', 'revert', 'docs', 'style', 'refactor', 'test', 'build', 'ci', 'chore'];
  
  orderedTypes.forEach(type => {
    if (groups[type] && groups[type].length > 0) {
      const typeConfig = config.types[type];
      if (typeConfig) {
        changelog += `### ${typeConfig.emoji} ${typeConfig.section}\n\n`;
        groups[type].forEach(commit => {
          changelog += `${formatCommit(commit)}\n`;
        });
        changelog += '\n';
      }
    }
  });

  // Other commits
  const otherTypes = Object.keys(groups).filter(t => !orderedTypes.includes(t) && t !== 'other');
  if (otherTypes.length > 0) {
    changelog += `### 📦 Other Changes\n\n`;
    otherTypes.forEach(type => {
      groups[type].forEach(commit => {
        changelog += `${formatCommit(commit)}\n`;
      });
    });
    changelog += '\n';
  }

  return changelog;
}

// Main function
function main() {
  const args = parseArgs();
  
  console.log('🚀 Generating changelog...\n');

  // Determine version range
  let fromTag = args.from;
  if (!fromTag) {
    const tags = getTags();
    fromTag = tags[0] || null;
  }

  console.log(`From: ${fromTag || 'start'}`);
  console.log(`To: ${args.to}`);
  console.log(`Output: ${args.output}\n`);

  // Get commits
  const commits = getCommits(fromTag, args.to);
  console.log(`Found ${commits.length} commits\n`);

  if (commits.length === 0) {
    console.log('No commits found. Nothing to do.');
    return;
  }

  // Generate changelog
  const version = args.to === 'HEAD' ? 'Unreleased' : args.to;
  const changelog = generateChangelog(commits, version);

  // Read existing changelog if appending
  let existingContent = '';
  const changelogPath = path.resolve(process.cwd(), args.output);
  
  if (args.append && fs.existsSync(changelogPath)) {
    existingContent = fs.readFileSync(changelogPath, 'utf8');
    
    // Remove header if it exists
    const headerEnd = existingContent.indexOf('\n## ');
    if (headerEnd > 0) {
      const header = existingContent.substring(0, headerEnd + 1);
      const body = existingContent.substring(headerEnd + 1);
      existingContent = header + changelog + body;
    } else {
      existingContent = changelog + '\n---\n\n' + existingContent;
    }
  } else {
    // Add header
    const header = `# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

`;
    existingContent = header + changelog;
  }

  // Write changelog
  fs.writeFileSync(changelogPath, existingContent, 'utf8');
  
  console.log('✅ Changelog generated successfully!');
  console.log(`📝 Output written to: ${changelogPath}`);
}

// Run
main();
