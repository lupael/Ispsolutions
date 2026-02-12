<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    /** @test */
    public function it_can_generate_an_invoice_as_a_pdf()
    {
        // 1. Create a customer and an invoice
        $customer = User::factory()->create(['is_subscriber' => true]);
        $invoice = Invoice::factory()->create(['user_id' => $customer->id]);

        // 2. Call the endpoint to download the invoice as a PDF
        $response = $this->actingAs($customer)->get(route('customer.invoices.pdf.download', $invoice));

        // 3. Assert that the response is successful and has the correct headers
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function it_can_generate_an_invoice_as_an_excel_file()
    {
        // 1. Create a customer and an invoice
        $customer = User::factory()->create(['is_subscriber' => true]);
        $invoice = Invoice::factory()->create(['user_id' => $customer->id]);

        // 2. Call the endpoint to download the invoice as an Excel file
        $response = $this->actingAs($customer)->get(route('customer.invoices.excel.download', $invoice));

        // 3. Assert that the response is successful and has the correct headers
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }
}