<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\NetworkUser;
use App\Enums\CustomerOverallStatus;
use Illuminate\Support\Collection;

class CustomerFilterService
{
    /**
     * Apply filters to customer collection.
     * 
     * Supports 16+ filter types:
     * - connection_type
     * - billing_type
     * - status (active/suspended/expired)
     * - payment_status
     * - overall_status (combined payment_type + status)
     * - zone_id
     * - package_id
     * - device_type
     * - expiry_date range
     * - registration_date range
     * - last_payment_date range
     * - balance range
     * - online_status
     * - custom fields
     */
    public function applyFilters(Collection $customers, array $filters): Collection
    {
        $filtered = $customers;

        // Filter by connection type
        if (!empty($filters['connection_type'])) {
            $filtered = $filtered->filter(function ($customer) use ($filters) {
                return isset($customer->connection_type) && $customer->connection_type === $filters['connection_type'];
            });
        }

        // Filter by billing type
        if (!empty($filters['billing_type'])) {
            $filtered = $filtered->filter(function ($customer) use ($filters) {
                return isset($customer->billing_type) && $customer->billing_type === $filters['billing_type'];
            });
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $filtered = $filtered->filter(function ($customer) use ($filters) {
                return $customer->status === $filters['status'];
            });
        }

        // Filter by payment status
        if (!empty($filters['payment_status'])) {
            $filtered = $filtered->filter(function ($customer) use ($filters) {
                return $customer->payment_status === $filters['payment_status'];
            });
        }

        // Filter by overall status (combines payment_type and status)
        if (!empty($filters['overall_status'])) {
            $filtered = $filtered->filter(function ($customer) use ($filters) {
                // Get customer's overall status
                $overallStatus = $customer->overall_status ?? null;
                return $overallStatus && $overallStatus->value === $filters['overall_status'];
            });
        }

        // Filter by zone
        if (!empty($filters['zone_id'])) {
            $filtered = $filtered->filter(function ($customer) use ($filters) {
                return $customer->zone_id == $filters['zone_id'];
            });
        }

        // Filter by package
        if (!empty($filters['package_id'])) {
            $filtered = $filtered->filter(function ($customer) use ($filters) {
                return $customer->package_id == $filters['package_id'];
            });
        }

        // Filter by device type
        if (!empty($filters['device_type'])) {
            $filtered = $filtered->filter(function ($customer) use ($filters) {
                return isset($customer->device_type) && $customer->device_type === $filters['device_type'];
            });
        }

        // Filter by expiry date range
        if (!empty($filters['expiry_date_from']) || !empty($filters['expiry_date_to'])) {
            $filtered = $filtered->filter(function ($customer) use ($filters) {
                // Get expiry date from network_user
                if (!$customer->expiry_date) {
                    return false;
                }

                $expiry = \Carbon\Carbon::parse($customer->expiry_date);

                if (!empty($filters['expiry_date_from'])) {
                    $from = \Carbon\Carbon::parse($filters['expiry_date_from']);
                    if ($expiry->lt($from)) {
                        return false;
                    }
                }

                if (!empty($filters['expiry_date_to'])) {
                    $to = \Carbon\Carbon::parse($filters['expiry_date_to']);
                    if ($expiry->gt($to)) {
                        return false;
                    }
                }

                return true;
            });
        }

        // Filter by registration date range
        if (!empty($filters['registration_date_from']) || !empty($filters['registration_date_to'])) {
            $filtered = $filtered->filter(function ($customer) use ($filters) {
                $regDate = \Carbon\Carbon::parse($customer->created_at);

                if (!empty($filters['registration_date_from'])) {
                    $from = \Carbon\Carbon::parse($filters['registration_date_from']);
                    if ($regDate->lt($from)) {
                        return false;
                    }
                }

                if (!empty($filters['registration_date_to'])) {
                    $to = \Carbon\Carbon::parse($filters['registration_date_to']);
                    if ($regDate->gt($to)) {
                        return false;
                    }
                }

                return true;
            });
        }

        // Filter by last payment date range
        if (!empty($filters['last_payment_date_from']) || !empty($filters['last_payment_date_to'])) {
            $filtered = $filtered->filter(function ($customer) use ($filters) {
                $lastPaymentDate = $customer->last_payment_date;
                if (!$lastPaymentDate) {
                    return false;
                }

                $payDate = \Carbon\Carbon::parse($lastPaymentDate);

                if (!empty($filters['last_payment_date_from'])) {
                    $from = \Carbon\Carbon::parse($filters['last_payment_date_from']);
                    if ($payDate->lt($from)) {
                        return false;
                    }
                }

                if (!empty($filters['last_payment_date_to'])) {
                    $to = \Carbon\Carbon::parse($filters['last_payment_date_to']);
                    if ($payDate->gt($to)) {
                        return false;
                    }
                }

                return true;
            });
        }

        // Filter by balance range
        if (isset($filters['balance_min']) || isset($filters['balance_max'])) {
            $filtered = $filtered->filter(function ($customer) use ($filters) {
                $balance = $customer->balance ?? 0;

                if (isset($filters['balance_min']) && $balance < $filters['balance_min']) {
                    return false;
                }

                if (isset($filters['balance_max']) && $balance > $filters['balance_max']) {
                    return false;
                }

                return true;
            });
        }

        // Filter by online status
        if (isset($filters['online_status'])) {
            $filtered = $filtered->filter(function ($customer) use ($filters) {
                $isOnline = $customer->online_status ?? false;
                return $isOnline === (bool) $filters['online_status'];
            });
        }

        // Filter by parent account (show only customers with child accounts)
        // Task 7.3: Integrated reseller filtering into existing customer management
        if (!empty($filters['has_child_accounts'])) {
            $filtered = $filtered->filter(function ($customer) {
                return isset($customer->parent_id) === false && 
                       isset($customer->childAccounts) && 
                       count($customer->childAccounts) > 0;
            });
        }

        // Filter by specific parent (show child accounts of a parent)
        if (!empty($filters['parent_id'])) {
            $filtered = $filtered->filter(function ($customer) use ($filters) {
                return $customer->parent_id == $filters['parent_id'];
            });
        }

        // Filter by search query (name, mobile, username)
        if (!empty($filters['search'])) {
            $searchTerm = strtolower($filters['search']);
            $filtered = $filtered->filter(function ($customer) use ($searchTerm) {
                return str_contains(strtolower($customer->name ?? ''), $searchTerm) ||
                       str_contains(strtolower($customer->mobile ?? ''), $searchTerm) ||
                       str_contains(strtolower($customer->username ?? ''), $searchTerm) ||
                       str_contains(strtolower($customer->user?->name ?? ''), $searchTerm) ||
                       str_contains(strtolower($customer->user?->mobile ?? ''), $searchTerm);
            });
        }

        return $filtered;
    }

    /**
     * Get available filter options for the UI.
     */
    public function getFilterOptions(int $tenantId): array
    {
        return [
            'connection_types' => $this->getConnectionTypes(),
            'billing_types' => $this->getBillingTypes(),
            'statuses' => $this->getStatuses(),
            'payment_statuses' => $this->getPaymentStatuses(),
            'overall_statuses' => $this->getOverallStatuses(),
            'device_types' => $this->getDeviceTypes(),
        ];
    }

    /**
     * Get connection types.
     */
    private function getConnectionTypes(): array
    {
        return [
            'pppoe' => 'PPPoE',
            'hotspot' => 'Hotspot',
            'static_ip' => 'Static IP',
            'other' => 'Other',
        ];
    }

    /**
     * Get billing types.
     */
    private function getBillingTypes(): array
    {
        return [
            'prepaid' => 'Prepaid',
            'postpaid' => 'Postpaid',
        ];
    }

    /**
     * Get statuses.
     */
    private function getStatuses(): array
    {
        return [
            'active' => 'Active',
            'suspended' => 'Suspended',
            'expired' => 'Expired',
            'inactive' => 'Inactive',
        ];
    }

    /**
     * Get payment statuses.
     */
    private function getPaymentStatuses(): array
    {
        return [
            'paid' => 'Paid',
            'pending' => 'Pending',
            'overdue' => 'Overdue',
        ];
    }

    /**
     * Get overall statuses (combined payment_type + status).
     */
    private function getOverallStatuses(): array
    {
        return [
            CustomerOverallStatus::PREPAID_ACTIVE->value => __('Prepaid - Active'),
            CustomerOverallStatus::PREPAID_SUSPENDED->value => __('Prepaid - Suspended'),
            CustomerOverallStatus::PREPAID_EXPIRED->value => __('Prepaid - Expired'),
            CustomerOverallStatus::PREPAID_INACTIVE->value => __('Prepaid - Inactive'),
            CustomerOverallStatus::POSTPAID_ACTIVE->value => __('Postpaid - Active'),
            CustomerOverallStatus::POSTPAID_SUSPENDED->value => __('Postpaid - Suspended'),
            CustomerOverallStatus::POSTPAID_EXPIRED->value => __('Postpaid - Expired'),
            CustomerOverallStatus::POSTPAID_INACTIVE->value => __('Postpaid - Inactive'),
        ];
    }

    /**
     * Get device types.
     */
    private function getDeviceTypes(): array
    {
        return [
            'router' => 'Router',
            'pc' => 'PC',
            'mobile' => 'Mobile',
            'other' => 'Other',
        ];
    }
}
