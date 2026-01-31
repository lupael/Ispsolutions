# Deprecated Attribute

A PHP 8 attribute for marking deprecated code elements with structured metadata and GUID tracking.

## Overview

The `Deprecated` attribute provides a modern, type-safe way to mark code as deprecated with comprehensive metadata. It offers significant advantages over traditional `@deprecated` DocBlock comments by providing structured, programmatically accessible deprecation information.

## Location

```
App\Attributes\Deprecated
```

## Features

- **GUID Tracking**: Unique identifier for each deprecated element
- **Structured Metadata**: Version info, alternatives, and removal plans
- **Type-Safe**: PHP 8 attribute with compile-time validation
- **Reflection API**: Programmatically query deprecation information
- **Multiple Targets**: Works with classes, methods, properties, and constants

## Usage

### Basic Example

```php
use App\Attributes\Deprecated;

#[Deprecated(
    guid: 'DEP-2024-001',
    message: 'This method is no longer maintained'
)]
public function oldMethod(): void
{
    // deprecated implementation
}
```

### Complete Example with All Parameters

```php
#[Deprecated(
    guid: 'DEP-2024-002',
    message: 'Use the new API implementation instead',
    since: 'v1.0.0',
    alternative: 'NewClass::newMethod()',
    removeIn: 'v2.0.0'
)]
public function legacyMethod(): void
{
    // deprecated implementation
}
```

### Supported Targets

#### 1. Methods

```php
#[Deprecated(
    guid: 'DEP-2024-003',
    message: 'Replaced by faster implementation',
    alternative: 'optimizedMethod()'
)]
public function slowMethod(): void
{
    // old implementation
}
```

#### 2. Classes

```php
#[Deprecated(
    guid: 'DEP-2024-004',
    message: 'Use the redesigned service class',
    since: 'v2.0.0',
    alternative: 'App\Services\NewService'
)]
class LegacyService
{
    // deprecated class
}
```

#### 3. Properties

```php
class User
{
    #[Deprecated(
        guid: 'DEP-2024-005',
        message: 'Use the new address structure',
        alternative: 'addressData property'
    )]
    public string $legacyAddress;
}
```

#### 4. Class Constants

```php
class User
{
    #[Deprecated(
        guid: 'DEP-2024-006',
        message: 'Use is_subscriber flag instead',
        since: 'v1.0.0',
        removeIn: 'v2.0.0'
    )]
    public const OPERATOR_LEVEL_CUSTOMER = 100;
}
```

## Parameters

### Required Parameters

- **`guid`** (string): Unique identifier for tracking this deprecation
  - Format: `DEP-YYYY-NNN` (e.g., `DEP-2024-001`)
  - Must be unique across the entire codebase

- **`message`** (string): Clear explanation of why this is deprecated
  - Should explain the reason for deprecation
  - Keep concise but informative

### Optional Parameters

- **`since`** (string|null): Version or date when deprecated
  - Examples: `'v1.0.0'`, `'v2.5.0 (2024-01-30)'`, `'2024-01-30'`

- **`alternative`** (string|null): Recommended replacement
  - Full class/method name: `'App\Services\NewService::newMethod()'`
  - Short reference: `'newMethod()'`
  - Multiple alternatives: `'methodA() or methodB()'`

- **`removeIn`** (string|null): Planned removal version
  - Examples: `'v2.0.0'`, `'v3.0.0'`
  - Helps users plan migration timeline

## Methods

### `getFormattedMessage(): string`

Returns a human-readable deprecation message with all information:

```php
$deprecated = new Deprecated(
    guid: 'DEP-2024-001',
    message: 'Old implementation',
    since: 'v1.0.0',
    alternative: 'newMethod()',
    removeIn: 'v2.0.0'
);

echo $deprecated->getFormattedMessage();
// Output: "DEPRECATED [DEP-2024-001] since v1.0.0 - Old implementation Use newMethod() instead. Will be removed in v2.0.0."
```

### `toArray(): array`

Converts the attribute to an associative array:

```php
$array = $deprecated->toArray();
/*
[
    'guid' => 'DEP-2024-001',
    'message' => 'Old implementation',
    'since' => 'v1.0.0',
    'alternative' => 'newMethod()',
    'removeIn' => 'v2.0.0',
    'formattedMessage' => '...'
]
*/
```

## Programmatic Access

Use PHP's Reflection API to query deprecation information:

### Get Deprecated Methods

```php
use ReflectionClass;
use App\Attributes\Deprecated;

$reflection = new ReflectionClass(MyClass::class);

foreach ($reflection->getMethods() as $method) {
    $attributes = $method->getAttributes(Deprecated::class);
    
    if (!empty($attributes)) {
        $deprecated = $attributes[0]->newInstance();
        echo "Method {$method->getName()} is deprecated:\n";
        echo $deprecated->getFormattedMessage() . "\n\n";
    }
}
```

### Check if Element is Deprecated

```php
$method = new ReflectionMethod(MyClass::class, 'oldMethod');
$isDeprecated = !empty($method->getAttributes(Deprecated::class));

if ($isDeprecated) {
    $deprecated = $method->getAttributes(Deprecated::class)[0]->newInstance();
    trigger_error($deprecated->getFormattedMessage(), E_USER_DEPRECATED);
}
```

## GUID Naming Convention

Use a consistent format for GUIDs to make tracking easier:

**Format**: `DEP-YYYY-NNN`

- `DEP`: Prefix indicating "deprecated"
- `YYYY`: Year when deprecated
- `NNN`: Sequential number (001, 002, etc.)

**Examples**:
- `DEP-2024-001` - First deprecation in 2024
- `DEP-2024-002` - Second deprecation in 2024
- `DEP-2025-001` - First deprecation in 2025

**Alternative formats** (also acceptable):
- `DEP-2024-USER-001` - Include component name
- `DEP-2024-Q1-001` - Include quarter

## Migration from @deprecated

### Before (DocBlock)

```php
/**
 * @deprecated since version 1.0 (2026-01-30). Use is_subscriber flag instead.
 *             Will be removed in version 2.0.
 */
public const OPERATOR_LEVEL_CUSTOMER = 100;
```

### After (Attribute)

```php
use App\Attributes\Deprecated;

/**
 * @deprecated since version 1.0 (2026-01-30). Use is_subscriber flag instead.
 *             Will be removed in version 2.0.
 */
#[Deprecated(
    guid: 'DEP-2026-001',
    message: 'Customers are no longer identified by operator_level',
    since: 'v1.0.0 (2026-01-30)',
    alternative: 'is_subscriber flag',
    removeIn: 'v2.0.0'
)]
public const OPERATOR_LEVEL_CUSTOMER = 100;
```

**Note**: During transition, both can coexist. Keep the `@deprecated` DocBlock for IDE support while adding the attribute for structured tracking.

## Benefits Over @deprecated

### Traditional @deprecated
```php
/**
 * @deprecated Use newMethod() instead
 */
public function oldMethod() {}
```

**Limitations**:
- Unstructured text
- No standardized format
- Not programmatically accessible
- No GUID tracking
- IDE-dependent behavior

### Deprecated Attribute
```php
#[Deprecated(
    guid: 'DEP-2024-001',
    message: 'Use new implementation',
    alternative: 'newMethod()'
)]
public function oldMethod() {}
```

**Advantages**:
- ✅ Structured, typed data
- ✅ Programmatically queryable via Reflection
- ✅ GUID-based tracking
- ✅ Consistent format enforced
- ✅ Generate deprecation reports
- ✅ Build tooling for automated migration

## Real-World Examples

### Example 1: Deprecated Constant in User Model

```php
use App\Attributes\Deprecated;

class User extends Model
{
    #[Deprecated(
        guid: 'DEP-2026-001',
        message: 'Customers are no longer identified by operator_level',
        since: 'v1.0.0 (2026-01-30)',
        alternative: 'is_subscriber flag',
        removeIn: 'v2.0.0'
    )]
    public const OPERATOR_LEVEL_CUSTOMER = 100;
}
```

### Example 2: Deprecated Relationship Method

```php
#[Deprecated(
    guid: 'DEP-2026-002',
    message: 'Network credentials now stored directly on User model',
    since: 'v1.0.0',
    alternative: 'User model fields (username, service_type, etc.)'
)]
public function networkUser(): HasOne
{
    return $this->hasOne(NetworkUser::class, 'user_id');
}
```

## Testing

The attribute includes comprehensive unit tests:

```bash
./vendor/bin/phpunit tests/Unit/Attributes/DeprecatedTest.php
```

Test coverage includes:
- Creating attributes with all/minimal parameters
- Formatted message generation
- Array conversion
- Application to classes, methods, properties, and constants
- Reflection API access

## Future Enhancements

Possible extensions for this attribute:

1. **Automated Deprecation Reports**
   - Scan codebase for all `#[Deprecated]` attributes
   - Generate markdown/HTML reports by GUID

2. **IDE Integration**
   - Custom inspections that read attribute metadata
   - Automated refactoring suggestions

3. **CI/CD Checks**
   - Fail builds if deprecated code exceeds threshold
   - Alert on usage of code marked for imminent removal

4. **Migration Tools**
   - Automated replacement based on `alternative` field
   - Track usage of deprecated elements

## See Also

- [DEPRECATED.md](../../DEPRECATED.md) - Project deprecation policy
- [PHP Attributes Documentation](https://www.php.net/manual/en/language.attributes.overview.php)
- [User Model](../Models/User.php) - Real-world examples

## Questions?

For questions about using the Deprecated attribute:
1. Review the examples in this document
2. Check [DEPRECATED.md](../../DEPRECATED.md) for project policy
3. See the unit tests for detailed usage examples
4. Open an issue on GitHub for clarification
