<?php

namespace Tests\Feature;

use App\Exports\BillingReportExport;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class BillingReportExportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function billing_report_export_contains_payment_method_column()
    {
        Excel::fake();

        $user = User::factory()->create();
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'payment_method' => 'credit_card',
        ]);

        $invoices = Invoice::with('payments')->get();

        $export = new BillingReportExport($invoices);

        $sheets = $export->sheets();
        $invoicesSheet = $sheets[1];
        $array = $invoicesSheet->array();

        $this->assertEquals('Payment Method', $array[0][13]);
        $this->assertEquals('credit_card', $array[1][13]);
    }
}
