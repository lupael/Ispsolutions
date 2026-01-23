# Developer Guide - ISP Solution

## Overview

This guide helps developers understand the ISP Solution architecture, add new features, and follow best practices.

## Architecture Overview

### Multi-Tenancy

The application uses a shared database with tenant isolation via `tenant_id` foreign keys. See `docs/tenancy.md` for details.

### Role-Based Access Control (RBAC)

#### Operator Levels

Operators have hierarchical levels (lower number = higher privilege):

- **Developer (0)**: Full system access
- **Super Admin (10)**: Cross-tenant administration
- **Admin (ISP, formerly Group Admin) (20)**: ISP administrator
- **Operator (30)**: Regular operations staff
- **Sub-Operator (40)**: Limited operator
- **Manager (50)**: Customer area manager
- **Card Distributor (60)**: Recharge card management
- **Accountant (70)**: Financial operations
- **Staff (80)**: Basic staff access
- **Customer (100)**: End user

#### Permissions

Permissions are defined in `config/operators_permissions.php` and organized by module:

- customers
- billing
- packages
- network
- cards
- operators
- reports
- settings

#### Special Permissions

Defined in `config/special_permissions.php`, these are granted individually to operators for enhanced access:

- `access_all_customers` - View customers across all zones
- `bypass_credit_limit` - Process payments exceeding limits
- `manual_discount` - Apply manual discounts
- `delete_transactions` - Delete payment transactions
- `modify_billing_cycle` - Change billing cycles
- `access_logs` - View system logs
- `bulk_operations` - Perform bulk operations
- `router_config_access` - Access router configurations
- `override_package_pricing` - Set custom pricing
- `view_sensitive_data` - View sensitive customer data
- `export_all_data` - Export complete data
- `manage_resellers` - Create and manage resellers

## Adding New Features

### Adding a Panel View

1. **Create the Controller**

```php
namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;

class MyFeatureController extends Controller
{
    public function index()
    {
        // Check permission
        $this->authorize('viewAny', MyModel::class);
        
        $items = MyModel::paginate(20);
        
        return view('panels.admin.my-feature.index', compact('items'));
    }
}
```

2. **Add Routes**

```php
// routes/web.php
Route::prefix('panel/admin')->name('panel.admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/my-feature', [MyFeatureController::class, 'index'])->name('my-feature.index');
    Route::post('/my-feature', [MyFeatureController::class, 'store'])->name('my-feature.store');
});
```

3. **Create View**

```blade
{{-- resources/views/panels/admin/my-feature/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'My Feature')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">My Feature</h1>
    
    {{-- Content here --}}
</div>
@endsection
```

4. **Add to Sidebar Menu**

Edit `config/sidebars.php`:

```php
'group_admin' => [
    // ... existing items
    [
        'key' => 'my_feature',
        'label' => 'My Feature',
        'icon' => 'bi-star',
        'permission' => 'view_my_feature',
        'route' => 'panel.admin.my-feature.index',
    ],
],
```

### Adding Permissions

1. **Update config/operators_permissions.php**

```php
'my_module' => [
    'view_my_feature' => 'View my feature',
    'create_my_feature' => 'Create my feature',
    'edit_my_feature' => 'Edit my feature',
    'delete_my_feature' => 'Delete my feature',
],
```

2. **Create Policy**

```php
namespace App\Policies;

use App\Models\User;
use App\Models\MyModel;

class MyModelPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_my_feature');
    }
    
    public function create(User $user): bool
    {
        return $user->hasPermission('create_my_feature');
    }
    
    // ... other policy methods
}
```

3. **Register Policy**

In `App\Providers\AuthServiceProvider`:

```php
protected $policies = [
    MyModel::class => MyModelPolicy::class,
];
```

### Adding a Model with Tenancy

```php
namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class MyModel extends Model
{
    use BelongsToTenant;
    
    protected $fillable = [
        'tenant_id',
        'name',
        // ... other fields
    ];
    
    protected $casts = [
        // ... casts
    ];
    
    // Relationships
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
```

### Creating Migrations

```bash
php artisan make:migration create_my_table
```

```php
public function up(): void
{
    Schema::create('my_table', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_id')
              ->nullable()
              ->constrained()
              ->nullOnDelete();
        $table->string('name');
        $table->timestamps();
        
        $table->index('tenant_id');
    });
}
```

## Panel Development

### Available Layouts

- `layouts/admin.blade.php` - Main admin layout with sidebar
- `panels/layouts/app.blade.php` - Alternative panel layout

### Blade Components

The application includes the following reusable Blade components:

- **Cards** - Pre-styled card layouts for content sections
- **Tables** - Data table components with sorting and filtering
- **Forms** - Form input components with validation styling
- **Pagination** - Pagination UI (see `panels/partials/pagination.blade.php`)
- **Alerts** - Toast notifications and alert messages
- **Modals** - Modal dialog components for user interactions

For component usage examples, refer to existing panel views in `resources/views/panels/`.

### Styling

The application uses Tailwind CSS with Bootstrap Icons. Follow these conventions:

- Use Tailwind utility classes
- Keep custom CSS minimal
- Use Bootstrap Icons: `<i class="bi bi-icon-name"></i>`
- Follow Metronic design patterns for consistency

## Testing

### Writing Tests

```php
namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyFeatureTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_can_view_feature_index(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'operator_level' => 20,
        ]);
        
        $response = $this->actingAs($user)
            ->get(route('panel.admin.my-feature.index'));
        
        $response->assertStatus(200);
    }
}
```

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/MyFeatureTest.php

# Run with coverage
php artisan test --coverage

# Run tests matching pattern
php artisan test --filter=Tenancy
```

## Services

### Creating a Service

```php
namespace App\Services;

class MyService
{
    public function performAction(): bool
    {
        // Business logic here
        return true;
    }
}
```

### Registering a Service

In `App\Providers\AppServiceProvider`:

```php
public function register(): void
{
    $this->app->singleton(MyService::class, function ($app) {
        return new MyService();
    });
}
```

### Using Services

```php
use App\Services\MyService;

class MyController extends Controller
{
    public function __construct(
        private MyService $myService
    ) {}
    
    public function index()
    {
        $this->myService->performAction();
    }
}
```

## Background Jobs

### Creating a Job

```bash
php artisan make:job ProcessMyTask
```

```php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessMyTask implements ShouldQueue
{
    use Queueable;
    
    public function handle(): void
    {
        // Job logic here
    }
}
```

### Dispatching Jobs

```php
use App\Jobs\ProcessMyTask;

ProcessMyTask::dispatch($data);

// Delayed dispatch
ProcessMyTask::dispatch($data)->delay(now()->addMinutes(5));

// On specific queue
ProcessMyTask::dispatch($data)->onQueue('high-priority');
```

## Useful Helpers

### Tenancy Helpers

```php
getCurrentTenant()        // Get current tenant
getCurrentTenantId()      // Get current tenant ID
```

### Menu Helpers

```php
isMenuActive($route)      // Check if menu is active
isMenuDisabled($key)      // Check if menu is disabled
canAccessMenu($item)      // Check if user can access menu
getSidebarMenu()          // Get sidebar menu for user
```

### Formatting Helpers

```php
formatCurrency($amount, $currency = 'BDT')  // Format as currency
```

## Code Style

### PSR-12 Compliance

Follow PSR-12 coding standards:

```bash
# Check code style
./vendor/bin/pint --test

# Fix code style
./vendor/bin/pint
```

### Type Hints

Always use type hints:

```php
public function myMethod(string $name, int $age): bool
{
    // ...
}
```

### Documentation

Use PHPDoc blocks:

```php
/**
 * Process customer payment.
 *
 * @param  int  $customerId
 * @param  float  $amount
 * @return bool
 */
public function processPayment(int $customerId, float $amount): bool
{
    // ...
}
```

## Debugging

### Logging

```php
use Illuminate\Support\Facades\Log;

Log::debug('Debug message', ['context' => $data]);
Log::info('Info message');
Log::warning('Warning message');
Log::error('Error message', ['exception' => $e]);
```

### Dump and Die

```php
dd($variable);        // Dump and die
dump($variable);      // Dump and continue
ray($variable);       // Use Ray debugger (if installed)
```

## Performance

### Query Optimization

```php
// Use eager loading
$customers = Customer::with(['package', 'invoices'])->get();

// Use select to limit columns
$customers = Customer::select(['id', 'name', 'email'])->get();

// Use chunking for large datasets
Customer::chunk(100, function ($customers) {
    foreach ($customers as $customer) {
        // Process customer
    }
});
```

### Caching

```php
use Illuminate\Support\Facades\Cache;

// Store in cache
Cache::put('key', $value, now()->addHours(1));

// Retrieve from cache
$value = Cache::get('key');

// Remember pattern
$users = Cache::remember('users', now()->addHours(1), function () {
    return User::all();
});
```

## Deployment

### Production Checklist

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate app key
- [ ] Run migrations
- [ ] Seed necessary data
- [ ] Compile assets: `npm run build`
- [ ] Optimize: `php artisan optimize`
- [ ] Configure queue workers
- [ ] Set up cron job for scheduler
- [ ] Configure backups
- [ ] Set up monitoring

### Optimization Commands

```bash
# Clear all caches
php artisan optimize:clear

# Optimize application
php artisan optimize

# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache
```

## Common Issues

### Issue: Changes not reflecting

**Solution:** Clear caches:
```bash
php artisan optimize:clear
composer dump-autoload
```

### Issue: Permission denied errors

**Solution:** Fix file permissions:
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## Resources

- Laravel Documentation: https://laravel.com/docs
- Tailwind CSS: https://tailwindcss.com/docs
- Bootstrap Icons: https://icons.getbootstrap.com/
- Project README: README.md
- Tenancy Guide: docs/tenancy.md

## Getting Help

1. Check existing documentation
2. Search through test files for examples
3. Review similar existing features in codebase
4. Ask team members
5. Create detailed issue on project tracker
