# Gemini Development Guide for ISP Solution

This document provides guidance for AI developers working on the ISP Solution project. All development must adhere to the architecture and principles outlined in `1. Mikrotik_Radius_architecture .md`.

## Core Architecture Principles

- **Framework**: Laravel (PHP)
- **Authentication**: FreeRADIUS for AAA (Authentication, Authorization, Accounting).
- **Network Integration**: MikroTik routers via RouterOS API.
- **Database**: Dual MySQL databases (one for the application, one for FreeRADIUS).
- **Architecture**: Service-oriented architecture. Core logic is in `app/Services`.
- **Multi-Tenancy**: The application supports multiple tenants.

## Development Guidelines

1.  **Read the Architecture Document**: Before making any changes, review `1. Mikrotik_Radius_architecture .md` to understand the system design.
2.  **Follow the TODO List**: The development progress is tracked in `TODO.md`. Follow the tasks in this file.
3.  **Service-Oriented Approach**: For new business logic, create or use services in `app/Services`.
4.  **Existing Conventions**: Adhere to the existing coding style, naming conventions, and architectural patterns.
5.  **Testing**: Write tests for new features and bug fixes.

By following these guidelines, we can ensure consistent and high-quality development.