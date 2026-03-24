# ISP Solution — Developer Walkthrough

> **Generated:** 2026-03-24  
> **Audience:** New developers, contributors, and onboarding engineers  
> **Prerequisites:** PHP 8.2+, Composer, Node.js LTS, MySQL 8.0, Redis

---

## Table of Contents

1. [Getting Started](#1-getting-started)
2. [Repository Structure](#2-repository-structure)
3. [Understanding the Role System](#3-understanding-the-role-system)
4. [Multi-Tenancy Explained](#4-multi-tenancy-explained)
5. [Adding a New Feature: Step-by-Step](#5-adding-a-new-feature-step-by-step)
6. [Working with RADIUS](#6-working-with-radius)
7. [Working with MikroTik API](#7-working-with-mikrotik-api)
8. [Working with the Billing System](#8-working-with-the-billing-system)
9. [Writing Tests](#9-writing-tests)
10. [Common Development Tasks](#10-common-development-tasks)
11. [Debugging Guide](#11-debugging-guide)
12. [Code Conventions](#12-code-conventions)
13. [Architecture Decision Records](#13-architecture-decision-records)

---

## 1. Getting Started

### Option A: Docker (Recommended)

```bash
# 1. Clone and enter repository
cd /path/to/ispsolution

# 2. Copy environment file
cp .env.example .env

# 3. Start Docker services
make up

# 4. Install dependencies
make install

# 5. Generate app key and run migrations
docker-compose exec app php artisan key:generate
make migrate

# 6. (Optional) Seed demo data
make seed

# 7. Build frontend assets
make build

# Access at: http://localhost:8000
# Email testing: http://localhost:8025
```

### Option B: Local Development

```bash
# 1. Install PHP dependencies
composer install

# 2. Install JS dependencies
npm install

# 3. Environment setup
cp .env.example .env
php artisan key:generate

# 4. Configure .env for your databases
# DB_* → Application database
# DB_RADIUS_* → RADIUS database (separate MySQL)

# 5. Run migrations (both databases)
php artisan migrate

# 6. Seed demo data (optional)
php artisan db:seed

# 7. Start development server
php artisan serve

# 8. In another terminal, build frontend
npm run dev
```

### Default Demo Credentials

After seeding:

| Role | Email | Password |
|------|-------|----------|
| Developer | dev@example.com | password |
| Super Admin | superadmin@example.com | password |
| Admin | admin@example.com | password |
| Operator | operator@example.com | password |
| Customer | customer@example.com | password |

---

## 2. Repository Structure

```
ispsolution/
├── app/
│   ├── Console/
│   │   └── Commands/        # 40+ Artisan commands
│   ├── Enums/               # PHP 8.1+ backed enums
│   ├── Events/              # Domain events
│   ├── Exceptions/          # Custom exception classes
│   ├── Exports/             # Maatwebsite Excel exports
│   ├── Helpers/             # Global helper functions
│   │   ├── DateHelper.php
│   │   ├── RouterCommentHelper.php
│   │   └── menu_helpers.php (autoloaded)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/         # REST API controllers
│   │   │   │   ├── V1/      # Versioned API
│   │   │   │   └── ...
│   │   │   ├── Auth/        # Authentication controllers
│   │   │   └── Panel/       # 90+ admin panel controllers
│   │   ├── Kernel.php       # HTTP kernel
│   │   ├── Middleware/      # 12 middleware classes
│   │   └── Requests/        # Form request validation
│   ├── Jobs/                # 17 queue jobs
│   ├── Listeners/           # Event listeners
│   ├── Mail/                # Mailable classes
│   ├── Models/              # 100+ Eloquent models
│   ├── Notifications/       # Laravel notifications
│   ├── Observers/           # Model observers
│   ├── Policies/            # Authorization policies
│   ├── Providers/           # Service providers
│   ├── Services/            # 75+ service classes (CORE)
│   ├── Traits/              # Reusable traits
│   └── View/                # View composers
├── database/
│   ├── factories/           # Model factories for testing
│   ├── migrations/          # 172 migrations
│   │   └── radius/          # RADIUS DB migrations
│   └── seeders/             # Database seeders
├── docs/                    # Technical documentation
├── resources/
│   ├── views/               # Blade templates
│   │   ├── panel/           # Legacy panel views (use panels/ for new)
│   │   └── panels/          # Canonical panel views by role
│   └── js/, css/            # Frontend source files
├── routes/
│   ├── web.php              # 1390-line web routes
│   ├── api.php              # 354-line API routes
│   └── console.php          # Scheduled tasks definition
├── tests/
│   ├── Feature/             # 60+ feature tests
│   ├── Unit/                # 20+ unit tests
│   └── Integration/         # Integration tests
├── Makefile                 # Common development commands
├── docker-compose.yml       # Docker service definitions
├── phpstan.neon             # Static analysis config
└── pint.json                # Code style config
```

---

## 3. Understanding the Role System

### Role Levels

Every user in the `users` table has an `operator_level` field (or `is_subscriber = true` for customers).

```php
// Checking roles in code
if ($user->operator_level <= 20) {
    // Admin or above
}

if ($user->is_subscriber) {
    // This is a customer/subscriber
}

// Using the Role model
$user->role->level  // integer level
$user->role->name   // 'admin', 'operator', etc.
```

### Role Middleware

Routes are protected by the `CheckRole` middleware:

```php
// In routes/web.php
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Admin-only routes
});

Route::middleware(['auth', 'role:operator,admin'])->group(function () {
    // Operator or Admin routes
});
```

### Special Permissions

Beyond roles, operators can have special permissions:

```php
// Check special permission
if ($user->hasSpecialPermission('generate_bills')) {
    // Allow billing action
}

// Grant permission (in admin UI or via service)
$specialPermissionService->grant($user, 'generate_bills');
```

Special permissions are defined in `config/special_permissions.php`.

---

## 4. Multi-Tenancy Explained

### How Tenant Resolution Works

1. Request arrives at Nginx → PHP-FPM
2. `ResolveTenant` middleware extracts host from request
3. `TenancyService` looks up tenant by domain or subdomain (cached in Redis)
4. Current tenant is stored in `TenancyService` singleton
5. All models using `BelongsToTenant` trait automatically filter by `tenant_id`

### Using the `BelongsToTenant` Trait

```php
// In your Model
use App\Traits\BelongsToTenant;

class Package extends Model
{
    use BelongsToTenant;
    // tenant_id is set automatically on create()
    // All queries are automatically scoped to current tenant
}

// Creating a record — tenant_id set automatically
$package = Package::create(['name' => 'Basic 10Mbps', ...]);

// Querying — automatically adds WHERE tenant_id = ?
$packages = Package::all();

// Bypass tenant scope (admin use only)
$allPackages = Package::allTenants()->get();

// Force specific tenant
$packages = Package::forTenant($tenantId)->get();
```

### TenancyService

```php
// Inject in controller/service
use App\Services\TenancyService;

class MyService
{
    public function __construct(private TenancyService $tenancy) {}

    public function doSomething()
    {
        $tenant = $this->tenancy->getCurrentTenant();
        $tenantId = $this->tenancy->getCurrentTenantId();

        // Run code in another tenant's context
        $this->tenancy->runForTenant($otherTenantId, function () {
            // This code sees the other tenant's data
        });
    }
}
```

---

## 5. Adding a New Feature: Step-by-Step

This example walks through adding a "Network Link" feature for tracking connections between network devices.

### Step 1: Create the Migration

```bash
php artisan make:migration create_network_links_table
```

```php
// In the migration file
public function up(): void
{
    Schema::create('network_links', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_id')->constrained('tenants');
        $table->foreignId('operator_id')->nullable()->constrained('users');
        $table->foreignId('from_device_id')->constrained('network_devices');
        $table->foreignId('to_device_id')->constrained('network_devices');
        $table->string('link_type'); // fiber, copper, wireless
        $table->integer('capacity_mbps')->nullable();
        $table->string('status')->default('active');
        $table->timestamps();
        $table->softDeletes();
    });
}
```

### Step 2: Create the Model

```bash
php artisan make:model NetworkLink
```

```php
// app/Models/NetworkLink.php
namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NetworkLink extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id', 'operator_id',
        'from_device_id', 'to_device_id',
        'link_type', 'capacity_mbps', 'status',
    ];

    public function fromDevice()
    {
        return $this->belongsTo(NetworkDevice::class, 'from_device_id');
    }

    public function toDevice()
    {
        return $this->belongsTo(NetworkDevice::class, 'to_device_id');
    }
}
```

### Step 3: Create the Service

```php
// app/Services/NetworkLinkService.php
namespace App\Services;

use App\Models\NetworkLink;

class NetworkLinkService
{
    public function create(array $data): NetworkLink
    {
        return NetworkLink::create($data);
    }

    public function getForOperator(int $operatorId): \Illuminate\Database\Eloquent\Collection
    {
        return NetworkLink::where('operator_id', $operatorId)
            ->with(['fromDevice', 'toDevice'])
            ->get();
    }
}
```

### Step 4: Create the Controller

```bash
php artisan make:controller Panel/NetworkLinkController
```

```php
// app/Http/Controllers/Panel/NetworkLinkController.php
namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\NetworkLinkService;
use Illuminate\Http\Request;

class NetworkLinkController extends Controller
{
    public function __construct(private NetworkLinkService $service) {}

    public function index()
    {
        $links = $this->service->getForOperator(auth()->id());
        return view('panels.admin.network-links.index', compact('links'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_device_id' => 'required|exists:network_devices,id',
            'to_device_id' => 'required|exists:network_devices,id',
            'link_type' => 'required|in:fiber,copper,wireless',
            'capacity_mbps' => 'nullable|integer|min:1',
        ]);

        $this->service->create($validated);
        return redirect()->route('admin.network-links.index')
            ->with('success', 'Network link created.');
    }
}
```

### Step 5: Register Routes

```php
// In routes/web.php, within the admin middleware group
Route::resource('network-links', NetworkLinkController::class)
    ->names('admin.network-links');
```

### Step 6: Create Blade Views

```
resources/views/panels/admin/network-links/
├── index.blade.php
├── create.blade.php
└── edit.blade.php
```

### Step 7: Register the Service (if needed)

Laravel auto-discovers services via constructor injection. For complex bindings, register in `AppServiceProvider`:

```php
// app/Providers/AppServiceProvider.php
$this->app->bind(NetworkLinkService::class, function ($app) {
    return new NetworkLinkService();
});
```

### Step 8: Write Tests

```php
// tests/Feature/NetworkLinkTest.php
class NetworkLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_network_link(): void
    {
        $admin = User::factory()->admin()->create();
        $from = NetworkDevice::factory()->create(['tenant_id' => $admin->tenant_id]);
        $to = NetworkDevice::factory()->create(['tenant_id' => $admin->tenant_id]);

        $response = $this->actingAs($admin)
            ->post(route('admin.network-links.store'), [
                'from_device_id' => $from->id,
                'to_device_id' => $to->id,
                'link_type' => 'fiber',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('network_links', [
            'from_device_id' => $from->id,
            'to_device_id' => $to->id,
        ]);
    }
}
```

---

## 6. Working with RADIUS

### Overview

The RADIUS database is a **separate MySQL instance** configured in `.env` as `DB_RADIUS_*`. The system maintains two databases:
- **App DB**: All application data, users, billing, etc.
- **RADIUS DB**: `radcheck`, `radreply`, `radacct`, `nas`

### RADIUS Models

All RADIUS models use the `radius` database connection:

```php
// app/Models/RadCheck.php
class RadCheck extends Model
{
    protected $connection = 'radius';
    protected $table = 'radcheck';
    public $timestamps = false; // RADIUS tables have no timestamps
}
```

### Provisioning a User to RADIUS

When a customer is created or their package changes, they must be provisioned to RADIUS:

```php
use App\Services\RadiusService;

class CustomerCreationService
{
    public function __construct(private RadiusService $radius) {}

    public function createCustomer(array $data): User
    {
        $user = User::create($data);

        // Sync to RADIUS
        $this->radius->provisionUser($user);

        return $user;
    }
}
```

### RadiusService Methods

```php
$radiusService->provisionUser(User $user);        // Create/update radcheck + radreply
$radiusService->removeUser(User $user);           // Remove from radcheck + radreply
$radiusService->suspendUser(User $user);          // Move to suspended IP pool
$radiusService->reactivateUser(User $user);       // Restore normal IP assignment
$radiusService->getUserAttributes(string $username); // Read current radreply entries
```

### Syncing RADIUS Manually

```bash
# Sync a single user
php artisan radius:sync-user 42

# Sync all users
php artisan radius:sync-users

# Check RADIUS DB is accessible
php artisan radius:install --check
```

### radcheck Entries

```
# Authentication entry
username = "john_doe"
attribute = "Cleartext-Password"
op = ":="
value = "secret_password"

# MAC binding (Hotspot)
username = "AA:BB:CC:DD:EE:FF"
attribute = "Cleartext-Password"
op = ":="
value = "AA:BB:CC:DD:EE:FF"
```

### radreply Entries

```
# IP assignment
attribute = "Framed-IP-Address"
op = ":="
value = "192.168.1.100"

# Rate limit (MikroTik format: Download/Upload)
attribute = "Mikrotik-Rate-Limit"
op = ":="
value = "10M/5M"

# IP pool assignment
attribute = "Framed-Pool"
op = ":="
value = "main_pool"
```

---

## 7. Working with MikroTik API

### Connection

```php
use App\Services\MikrotikApiService;

class RouterService
{
    public function __construct(private MikrotikApiService $mikrotik) {}

    public function getActiveSessions(MikrotikRouter $router): array
    {
        $connection = $this->mikrotik->connect($router);
        return $connection->query('/ppp/active/print')->read();
    }
}
```

### Common Operations

```php
// Get active PPPoE sessions
$sessions = $mikrotik->query('/ppp/active/print')->read();

// Disconnect a user
$mikrotik->command('/ppp/active/remove', ['.id' => $sessionId]);

// Add IP pool
$mikrotik->command('/ip/pool/add', [
    'name' => 'customers_pool',
    'ranges' => '10.0.0.1-10.0.255.254',
]);

// Get interfaces
$interfaces = $mikrotik->query('/interface/print')->read();

// Set bandwidth limit via queue
$mikrotik->command('/queue/simple/add', [
    'name' => "customer_{$userId}",
    'target' => $ipAddress,
    'max-limit' => '10M/5M',
    'comment' => "Customer {$userId}",
]);
```

### RouterCommentHelper

The `RouterCommentHelper` encodes customer metadata in RouterOS comments for easy identification:

```php
use App\Helpers\RouterCommentHelper;

// Encode comment
$comment = RouterCommentHelper::encode([
    'customer_id' => 42,
    'operator_id' => 5,
    'package_id' => 3,
]);
// Result: "ispbills:42:5:3" or similar pattern

// Decode comment
$data = RouterCommentHelper::decode($comment);
// Result: ['customer_id' => 42, 'operator_id' => 5, 'package_id' => 3]
```

### Automated Router Configuration

```bash
# Configure a router via CLI
php artisan mikrotik:configure --router=1

# This runs RouterConfigurationService which sets up:
# - RADIUS settings
# - Firewall rules
# - PPPoE server
# - Hotspot profile
# - SNMP
# - Suspended pool
```

### MikroTik Import

```bash
# Import IP pools from router
php artisan mikrotik:import-pools --router=1

# Import PPP profiles
php artisan mikrotik:import-profiles --router=1

# Import PPP secrets (customers)
php artisan mikrotik:import-secrets --router=1

# Import all
php artisan mikrotik:sync-all --router=1
```

---

## 8. Working with the Billing System

### Billing Profiles

```php
use App\Models\BillingProfile;

// Get operator's billing profile
$profile = BillingProfile::where('tenant_id', $tenantId)->first();

// Profile properties
$profile->type;          // 'daily' or 'monthly'
$profile->billing_day;   // Day of month (1-28) for monthly
$profile->grace_days;    // Days after due before auto-suspend
$profile->auto_suspend;  // bool
```

### Generating Invoices

```php
use App\Services\BillingService;

$billingService->generateInvoice(User $customer): Invoice;
$billingService->generateMonthlyInvoices(): void;   // Typically called by scheduler
$billingService->generateDailyInvoices(): void;     // For daily prepaid customers
```

### Recording Payments

```php
use App\Services\PaymentGatewayService;

// Record manual payment
$paymentService->recordManualPayment(Invoice $invoice, array $data): Payment;

// Process online payment
$paymentService->initiatePayment(Invoice $invoice, string $gateway): array;

// Webhook callback processing
$paymentService->processWebhook(string $gateway, array $data): void;
```

### Package Pricing Hierarchy

When determining the price a customer pays:

```
MasterPackage.price (Admin-level base)
    → Package.price (Operator-level, may override)
        → OperatorPackageRate.custom_price (per-operator custom price)
            → CustomPrice.price (per-customer override, highest priority)
```

```php
use App\Services\PackageHierarchyService;

$effectivePrice = $packageHierarchyService->resolvePrice(
    package: $package,
    operator: $operator,
    customer: $customer
);
```

### Commission Calculation

```php
use App\Services\CommissionService;

// Calculate and record commission on payment
$commissionService->calculateCommissions(Payment $payment): void;

// Get pending commissions for an operator
$commissions = $commissionService->getPendingCommissions($operatorId);

// Pay out pending commissions
$commissionService->payPendingCommissions($operatorId);
```

---

## 9. Writing Tests

### Test Setup

All tests extend `Tests\TestCase` which sets up the SQLite in-memory database.

```php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_example(): void
    {
        $admin = User::factory()->create(['operator_level' => 20]);

        $response = $this->actingAs($admin)
            ->get(route('panel.admin.dashboard'));

        $response->assertOk();
    }
}
```

### Factory Usage

```php
// Create a tenant
$tenant = Tenant::factory()->create();

// Create an admin for that tenant
$admin = User::factory()->create([
    'tenant_id' => $tenant->id,
    'operator_level' => 20,
]);

// Create a customer (subscriber)
$customer = User::factory()->create([
    'tenant_id' => $tenant->id,
    'is_subscriber' => true,
    'operator_level' => null,
]);

// Create a package
$package = Package::factory()->create([
    'tenant_id' => $tenant->id,
]);
```

### Testing with RADIUS

For tests involving RADIUS operations, use the `radius` connection with SQLite:

```php
// In .env.testing
DB_RADIUS_CONNECTION=sqlite
DB_RADIUS_DATABASE=:memory:
```

```php
// Test RADIUS provisioning
public function test_customer_provisioned_to_radius(): void
{
    $customer = User::factory()->subscriber()->create();

    $this->radiusService->provisionUser($customer);

    $this->assertDatabaseHas('radcheck', [
        'username' => $customer->username,
    ], 'radius');
}
```

### Testing Tenant Isolation

```php
public function test_operator_cannot_see_other_tenant_data(): void
{
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    $admin1 = User::factory()->create(['tenant_id' => $tenant1->id, 'operator_level' => 20]);
    $package2 = Package::factory()->create(['tenant_id' => $tenant2->id]);

    // Simulate tenant1 context
    app(TenancyService::class)->setCurrentTenant($tenant1);

    $this->actingAs($admin1)
        ->get(route('packages.show', $package2))
        ->assertNotFound();
}
```

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/BillingServiceTest.php

# Run with coverage
php artisan test --coverage

# Static analysis
vendor/bin/phpstan analyse

# Code style check
vendor/bin/pint --test
```

---

## 10. Common Development Tasks

### Creating an Artisan Command

```bash
php artisan make:command SuspendExpiredHotspotUsers
```

```php
// app/Console/Commands/SuspendExpiredHotspotUsers.php
class SuspendExpiredHotspotUsers extends Command
{
    protected $signature = 'hotspot:suspend-expired {--dry-run : Preview without making changes}';
    protected $description = 'Suspend expired hotspot users';

    public function handle(BillingService $billing): int
    {
        $users = HotspotUser::where('expires_at', '<', now())->get();

        foreach ($users as $user) {
            if (!$this->option('dry-run')) {
                $billing->suspendUser($user);
            }
            $this->line("Would suspend: {$user->username}");
        }

        return Command::SUCCESS;
    }
}
```

Register in `routes/console.php` for scheduling:

```php
Schedule::command('hotspot:suspend-expired')->daily();
```

### Adding a Queue Job

```bash
php artisan make:job SendSuspensionSmsJob
```

```php
// app/Jobs/SendSuspensionSmsJob.php
class SendSuspensionSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private User $user) {}

    public function handle(SmsService $sms): void
    {
        $sms->send(
            $this->user->phone,
            "Your account has been suspended. Please contact support."
        );
    }
}

// Dispatch the job
SendSuspensionSmsJob::dispatch($user);

// Dispatch with delay
SendSuspensionSmsJob::dispatch($user)->delay(now()->addMinutes(5));
```

### Adding a New API Endpoint

```php
// routes/api.php
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::get('network-links', [NetworkLinkApiController::class, 'index']);
    Route::post('network-links', [NetworkLinkApiController::class, 'store']);
});
```

```php
// app/Http/Controllers/Api/V1/NetworkLinkApiController.php
class NetworkLinkApiController extends Controller
{
    public function index(): JsonResponse
    {
        $links = NetworkLink::with(['fromDevice', 'toDevice'])->paginate(20);
        return response()->json($links);
    }
}
```

### Adding a Migration

```bash
php artisan make:migration add_notes_to_network_links_table
```

```php
public function up(): void
{
    Schema::table('network_links', function (Blueprint $table) {
        $table->text('notes')->nullable()->after('status');
    });
}

public function down(): void
{
    Schema::table('network_links', function (Blueprint $table) {
        $table->dropColumn('notes');
    });
}
```

### Adding SMS Notifications

```php
use App\Services\SmsService;

class CustomerService
{
    public function __construct(private SmsService $sms) {}

    public function sendExpirationWarning(User $customer): void
    {
        $this->sms->sendToCustomer(
            $customer,
            template: 'pre_expiration',
            variables: ['days_remaining' => 3]
        );
    }
}
```

---

## 11. Debugging Guide

### Application Logs

```bash
# Tail live logs
tail -f storage/logs/laravel.log

# With Docker
docker-compose exec app tail -f storage/logs/laravel.log

# With make
make logs
```

### Common Issues

**RADIUS connection fails:**
```bash
# Check RADIUS DB container
docker-compose ps radius-db

# Test connection from app container
docker-compose exec app php artisan radius:install --check

# Verify .env settings
grep DB_RADIUS .env
```

**MikroTik API connection fails:**
```bash
# Test connectivity
php artisan mikrotik:health-check --router=1

# Check router API service is enabled
# On router: /ip service print | where name=api
# Should show: api enabled port=8728
```

**Queue jobs not processing:**
```bash
# Start queue worker manually
php artisan queue:work --tries=3 -v

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

**Tenant resolution fails (404 on all routes):**
```bash
# Check tenant exists for this domain
php artisan tinker
>>> App\Models\Tenant::where('domain', 'localhost')->first()
```

**Permission denied errors:**
```bash
# Fix storage permissions
chmod -R 775 storage/
chown -R www-data:www-data storage/

# Clear all caches
php artisan optimize:clear
```

### Debugging in Tinker

```bash
php artisan tinker

# Test billing service
>>> $billing = app(App\Services\BillingService::class)
>>> $user = App\Models\User::find(1)
>>> $billing->generateInvoice($user)

# Check RADIUS user
>>> App\Models\RadCheck::where('username', 'john_doe')->get()

# Test MikroTik connection
>>> $api = app(App\Services\MikrotikApiService::class)
>>> $router = App\Models\MikrotikRouter::find(1)
>>> $api->connect($router)->query('/ppp/active/print')->read()
```

### Using Laravel Pail (Enhanced Logging)

```bash
# Stream all log events in real-time
php artisan pail

# Filter by level
php artisan pail --level=error

# Filter by message
php artisan pail --filter="RADIUS"
```

---

## 12. Code Conventions

### Naming Conventions

| Type | Convention | Example |
|------|-----------|---------|
| Classes | PascalCase | `BillingService` |
| Methods | camelCase | `generateInvoice()` |
| Variables | camelCase | `$activeCustomers` |
| Constants | UPPER_SNAKE_CASE | `OPERATOR_LEVEL_ADMIN` |
| DB tables | snake_case plural | `billing_profiles` |
| DB columns | snake_case | `tenant_id` |
| Routes | kebab-case | `network-links` |
| Route names | dot notation | `admin.network-links.index` |
| Blade files | kebab-case | `network-link-form.blade.php` |
| Blade dirs | kebab-case | `panels/admin/network-links/` |

### Service Pattern

Services encapsulate business logic. Controllers should be thin:

```php
// ✅ Good: Thin controller
class CustomerController extends Controller
{
    public function store(StoreCustomerRequest $request, CustomerCreationService $service)
    {
        $customer = $service->create($request->validated());
        return redirect()->route('customers.show', $customer);
    }
}

// ❌ Bad: Fat controller with business logic
class CustomerController extends Controller
{
    public function store(Request $request)
    {
        $user = User::create([...]);
        RadCheck::create([...]);
        $this->sendSms($user->phone, "Welcome!");
        // 50 more lines...
    }
}
```

### Tenant Safety

All new models that belong to a tenant MUST use `BelongsToTenant`:

```php
// ✅ Required for all tenant-scoped models
class NetworkLink extends Model
{
    use BelongsToTenant; // Always include this

    protected $fillable = [
        'tenant_id',    // Always include in fillable
        'operator_id',  // Always include for sub-tenancy
        ...
    ];
}
```

### Error Handling

```php
// Use specific exceptions
use App\Exceptions\MikrotikConnectionException;
use App\Exceptions\RadiusProvisioningException;

try {
    $this->mikrotik->connect($router);
} catch (MikrotikConnectionException $e) {
    Log::error("Router {$router->id} connection failed", ['error' => $e->getMessage()]);
    throw $e; // Re-throw or return graceful error
}
```

### Commit Messages

Follow Conventional Commits format:

```
feat(billing): add auto-debit payment processing
fix(radius): handle null IP pool assignment gracefully
docs: update WALKTHROUGH.md with OLT section
refactor(services): consolidate PDF services into DocumentExportService
test(billing): add coverage for commission hierarchy calculation
chore(deps): update axios to 1.12.0
```

---

## 13. Architecture Decision Records

### ADR-001: Separate RADIUS Database

**Decision:** Use a separate MySQL instance for FreeRADIUS data.  
**Reason:** FreeRADIUS requires a specific schema it manages directly. Keeping it separate prevents conflicts with Laravel migrations and allows FreeRADIUS to be upgraded independently.  
**Implementation:** `DB_RADIUS_*` env vars, `radius` database connection, RADIUS models use `protected $connection = 'radius'`.

### ADR-002: `is_subscriber` Flag for Customers

**Decision:** Customers use `is_subscriber = true` in the `users` table instead of `operator_level = 100`.  
**Reason:** Customers are external subscribers, not part of the internal admin hierarchy. A boolean flag cleanly separates them. The `operator_level` field should only contain hierarchy levels (0–80) for staff.  
**Migration:** `2026_01_30_200800_add_is_subscriber_to_users_table.php`

### ADR-003: BelongsToTenant Global Scope

**Decision:** Use a global Eloquent scope via trait for tenant isolation rather than per-query scoping.  
**Reason:** Developer ergonomics — avoids accidental data leaks from forgetting to scope queries. The trait makes tenant isolation the default, opt-out behavior.  
**Risk:** Global scopes can be surprising in admin/developer contexts. Use `allTenants()` to bypass when needed.

### ADR-004: Dual View Directories

**Decision (legacy):** `resources/views/panel/` was used initially; `resources/views/panels/` is now canonical.  
**Current state:** Both exist. `panels/` is organized by role; `panel/` has feature-specific views.  
**Resolution plan:** Gradually migrate `panel/` views into `panels/` with appropriate role directories. No immediate breaking change needed.

### ADR-005: Service-Oriented Architecture

**Decision:** Business logic lives in `app/Services/`, not in controllers or models.  
**Reason:** Controllers should handle HTTP (request parsing, response formatting). Models should handle DB persistence. Services handle business rules.  
**Guidance:** If a method is more than 10 lines in a controller, it should be in a service.

### ADR-006: Dual MikroTik API Services

**Current state:** Both `MikrotikService` (35 methods) and `MikrotikApiService` (15 methods) exist, plus legacy `RouterosAPI` wrapper.  
**Intended resolution:** `MikrotikApiService` should be the canonical interface. `RouterosAPI` should be removed. `MikrotikService` should delegate to `MikrotikApiService`.  
**Status:** Outstanding — tracked in `FINAL_TODO.md` under Code Quality.

---

*This walkthrough was generated from a deep investigation of the repository on 2026-03-24. For updates, see `CHANGELOG.md`.*
