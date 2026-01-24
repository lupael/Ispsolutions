<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\NetworkUser;
use Illuminate\Support\Collection;

class CustomerFilterService
{
    /**
     * Apply filters to customer collection.
     * 
     * Supports 15+ filter types:
     * - connection_type
     * - billing_type
     * - status (active/suspended/expired)
     * - payment_status
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
                return $customer->connection_type === $filters['connection_type'];
            });
        }

        // Filter by billing type
        if (!empty($filters['billing_type'])) {
            $filtered = $filtered->filter(function ($customer) use ($filters) {
                return $customer->billing_type === $filters['billing_type'];
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
                return $customer->device_type === $filters['device_type'];
            });
        }

        // Filter by expiry date range
        if (!empty($filters['expiry_date_from']) || !empty($filters['expiry_date_to'])) {
            $filtered = $filtered->filter(function ($customer) use ($filters) {
                $expiryDate = $customer->expiry_date ?? $customer->user?->expiry_date;
                if (!$expiryDate) {
                    return false;
                }

                $expiry = \Carbon\Carbon::parse($expiryDate);

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
