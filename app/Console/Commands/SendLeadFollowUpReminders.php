<?php

namespace App\Console\Commands;

use App\Services\LeadService;
use Illuminate\Console\Command;

class SendLeadFollowUpReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'leads:send-follow-up-reminders {--tenant-id=}';

    /**
     * The console command description.
     */
    protected $description = 'Send follow-up reminders for leads that need attention';

    /**
     * Execute the console command.
     */
    public function handle(LeadService $leadService): int
    {
        $this->info('Checking for leads requiring follow-up...');

        try {
            $tenantIds = $this->option('tenant-id')
                ? [$this->option('tenant-id')]
                : \App\Models\Tenant::pluck('id')->toArray();

            if (empty($tenantIds)) {
                $this->warn('No tenants found. Skipping follow-up reminder processing.');

                return Command::SUCCESS;
            }

            $totalOverdue = 0;
            $totalToday = 0;

            foreach ($tenantIds as $tenantId) {
                $overdueLeads = $leadService->getOverdueFollowUps($tenantId);
                $todayLeads = $leadService->getTodayFollowUps($tenantId);

                $totalOverdue += $overdueLeads->count();
                $totalToday += $todayLeads->count();

                // Log or send notifications for these leads
                foreach ($overdueLeads as $lead) {
                    $this->warn("Overdue: {$lead->name} - Follow-up date: {$lead->next_follow_up_date}");
                }

                foreach ($todayLeads as $lead) {
                    $this->info("Today: {$lead->name} - Follow-up date: {$lead->next_follow_up_date}");
                }
            }

            $this->info("Found {$totalOverdue} overdue and {$totalToday} today's follow-ups.");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to process lead follow-ups: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
