<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Olt;
use App\Models\OltSnmpTrap;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SnmpTrapReceiverController extends Controller
{
    /**
     * Receive and process SNMP traps from OLT devices.
     * 
     * This endpoint is designed to be called by snmptrapd daemon
     * when it receives traps from OLT devices. It can also be
     * called directly for testing purposes.
     * 
     * Expected request format:
     * - source_ip: IP address of the device sending the trap
     * - trap_type: Type of trap (e.g., linkDown, linkUp, coldStart)
     * - oid: Object Identifier of the trap
     * - severity: Severity level (critical, warning, info)
     * - message: Human-readable trap message
     * - trap_data: Additional trap data (JSON)
     */
    public function receive(Request $request): JsonResponse
    {
        // Log the incoming trap request for debugging (limited fields for security)
        Log::info('Received SNMP trap', [
            'source_ip' => $request->input('source_ip'),
            'trap_type' => $request->input('trap_type'),
            'severity' => $request->input('severity'),
        ]);

        try {
            // Extract trap data from request
            $sourceIp = $request->input('source_ip', $request->ip());
            $trapType = $request->input('trap_type', 'unknown');
            $oid = $request->input('oid', '');
            $severity = $request->input('severity', 'info');
            $message = $request->input('message', '');
            $trapData = $request->input('trap_data', []);

            // Ensure trap_data is an array
            if (is_string($trapData)) {
                $trapData = json_decode($trapData, true) ?? [];
            }

            // Find the OLT by IP address
            $olt = Olt::where('ip_address', $sourceIp)->first();

            if (!$olt) {
                Log::warning("Received trap from unknown OLT IP: {$sourceIp}");
                
                // Still record the trap even if OLT is not found
                // This helps with troubleshooting and discovering new devices
                OltSnmpTrap::create([
                    'olt_id' => null,
                    'tenant_id' => null,
                    'source_ip' => $sourceIp,
                    'trap_type' => $trapType,
                    'oid' => $oid,
                    'severity' => $severity,
                    'message' => $message ?: "Unknown trap from {$sourceIp}",
                    'trap_data' => $trapData,
                    'is_acknowledged' => false,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Trap received but OLT not found in system',
                    'warning' => "No OLT found with IP {$sourceIp}",
                ]);
            }

            // Parse the trap and determine severity if not provided
            if ($severity === 'info' && $trapType) {
                $severity = $this->determineSeverity($trapType, $oid);
            }

            // Create trap record
            $trap = OltSnmpTrap::create([
                'olt_id' => $olt->id,
                'tenant_id' => $olt->tenant_id,
                'source_ip' => $sourceIp,
                'trap_type' => $trapType,
                'oid' => $oid,
                'severity' => $severity,
                'message' => $message ?: $this->generateMessageFromTrap($trapType, $oid, $trapData),
                'trap_data' => $trapData,
                'is_acknowledged' => false,
            ]);

            // Handle critical traps
            if ($severity === 'critical') {
                $this->handleCriticalTrap($olt, $trap);
            }

            Log::info("SNMP trap recorded", [
                'trap_id' => $trap->id,
                'olt_id' => $olt->id,
                'trap_type' => $trapType,
                'severity' => $severity,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Trap received and processed successfully',
                'trap_id' => $trap->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing SNMP trap', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process trap',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Receive trap in snmptrapd format.
     * 
     * This endpoint accepts trap data in the format sent by snmptrapd
     * using a custom script. The format is typically plain text with
     * key-value pairs or a specific format defined in traphandle config.
     */
    public function receiveLegacy(Request $request): JsonResponse
    {
        try {
            // Parse the legacy format
            $body = $request->getContent();
            Log::debug('Received legacy SNMP trap', ['body' => $body]);

            // Parse common snmptrapd output format
            $lines = explode("\n", trim($body));
            $data = [];
            $sourceIp = $request->ip();

            foreach ($lines as $line) {
                if (empty(trim($line))) {
                    continue;
                }

                // Try to parse key-value pairs
                if (strpos($line, '=') !== false) {
                    [$key, $value] = array_map('trim', explode('=', $line, 2));
                    $data[strtolower($key)] = $value;
                }

                // Extract source IP if present
                if (preg_match('/UDP: \[([^\]]+)\]/', $line, $matches)) {
                    $sourceIp = $matches[1];
                    $data['source_ip'] = $sourceIp;
                }

                // Extract OID if present
                if (preg_match('/\.1\.3\.6\.1[^\s]+/', $line, $matches)) {
                    $data['oid'] = $matches[0];
                }
            }

            // Convert to standard format and call the main receive method
            $standardRequest = new Request([
                'source_ip' => $data['source_ip'] ?? $sourceIp,
                'trap_type' => $data['trap_type'] ?? $data['traptype'] ?? 'unknown',
                'oid' => $data['oid'] ?? '',
                'severity' => $data['severity'] ?? 'info',
                'message' => $data['message'] ?? implode(' | ', array_slice($lines, 0, 3)),
                'trap_data' => $data,
            ]);

            return $this->receive($standardRequest);

        } catch (\Exception $e) {
            Log::error('Error processing legacy SNMP trap', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process legacy trap format',
            ], 500);
        }
    }

    /**
     * Determine trap severity based on trap type and OID.
     */
    private function determineSeverity(string $trapType, string $oid): string
    {
        $criticalTraps = [
            'linkDown',
            'coldStart',
            'warmStart',
            'authenticationFailure',
            'powerSupplyFailure',
            'fanFailure',
            'temperatureAlarm',
        ];

        $warningTraps = [
            'linkUp',
            'oltRestart',
            'configurationChanged',
        ];

        $trapTypeLower = strtolower($trapType);

        foreach ($criticalTraps as $critical) {
            if (stripos($trapTypeLower, strtolower($critical)) !== false) {
                return 'critical';
            }
        }

        foreach ($warningTraps as $warning) {
            if (stripos($trapTypeLower, strtolower($warning)) !== false) {
                return 'warning';
            }
        }

        return 'info';
    }

    /**
     * Generate human-readable message from trap data.
     */
    private function generateMessageFromTrap(string $trapType, string $oid, array $trapData): string
    {
        $messages = [
            'linkDown' => 'Network link went down',
            'linkUp' => 'Network link came up',
            'coldStart' => 'Device cold started',
            'warmStart' => 'Device warm started',
            'authenticationFailure' => 'Authentication failure detected',
            'powerSupplyFailure' => 'Power supply failure detected',
            'fanFailure' => 'Fan failure detected',
            'temperatureAlarm' => 'Temperature alarm triggered',
        ];

        $message = $messages[$trapType] ?? "SNMP trap: {$trapType}";

        // Add additional context from trap data if available
        if (!empty($trapData)) {
            if (isset($trapData['interface'])) {
                $message .= " on interface {$trapData['interface']}";
            }
            if (isset($trapData['port'])) {
                $message .= " on port {$trapData['port']}";
            }
        }

        return $message;
    }

    /**
     * Handle critical traps that require immediate action.
     */
    private function handleCriticalTrap(Olt $olt, OltSnmpTrap $trap): void
    {
        // Update OLT health status if it's a critical trap
        if (in_array($trap->trap_type, ['coldStart', 'warmStart', 'linkDown', 'powerSupplyFailure'])) {
            $olt->update([
                'health_status' => 'degraded',
                'last_health_check_at' => now(),
            ]);
        }

        // TODO: Send notifications to administrators
        // This could trigger email, SMS, or push notifications
        // depending on the configured notification channels
        
        Log::critical("Critical SNMP trap received", [
            'olt_id' => $olt->id,
            'olt_name' => $olt->name,
            'trap_type' => $trap->trap_type,
            'message' => $trap->message,
        ]);
    }

    /**
     * Test endpoint to simulate receiving a trap.
     * Only available in non-production environments.
     */
    public function test(Request $request): JsonResponse
    {
        if (config('app.env') === 'production') {
            return response()->json([
                'success' => false,
                'message' => 'Test endpoint not available in production',
            ], 403);
        }

        // Simulate a test trap
        $testRequest = new Request([
            'source_ip' => $request->input('source_ip', '192.168.1.100'),
            'trap_type' => $request->input('trap_type', 'linkDown'),
            'oid' => $request->input('oid', '.1.3.6.1.6.3.1.1.5.3'),
            'severity' => $request->input('severity', 'critical'),
            'message' => $request->input('message', 'Test SNMP trap'),
            'trap_data' => $request->input('trap_data', ['test' => true]),
        ]);

        return $this->receive($testRequest);
    }
}
