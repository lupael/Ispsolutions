# Three-Tier Multi-Tenancy Architecture

This document explains the three-tier multi-tenancy architecture of the application.

## 1. Overview

The application uses a three-tier multi-tenancy architecture to support a "B2B2B" (Business-to-Business-to-Business) model. The three tiers are:

*   **Platform Owner (Developer):** The owner of the application. The Platform Owner can manage Super Admins.
*   **Super Admins (Resellers):** Customers of the Platform Owner. Super Admins can manage Admins.
*   **Admins (ISPs):** Customers of Super Admins. Admins can manage their own users and resources.

## 2. Database Schema

The database schema is designed to support multi-tenancy. The following tables are used:

*   `tenants`: Stores information about each tenant.
*   `users`: Stores information about each user. Each user belongs to a tenant and has a `parent_id` that points to the user that created them.
*   `roles`: Stores information about each role.
*   `role_user`: Links users to roles and tenants.
*   `subscriptions`: Stores information about each Super Admin's subscription.

## 3. Roles and Permissions

The application uses a role-based access control (RBAC) system to manage user permissions. The following roles are used:

*   `developer`: Has full access to the application.
*   `super-admin`: Can manage Admins and their own resources.
*   `admin`: Can manage their own users and resources.

## 4. Developer Panel

The Developer Panel is used by the Platform Owner to manage Super Admins. The Developer Panel is located at `/panel/developer`.

## 5. Super Admin Panel

The Super Admin Panel is used by Super Admins to manage Admins. The Super Admin Panel is located at `/panel/super-admin`.
