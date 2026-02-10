# CheckUnusedComponents Command Guide

## Overview

The `app:check-unused-components` Artisan command is a comprehensive code analysis tool that helps identify unused, mismatched, and potentially problematic components in your Laravel application.

## Purpose

This command analyzes your codebase to find:
- Unused controllers and controller methods
- Unused models
- Unused Blade templates/views
- Broken or mismatched routes
- Unused service classes
- Unused job classes
- Command registration status
- Unused API controllers

## Usage

### Basic Usage

```bash
php artisan app:check-unused-components
```

This will run a full analysis and display a summary of findings with statistics.

### Detailed Mode

```bash
php artisan app:check-unused-components --detailed
```

Use the `--detailed` flag to see:
- Exact file paths and line numbers for broken routes
- Full code snippets of problematic routes
- More verbose output for debugging

### Panel View Linkage Suggestions (NEW)

```bash
php artisan app:check-unused-components --suggest-links
```

Use the `--suggest-links` flag to see:
- Detailed suggestions for linking unused panel views
- Controller existence checks for each unused panel view
- Suggested routes and controller methods for linking views
- Dynamic view detection results
- Grouped suggestions by panel type (Admin, Developer, Customer, etc.)

### Combined Options

```bash
php artisan app:check-unused-components --detailed --suggest-links
```

You can combine both options for maximum detail.

## What It Analyzes

### 1. Controllers Analysis 📋

**What it does:**
- Scans all controllers in `app/Http/Controllers`
- Checks if controllers are referenced in route files (`web.php`, `api.php`)
- Identifies public methods that aren't used in routes

**Output:**
```
📋 Analyzing Controllers...
  ⚠️  Found 46 unused controller(s):
    - app/Http/Controllers/Panel/CustomerWizardController.php
    - app/Http/Controllers/Panel/ExpenseManagementController.php
  ⚠️  Found controller(s) with unused methods:
    - app/Http/Controllers/HotspotController.php:
      • signupForm
```

### 2. Models Analysis 📦

**What it does:**
- Scans all models in `app/Models`
- Checks usage in controllers, commands, jobs, services, listeners, mail, policies
- Looks for various usage patterns (instantiation, static calls, type hints, use statements)

**Output:**
```
📦 Analyzing Models...
  ⚠️  Found 11 unused model(s):
    - app/Models/CustomerCustomAttribute.php
    - app/Models/NetworkDevice.php
```

### 3. Views/Blade Templates Analysis 👁️

**What it does:**
- Scans all Blade templates in `resources/views`
- Checks if views are returned in controllers, mail classes, or notifications
- Looks for `view()`, `View::make()`, and `Inertia::render()` calls
- **NEW:** Detects dynamic view references (e.g., `view($this->getViewPrefix() . '.index')`)
- **NEW:** Categorizes unused views by type (Panel, PDF, Email, Error, Other)
- Excludes layouts, components, and partials (files starting with `_` or in `/layouts/`, `/components/`, `/partials/`)

**Output:**
```
👁️  Analyzing Views/Blade Templates...
  ⚠️  Found 40 unused view(s):
    📁 Panel Views (8):
      - resources/views/panels/admin/ip-pools/migration-progress.blade.php
      - resources/views/panels/admin/logs/system.blade.php
    📁 Pdf Views (15):
      - resources/views/pdf/customer-statement.blade.php
      - resources/views/pdf/invoice.blade.php
    📁 Email Views (2):
      - resources/views/emails/invoice.blade.php
    📁 Error Views (3):
      - resources/views/errors/429.blade.php
    📁 Other Views (12):
      - resources/views/pages/demo10/index.blade.php

    💡 Run with --suggest-links to see linkage suggestions for panel views
```

**Dynamic View Detection:**

The command now intelligently detects views that are used through dynamic patterns:
- `view($this->getViewPrefix() . '.index')`
- `view($viewPath . '.show')`
- Dynamic path construction in controllers

For example, if a controller uses:
```php
protected function getViewPrefix(): string
{
    // Determine panel context based on user role or route
    return auth()->user()->isAdmin() 
        ? 'panels.admin.master-packages' 
        : 'panels.developer.master-packages';
}

public function index()
{
    return view($this->getViewPrefix() . '.index');
}
```

The command will correctly detect that both `panels/admin/master-packages/index.blade.php` and `panels/developer/master-packages/index.blade.php` are being used.

**Panel View Linkage Suggestions (with --suggest-links):**

When you use the `--suggest-links` option, the command provides intelligent suggestions for linking unused panel views:

```
📋 Panel View Linkage Suggestions:

  Admin Panel:
    - panels/admin/ip-pools/migration-progress.blade.php
      Controller: App\Http\Controllers\Panel\IpPoolsController ✗
      Suggestion: Create controller or add route:
        Route (in web.php, admin section):
          Route::get('/ip-pools', [App\Http\Controllers\Panel\IpPoolsController::class, 'migrationProgress'])->name('ip-pools.migration-progress');
        Controller method:
          public function migrationProgress() {
              return view('panels.admin.ip-pools.migration-progress');
          }
    
    - panels/admin/logs/system.blade.php
      Controller: App\Http\Controllers\Panel\LogsController ✗
      Suggestion: Create controller or add route:
        Route (in web.php, admin section):
          Route::get('/logs', [YourController::class, 'system'])->name('logs.system');
        Controller method:
          public function system() {
              return view('panels.admin.logs.system');
          }

  Developer Panel:
    - panels/developer/reports/analytics.blade.php
      Controller: App\Http\Controllers\Panel\ReportsController ✓
      Suggestion: View is likely used via dynamic path $this->getViewPrefix()
```

The suggestions include:
- Whether the suggested controller exists (✓ or ✗)
- For existing controllers: hints about dynamic view usage
- For missing controllers: complete route and method suggestions
- Proper naming conventions based on Laravel standards
- Panel-type-specific route grouping suggestions

### 4. Routes Analysis 🛣️

**What it does:**
- Parses `web.php` and `api.php` route files
- Validates that referenced controllers exist
- Validates that referenced controller methods exist
- Supports both modern array syntax `[Controller::class, 'method']` and legacy string syntax `'Controller@method'`

**Output:**
```
🛣️  Analyzing Routes...
  ❌ Found 4 broken route(s):
    - web.php:721 - Method 'storePayment' not found in App\Http\Controllers\Panel\OperatorController
    - api.php:237 - Method 'validate' not found in App\Http\Controllers\Panel\IpPoolMigrationController
```

**With --detailed flag:**
```
🛣️  Analyzing Routes...
  ❌ Found 4 broken route(s):
    - web.php:721 - Method 'storePayment' not found in App\Http\Controllers\Panel\OperatorController
      Code: Route::post('/payments', [\App\Http\Controllers\Panel\OperatorController::class, 'storePayment'])->name('payments.store');
```

### 5. Services Analysis ⚙️

**What it does:**
- Scans all service classes in `app/Services`
- Checks usage in controllers, commands, jobs, listeners, and service providers
- Looks for instantiation, dependency injection, static calls, and use statements

**Output:**
```
⚙️  Analyzing Services...
  ⚠️  Found 10 unused service(s):
    - app/Services/AuditLogService.php
    - app/Services/RouterManager.php
```

### 6. Jobs Analysis 💼

**What it does:**
- Scans all job classes in `app/Jobs`
- Checks if jobs are dispatched in controllers, commands, services, or listeners
- Looks for `dispatch()` calls, `Job::dispatch()` static calls, and job instantiation

**Output:**
```
💼 Analyzing Jobs...
  ⚠️  Found 10 unused job(s):
    - app/Jobs/CheckRouterHealth.php
    - app/Jobs/SendBulkSmsJob.php
```

### 7. Commands Analysis ⌨️

**What it does:**
- Scans all console commands in `app/Console/Commands`
- Verifies command signatures exist
- Note: Laravel 5.5+ has auto-discovery, so all commands with valid signatures are automatically registered

**Output:**
```
⌨️  Analyzing Console Commands...
  ✓ All commands appear to be properly registered (auto-discovery enabled)
```

### 8. API Controllers Analysis 🔌

**What it does:**
- Specifically scans API controllers in `app/Http/Controllers/Api`
- Checks if they're referenced in `routes/api.php`

**Output:**
```
🔌 Analyzing API Controllers...
  ⚠️  Found 5 unused API controller(s):
    - app/Http/Controllers/Api/ChartController.php
    - app/Http/Controllers/Api/WebhookController.php
```

## Statistics Summary

At the end of the analysis, you'll see a comprehensive statistics table:

```
📊 Analysis Statistics:

+---------------------+-------+--------+------------+
| Component           | Total | Unused | Usage Rate |
+---------------------+-------+--------+------------+
| Controllers         | 80    | 46     | 42.5%      |
|   └─ Unused Methods | -     | 5      | -          |
| Models              | 97    | 11     | 88.7%      |
| Views               | 340   | 40     | 88.2%      |
|   └─ Panel Views    | -     | 8      | -          |
|   └─ Pdf Views      | -     | 15     | -          |
|   └─ Email Views    | -     | 2      | -          |
|   └─ Error Views    | -     | 3      | -          |
|   └─ Other Views    | -     | 12     | -          |
| Routes              | 700   | 0      | 100%       |
| Services            | 54    | 10     | 81.5%      |
| Jobs                | 12    | 10     | 16.7%      |
| Commands            | 34    | 0      | 100%       |
| Api controllers     | 14    | 5      | 64.3%      |
+---------------------+-------+--------+------------+

⚠️  Found 122 unused components and 4 broken routes out of 1331 total components.
   Consider reviewing and removing unused code to improve maintainability.
```

**New in Latest Version:**
- Views are now categorized by type (Panel, PDF, Email, Error, Other)
- Each category shows a breakdown count in the statistics
- Panel views are further grouped by panel type (Admin, Developer, Customer, etc.)

## Interpreting Results

### ✅ Green Messages (Info)
- Indicates everything is working correctly
- No action needed

### ⚠️ Yellow Messages (Warning)
- Indicates unused components
- Consider reviewing and potentially removing these to improve code maintainability
- **Note:** Some components might be intentionally kept for future use or are used dynamically

### ❌ Red Messages (Error)
- Indicates broken references or mismatches
- **Action Required:** These should be fixed as they can cause runtime errors

## Best Practices

### 1. Regular Analysis
Run this command regularly (e.g., weekly or before major releases) to keep your codebase clean.

### 2. False Positives
Be aware of potential false positives:
- **Dynamic Loading:** Classes loaded dynamically might be flagged as unused
  - **Improved:** The command now detects common dynamic view patterns like `$this->getViewPrefix()`
- **Config Files:** Classes referenced in config files might not be detected
- **Blade Components:** Some Blade components might be flagged incorrectly
- **API/Event Listeners:** Some listeners or API routes might be used externally

### 3. Before Removing Code
Before removing any "unused" component:
1. Search the entire codebase manually to confirm
2. Check if it's referenced in config files
3. Check if it's used in JavaScript/frontend code
4. Consider if it's part of a planned feature
5. Review git history to understand why it was created
6. **NEW:** For panel views, run with `--suggest-links` to see if they might be used via dynamic paths

### 4. Broken Routes
**Priority:** Fix broken routes immediately as they will cause 404 or 500 errors in production.

```bash
# Use detailed mode to see exact code
php artisan app:check-unused-components --detailed
```

### 5. Panel View Linkage
For unused panel views, use the `--suggest-links` option to:
- Check if a controller exists but uses dynamic view paths
- Get specific route and method suggestions
- Understand the panel structure and naming conventions
- Plan your view-to-controller mapping strategy

## Integration with CI/CD

You can integrate this command into your CI/CD pipeline:

```bash
# In your CI script
php artisan app:check-unused-components

# Check exit code
if [ $? -ne 0 ]; then
    echo "Component analysis failed"
    exit 1
fi
```

## Performance

The command analyzes your entire codebase, which may take:
- Small projects: 5-10 seconds
- Medium projects: 15-30 seconds
- Large projects: 30-60 seconds

The analysis is read-only and safe to run in production.

## Limitations

1. **Dynamic Code:** Cannot detect all components loaded dynamically or through reflection
   - **Improved:** Now detects common patterns like `$this->getViewPrefix()` and variable concatenation
   - May still miss complex dynamic view construction
2. **External References:** Cannot detect usage from external packages or JavaScript
3. **Magic Methods:** May not detect all Laravel magic method usage
4. **Vendor Code:** Does not analyze vendor directory
5. **Database/Config:** Does not check references in database or config files

## Recent Enhancements

### Version 2.0 Features (Latest)

1. **Dynamic View Detection:**
   - Detects views used via `$this->getViewPrefix()`
   - Identifies variable-based view paths
   - Recognizes string concatenation patterns

2. **View Categorization:**
   - Automatically categorizes unused views by type
   - Panel views grouped by panel type
   - PDF, Email, and Error views separately categorized

3. **Panel Linkage Suggestions:**
   - New `--suggest-links` option
   - Intelligent controller suggestions based on view paths
   - Complete route and method code examples
   - Controller existence verification

4. **Enhanced Statistics:**
   - Breakdown of unused views by category
   - Better visualization of component types
   - More actionable insights

## Exit Codes

- `0` - Analysis completed successfully (may still have unused components)
- `1` - Analysis failed due to an error

## Examples

### Example 1: Quick Check
```bash
php artisan app:check-unused-components
```

### Example 2: Detailed Analysis for Debugging
```bash
php artisan app:check-unused-components --detailed > analysis.txt
```

### Example 3: Focus on Errors Only
```bash
php artisan app:check-unused-components --quiet
```

## Troubleshooting

### "Controller not found" Error
This usually means:
1. The controller file doesn't exist at the expected location
2. The namespace doesn't match the directory structure
3. The route definition has a typo

### "Method not found" Error
This means:
1. The method doesn't exist in the controller
2. The method is private/protected (should be public)
3. There's a typo in the route definition

### False Positive for Unused Components
If a component is flagged as unused but you know it's used:
1. Check if it's loaded dynamically
2. Check config files and database seeders
3. Check JavaScript/frontend code
4. Check if it's referenced in comments or documentation only

## Support

For issues or questions about this command:
1. Check the command source: `app/Console/Commands/CheckUnusedComponents.php`
2. Run with `--detailed` flag for more information
3. Check Laravel logs: `storage/logs/laravel.log`

## Version History

- **v2.0.0** - Enhanced release
  - Dynamic view detection for `getViewPrefix()` patterns
  - View categorization by type (Panel, PDF, Email, Error, Other)
  - Panel linkage suggestions with `--suggest-links` option
  - Controller mapping from view paths
  - Ready-to-use route and method generation
  - Enhanced statistics with view breakdown

- **v1.0.0** - Initial release
  - Controller analysis
  - Model analysis
  - View analysis
  - Route validation
  - Service analysis
  - Job analysis
  - Command check
  - API controller analysis
  - Statistics summary
  - Detailed mode

## Related Commands

- `php artisan route:list` - List all registered routes
- `php artisan view:cache` - Cache views
- `php artisan optimize` - Cache framework bootstrap files
- `php artisan about` - Display application information
