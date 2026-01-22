<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LeadService
{
    /**
     * Create a new lead
     */
    public function createLead(array $data): Lead
    {
        return DB::transaction(function () use ($data) {
            $lead = Lead::create($data);

            // Log the creation activity
            $this->logActivity($lead, LeadActivity::TYPE_NOTE, 'Lead Created', 'Lead was created in the system');

            return $lead;
        });
    }

    /**
     * Update a lead
     */
    public function updateLead(Lead $lead, array $data): Lead
    {
        $oldStatus = $lead->status;

        $lead->update($data);

        // Log status change if status was updated
        if (isset($data['status']) && $data['status'] !== $oldStatus) {
            $this->logActivity(
                $lead,
                LeadActivity::TYPE_STATUS_CHANGE,
                'Status Changed',
                "Status changed from {$oldStatus} to {$data['status']}"
            );
        }

        return $lead->fresh();
    }

    /**
     * Assign lead to a user
     */
    public function assignLead(Lead $lead, int $userId): Lead
    {
        $user = User::findOrFail($userId);

        $lead->update(['assigned_to' => $userId]);

        $this->logActivity(
            $lead,
            LeadActivity::TYPE_NOTE,
            'Lead Assigned',
            "Lead assigned to {$user->name}"
        );

        return $lead->fresh();
    }

    /**
     * Convert lead to customer
     */
    public function convertLead(Lead $lead, User $customer): Lead
    {
        return DB::transaction(function () use ($lead, $customer) {
            $lead->update([
                'status' => Lead::STATUS_WON,
                'converted_to_customer_id' => $customer->id,
                'converted_at' => now(),
            ]);

            $this->logActivity(
                $lead,
                LeadActivity::TYPE_NOTE,
                'Lead Converted',
                "Lead converted to customer: {$customer->name}"
            );

            return $lead->fresh();
        });
    }

    /**
     * Mark lead as lost
     */
    public function markAsLost(Lead $lead, string $reason): Lead
    {
        $lead->update([
            'status' => Lead::STATUS_LOST,
            'lost_reason' => $reason,
        ]);

        $this->logActivity(
            $lead,
            LeadActivity::TYPE_NOTE,
            'Lead Lost',
            "Lead marked as lost. Reason: {$reason}"
        );

        return $lead->fresh();
    }

    /**
     * Log an activity for a lead
     */
    public function logActivity(
        Lead $lead,
        string $type,
        string $title,
        ?string $description = null,
        ?array $additionalData = []
    ): LeadActivity {
        return LeadActivity::create([
            'tenant_id' => $lead->tenant_id,
            'lead_id' => $lead->id,
            'user_id' => auth()->id() ?? $lead->created_by,
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'activity_date' => now(),
            ...$additionalData,
        ]);
    }

    /**
     * Get lead statistics
     */
    public function getLeadStatistics(int $tenantId): array
    {
        $statusCounts = Lead::where('tenant_id', $tenantId)
            ->select('status', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $total = $statusCounts->sum();

        return [
            'total' => $total,
            'new' => $statusCounts[Lead::STATUS_NEW] ?? 0,
            'contacted' => $statusCounts[Lead::STATUS_CONTACTED] ?? 0,
            'qualified' => $statusCounts[Lead::STATUS_QUALIFIED] ?? 0,
            'proposal' => $statusCounts[Lead::STATUS_PROPOSAL] ?? 0,
            'negotiation' => $statusCounts[Lead::STATUS_NEGOTIATION] ?? 0,
            'won' => $statusCounts[Lead::STATUS_WON] ?? 0,
            'lost' => $statusCounts[Lead::STATUS_LOST] ?? 0,
            'conversion_rate' => $this->calculateConversionRate($tenantId),
        ];
    }

    /**
     * Calculate conversion rate
     */
    protected function calculateConversionRate(int $tenantId): float
    {
        $total = Lead::where('tenant_id', $tenantId)
            ->whereIn('status', [Lead::STATUS_WON, Lead::STATUS_LOST])
            ->count();

        if ($total === 0) {
            return 0;
        }

        $won = Lead::where('tenant_id', $tenantId)
            ->byStatus(Lead::STATUS_WON)
            ->count();

        return round(($won / $total) * 100, 2);
    }

    /**
     * Get overdue follow-ups
     */
    public function getOverdueFollowUps(int $tenantId): \Illuminate\Database\Eloquent\Collection
    {
        return Lead::where('tenant_id', $tenantId)
            ->whereNotNull('next_follow_up_date')
            ->where('next_follow_up_date', '<', now())
            ->active()
            ->with(['assignedTo', 'creator'])
            ->get();
    }

    /**
     * Get today's follow-ups
     */
    public function getTodayFollowUps(int $tenantId): \Illuminate\Database\Eloquent\Collection
    {
        return Lead::where('tenant_id', $tenantId)
            ->whereDate('next_follow_up_date', today())
            ->active()
            ->with(['assignedTo', 'creator'])
            ->get();
    }
}
