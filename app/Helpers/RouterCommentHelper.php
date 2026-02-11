<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\Customer;
use App\Models\MikrotikRouter;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Router Comment Helper
 * 
 * Generates standardized comments for router objects (PPP secrets, hotspot users, etc.)
 * Supports two formats:
 * 1. Legacy pipe format: username|user_id|package_id|expiry_date|service_type
 * 2. IspBills format: key--value,key--value,... (uid--123,name--John,mobile--01712345678,...)
 * 
 * This embeds customer metadata directly into router configurations for:
 * - Easy troubleshooting from router interface
 * - Customer identification without database lookup
 * - Audit trail on router side
 */
class RouterCommentHelper
{
    /**
     * Build a structured comment string for a user on the router (legacy pipe format)
     * Format: username|user_id|package_id|expiry_date|service_type
     * 
     * @deprecated since v1.1.0 Use getComment() for new implementations (IspBills pattern)
     * @param User $user
     * @return string
     */
    public static function buildUserComment(User $user): string
    {
        $parts = [
            'username' => self::sanitizePipe($user->username),
            'user_id' => $user->id ?? '',
            'package_id' => $user->service_package_id ?? '',
            'expiry_date' => $user->expiry_date?->format('Y-m-d') ?? '',
            'service_type' => self::sanitizePipe($user->service_type ?? 'pppoe'),
        ];

        return implode('|', array_values($parts));
    }

    /**
     * Generate router comment string from customer/user data (IspBills pattern)
     * Format: key--value,key--value,...
     * 
     * @param Model $entity Customer or User model
     * @return string Formatted comment string
     */
    public static function getComment(Model $entity): string
    {
        if ($entity instanceof User && $entity->is_subscriber) {
            return self::getSubscriberComment($entity);
        }
        
        if ($entity instanceof Customer) {
            return self::getCustomerComment($entity);
        }
        
        return '';
    }
    
    /**
     * Alias for getComment() for backward compatibility
     * 
     * @param Model $entity Customer or User model
     * @return string Formatted comment string
     */
    public static function buildComment(Model $entity): string
    {
        return self::getComment($entity);
    }
    
    /**
     * Generate comment for a subscriber User (PPPoE user) - IspBills pattern
     * Format: uid--123,name--John Doe,mobile--01712345678,zone--5,pkg--10,exp--2026-12-31,status--active
     * 
     * Pulls customer information from the user model
     */
    protected static function getSubscriberComment(User $user): string
    {
        $parts = [
            'uid' => $user->id,
            'name' => self::sanitize($user->name),
            'mobile' => self::sanitize($user->mobile ?? $user->phone ?? 'N/A'),
            'zone' => $user->zone_id ?? 'N/A',
            'pkg' => $user->service_package_id ?? 'N/A',
            'exp' => $user->expiry_date?->format('Y-m-d') ?? 'N/A',
            'status' => $user->status ?? 'active',
        ];
        
        return self::buildCommentString($parts);
    }
    
    /**
     * Generate comment for Customer - IspBills pattern
     * Format: cid--456,name--Jane Smith,mobile--01898765432,zone--3,exp--2026-06-30,status--active
     */
    protected static function getCustomerComment(Customer $customer): string
    {
        $parts = [
            'cid' => $customer->id,
            'name' => self::sanitize($customer->name),
            'mobile' => self::sanitize($customer->mobile ?? $customer->phone ?? 'N/A'),
            'zone' => $customer->zone_id ?? 'N/A',
            'exp' => $customer->expiry_date?->format('Y-m-d') ?? 'N/A',
            'status' => $customer->status ?? 'active',
        ];
        
        return self::buildCommentString($parts);
    }
    
    /**
     * Build comment string from key-value pairs
     */
    protected static function buildCommentString(array $parts): string
    {
        $segments = [];
        
        foreach ($parts as $key => $value) {
            $segments[] = $key . '--' . $value;
        }
        
        return implode(',', $segments);
    }

    /**
     * Parse a router comment back into an array of metadata
     * Auto-detects format (legacy pipe or IspBills key--value)
     * Returns associative array
     */
    public static function parseComment(string $comment): array
    {
        // Detect format by checking for '--' separator (IspBills format)
        if (Str::contains($comment, '--')) {
            return self::parseIspBillsComment($comment);
        }
        
        // Legacy pipe format
        return self::parseLegacyComment($comment);
    }
    
    /**
     * Parse legacy pipe-delimited comment
     */
    protected static function parseLegacyComment(string $comment): array
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
     * Parse IspBills key--value comment format
     */
    protected static function parseIspBillsComment(string $comment): array
    {
        $parts = explode(',', $comment);
        $data = [];
        
        foreach ($parts as $part) {
            if (Str::contains($part, '--')) {
                [$key, $value] = explode('--', $part, 2);
                $data[trim($key)] = trim($value);
            }
        }
        
        return $data;
    }

    /**
     * Sanitize a value for use in a router comment (IspBills format)
     * Removes special characters that might break comment format
     * 
     * @param string|null $value Value to sanitize
     * @return string Sanitized value (returns 'N/A' for null/empty values)
     * 
     * @note When parsed back, 'N/A' cannot be distinguished from an actual 'N/A' input.
     *       This is intentional to maintain compatibility with IspBills pattern.
     */
    public static function sanitize(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return 'N/A';
        }
        
        // Remove or replace special characters that might break comment format
        // Note: '--' is replaced with '-' to prevent parsing issues
        $value = str_replace([',', '--', ';', "\n", "\r"], ['_', '-', '_', ' ', ' '], $value);
        
        // Limit length to prevent overly long comments
        if (strlen($value) > 50) {
            $value = substr($value, 0, 47) . '...';
        }
        
        return trim($value);
    }
    
    /**
     * Sanitize value for legacy pipe format
     * Remove pipe characters and trim whitespace
     */
    protected static function sanitizePipe(string $value): string
    {
        return trim(str_replace('|', '', $value));
    }
    
    /**
     * Extract customer/user ID from comment
     */
    public static function extractUserId(string $comment): ?int
    {
        $data = self::parseComment($comment);
        
        // IspBills format
        if (isset($data['uid']) && is_numeric($data['uid'])) {
            return (int) $data['uid'];
        }
        
        if (isset($data['cid']) && is_numeric($data['cid'])) {
            return (int) $data['cid'];
        }
        
        // Legacy format
        if (isset($data['user_id']) && is_numeric($data['user_id'])) {
            return (int) $data['user_id'];
        }
        
        return null;
    }
    
    /**
     * Extract mobile number from comment
     */
    public static function extractMobile(string $comment): ?string
    {
        $data = self::parseComment($comment);
        return $data['mobile'] ?? null;
    }
    
    /**
     * Check if comment indicates expired user
     * 
     * @param string $comment Comment string
     * @return bool True if expiry date is in the past
     * 
     * @note Uses server timezone for comparison. Ensure consistent timezone configuration.
     */
    public static function isExpired(string $comment): bool
    {
        $data = self::parseComment($comment);
        
        $expiryDateStr = $data['exp'] ?? $data['expiry_date'] ?? null;
        
        if (!$expiryDateStr || $expiryDateStr === 'N/A' || $expiryDateStr === '') {
            return false;
        }
        
        try {
            // Use application timezone for consistency
            $expiryDate = new \DateTime($expiryDateStr);
            $now = new \DateTime();
            
            return $expiryDate < $now;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Update the comment for a user on a specific router
     * This method would typically be called via the MikroTik API
     * 
     * @param User $user
     * @param MikrotikRouter $router
     * @param mixed $api MikroTik API client instance
     * @return bool
     */
    public static function updateRouterComment(User $user, MikrotikRouter $router, $api): bool
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