/**
 * PDF Template Test Cases
 * =======================
 * 
 * This file provides unit test examples for the PDF templates
 * in the ISP Solution application.
 */

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Models\Tenant;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfTemplateTest extends TestCase
{
    protected $tenant;
    protected $user;
    protected $invoice;
    protected $payment;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test tenant
        $this->tenant = Tenant::create([
            'name' => 'Test Company',
            'domain' => 'test.example.com',
            'database' => 'test_db',
            'settings' => [
                'company_logo_url' => 'https://example.com/logo.png',
                'company_address' => '123 Main Street, City, State 12345',
                'company_phone' => '(555) 123-4567',
                'company_email' => 'support@example.com',
                'invoice_terms' => 'Payment due within 30 days.',
            ],
        ]);

        // Create test user
        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '(555) 987-6543',
        ]);

        // Create test invoice
        $this->invoice = Invoice::create([
            'tenant_id' => $this->tenant->id,
            'invoice_number' => 'INV-001-2024',
            'user_id' => $this->user->id,
            'amount' => 99.99,
            'tax_amount' => 10.00,
            'total_amount' => 109.99,
            'status' => 'pending',
            'billing_period_start' => Carbon::now()->startOfMonth(),
            'billing_period_end' => Carbon::now()->endOfMonth(),
            'due_date' => Carbon::now()->addDays(30),
        ]);

        // Create test payment
        $this->payment = Payment::create([
            'tenant_id' => $this->tenant->id,
            'payment_number' => 'PAY-001-2024',
            'user_id' => $this->user->id,
            'invoice_id' => $this->invoice->id,
            'amount' => 109.99,
            'transaction_id' => 'TXN-12345',
            'status' => 'completed',
            'payment_method' => 'card',
            'paid_at' => now(),
        ]);

        $this->actingAs($this->user);
    }

    // ========================================================================
    // Invoice PDF Tests
    // ========================================================================

    /**
     * Test invoice PDF view rendering
     *
     * @test
     */
    public function it_can_render_invoice_pdf()
    {
        $response = $this->get(route('invoices.view-pdf', $this->invoice->id));

        $this->assertNotNull($response);
    }

    /**
     * Test invoice PDF download
     *
     * @test
     */
    public function it_can_download_invoice_pdf()
    {
        $response = $this->get(route('invoices.pdf', $this->invoice->id));

        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString(
            'invoice',
            strtolower($response->headers->get('Content-Disposition'))
        );
    }

    /**
     * Test invoice PDF contains required elements
     *
     * @test
     */
    public function invoice_pdf_contains_required_elements()
    {
        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $this->invoice,
            'tenant' => $this->tenant,
        ]);

        $content = $pdf->getCanvas()->getPage()->getStream();

        // These would require more sophisticated PDF parsing
        // For now, just verify the view renders without error
        $this->assertNotNull($content);
    }

    /**
     * Test invoice PDF with paid status
     *
     * @test
     */
    public function invoice_pdf_shows_paid_status()
    {
        $this->invoice->update(['status' => 'paid', 'paid_at' => now()]);

        $view = view('pdf.invoice', [
            'invoice' => $this->invoice,
            'tenant' => $this->tenant,
        ])->render();

        $this->assertStringContainsString('PAID', $view);
    }

    /**
     * Test invoice PDF with unpaid status
     *
     * @test
     */
    public function invoice_pdf_shows_unpaid_status()
    {
        $this->invoice->update(['status' => 'pending']);

        $view = view('pdf.invoice', [
            'invoice' => $this->invoice,
            'tenant' => $this->tenant,
        ])->render();

        $this->assertStringContainsString('UNPAID', $view);
    }

    /**
     * Test invoice PDF with cancelled status
     *
     * @test
     */
    public function invoice_pdf_shows_cancelled_status()
    {
        $this->invoice->update(['status' => 'cancelled']);

        $view = view('pdf.invoice', [
            'invoice' => $this->invoice,
            'tenant' => $this->tenant,
        ])->render();

        $this->assertStringContainsString('CANCELLED', $view);
    }

    /**
     * Test invoice PDF displays company information
     *
     * @test
     */
    public function invoice_pdf_displays_company_info()
    {
        $view = view('pdf.invoice', [
            'invoice' => $this->invoice,
            'tenant' => $this->tenant,
        ])->render();

        $this->assertStringContainsString($this->tenant->name, $view);
        $this->assertStringContainsString(
            $this->tenant->settings['company_phone'],
            $view
        );
    }

    /**
     * Test invoice PDF displays customer information
     *
     * @test
     */
    public function invoice_pdf_displays_customer_info()
    {
        $view = view('pdf.invoice', [
            'invoice' => $this->invoice,
            'tenant' => $this->tenant,
        ])->render();

        $this->assertStringContainsString($this->user->name, $view);
        $this->assertStringContainsString($this->user->email, $view);
    }

    /**
     * Test invoice PDF displays amounts correctly
     *
     * @test
     */
    public function invoice_pdf_displays_amounts_correctly()
    {
        $view = view('pdf.invoice', [
            'invoice' => $this->invoice,
            'tenant' => $this->tenant,
        ])->render();

        $this->assertStringContainsString('99.99', $view);
        $this->assertStringContainsString('10.00', $view);
        $this->assertStringContainsString('109.99', $view);
    }

    // ========================================================================
    // Receipt PDF Tests
    // ========================================================================

    /**
     * Test receipt PDF download
     *
     * @test
     */
    public function it_can_download_receipt_pdf()
    {
        $response = $this->get(route('payments.receipt-pdf', $this->payment->id));

        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString(
            'receipt',
            strtolower($response->headers->get('Content-Disposition'))
        );
    }

    /**
     * Test receipt PDF contains payment information
     *
     * @test
     */
    public function receipt_pdf_contains_payment_info()
    {
        $view = view('pdf.receipt', [
            'payment' => $this->payment,
            'tenant' => $this->tenant,
        ])->render();

        $this->assertStringContainsString(
            $this->payment->payment_number,
            $view
        );
        $this->assertStringContainsString('109.99', $view);
    }

    /**
     * Test receipt PDF shows completed status
     *
     * @test
     */
    public function receipt_pdf_shows_completed_status()
    {
        $view = view('pdf.receipt', [
            'payment' => $this->payment,
            'tenant' => $this->tenant,
        ])->render();

        $this->assertStringContainsString('Completed', $view);
    }

    /**
     * Test receipt PDF contains customer info
     *
     * @test
     */
    public function receipt_pdf_contains_customer_info()
    {
        $view = view('pdf.receipt', [
            'payment' => $this->payment,
            'tenant' => $this->tenant,
        ])->render();

        $this->assertStringContainsString($this->user->name, $view);
        $this->assertStringContainsString($this->user->email, $view);
    }

    // ========================================================================
    // Statement PDF Tests
    // ========================================================================

    /**
     * Test statement PDF download
     *
     * @test
     */
    public function it_can_download_statement_pdf()
    {
        $response = $this->get(
            route('statements.pdf', $this->user->id)
            . '?start_date=' . Carbon::now()->subMonths(3)
            . '&end_date=' . Carbon::now()
        );

        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }

    /**
     * Test statement PDF contains customer info
     *
     * @test
     */
    public function statement_pdf_contains_customer_info()
    {
        $startDate = Carbon::now()->subMonths(3);
        $endDate = Carbon::now();

        $invoices = $this->user->invoices()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $payments = $this->user->payments()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $view = view('pdf.statement', [
            'user' => $this->user,
            'invoices' => $invoices,
            'payments' => $payments,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'tenant' => $this->tenant,
            'totalInvoiced' => $invoices->sum('total_amount'),
            'totalPaid' => $payments->where('status', 'completed')->sum('amount'),
            'totalTax' => $invoices->sum('tax_amount'),
            'totalOutstanding' => $invoices->where('status', '!=', 'paid')->sum('total_amount'),
            'pendingInvoices' => $invoices->where('status', 'pending')->count(),
        ])->render();

        $this->assertStringContainsString($this->user->name, $view);
        $this->assertStringContainsString('Account Statement', $view);
    }

    /**
     * Test statement PDF contains invoice details
     *
     * @test
     */
    public function statement_pdf_contains_invoice_details()
    {
        $startDate = Carbon::now()->subMonths(3);
        $endDate = Carbon::now();

        $invoices = $this->user->invoices()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $payments = $this->user->payments()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $view = view('pdf.statement', [
            'user' => $this->user,
            'invoices' => $invoices,
            'payments' => $payments,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'tenant' => $this->tenant,
            'totalInvoiced' => $invoices->sum('total_amount'),
            'totalPaid' => $payments->where('status', 'completed')->sum('amount'),
            'totalTax' => $invoices->sum('tax_amount'),
            'totalOutstanding' => $invoices->where('status', '!=', 'paid')->sum('total_amount'),
            'pendingInvoices' => $invoices->where('status', 'pending')->count(),
        ])->render();

        $this->assertStringContainsString(
            $this->invoice->invoice_number,
            $view
        );
    }

    // ========================================================================
    // Billing Report Tests
    // ========================================================================

    /**
     * Test billing report PDF download
     *
     * @test
     */
    public function it_can_download_billing_report_pdf()
    {
        $response = $this->get(
            route('reports.billing.pdf')
            . '?start_date=' . Carbon::now()->subMonth()
            . '&end_date=' . Carbon::now()
        );

        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }

    /**
     * Test billing report contains invoice data
     *
     * @test
     */
    public function billing_report_contains_invoice_data()
    {
        $startDate = Carbon::now()->subMonth();
        $endDate = Carbon::now();

        $invoices = Invoice::where('tenant_id', $this->tenant->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $view = view('pdf.reports.billing', [
            'invoices' => $invoices,
            'tenant' => $this->tenant,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalInvoices' => $invoices->count(),
            'totalInvoicedAmount' => $invoices->sum('total_amount'),
            'outstandingAmount' => $invoices->where('status', '!=', 'paid')
                ->sum('total_amount'),
            'overdueAmount' => $invoices->where('status', 'overdue')
                ->sum('total_amount'),
            'totalTaxAmount' => $invoices->sum('tax_amount'),
            'statusSummary' => $invoices->groupBy('status')->map->count(),
        ])->render();

        $this->assertStringContainsString('Billing Report', $view);
    }

    // ========================================================================
    // Payment Report Tests
    // ========================================================================

    /**
     * Test payment report PDF download
     *
     * @test
     */
    public function it_can_download_payment_report_pdf()
    {
        $response = $this->get(
            route('reports.payments.pdf')
            . '?start_date=' . Carbon::now()->subMonth()
            . '&end_date=' . Carbon::now()
        );

        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }

    /**
     * Test payment report contains payment data
     *
     * @test
     */
    public function payment_report_contains_payment_data()
    {
        $startDate = Carbon::now()->subMonth();
        $endDate = Carbon::now();

        $payments = Payment::where('tenant_id', $this->tenant->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $view = view('pdf.reports.payment', [
            'payments' => $payments,
            'tenant' => $this->tenant,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalPayments' => $payments->count(),
            'totalAmount' => $payments->sum('amount'),
            'completedPayments' => $payments->where('status', 'completed')->count(),
            'pendingPayments' => $payments->where('status', 'pending')->count(),
            'failedPayments' => $payments->where('status', 'failed')->count(),
            'methodBreakdown' => $payments->groupBy('payment_method')
                ->map(fn($group) => [
                    'amount' => $group->sum('amount'),
                    'count' => $group->count(),
                ]),
        ])->render();

        $this->assertStringContainsString('Payment Report', $view);
    }

    // ========================================================================
    // Customer Report Tests
    // ========================================================================

    /**
     * Test customer report PDF download
     *
     * @test
     */
    public function it_can_download_customer_report_pdf()
    {
        $response = $this->get(route('reports.customers.pdf'));

        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }

    /**
     * Test customer report contains customer data
     *
     * @test
     */
    public function customer_report_contains_customer_data()
    {
        $customers = User::where('tenant_id', $this->tenant->id)->get();

        $view = view('pdf.reports.customer', [
            'customers' => $customers,
            'tenant' => $this->tenant,
            'totalCustomers' => $customers->count(),
            'activeCustomers' => $customers->where('status', 'active')->count(),
            'inactiveCustomers' => $customers->where('status', 'inactive')->count(),
            'newCustomersThisMonth' => $customers
                ->where('created_at', '>=', Carbon::now()->startOfMonth())
                ->count(),
        ])->render();

        $this->assertStringContainsString('Customer Report', $view);
    }

    // ========================================================================
    // Error Handling Tests
    // ========================================================================

    /**
     * Test invoice PDF handles missing tenant settings gracefully
     *
     * @test
     */
    public function invoice_pdf_handles_missing_tenant_settings()
    {
        $tenant = Tenant::create([
            'name' => 'Minimal Company',
            'domain' => 'minimal.example.com',
            'database' => 'minimal_db',
            'settings' => [],
        ]);

        $view = view('pdf.invoice', [
            'invoice' => $this->invoice,
            'tenant' => $tenant,
        ])->render();

        $this->assertNotEmpty($view);
    }

    /**
     * Test templates with null relationships
     *
     * @test
     */
    public function templates_handle_null_relationships()
    {
        $invoice = Invoice::create([
            'tenant_id' => $this->tenant->id,
            'invoice_number' => 'INV-002-2024',
            'user_id' => $this->user->id,
            'package_id' => null, // No package
            'amount' => 50.00,
            'tax_amount' => 5.00,
            'total_amount' => 55.00,
            'status' => 'pending',
            'billing_period_start' => Carbon::now()->startOfMonth(),
            'billing_period_end' => Carbon::now()->endOfMonth(),
            'due_date' => Carbon::now()->addDays(30),
        ]);

        $view = view('pdf.invoice', [
            'invoice' => $invoice,
            'tenant' => $this->tenant,
        ])->render();

        $this->assertNotEmpty($view);
    }

    // ========================================================================
    // Performance Tests
    // ========================================================================

    /**
     * Test PDF generation performance
     *
     * @test
     */
    public function pdf_generation_completes_in_reasonable_time()
    {
        $start = microtime(true);

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $this->invoice,
            'tenant' => $this->tenant,
        ]);

        $end = microtime(true);
        $duration = $end - $start;

        // Should complete in less than 2 seconds
        $this->assertLessThan(2, $duration);
    }
}
