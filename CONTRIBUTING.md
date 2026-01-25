# Contributing to ISP Solution

Thank you for considering contributing to ISP Solution! This document provides guidelines and instructions for contributing to the project.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Workflow](#development-workflow)
- [Commit Message Guidelines](#commit-message-guidelines)
- [Pull Request Process](#pull-request-process)
- [Coding Standards](#coding-standards)
- [Testing](#testing)
- [Documentation](#documentation)

## Code of Conduct

This project adheres to a code of conduct. By participating, you are expected to uphold this code. Please report unacceptable behavior to the project maintainers.

## Getting Started

1. **Fork the repository** on GitHub
2. **Clone your fork** locally:
   ```bash
   git clone https://github.com/YOUR_USERNAME/ispsolution.git
   cd ispsolution
   ```
3. **Install dependencies**:
   ```bash
   composer install
   npm install
   ```
4. **Set up environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
5. **Run migrations**:
   ```bash
   php artisan migrate
   ```

## Development Workflow

1. **Create a feature branch** from `main`:
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Make your changes** following our coding standards

3. **Test your changes**:
   ```bash
   composer test
   npm run build
   ```

4. **Commit your changes** using conventional commit format:
   ```bash
   git commit -m "feat(module): add new feature"
   ```

5. **Push to your fork**:
   ```bash
   git push origin feature/your-feature-name
   ```

6. **Create a Pull Request** on GitHub

## Commit Message Guidelines

We use [Conventional Commits](https://www.conventionalcommits.org/) for clear and structured commit messages. This allows us to automatically generate changelogs.

### Format

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types

- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks
- `perf`: Performance improvements
- `ci`: CI/CD changes
- `build`: Build system changes

### Examples

```bash
# Feature with scope
git commit -m "feat(billing): add PayPal payment gateway"

# Bug fix
git commit -m "fix(auth): resolve session timeout issue"

# Documentation
git commit -m "docs: update API documentation"

# Breaking change
git commit -m "feat(api)!: change authentication endpoint

BREAKING CHANGE: Authentication endpoint now requires API version"
```

### Detailed Guide

For more information about commit messages and changelog generation, see [Changelog Guide](docs/CHANGELOG_GUIDE.md).

## Pull Request Process

1. **Update documentation** for any changed functionality
2. **Add tests** for new features or bug fixes
3. **Follow coding standards** (run `./vendor/bin/pint`)
4. **Ensure tests pass**: Run `composer test`
5. **Update CHANGELOG.md** if making significant changes (optional - will be auto-generated)
6. **Reference issues**: Link related issues using `Closes #123` or `Fixes #456`
7. **Request review** from maintainers

### Pull Request Title

Use the same conventional commit format for PR titles:

```
feat(billing): Add PayPal integration
fix(auth): Resolve token refresh bug
docs: Update installation guide
```

### Pull Request Description

Include:
- **What changed**: Brief description of changes
- **Why**: Reason for the change
- **How to test**: Steps to verify the changes
- **Related issues**: Link to related issues
- **Screenshots**: For UI changes
- **Breaking changes**: List any breaking changes

## Coding Standards

### PHP

- Follow **PSR-12** coding standard
- Use **strict types**: `declare(strict_types=1);`
- Run **PHP CS Fixer** before committing:
  ```bash
  ./vendor/bin/pint
  ```
- Run **PHPStan** for static analysis:
  ```bash
  ./vendor/bin/phpstan analyse
  ```

### JavaScript

- Use **ES6+** features
- Follow **Airbnb style guide**
- Format code with **Prettier**
- Lint code before committing:
  ```bash
  npm run lint
  ```

### Blade Templates

- Use **Blade components** for reusable UI elements
- Keep templates clean and readable
- Format with **blade-formatter**

### Database

- Create **migrations** for all schema changes
- Include **rollback** methods
- Add **indexes** for performance
- Use **foreign key constraints**

### Testing

- Write **unit tests** for new functionality
- Add **feature tests** for user-facing features
- Maintain or improve **test coverage**
- Mock external services

## Testing

### Running Tests

```bash
# Run all tests
composer test

# Run specific test suite
php artisan test --filter=BillingTest

# Run with coverage
php artisan test --coverage

# Run PHPStan
./vendor/bin/phpstan analyse
```

### Writing Tests

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_invoice(): void
    {
        $response = $this->post('/api/invoices', [
            'customer_id' => 1,
            'amount' => 100.00,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('invoices', [
            'customer_id' => 1,
            'amount' => 100.00,
        ]);
    }
}
```

## Documentation

### Code Documentation

- Add **PHPDoc** blocks for all classes and methods
- Document **parameters** and **return types**
- Include **usage examples** for complex functionality
- Keep documentation **up to date**

### User Documentation

- Update **README.md** for significant changes
- Create **guides** in the `docs/` directory
- Include **screenshots** for UI features
- Provide **code examples**

### API Documentation

- Document all **API endpoints**
- Include **request/response** examples
- List **authentication** requirements
- Document **error codes**

## Project Structure

```
ispsolution/
├── app/                    # Application code
│   ├── Http/              # Controllers, middleware
│   ├── Models/            # Eloquent models
│   ├── Services/          # Business logic
│   └── Helpers/           # Helper functions
├── config/                # Configuration files
├── database/              # Migrations, seeders
├── docs/                  # Documentation
├── resources/             # Views, assets
├── routes/                # Route definitions
├── scripts/               # Utility scripts
├── tests/                 # Test files
└── public/                # Public assets
```

## Getting Help

- **GitHub Issues**: Report bugs or request features
- **GitHub Discussions**: Ask questions or discuss ideas
- **Documentation**: Check existing documentation
- **Pull Requests**: Review other PRs to learn

## Review Process

1. **Automated checks**: CI/CD runs tests and linting
2. **Code review**: Maintainers review your code
3. **Feedback**: Address review comments
4. **Approval**: At least one maintainer approval required
5. **Merge**: Maintainers merge approved PRs

## Release Process

1. **Version bump**: Using semantic versioning
2. **Changelog**: Auto-generated from commits
3. **Tag creation**: Git tag for release
4. **Release notes**: Published on GitHub
5. **Deployment**: Automated deployment to staging/production

See [Changelog Guide](docs/CHANGELOG_GUIDE.md) for details on releases.

## Recognition

Contributors are recognized in:
- **CHANGELOG.md**: Automatic attribution
- **README.md**: Top contributors
- **GitHub**: Contributor graph

## License

By contributing to ISP Solution, you agree that your contributions will be licensed under the MIT License.

---

Thank you for contributing to ISP Solution! 🚀
