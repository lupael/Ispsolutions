# ISP Solution - Documentation Index

Welcome to the ISP Solution documentation! This index provides quick access to all documentation organized by category.

## 📖 Quick Start

- **[Installation Script](../install.sh)** ⭐ - Complete Ubuntu installation script (A to Z setup)
- **[README](../README.md)** - Project overview and quick start
- **[CHANGELOG](../CHANGELOG.md)** - Version history and recent changes
- **[TODO](../TODO.md)** - Current development roadmap

## 👥 User Guides (By Role)

Comprehensive guides for each user role in the system:

### Management Level
- **[Developer Guide](guides/DEVELOPER_GUIDE.md)** ⭐ Level 0 - Supreme authority, system development
- **[Super Admin Guide](guides/SUPERADMIN_GUIDE.md)** ⭐ Level 10 - Tenant management
- **[Admin Guide](guides/ADMIN_GUIDE.md)** ⭐ Level 20 - ISP owner operations

### Operational Level
- **[Operator Guide](guides/OPERATOR_GUIDE.md)** 👷 Level 30 - Area/zone management
- **[Sub-Operator Guide](guides/SUBOPERATOR_GUIDE.md)** 👷 Level 40 - Local customer management

### Support Level
- **[Manager Guide](guides/MANAGER_GUIDE.md)** 📊 Level 50 - Oversight and reporting
- **[Staff Guide](guides/STAFF_GUIDE.md)** 📝 Level 80 - Administrative support

### Customer Level
- **[Customer Guide](guides/CUSTOMER_GUIDE.md)** 👤 Level 100 - End user self-service

## 🔧 Technical Documentation

### Core Systems
- **[Role System](technical/ROLE_SYSTEM.md)** - Complete role hierarchy and permissions (v3.1)
- **[Data Isolation](technical/DATA_ISOLATION.md)** - Multi-tenancy and data security
- **[Multi-Tenancy Architecture](technical/MULTI_TENANCY_ISOLATION.md)** - Tenant isolation overview
- **[Security Enhancements](technical/PHASE_6_SECURITY_ENHANCEMENTS.md)** - Security features
- **[Roles & Permissions](ROLES_AND_PERMISSIONS.md)** - Detailed permissions guide

### Network Services
- **[Network Services Guide](NETWORK_SERVICES.md)** - RADIUS, MikroTik, IPAM integration
- **[OLT Service Guide](OLT_SERVICE_GUIDE.md)** - OLT/ONU management
- **[OLT API Reference](OLT_API_REFERENCE.md)** - OLT API documentation
- **[Monitoring System](MONITORING_SYSTEM.md)** - Network monitoring

### MikroTik Integration
- **[MikroTik Quick Start](../MIKROTIK_QUICKSTART.md)** - Getting started with MikroTik
- **[MikroTik Advanced Features](../MIKROTIK_ADVANCED_FEATURES.md)** - Advanced configurations

### Database & Optimization
- **[Database Optimization](DATABASE_OPTIMIZATION.md)** - Performance tuning

## 🚀 Development

### Getting Started
- **[Developer Guide](guides/DEVELOPER_GUIDE.md)** - Complete development setup
- **[Developer Guide (Original)](developer-guide.md)** - Alternative developer guide
- **[Development TODO](TODO_REIMPLEMENT.md)** - Phase-by-phase implementation checklist

### API Documentation
- **[API Documentation](API.md)** - Complete REST API reference

### Testing & Quality
- **[Testing Guide](TESTING.md)** - How to run and write tests
- **[Deployment Guide](DEPLOYMENT.md)** - Production deployment instructions

## 🎯 Feature Specifications

### Core Features
- **[TODO Features A-Z](../TODO_FEATURES_A2Z.md)** - Complete feature list and specifications
- **[Feature Requests](../Feature.md)** - Requested features
- **[Implementation Status](IMPLEMENTATION_STATUS.md)** - Current development status (Feature tracking)
- **[Network Services Status](../IMPLEMENTATION_STATUS.md)** - Network services implementation
- **[Implementation TODO](../IMPLEMENTATION_TODO.md)** - Implementation tasks

### Panel & UI
- **[Panel Specifications](../PANELS_SPECIFICATION.md)** - Detailed panel documentation
- **[Panel Implementation Guide](../PANEL_IMPLEMENTATION_GUIDE.md)** - Panel development guide
- **[Panel README](../PANEL_README.md)** - Panel overview
- **[Role-Based Menu](ROLE_BASED_MENU.md)** - Dynamic menu system

### Specialized Features
- **[Payment Gateway Guide](../PAYMENT_GATEWAY_GUIDE.md)** - Payment integration
- **[Hotspot Self-Signup Guide](../HOTSPOT_SELF_SIGNUP_GUIDE.md)** - Self-service signup
- **[Analytics Dashboard Guide](../ANALYTICS_DASHBOARD_GUIDE.md)** - Analytics features
- **[Form Validation](../FORM_VALIDATION_DOCUMENTATION.md)** - Form validation system
- **[PDF Templates](../PDF_TEMPLATES_MANIFEST.md)** - PDF generation system

## 📋 Guides & References

### Quick Reference Guides
- **[Routing Troubleshooting](../ROUTING_TROUBLESHOOTING_GUIDE.md)** - Diagnose and fix routing issues
- **[Pagination Fix](../PAGINATION_FIX_DOCUMENTATION.md)** - Pagination implementation guide

### Specialized Guides
- **[User Guides](USER_GUIDES.md)** - Consolidated user documentation
- **[Tenancy Guide](tenancy.md)** - Multi-tenancy implementation
- **[Payment Gateway Integration](PAYMENT_GATEWAY_INTEGRATION.md)** - Payment setup

## 🛠️ Setup & Deployment

### Installation
- **[Installation Script](../install.sh)** ⭐ - Complete automated Ubuntu installation
- **[Deployment Guide](DEPLOYMENT.md)** - Production deployment
- **[Docker Setup](../docker-compose.yml)** - Containerized environment
- **[Makefile](../Makefile)** - Development commands

### Configuration
- **[Environment Example](../.env.example)** - Configuration template
- **[Deployment Audit](technical/DEPLOYMENT_AUDIT_REPORT.md)** - Deployment checklist

## 📚 Additional Resources

### Archived Documentation
- **Historical Implementation Reports** - See `docs/archived/` folder
- **Deprecated Files** - Listed in [DEPRECATED.md](../DEPRECATED.md)

### External Links
- **[Laravel 12 Documentation](https://laravel.com/docs/12.x)**
- **[Tailwind CSS](https://tailwindcss.com/docs)**
- **[FreeRADIUS](https://freeradius.org/documentation/)**
- **[MikroTik Wiki](https://wiki.mikrotik.com/)**

## 🗂️ Documentation Categories

### By User Role
- **Developers** → [Developer Guide](guides/DEVELOPER_GUIDE.md), [API Docs](API.md), [Testing](TESTING.md)
- **Super Admins** → [Super Admin Guide](guides/SUPERADMIN_GUIDE.md), [Tenant Management](technical/MULTI_TENANCY_ISOLATION.md)
- **Admins** → [Admin Guide](guides/ADMIN_GUIDE.md), [Panel Specs](../PANELS_SPECIFICATION.md)
- **Operators** → [Operator Guide](guides/OPERATOR_GUIDE.md), [Network Services](NETWORK_SERVICES.md)
- **Sub-Operators** → [Sub-Operator Guide](guides/SUBOPERATOR_GUIDE.md)
- **Managers/Staff** → [Manager Guide](guides/MANAGER_GUIDE.md), [Staff Guide](guides/STAFF_GUIDE.md)
- **Customers** → [Customer Guide](guides/CUSTOMER_GUIDE.md)

### By Topic
- **Network** → [Network Services](NETWORK_SERVICES.md), [MikroTik](../MIKROTIK_QUICKSTART.md), [OLT](OLT_SERVICE_GUIDE.md)
- **Security** → [Data Isolation](technical/DATA_ISOLATION.md), [Security Enhancements](technical/PHASE_6_SECURITY_ENHANCEMENTS.md)
- **Development** → [Developer Guide](guides/DEVELOPER_GUIDE.md), [API](API.md), [Testing](TESTING.md)
- **Business** → [Payment Gateway](../PAYMENT_GATEWAY_GUIDE.md), [Analytics](../ANALYTICS_DASHBOARD_GUIDE.md)
- **Support** → [Customer Guide](guides/CUSTOMER_GUIDE.md), [Troubleshooting](../ROUTING_TROUBLESHOOTING_GUIDE.md)

## 🔍 Finding Information

### Search Tips
1. **Use Ctrl+F** to search within this index
2. **Check role-specific guides** first (in `guides/` folder)
3. **Browse by topic** for technical details
4. **Check archived docs** (`archived/` folder) for historical information

### Still Can't Find It?
- Check the [README](../README.md) for general information
- Review [TODO](../TODO.md) for upcoming features
- Check [CHANGELOG](../CHANGELOG.md) for recent changes
- Open an issue on GitHub

## 📝 Documentation Standards

### Contributing to Documentation
- Use Markdown format
- Follow existing structure
- Include code examples
- Add screenshots for UI features
- Update this index when adding docs

### Documentation Locations
- **Root** (`/`): Main project documentation
- **docs/** (`/docs/`): Organized documentation
- **docs/guides/** (`/docs/guides/`): User guides by role
- **docs/technical/** (`/docs/technical/`): Technical specifications
- **docs/archived/** (`/docs/archived/`): Historical/deprecated docs

## 🔄 Recently Updated

- **2026-01-23**: Documentation cleanup - removed outdated files and marked progress as complete
- **2026-01-23**: Archived historical completion summaries and deprecated role hierarchy files
- **2026-01-23**: Created comprehensive role-based user guides (8 guides)
- **2026-01-23**: Reorganized documentation structure
- **2026-01-23**: Created automated installation script (install.sh)
- **2026-01-23**: Moved historical docs to archived folder

## 📌 Most Important Documents

For new users, start with these:

1. **[Installation Script](../install.sh)** - Get system running quickly
2. **[README](../README.md)** - Understand the project
3. **Your Role Guide** - Check `guides/` folder for your specific role
4. **[Network Services](NETWORK_SERVICES.md)** - Configure services
5. **[API Documentation](API.md)** - If integrating/developing

## ✅ Documentation Status

| Category | Status | Last Updated |
|----------|--------|--------------|
| User Guides | ✅ Complete | 2026-01-23 |
| Technical Docs | ✅ Complete | 2026-01-23 |
| API Docs | ✅ Complete | 2026-01 |
| Installation | ✅ Complete | 2026-01-23 |
| Archived | ✅ Organized | 2026-01-23 |

## 📄 Document Conventions

### Status Badges
- ✅ **Complete** - Fully documented and up-to-date
- 🚧 **In Progress** - Documentation being updated
- 📌 **Planned** - Documentation planned but not started
- ⚠️ **Deprecated** - No longer maintained, see DEPRECATED.md

### File Naming
- `UPPERCASE.md` - Root-level documentation
- `lowercase.md` - docs/ subdirectory documentation
- `guides/ROLE_GUIDE.md` - User role guides

---

**Version**: 2.0  
**Last Updated**: January 23, 2026  
**Maintainer**: Development Team

**Need Help?** Open an issue on GitHub or check your role-specific guide in `docs/guides/`.
