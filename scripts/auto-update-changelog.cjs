#!/usr/bin/env node

/**
 * Auto-Update Changelog Script
 * 
 * This script automatically updates the changelog by prepending new entries
 * from recent commits while preserving the existing changelog content.
 * 
 * Usage:
 *   node scripts/auto-update-changelog.cjs
 */

const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

// Configuration
const CHANGELOG_FILE = 'CHANGELOG.md';
const UNRELEASED_MARKER = '## [Unreleased]';

// Execute shell command
function exec(command) {
  try {
    return execSync(command, { encoding: 'utf8' }).trim();
  } catch (error) {
    return '';
  }
}

// Get commits from last tag or all commits
function getRecentCommits() {
  // Try to get the last tag
  const lastTag = exec('git describe --tags --abbrev=0 2>/dev/null');
  
  const range = lastTag ? `${lastTag}..HEAD` : 'HEAD';
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

    if (match) {
      type = match[1].toLowerCase();
      scope = match[2] || null;
      description = match[3];
    }

    // Skip certain commit types
    if (subject.includes('[skip ci]') || subject.includes('auto-update CHANGELOG')) {
      return;
    }

    // Extract PR number
    const prMatch = subject.match(/#(\d+)/);
    const prNumber = prMatch ? prMatch[1] : null;

    commits.push({
      hash,
      shortHash: hash.substring(0, 7),
      author,
      timestamp: parseInt(timestamp),
      subject,
      type,
      scope,
      description,
      prNumber
    });
  });

  return commits;
}

// Group commits by type
function groupCommits(commits) {
  const groups = {};

  commits.forEach(commit => {
    const type = commit.type;
    if (!groups[type]) {
      groups[type] = [];
    }
    groups[type].push(commit);
  });

  return groups;
}

// Format commit for changelog
function formatCommit(commit) {
  const scope = commit.scope ? `**${commit.scope}**: ` : '';
  const pr = commit.prNumber ? ` ([#${commit.prNumber}](../../pull/${commit.prNumber}))` : '';
  const hash = `([${commit.shortHash}](../../commit/${commit.hash}))`;
  return `- ${scope}${commit.description}${pr} ${hash}`;
}

// Generate unreleased section
function generateUnreleasedSection(commits) {
  if (commits.length === 0) return '';

  const groups = groupCommits(commits);
  const dateStr = new Date().toISOString().split('T')[0];
  
  let section = `${UNRELEASED_MARKER} - ${dateStr}\n\n`;

  const typeConfig = {
    feat: { emoji: '✨', section: 'Features', order: 1 },
    feature: { emoji: '✨', section: 'Features', order: 1 },
    fix: { emoji: '🐛', section: 'Bug Fixes', order: 2 },
    perf: { emoji: '⚡', section: 'Performance Improvements', order: 3 },
    revert: { emoji: '⏪', section: 'Reverts', order: 4 },
    docs: { emoji: '📚', section: 'Documentation', order: 5 },
    style: { emoji: '💄', section: 'Styles', order: 6 },
    refactor: { emoji: '♻️', section: 'Code Refactoring', order: 7 },
    test: { emoji: '✅', section: 'Tests', order: 8 },
    build: { emoji: '🏗️', section: 'Build System', order: 9 },
    ci: { emoji: '👷', section: 'CI/CD', order: 10 },
    chore: { emoji: '🔧', section: 'Chores', order: 11 }
  };

  const orderedTypes = Object.keys(typeConfig).sort((a, b) => {
    return (typeConfig[a]?.order || 999) - (typeConfig[b]?.order || 999);
  });
  
  orderedTypes.forEach(type => {
    if (groups[type] && groups[type].length > 0) {
      const config = typeConfig[type];
      if (config) {
        section += `### ${config.emoji} ${config.section}\n\n`;
        groups[type].forEach(commit => {
          section += `${formatCommit(commit)}\n`;
        });
        section += '\n';
      }
    }
  });

  return section + '---\n\n';
}

// Update changelog file
function updateChangelog() {
  console.log('🚀 Auto-updating changelog...\n');

  const changelogPath = path.resolve(process.cwd(), CHANGELOG_FILE);
  
  // Get recent commits
  const commits = getRecentCommits();
  console.log(`Found ${commits.length} new commits\n`);

  if (commits.length === 0) {
    console.log('✅ No new commits to add to changelog');
    return;
  }

  // Generate unreleased section
  const unreleasedSection = generateUnreleasedSection(commits);

  // Read existing changelog
  let existingContent = '';
  if (fs.existsSync(changelogPath)) {
    existingContent = fs.readFileSync(changelogPath, 'utf8');
  }

  // Remove existing [Unreleased] section if it exists
  const unreleasedRegex = /## \[Unreleased\].*?\n\n(.*?)\n---\n\n/s;
  existingContent = existingContent.replace(unreleasedRegex, '');

  // Find where to insert (after header, before first version)
  const firstVersionMatch = existingContent.match(/\n## \[[\d.]+\]/);
  let newContent;

  if (firstVersionMatch) {
    const insertPos = firstVersionMatch.index;
    newContent = 
      existingContent.substring(0, insertPos + 1) + 
      unreleasedSection + 
      existingContent.substring(insertPos + 1);
  } else {
    // No versions yet, add after header
    const headerEnd = existingContent.indexOf('---\n\n');
    if (headerEnd > 0) {
      newContent = 
        existingContent.substring(0, headerEnd + 5) + 
        unreleasedSection + 
        existingContent.substring(headerEnd + 5);
    } else {
      // No header, just prepend
      newContent = unreleasedSection + existingContent;
    }
  }

  // Write updated changelog
  fs.writeFileSync(changelogPath, newContent, 'utf8');
  
  console.log('✅ Changelog updated successfully!');
  console.log(`📝 Added ${commits.length} commits to [Unreleased] section`);
  console.log(`📁 File: ${changelogPath}`);
}

// Run
try {
  updateChangelog();
} catch (error) {
  console.error('❌ Error updating changelog:', error.message);
  process.exit(1);
}
