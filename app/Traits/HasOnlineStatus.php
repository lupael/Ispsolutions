<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait HasOnlineStatus
{
    /**
     * Check if the customer is currently online.
     * Queries radacct table for active sessions.
     */
    public function isOnline(): bool
    {
        try {
            // Check if there's an active session in radacct
            $activeSession = DB::connection('radius')->table('radacct')
                ->where('username', $this->username)
                ->whereNull('acctstoptime')
                ->exists();

            return $activeSession;
        } catch (\Exception $e) {
            Log::error('Failed to check online status', [
                'customer_id' => $this->id,
                'username' => $this->username,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get the online status as a virtual attribute.
     */
    public function getOnlineStatusAttribute(): bool
    {
        // If a cached / pre-attached value exists, use it to avoid extra queries
        if (property_exists($this, 'attributes')
            && is_array($this->attributes)
            && array_key_exists('online_status', $this->attributes)) {
            return (bool) $this->attributes['online_status'];
        }

        // Fallback: compute the status by querying the database
        return $this->isOnline();
    }

    /**
     * Get current session information if online.
     */
    public function getCurrentSession(): ?object
    {
        try {
            return DB::connection('radius')->table('radacct')
                ->where('username', $this->username)
                ->whereNull('acctstoptime')
                ->orderBy('acctstarttime', 'desc')
                ->first();
        } catch (\Exception $e) {
            Log::error('Failed to get current session', [
                'customer_id' => $this->id,
                'username' => $this->username,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get session duration in seconds.
     */
    public function getSessionDuration(): int
    {
        $session = $this->getCurrentSession();
        if (!$session) {
            return 0;
        }

        $startTime = strtotime($session->acctstarttime);
        return time() - $startTime;
    }

    /**
     * Scope to filter only online customers.
     */
    public function scopeOnline($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereIn('username', function ($subQuery) {
            $subQuery->select('username')
                ->from('radius.radacct')
                ->whereNull('acctstoptime');
        });
    }

    /**
     * Scope to filter only offline customers.
     */
    public function scopeOffline($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereNotIn('username', function ($subQuery) {
            $subQuery->select('username')
                ->from('radius.radacct')
                ->whereNull('acctstoptime');
        });
    }
}
