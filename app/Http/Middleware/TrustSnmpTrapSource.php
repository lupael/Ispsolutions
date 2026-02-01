<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to restrict SNMP trap receiver access to trusted IPs.
 * 
 * This middleware checks the request IP against a configured allowlist
 * to prevent unauthorized devices from sending fake traps.
 */
class TrustSnmpTrapSource
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowedIps = config('snmp.trap_allowed_ips', []);
        
        // If no IPs are configured, allow all (backward compatibility)
        // This should be configured in production
        if (empty($allowedIps)) {
            \Log::warning('SNMP trap IP allowlist is empty. Configure snmp.trap_allowed_ips for security.');
            return $next($request);
        }
        
        $clientIp = $request->ip();
        
        // Check if the client IP is in the allowlist
        if (!$this->isIpAllowed($clientIp, $allowedIps)) {
            \Log::warning('SNMP trap request from unauthorized IP blocked', [
                'ip' => $clientIp,
                'allowed_ips' => $allowedIps,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: IP address not allowed',
            ], 403);
        }
        
        return $next($request);
    }
    
    /**
     * Check if an IP address is allowed.
     * 
     * Supports both individual IPs and CIDR notation.
     */
    private function isIpAllowed(string $ip, array $allowedIps): bool
    {
        foreach ($allowedIps as $allowed) {
            // Exact match
            if ($ip === $allowed) {
                return true;
            }
            
            // CIDR notation
            if (strpos($allowed, '/') !== false) {
                if ($this->ipInCidr($ip, $allowed)) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Check if an IP is within a CIDR range.
     */
    private function ipInCidr(string $ip, string $cidr): bool
    {
        list($subnet, $mask) = explode('/', $cidr);
        
        // Convert to long integers
        $ip_long = ip2long($ip);
        $subnet_long = ip2long($subnet);
        $mask_long = -1 << (32 - (int) $mask);
        
        // Check if IP is in subnet
        return ($ip_long & $mask_long) === ($subnet_long & $mask_long);
    }
}
