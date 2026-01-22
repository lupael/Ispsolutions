<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateBillingReportJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $tenantId,
        public string $reportType,
        public array $parameters = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Generating billing report', [
                'tenant_id' => $this->tenantId,
                'report_type' => $this->reportType,
                'parameters' => $this->parameters,
            ]);

            $financialReportService = app(\App\Services\FinancialReportService::class);

            $reportData = match ($this->reportType) {
                'monthly_revenue' => $financialReportService->generateRevenueByServiceReport(
                    $this->parameters['start_date'] ?? now()->startOfMonth(),
                    $this->parameters['end_date'] ?? now()->endOfMonth()
                ),
                'income_statement' => $financialReportService->generateIncomeStatement(
                    $this->parameters['start_date'] ?? now()->startOfMonth(),
                    $this->parameters['end_date'] ?? now()->endOfMonth()
                ),
                'balance_sheet' => $financialReportService->generateBalanceSheet(
                    $this->parameters['date'] ?? now()
                ),
                'cash_flow' => $financialReportService->generateCashFlowStatement(
                    $this->parameters['start_date'] ?? now()->startOfMonth(),
                    $this->parameters['end_date'] ?? now()->endOfMonth()
                ),
                'vat_report' => $financialReportService->generateVATReport(
                    $this->parameters['start_date'] ?? now()->startOfMonth(),
                    $this->parameters['end_date'] ?? now()->endOfMonth()
                ),
                'ar_aging' => $financialReportService->generateARAgingReport(
                    $this->parameters['date'] ?? now()
                ),
                default => throw new \Exception("Unknown report type: {$this->reportType}")
            };

            // Store report data or send notification
            if (isset($this->parameters['user_email'])) {
                // Send report via email
                Log::info('Billing report would be emailed', [
                    'email' => $this->parameters['user_email'],
                    'report_type' => $this->reportType,
                ]);
            }

            Log::info('Billing report generated successfully', [
                'tenant_id' => $this->tenantId,
                'report_type' => $this->reportType,
                'records_count' => is_array($reportData) ? count($reportData) : 'N/A',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to generate billing report', [
                'tenant_id' => $this->tenantId,
                'report_type' => $this->reportType,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?\Throwable $exception): void
    {
        Log::error('GenerateBillingReportJob failed permanently', [
            'tenant_id' => $this->tenantId,
            'report_type' => $this->reportType,
            'error' => $exception?->getMessage(),
        ]);
    }
}
