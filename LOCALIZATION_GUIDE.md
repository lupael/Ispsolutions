# Localization Guide

## Overview

This guide explains how to add and manage multi-language support in the ISP Solution platform. The system currently supports English (en) and Bengali (bn), with the ability to add more languages.

## Architecture

### Language Files

Language files are located in the `lang/` directory:

```
lang/
├── en/           # English translations
│   ├── billing.php
│   ├── customer.php
│   ├── package.php
│   └── ...
└── bn/           # Bengali translations
    ├── billing.php
    ├── customer.php
    ├── package.php
    └── ...
```

### Database Support

The `users` table includes a `language` column that stores each user's preferred language:

```php
$table->string('language', 5)->default('en');
```

### Middleware

The `SetLocale` middleware automatically sets the application locale based on:
1. User's saved language preference (from database)
2. Session locale
3. Browser Accept-Language header
4. System default ('en')

## Adding a New Language

### Step 1: Create Language Directory

Create a new directory in `lang/` for your language code (ISO 639-1):

```bash
mkdir lang/es  # For Spanish
mkdir lang/fr  # For French
mkdir lang/ar  # For Arabic
```

### Step 2: Copy Base Translation Files

Copy all files from `lang/en/` to your new language directory:

```bash
cp -r lang/en/* lang/es/
```

### Step 3: Translate Content

Edit each PHP file in your language directory:

**Example: `lang/es/billing.php`**

```php
<?php

return [
    'paid' => 'Pagado',
    'unpaid' => 'No pagado',
    'due_date' => 'Fecha de vencimiento',
    'invoice' => 'Factura',
    'amount' => 'Cantidad',
    'payment_method' => 'Método de pago',
    'status' => 'Estado',
    // ... more translations
];
```

### Step 4: Add to Language Switcher

Update the language switcher component to include your new language:

**File: `resources/views/components/language-switcher.blade.php`**

```blade
<select name="language" onchange="switchLanguage(this.value)">
    <option value="en">English</option>
    <option value="bn">বাংলা</option>
    <option value="es">Español</option>  <!-- Add your language -->
</select>
```

### Step 5: Test the Translation

1. Change your user language in settings
2. Verify all translated strings appear correctly
3. Test date formatting with the new locale
4. Check RTL support if applicable (Arabic, Hebrew, etc.)

## Translation Best Practices

### 1. Use Translation Keys

Always use translation keys instead of hardcoded text:

**❌ Bad:**
```blade
<p>Customer is active</p>
```

**✅ Good:**
```blade
<p>{{ __('customer.is_active') }}</p>
```

### 2. Use Placeholders for Dynamic Content

**Translation file:**
```php
'welcome_message' => 'Welcome, :name!',
'days_remaining' => ':count days remaining',
```

**Usage:**
```blade
{{ __('customer.welcome_message', ['name' => $customer->name]) }}
{{ __('customer.days_remaining', ['count' => $daysLeft]) }}
```

### 3. Handle Pluralization

Laravel provides pluralization helpers:

**Translation file:**
```php
'customers' => '{0} No customers|{1} One customer|[2,*] :count customers',
```

**Usage:**
```blade
{{ trans_choice('customer.customers', $count) }}
```

### 4. Organize by Feature

Keep translations organized by feature/module:

```
lang/en/
├── billing.php      # Billing-related translations
├── customer.php     # Customer-related translations
├── package.php      # Package-related translations
├── auth.php         # Authentication translations
├── validation.php   # Validation messages
└── common.php       # Common/shared translations
```

### 5. Use Consistent Naming

Follow a consistent naming convention:

```php
// Entity name
'customer' => 'Customer',
'customers' => 'Customers',

// Actions
'create_customer' => 'Create Customer',
'edit_customer' => 'Edit Customer',
'delete_customer' => 'Delete Customer',

// Status messages
'customer_created' => 'Customer created successfully',
'customer_updated' => 'Customer updated successfully',
'customer_deleted' => 'Customer deleted successfully',

// Error messages
'customer_not_found' => 'Customer not found',
'customer_creation_failed' => 'Failed to create customer',
```

## Using Translations in Code

### Blade Templates

```blade
{{-- Simple translation --}}
{{ __('billing.paid') }}

{{-- With parameters --}}
{{ __('billing.amount_due', ['amount' => $invoice->amount]) }}

{{-- Pluralization --}}
{{ trans_choice('package.customers', $count) }}

{{-- Check if translation exists --}}
@if (Lang::has('billing.custom_message'))
    {{ __('billing.custom_message') }}
@endif
```

### Controllers

```php
use Illuminate\Support\Facades\Lang;

// Simple translation
$message = __('customer.created_successfully');

// With parameters
$message = __('customer.welcome', ['name' => $customer->name]);

// Get all translations for a file
$billingTranslations = Lang::get('billing');

// Check if key exists
if (Lang::has('billing.custom_key')) {
    // ...
}
```

### JavaScript/Vue.js

Expose translations to JavaScript:

```blade
<script>
    window.translations = {
        save: "{{ __('common.save') }}",
        cancel: "{{ __('common.cancel') }}",
        confirm: "{{ __('common.confirm') }}",
    };
</script>
```

Or use Laravel Mix to compile translation files:

```javascript
import lang from 'lang.js';

const messages = lang.get('billing.messages');
```

## Date Formatting with Locales

### Using Carbon

```php
use Carbon\Carbon;

// Set locale globally
Carbon::setLocale($user->language);

// Format date in user's locale
$date = Carbon::now()->translatedFormat('l, F j, Y');

// Relative time in user's locale
$timeAgo = $invoice->created_at->diffForHumans();
// Output: "3 দিন আগে" (Bengali)
// Output: "3 days ago" (English)
```

### In Blade Templates

```blade
{{-- Format with user's locale --}}
{{ $date->locale(auth()->user()->language)->translatedFormat('F j, Y') }}

{{-- Relative time --}}
{{ $date->locale(auth()->user()->language)->diffForHumans() }}
```

## Right-to-Left (RTL) Support

For RTL languages like Arabic or Hebrew:

### Step 1: Detect RTL Language

```php
// In AppServiceProvider or Middleware
$rtlLanguages = ['ar', 'he', 'fa', 'ur'];
$isRtl = in_array(app()->getLocale(), $rtlLanguages);

view()->share('isRtl', $isRtl);
```

### Step 2: Add RTL CSS

```blade
<html dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    @if($isRtl)
        <link rel="stylesheet" href="{{ asset('css/rtl.css') }}">
    @endif
</head>
```

### Step 3: Create RTL Styles

```css
/* rtl.css */
[dir="rtl"] .text-left {
    text-align: right;
}

[dir="rtl"] .ml-4 {
    margin-left: 0;
    margin-right: 1rem;
}

/* Use logical properties */
.text-start {
    text-align: start;
}

.ms-4 {
    margin-inline-start: 1rem;
}
```

## Testing Translations

### Unit Tests

```php
public function test_translation_exists()
{
    $this->assertTrue(Lang::has('billing.paid'));
}

public function test_translation_parameters()
{
    $translation = __('billing.amount_due', ['amount' => 100]);
    
    $this->assertStringContainsString('100', $translation);
}
```

### Manual Testing Checklist

- [ ] All UI text is translated
- [ ] Date formats are correct for each locale
- [ ] Number formats match locale conventions
- [ ] Currency symbols are correct
- [ ] Validation messages are translated
- [ ] Email templates are translated
- [ ] PDF documents use correct fonts for the language
- [ ] RTL layout works correctly (if applicable)

## Common Issues and Solutions

### Missing Translation Keys

If a translation key is missing, Laravel will return the key itself:

```blade
{{ __('billing.missing_key') }}
<!-- Output: "billing.missing_key" -->
```

**Solution:** Add the key to your translation file.

### Fallback Language

Configure a fallback language in `config/app.php`:

```php
'fallback_locale' => 'en',
```

### Cache Issues

If translations don't update, clear the cache:

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Font Support

Ensure your font supports all characters in your language:

```css
@font-face {
    font-family: 'Multilingual';
    src: url('/fonts/NotoSans-Regular.ttf');
}

body {
    font-family: 'Multilingual', sans-serif;
}
```

## Resources

- [Laravel Localization Documentation](https://laravel.com/docs/10.x/localization)
- [Carbon Date Formatting](https://carbon.nesbot.com/docs/#api-localization)
- [ISO 639-1 Language Codes](https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes)
- [Google Translate API](https://cloud.google.com/translate) - For initial translations

## Contributing Translations

To contribute translations to the project:

1. Fork the repository
2. Create your language directory
3. Translate all files
4. Test thoroughly
5. Submit a pull request

Please include:
- Native speaker verification
- Screenshot of the UI in your language
- List of any special requirements (fonts, RTL, etc.)

---

For questions or issues, please contact the development team or open an issue on GitHub.
