# Network Services Re-implementation - Phase-by-Phase Checklist

## Overview

This document outlines the complete implementation plan for re-implementing billing and network monitoring features for the ISP project using Laravel 12, PHP 8.2+, Tailwind CSS, and Vite.

## Goal

Re-implement the billing and network monitoring features from scratch with:
- Clean, modular Laravel 12 application structure
- Three primary network service domains: RADIUS, MikroTik, and IPAM
- Service contracts and concrete implementations
- Docker Compose development environment
- REST API and artisan commands
- Comprehensive unit and integration tests
- GitHub Actions CI/CD pipeline

## Tech Stack

- **Laravel**: 12.x (Latest)
- **PHP**: 8.2+
- **Database**: MySQL 8.0+ (app + radius databases)
- **Redis**: Latest (caching and queues)
- **Tailwind CSS**: 4.x (already installed)
- **Vite**: 7.x (already installed)
- **Docker**: Latest with Docker Compose
- **Testing**: PHPUnit 11.x
- **Static Analysis**: PHPStan
- **Code Style**: PHP-CS-Fixer (Laravel Pint)

---

## Phase 0 — Repository Analysis & Branch Setup

**Goal**: Understand existing codebase and prepare branch

- [x] Inspect repository structure and demo1 assets
  - Location: `/resources/views/layouts/demo1/` and `/resources/views/pages/demo1/`
  - Base layout: `base.blade.php` with sidebar, header, footer
  - Tailwind CSS 4.x and Vite 7.x already configured
- [x] Verify Laravel 12, PHP 8.2+ setup
  - `composer.json` confirms Laravel ^12.0 and PHP ^8.2
- [x] Branch already exists: `copilot/reimplement-billing-network-monitoring`
- [x] Create this TODO_REIMPLEMENT.md documentation

**Commit**: `docs: create comprehensive TODO_REIMPLEMENT.md`

---

## Phase 1 — Scaffold & Tooling

**Goal**: Setup development environment, Docker, and tooling

### 1.1 Update Composer Dependencies
- [ ] Add PHPStan for static analysis
- [ ] Verify Laravel Pint is available (already included)
- [ ] Add MikroTik RouterOS API client package

### 1.2 Docker Environment
- [ ] Create `Dockerfile` for PHP 8.2 application container
- [ ] Create `docker-compose.yml` with services (app, db, radius-db, redis, mailpit)
- [ ] Create `.dockerignore` file

### 1.3 Makefile
- [ ] Create `Makefile` with common commands (up, down, shell, test, migrate, seed, lint, install, build)

### 1.4 Environment Configuration
- [ ] Update `.env.example` with RADIUS_*, MIKROTIK_*, IPAM_* configuration

### 1.5 README Updates
- [ ] Update `README.md` with Docker setup and network services documentation

**Commit**: `chore(scaffold): add Docker, Makefile, and environment setup`

---

## Phase 2 — Core Models & Migrations

**Goal**: Create database schema and Eloquent models for network services

### 2.1 ServicePackage Model
- [ ] Create migration and model for service packages

### 2.2 IP Management Models
- [ ] Create IpPool model and migration
- [ ] Create IpSubnet model and migration
- [ ] Create IpAllocation model and migration
- [ ] Create IpAllocationHistory model and migration

### 2.3 RADIUS Models
- [ ] Create RadiusSession model and migration (local app DB)
- [ ] Create RADIUS database migrations (radcheck, radreply, radacct)

### 2.4 User Model Enhancement
- [ ] Update User model with relations to service packages and allocations

### 2.5 Factories and Seeders
- [ ] Create factories for all models
- [ ] Create seeders for sample data

**Commit**: `feat(db): add core models, migrations, factories, and seeders`

---

## Phase 3 — Contracts & ServiceProvider

**Goal**: Define service interfaces and dependency injection bindings

### 3.1 Service Contracts
- [ ] Create `app/Contracts/IpamServiceInterface.php`
- [ ] Create `app/Contracts/RadiusServiceInterface.php`
- [ ] Create `app/Contracts/MikroTikServiceInterface.php`

### 3.2 Service Provider
- [ ] Create `app/Providers/NetworkServiceProvider.php`

### 3.3 Registration
- [ ] Add `NetworkServiceProvider` to configuration

**Commit**: `chore: add service contracts and NetworkServiceProvider`

---

## Phase 4 — IPAM Implementation

**Goal**: Implement IP address management with transaction safety

### 4.1 Configuration
- [ ] Create `config/ipam.php`

### 4.2 Service Implementation
- [ ] Create `app/Services/IpamService.php` with all interface methods
- [ ] Create helper classes for IP calculations

### 4.3 Unit Tests
- [ ] Create comprehensive unit tests for IPAM service

### 4.4 Integration Tests
- [ ] Create integration tests with real database

**Commit**: `feat(ipam): implement IPAM service with tests`

---

## Phase 5 — RADIUS Implementation

**Goal**: Implement RADIUS authentication and accounting

### 5.1 Configuration
- [ ] Create `config/radius.php`
- [ ] Update `config/database.php` with radius connection

### 5.2 Service Implementation
- [ ] Create `app/Services/RadiusService.php`
- [ ] Create RADIUS models for radcheck, radreply, radacct

### 5.3 Unit Tests
- [ ] Create unit tests for authentication and accounting

### 5.4 Integration Tests
- [ ] Create integration tests with radius-db container

**Commit**: `feat(radius): implement RADIUS service with authentication and accounting`

---

## Phase 6 — MikroTik Implementation

**Goal**: Implement MikroTik RouterOS API client for PPPoE management

### 6.1 Configuration
- [ ] Create `config/mikrotik.php`

### 6.2 Service Implementation
- [ ] Create `app/Services/MikroTikService.php` with RouterOS API client
- [ ] Add retry logic and error handling

### 6.3 Exception Classes
- [ ] Create custom exception classes for MikroTik errors

### 6.4 Unit Tests
- [ ] Create unit tests with mocked API client

### 6.5 Integration Tests
- [ ] Create integration tests (mark as skipped if no test container)

**Commit**: `feat(mikrotik): implement MikroTik RouterOS API client`

---

## Phase 7 — API, Commands & Scheduler

**Goal**: Expose REST API and create CLI commands

### 7.1 API Routes
- [ ] Create API routes for IPAM, RADIUS, and MikroTik operations

### 7.2 API Controllers
- [ ] Create IpamController with all endpoints
- [ ] Create RadiusController with authentication and accounting
- [ ] Create MikroTikController with session management

### 7.3 API Requests
- [ ] Create form request classes for validation

### 7.4 Artisan Commands
- [ ] Create `ipam:cleanup` command
- [ ] Create `radius:sync-user` command
- [ ] Create `mikrotik:health-check` command

### 7.5 Scheduler
- [ ] Register scheduled tasks in Kernel

### 7.6 API Tests
- [ ] Create feature tests for all API endpoints

**Commit**: `feat(api): add REST API endpoints and artisan commands`

---

## Phase 8 — UI & Styling

**Goal**: Create admin interface based on demo1 layout

### 8.1 Layouts
- [ ] Create admin base layout extending demo1

### 8.2 IPAM Views
- [ ] Create pool management views
- [ ] Create subnet management views
- [ ] Create allocation management views

### 8.3 Session Monitoring Views
- [ ] Create active sessions view
- [ ] Create session history view

### 8.4 Network Users Views
- [ ] Create user list with network information
- [ ] Create user detail view with session history

### 8.5 Web Controllers
- [ ] Create web controllers for admin interface

### 8.6 Web Routes
- [ ] Add web routes for admin interface

### 8.7 Vite Assets
- [ ] Build and test assets

**Commit**: `feat(ui): add admin interface for IPAM and session monitoring`

---

## Phase 9 — CI, Documentation & Polish

**Goal**: Setup CI/CD, static analysis, and documentation

### 9.1 PHPStan Configuration
- [ ] Create `phpstan.neon`

### 9.2 PHP-CS-Fixer / Laravel Pint
- [ ] Create or verify `pint.json`

### 9.3 GitHub Actions Workflows
- [ ] Create `.github/workflows/test.yml`
- [ ] Create `.github/workflows/lint.yml`
- [ ] Create `.github/workflows/integration.yml`

### 9.4 Documentation
- [ ] Create `docs/NETWORK_SERVICES.md`
- [ ] Create `docs/API.md`
- [ ] Update README or create `docs/DEVELOPMENT.md`

### 9.5 Code Quality
- [ ] Run PHPStan and fix issues
- [ ] Run Laravel Pint and fix style issues
- [ ] Run all tests and ensure pass rate

### 9.6 Git Ignore
- [ ] Update `.gitignore` for Docker and test artifacts

**Commit**: `ci(workflows): add GitHub Actions for tests, lint, and integration`
**Commit**: `docs: add comprehensive network services documentation`

---

## Phase 10 — Final Review & Security

**Goal**: Code review, security scan, and final polish

### 10.1 Code Review
- [ ] Run code review tool
- [ ] Address all critical comments
- [ ] Review service implementations

### 10.2 Security Scan (CodeQL)
- [ ] Run CodeQL security scanner
- [ ] Fix all identified vulnerabilities
- [ ] Re-run to verify fixes

### 10.3 Final Validation
- [ ] Run full test suite with coverage
- [ ] Test Docker setup locally
- [ ] Test all API endpoints
- [ ] Test UI in browser

### 10.4 PR Preparation
- [ ] Write comprehensive PR description
- [ ] Add checklist to PR
- [ ] Request reviewers

### 10.5 Security Summary
- [ ] Document any discovered vulnerabilities
- [ ] Confirm all are fixed

**Commit**: `chore: final code review fixes and security improvements`

---

## Release (Post-Merge)

### Tag and Release Notes
- [ ] Create tag `v1.0.0`
- [ ] Create GitHub release with detailed notes

---

## Acceptance Criteria Checklist

### Functional Requirements

#### RADIUS
- [ ] `authenticate(username, password)` validates credentials
- [ ] Accounting start/update/stop persist sessions
- [ ] `syncUser(User $user)` writes to RADIUS DB
- [ ] Unit tests for authentication
- [ ] Unit tests for accounting lifecycle

#### MikroTik
- [ ] Client connect/disconnect with retry
- [ ] Add/update/remove PPPoE users
- [ ] List active sessions
- [ ] Disconnect individual sessions
- [ ] Integration tests

#### IPAM
- [ ] Create pools and subnets
- [ ] Allocate and release IPs
- [ ] Detect subnet overlap
- [ ] Track allocation history
- [ ] Transaction-safe allocation
- [ ] Cleanup expired allocations

### Infrastructure & DevOps
- [ ] Docker Compose (app, db, radius-db, redis)
- [ ] Dockerfile
- [ ] Makefile
- [ ] `.env.example` complete
- [ ] GitHub Actions (test, lint, integration)
- [ ] All CI jobs pass

### Code Quality
- [ ] PHPStan level 6 passes
- [ ] Laravel Pint passes
- [ ] PHPUnit tests pass (100%)
- [ ] Service contracts follow SOLID
- [ ] Dependency injection throughout
- [ ] Typed properties

### Documentation
- [ ] `docs/TODO_REIMPLEMENT.md` (this file)
- [ ] `docs/NETWORK_SERVICES.md`
- [ ] `docs/API.md`
- [ ] Updated `README.md`
- [ ] Inline comments where needed

### Testing
- [ ] Unit tests for IPAM
- [ ] Unit tests for RADIUS
- [ ] Unit tests for MikroTik
- [ ] Integration tests for IPAM
- [ ] Integration tests for RADIUS
- [ ] Integration tests for MikroTik
- [ ] API tests for all endpoints

---

## Progress Tracking

**Last Updated**: January 16, 2026
**Current Phase**: Phase 0 (Documentation)
**Overall Progress**: 5%
