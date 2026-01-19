# PDF Templates Documentation

This directory contains professional PDF templates for ISP Solution. All templates are designed to be responsive, print-friendly, and professionally styled using Bootstrap CSS principles.

## Template Overview

### 1. Invoice Template (`invoice.blade.php`)

Professional invoice PDF template with comprehensive billing details.

**Features:**
- Company logo and information
- Customer billing details
- Itemized service billing
- Tax calculation
- Payment summary
- Watermark indicator for invoice status (Paid/Unpaid/Cancelled)
- Professional header and footer
- Terms & conditions section

**Required Variables:**
```php
[
    'invoice' => Invoice::class,  // Invoice model
    'tenant' => Tenant::class,    // Tenant/Company details
]
```

**Example Usage:**
```php
$pdf = PDF::loadView('pdf.invoice', [
    'invoice' => Invoice::find(1),
    'tenant' => auth()->user()->tenant,
]);
return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
```

**Status Indicators:**
- Paid: Green watermark with "PAID"
- Unpaid: Red watermark with "UNPAID"
- Cancelled: Gray watermark with "CANCELLED"

---

### 2. Receipt Template (`receipt.blade.php`)

Payment receipt PDF template for transaction documentation.

**Features:**
- Receipt number and date
- Payment received stamp
- Transaction details
- Payment method information
- Customer information
- Invoice reference
- Transaction ID display
- Professional footer with support information

**Required Variables:**
```php
[
    'payment' => Payment::class,  // Payment model
    'tenant' => Tenant::class,    // Tenant/Company details
]
```

**Example Usage:**
```php
$pdf = PDF::loadView('pdf.receipt', [
    'payment' => Payment::find(1),
    'tenant' => auth()->user()->tenant,
]);
return $pdf->download("receipt-{$payment->payment_number}.pdf");
```

**Status Indicators:**
- Completed: Green badge
- Pending: Yellow badge
- Failed: Red badge
- Refunded: Blue badge

---

### 3. Statement Template (`statement.blade.php`)

Comprehensive account statement showing all invoices and payments within a date range.

**Features:**
- Customer account summary
- Account statistics (total invoices, paid, outstanding, pending)
- Detailed invoice listing with running balance
- Payment history with running balance
- Summary footer with totals
- Date range filtering
- Balance indicators showing outstanding/paid status

**Required Variables:**
```php
[
    'user' => User::class,                    // Customer
    'invoices' => Collection::class,          // Invoices for period
    'payments' => Collection::class,          // Payments for period
    'startDate' => Carbon::class,             // Period start date
    'endDate' => Carbon::class,               // Period end date
    'tenant' => Tenant::class,                // Tenant/Company details
    'totalInvoiced' => float,                 // Total invoiced amount
    'totalPaid' => float,                     // Total paid amount
    'totalTax' => float,                      // Total tax amount
    'totalOutstanding' => float,              // Outstanding balance
    'pendingInvoices' => int,                 // Count of pending invoices
]
```

**Example Usage:**
```php
$startDate = Carbon::now()->subMonths(3);
$endDate = Carbon::now();
$invoices = Invoice::where('user_id', $user->id)
    ->whereBetween('created_at', [$startDate, $endDate])
    ->get();
$payments = Payment::where('user_id', $user->id)
    ->whereBetween('created_at', [$startDate, $endDate])
    ->get();

$pdf = PDF::loadView('pdf.statement', [
    'user' => $user,
    'invoices' => $invoices,
    'payments' => $payments,
    'startDate' => $startDate,
    'endDate' => $endDate,
    'tenant' => auth()->user()->tenant,
    'totalInvoiced' => $invoices->sum('total_amount'),
    'totalPaid' => $payments->where('status', 'completed')->sum('amount'),
    'totalTax' => $invoices->sum('tax_amount'),
    'totalOutstanding' => $invoices->where('status', '!=', 'paid')->sum('total_amount'),
    'pendingInvoices' => $invoices->where('status', 'pending')->count(),
]);
return $pdf->download("statement-{$user->id}-{$endDate->format('Y-m-d')}.pdf");
```

---

### 4. Billing Report Template (`reports/billing.blade.php`)

Comprehensive billing report with summary statistics and detailed invoice listing.

**Features:**
- Summary cards showing key metrics
- Invoice status breakdown
- Financial metrics summary
- Detailed invoice listing table
- Additional statistics section
- Date range filtering

**Required Variables:**
```php
[
    'invoices' => Collection::class,          // All invoices for period
    'tenant' => Tenant::class,                // Tenant/Company details
    'startDate' => Carbon::class,             // Report start date
    'endDate' => Carbon::class,               // Report end date
    'totalInvoices' => int,                   // Total invoice count
    'totalInvoicedAmount' => float,           // Total invoiced amount
    'outstandingAmount' => float,             // Outstanding balance
    'overdueAmount' => float,                 // Overdue amount
    'totalTaxAmount' => float,                // Total tax amount
    'statusSummary' => Collection::class,     // Status breakdown (optional)
    'paidInvoices' => int,                    // Count of paid invoices (optional)
    'pendingInvoices' => int,                 // Count of pending invoices (optional)
    'overdueInvoices' => int,                 // Count of overdue invoices (optional)
    'cancelledInvoices' => int,               // Count of cancelled invoices (optional)
]
```

**Example Usage:**
```php
$startDate = Carbon::now()->subMonth();
$endDate = Carbon::now();
$invoices = Invoice::whereBetween('created_at', [$startDate, $endDate])->get();

$pdf = PDF::loadView('pdf.reports.billing', [
    'invoices' => $invoices,
    'tenant' => auth()->user()->tenant,
    'startDate' => $startDate,
    'endDate' => $endDate,
    'totalInvoices' => $invoices->count(),
    'totalInvoicedAmount' => $invoices->sum('total_amount'),
    'outstandingAmount' => $invoices->where('status', '!=', 'paid')->sum('total_amount'),
    'overdueAmount' => $invoices->where('status', 'overdue')->sum('total_amount'),
    'totalTaxAmount' => $invoices->sum('tax_amount'),
    'statusSummary' => $invoices->groupBy('status')->map->count(),
    'paidInvoices' => $invoices->where('status', 'paid')->count(),
    'pendingInvoices' => $invoices->where('status', 'pending')->count(),
    'overdueInvoices' => $invoices->where('status', 'overdue')->count(),
    'cancelledInvoices' => $invoices->where('status', 'cancelled')->count(),
]);
return $pdf->download("billing-report-{$endDate->format('Y-m-d')}.pdf");
```

---

### 5. Payment Report Template (`reports/payment.blade.php`)

Payment report with summary by payment method and detailed payment listing.

**Features:**
- Summary cards showing payment metrics
- Payment status breakdown
- Payment method breakdown with amounts
- Detailed payment listing
- Financial metrics summary
- Payment method statistics

**Required Variables:**
```php
[
    'payments' => Collection::class,          // All payments for period
    'tenant' => Tenant::class,                // Tenant/Company details
    'startDate' => Carbon::class,             // Report start date
    'endDate' => Carbon::class,               // Report end date
    'totalPayments' => int,                   // Total payment count
    'totalAmount' => float,                   // Total payment amount
    'completedPayments' => int,               // Count of completed payments (optional)
    'pendingPayments' => int,                 // Count of pending payments (optional)
    'failedPayments' => int,                  // Count of failed payments (optional)
    'refundedPayments' => int,                // Count of refunded payments (optional)
    'completedAmount' => float,               // Total completed amount (optional)
    'pendingAmount' => float,                 // Total pending amount (optional)
    'failedAmount' => float,                  // Total failed amount (optional)
    'statusSummary' => Collection::class,     // Status breakdown (optional)
    'methodBreakdown' => Collection::class,   // Method breakdown with amounts/count (optional)
]
```

**Example Usage:**
```php
$startDate = Carbon::now()->subMonth();
$endDate = Carbon::now();
$payments = Payment::whereBetween('created_at', [$startDate, $endDate])->get();

$methodBreakdown = $payments->groupBy('payment_method')
    ->map(fn($group) => [
        'amount' => $group->sum('amount'),
        'count' => $group->count(),
    ]);

$pdf = PDF::loadView('pdf.reports.payment', [
    'payments' => $payments,
    'tenant' => auth()->user()->tenant,
    'startDate' => $startDate,
    'endDate' => $endDate,
    'totalPayments' => $payments->count(),
    'totalAmount' => $payments->sum('amount'),
    'completedPayments' => $payments->where('status', 'completed')->count(),
    'pendingPayments' => $payments->where('status', 'pending')->count(),
    'failedPayments' => $payments->where('status', 'failed')->count(),
    'refundedPayments' => $payments->where('status', 'refunded')->count(),
    'completedAmount' => $payments->where('status', 'completed')->sum('amount'),
    'pendingAmount' => $payments->where('status', 'pending')->sum('amount'),
    'failedAmount' => $payments->where('status', 'failed')->sum('amount'),
    'statusSummary' => $payments->groupBy('status')->map->count(),
    'methodBreakdown' => $methodBreakdown,
]);
return $pdf->download("payment-report-{$endDate->format('Y-m-d')}.pdf");
```

---

### 6. Customer Report Template (`reports/customer.blade.php`)

Customer report with summary statistics and customer listing.

**Features:**
- Summary cards showing customer metrics
- Customer status breakdown
- Customer segmentation
- Detailed customer listing
- Growth metrics
- Financial summary per customer

**Required Variables:**
```php
[
    'customers' => Collection::class,         // All customers
    'tenant' => Tenant::class,                // Tenant/Company details
    'totalCustomers' => int,                  // Total customer count
    'activeCustomers' => int,                 // Count of active customers (optional)
    'inactiveCustomers' => int,               // Count of inactive customers (optional)
    'newCustomersThisMonth' => int,           // Count of new customers this month (optional)
    'totalLifetimeValue' => float,            // Total lifetime value (optional)
    'totalOutstandingBalance' => float,       // Total outstanding balance (optional)
    'statusBreakdown' => Collection::class,   // Status breakdown (optional)
    'segmentBreakdown' => Collection::class,  // Segment breakdown (optional)
]
```

**Example Usage:**
```php
$customers = User::where('tenant_id', auth()->user()->tenant_id)->get();
$startOfMonth = Carbon::now()->startOfMonth();

$pdf = PDF::loadView('pdf.reports.customer', [
    'customers' => $customers,
    'tenant' => auth()->user()->tenant,
    'totalCustomers' => $customers->count(),
    'activeCustomers' => $customers->where('status', 'active')->count(),
    'inactiveCustomers' => $customers->where('status', 'inactive')->count(),
    'newCustomersThisMonth' => $customers->where('created_at', '>=', $startOfMonth)->count(),
    'totalLifetimeValue' => $customers->sum(fn($c) => $c->invoices()->sum('total_amount')),
    'totalOutstandingBalance' => $customers->sum(fn($c) => $c->invoices()
        ->where('status', '!=', 'paid')->sum('total_amount')),
    'statusBreakdown' => $customers->groupBy('status')->map->count(),
    'segmentBreakdown' => $customers->groupBy('segment')->map->count(),
]);
return $pdf->download("customer-report-{Carbon::now()->format('Y-m-d')}.pdf");
```

---

## Design Features

### Responsive Design
All templates use:
- CSS Grid for modern layouts
- Mobile-responsive breakpoints
- Print-optimized styling
- Professional typography

### Color Scheme
- **Primary**: #007bff (Blue) - Used for main headings and borders
- **Success**: #28a745 (Green) - Used for paid/completed status
- **Danger**: #dc3545 (Red) - Used for unpaid/failed status
- **Warning**: #ffc107 (Yellow) - Used for pending status
- **Info**: #17a2b8 (Cyan) - Used for secondary headings
- **Light**: #f8f9fa (Light Gray) - Used for backgrounds

### Print Optimization
- Fixed page sizes (A4: 210mm x 297mm)
- Optimized for both color and black & white printing
- Page break handling for long tables
- No shadow effects on print
- High contrast text colors

### Typography
- Font: System UI fonts for optimal rendering
- Base size: 12px for body text
- Headings: Bold with letter-spacing for emphasis
- Table headers: Bold uppercase with reduced size

## Tenant Configuration

Templates use the following tenant settings:
- `company_logo_url` - Company logo image URL
- `company_name` - Company name (falls back to app name)
- `company_address` - Company address
- `company_phone` - Company phone number
- `company_email` - Company email address
- `invoice_terms` - Default invoice terms and conditions

**Configure in tenant settings:**
```php
Tenant::create([
    'name' => 'Company Name',
    'settings' => [
        'company_logo_url' => 'https://example.com/logo.png',
        'company_address' => '123 Main Street, City, State 12345',
        'company_phone' => '(555) 123-4567',
        'company_email' => 'support@example.com',
        'invoice_terms' => 'Payment due within 30 days. Thank you for your business!',
    ],
]);
```

## Integration with Laravel PDF Libraries

These templates are compatible with popular PDF libraries:

### Using mPDF
```php
use Mpdf\Mpdf;

$mpdf = new Mpdf();
$html = view('pdf.invoice', ['invoice' => $invoice])->render();
$mpdf->WriteHTML($html);
$mpdf->Output('invoice.pdf', 'D');
```

### Using DomPDF
```php
use Barryvdh\DomPDF\Facade\Pdf;

$pdf = Pdf::loadView('pdf.invoice', ['invoice' => $invoice]);
return $pdf->download('invoice.pdf');
```

### Using TCPDF
```php
use Elibyy\TCPDF\TCPDF;

$tcpdf = new TCPDF();
$tcpdf->AddPage();
$html = view('pdf.invoice', ['invoice' => $invoice])->render();
$tcpdf->WriteHTML($html);
$tcpdf->Output('invoice.pdf', 'D');
```

## CSS Classes and Utilities

### Text Alignment
- `.text-left` - Left align text
- `.text-center` - Center align text
- `.text-right` - Right align text

### Status Badges
- `.status-badge.paid` - Green paid badge
- `.status-badge.pending` - Yellow pending badge
- `.status-badge.overdue` - Red overdue badge
- `.status-badge.cancelled` - Gray cancelled badge
- `.status-badge.completed` - Green completed badge
- `.status-badge.failed` - Red failed badge
- `.status-badge.refunded` - Blue refunded badge

### Utility Classes
- `.no-data` - Empty state message styling
- `.page-break` - Force page break in print
- `.watermark` - Watermark styling
- `.section-title` - Section header styling
- `.stat-card` - Statistics card styling

## Best Practices

1. **Data Validation**: Always validate and sanitize data before passing to templates
2. **Locale Formatting**: Use Carbon for date formatting and number_format() for amounts
3. **Error Handling**: Implement try-catch blocks around PDF generation
4. **Performance**: Use pagination for large datasets
5. **Security**: Authenticate users before generating PDFs
6. **Caching**: Cache generated PDFs when appropriate

## Troubleshooting

### Common Issues

**Issue: Images not showing in PDF**
- Ensure image URLs are absolute URLs
- Check image file permissions
- Verify image format is supported (JPG, PNG, GIF)

**Issue: Layout breaking across pages**
- Use `page-break-inside: avoid;` on table elements
- Ensure content fits within page margins
- Test with expected data volume

**Issue: Fonts appearing wrong**
- Use system fonts instead of Google Fonts
- Specify fallback font stack
- Test rendering in target PDF library

**Issue: Colors not printing**
- Use background-color instead of background-image
- Test with black and white printing
- Provide adequate contrast ratios

## Future Enhancements

Potential improvements for these templates:
- Multi-language support
- Custom color themes
- Barcode/QR code integration
- Chart generation
- Digital signatures
- Email integration
- Template customization UI

## License

These templates are part of the ISP Solution application and follow the same license terms.

## Support

For issues, questions, or feature requests, please contact the development team.
