<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\MikrotikRouter;
use App\Models\NetworkUser;
use Illuminate\Support\Facades\Log;

class RouterCommentHelper
{
    /**
     * Build a structured comment string for a user on the router
     * Format: username|user_id|package_id|expiry_date
     */
    public static function buildUserComment(NetworkUser $user): string
    {
        $parts = [
            'username' => self::sanitize($user->username),
            'user_id' => $user->user_id ?? '',
            'package_id' => $user->package_id ?? '',
            'expiry_date' => $user->expiry_date?->format('Y-m-d') ?? '',
            'service_type' => self::sanitize($user->service_type ?? 'pppoe'),
        ];

        return implode('|', array_values($parts));
    }

    /**
     * Parse a router comment back into an array of metadata
     * Returns associative array with keys: username, user_id, package_id, expiry_date, service_type
     */
    public static function parseComment(string $comment): array
    {
        $parts = explode('|', $comment);
        
        return [
            'username' => $parts[0] ?? '',
            'user_id' => $parts[1] ?? '',
            'package_id' => $parts[2] ?? '',
            'expiry_date' => $parts[3] ?? '',
            'service_type' => $parts[4] ?? 'pppoe',
        ];
    }

    /**
     * Sanitize a value for use in a router comment
     * Remove pipe characters and trim whitespace
     */
    public static function sanitize(string $value): string
    {
        return trim(str_replace('|', '', $value));
    }

    /**
     * Update the comment for a user on a specific router
     * This method would typically be called via the MikroTik API
     * 
     * @param NetworkUser $user
     * @param MikrotikRouter $router
     * @param mixed $api MikroTik API client instance
     * @return bool
     */
    public static function updateRouterComment(NetworkUser $user, MikrotikRouter $router, $api): bool
    {
        try {
            $comment = self::buildUserComment($user);
            
            // Find the PPP secret by username
            $secrets = $api->comm('/ppp/secret/print', [
                '?name' => $user->username,
            ]);

            if (empty($secrets)) {
                return false;
            }

            $secret = $secrets[0];
            
            // Update the comment field
            $api->comm('/ppp/secret/set', [
                '.id' => $secret['.id'],
                'comment' => $comment,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update router comment', [
                'user_id' => $user->id,
                'username' => $user->username,
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
