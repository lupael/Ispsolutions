<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\AuditLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Customer Activity Service
 * 
 * Aggregates and formats customer activities from multiple sources:
 * - Payments
 * - Package changes
 * - Status changes
 * - Tickets
 * - Audit logs
 */
class CustomerActivityService
{
    /**
     * Get comprehensive activity timeline for a customer
     * 
     * @param User $customer
     * @param int $limit Maximum number of activities to return
     * @return Collection Sorted collection of activities
     */
    public function getActivityTimeline(User $customer, int $limit = 50): Collection
    {
        $activities = collect();

        // Get payment activities
        $activities = $activities->merge($this->getPaymentActivities($customer, $limit));

        // Get package change activities
        $activities = $activities->merge($this->getPackageChangeActivities($customer, $limit));

        // Get status change activities
        $activities = $activities->merge($this->getStatusChangeActivities($customer, $limit));

        // Get ticket activities
        $activities = $activities->merge($this->getTicketActivities($customer, $limit));

        // Sort by date descending and limit
        return $activities
            ->sortByDesc('timestamp')
            ->take($limit)
            ->values();
    }

    /**
     * Get payment activities
     */
    protected function getPaymentActivities(User $customer, int $limit): Collection
    {
        $payments = Payment::where('user_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return $payments->map(function ($payment) {
            return [
                'type' => 'payment',
                'icon' => 'currency-dollar',
                'color' => 'green',
                'title' => __('Payment Received'),
                'description' => __('Payment of :amount via :method', [
                    'amount' => number_format($payment->amount, 2),
                    'method' => $payment->payment_method ?? 'N/A',
                ]),
                'amount' => $payment->amount,
                'timestamp' => $payment->created_at,
                'metadata' => [
                    'payment_id' => $payment->id,
                    'method' => $payment->payment_method,
                    'status' => $payment->status ?? 'completed',
                ],
            ];
        });
    }

    /**
     * Get package change activities from audit logs
     */
    protected function getPackageChangeActivities(User $customer, int $limit): Collection
    {
        $packageChanges = AuditLog::where('auditable_type', User::class)
            ->where('auditable_id', $customer->id)
            ->where('event', 'updated')
            ->whereNotNull(DB::raw("JSON_EXTRACT(new_values, '$.service_package_id')"))
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return $packageChanges->map(function ($log) {
            $oldPackageId = $log->old_values['service_package_id'] ?? null;
            $newPackageId = $log->new_values['service_package_id'] ?? null;

            return [
                'type' => 'package_change',
                'icon' => 'refresh',
                'color' => 'blue',
                'title' => __('Package Changed'),
                'description' => __('Package changed from :old to :new', [
                    'old' => $oldPackageId ? "Package #{$oldPackageId}" : 'N/A',
                    'new' => $newPackageId ? "Package #{$newPackageId}" : 'N/A',
                ]),
                'timestamp' => $log->created_at,
                'metadata' => [
                    'old_package_id' => $oldPackageId,
                    'new_package_id' => $newPackageId,
                    'user_id' => $log->user_id,
                ],
            ];
        });
    }

    /**
     * Get status change activities
     */
    protected function getStatusChangeActivities(User $customer, int $limit): Collection
    {
        $statusChanges = AuditLog::where('auditable_type', User::class)
            ->where('auditable_id', $customer->id)
            ->where('event', 'updated')
            ->where(function ($query) {
                $query->whereNotNull(DB::raw("JSON_EXTRACT(old_values, '$.status')"))
                    ->whereNotNull(DB::raw("JSON_EXTRACT(new_values, '$.status')"));
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return $statusChanges->map(function ($log) {
            $oldStatus = $log->old_values['status'] ?? null;
            $newStatus = $log->new_values['status'] ?? null;

            $color = match ($newStatus) {
                'active' => 'green',
                'suspended' => 'yellow',
                'expired' => 'red',
                default => 'gray',
            };

            return [
                'type' => 'status_change',
                'icon' => 'shield-check',
                'color' => $color,
                'title' => __('Status Changed'),
                'description' => __('Status changed from :old to :new', [
                    'old' => ucfirst($oldStatus ?? 'N/A'),
                    'new' => ucfirst($newStatus ?? 'N/A'),
                ]),
                'timestamp' => $log->created_at,
                'metadata' => [
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'user_id' => $log->user_id,
                ],
            ];
        });
    }

    /**
     * Get ticket activities
     */
    protected function getTicketActivities(User $customer, int $limit): Collection
    {
        $tickets = Ticket::where('user_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return $tickets->map(function ($ticket) {
            $color = match ($ticket->status) {
                'open' => 'red',
                'in_progress' => 'yellow',
                'resolved' => 'green',
                'closed' => 'gray',
                default => 'blue',
            };

            return [
                'type' => 'ticket',
                'icon' => 'ticket',
                'color' => $color,
                'title' => __('Support Ticket'),
                'description' => __('Ticket #:id - :subject (:status)', [
                    'id' => $ticket->id,
                    'subject' => $ticket->subject ?? 'No subject',
                    'status' => $ticket->status ?? 'open',
                ]),
                'timestamp' => $ticket->created_at,
                'metadata' => [
                    'ticket_id' => $ticket->id,
                    'status' => $ticket->status,
                    'priority' => $ticket->priority ?? 'normal',
                ],
            ];
        });
    }

    /**
     * Get activity statistics
     * 
     * @param User $customer
     * @param int $days Number of days to look back
     * @return array
     */
    public function getActivityStats(User $customer, int $days = 30): array
    {
        $since = now()->subDays($days);

        return [
            'payments_count' => Payment::where('user_id', $customer->id)
                ->where('created_at', '>=', $since)
                ->count(),
            'payments_total' => Payment::where('user_id', $customer->id)
                ->where('created_at', '>=', $since)
                ->sum('amount'),
            'tickets_count' => Ticket::where('user_id', $customer->id)
                ->where('created_at', '>=', $since)
                ->count(),
            'package_changes' => AuditLog::where('auditable_type', User::class)
                ->where('auditable_id', $customer->id)
                ->where('event', 'updated')
                ->where('created_at', '>=', $since)
                ->whereNotNull(DB::raw("JSON_EXTRACT(new_values, '$.service_package_id')"))
                ->count(),
            'status_changes' => AuditLog::where('auditable_type', User::class)
                ->where('auditable_id', $customer->id)
                ->where('event', 'updated')
                ->where('created_at', '>=', $since)
                ->whereNotNull(DB::raw("JSON_EXTRACT(new_values, '$.status')"))
                ->count(),
        ];
    }

    /**
     * Get recent activities (last 10)
     * 
     * @param User $customer
     * @return Collection
     */
    public function getRecentActivities(User $customer): Collection
    {
        return $this->getActivityTimeline($customer, 10);
    }
}
