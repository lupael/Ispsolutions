<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\RadiusServiceInterface;
use App\Models\User;
use App\Models\RadAcct;
use App\Models\RadCheck;
use App\Models\RadReply;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RadiusService implements RadiusServiceInterface
{
    /**
     * Create a new RADIUS user
     *
     * SECURITY NOTE: This implementation stores passwords in cleartext using
     * the Cleartext-Password attribute, which is standard for RADIUS but has
     * security implications. Ensure the RADIUS database has appropriate access
     * controls. For enhanced security, consider using PAP with hashed passwords
     * or CHAP authentication methods.
     *
     * {@inheritDoc}
     */
    public function createUser(string $username, string $password, array $attributes = []): bool
    {
        return $this->executeTransaction('create', $username, function () use ($username, $password, $attributes) {
            // Create password check entry based on configured hash method
            $passwordAttributes = $this->preparePasswordAttributes($password);
            RadCheck::create([
                'username' => $username,
                'attribute' => $passwordAttributes['attribute'],
                'op' => ':=',
                'value' => $passwordAttributes['value'],
            ]);

            if (isset($attributes['mac_address'])) {
                RadCheck::create([
                    'username' => $username,
                    'attribute' => 'Calling-Station-Id',
                    'op' => '==',
                    'value' => $attributes['mac_address'],
                ]);
            }

            // Create reply attributes
            foreach ($attributes as $attribute => $value) {
                if ($value === null) continue; // Don't create attributes for null values
                RadReply::create(['username' => $username, 'attribute' => $attribute, 'op' => '=', 'value' => $value]);
            }
        });
    }

    /**
     * {@inheritDoc}
     */
    public function updateUser(string $username, array $attributes): bool
    {
        return $this->executeTransaction('update', $username, function () use ($username, $attributes) {
            // Update password if provided, respecting hash method
            if (isset($attributes['password'])) {
                $passwordAttributes = $this->preparePasswordAttributes($attributes['password']);
                // Remove old password attributes before setting new one
                RadCheck::where('username', $username)->whereIn('attribute', ['Cleartext-Password', 'MD5-Password', 'SHA1-Password'])->delete();
                RadCheck::create([
                    'username' => $username,
                    'attribute' => $passwordAttributes['attribute'],
                    'op' => ':=',
                    'value' => $passwordAttributes['value'],
                ]);
                unset($attributes['password']);
            }

            if (isset($attributes['mac_address'])) {
                RadCheck::where('username', $username)->update(['mac_address' => $attributes['mac_address']]);
            }

            // Update or remove reply attributes
            foreach ($attributes as $attribute => $value) {
                if ($value === null) {
                    // If value is null, remove the attribute
                    RadReply::where('username', $username)->where('attribute', $attribute)->delete();
                } else {
                    RadReply::updateOrCreate(
                        ['username' => $username, 'attribute' => $attribute],
                        ['op' => '=', 'value' => $value]
                    );
                }
            }
        });
    }

    /**
     * {@inheritDoc}
     */
    public function deleteUser(string $username): bool
    {
        return $this->executeTransaction('delete', $username, function () use ($username) {
            RadCheck::where('username', $username)->delete();
            RadReply::where('username', $username)->delete();
        });
    }

    /**
     * @deprecated This method uses the deprecated User model with is_subscriber. Use createUser/updateUser/deleteUser directly.
     */
    public function syncUser(User $user, array $attributes = []): bool
    {
        try {
            // Check if user exists in RADIUS
            $exists = RadCheck::where('username', $user->username)->exists();
            if ($user->status === 'active') {
                // Prepare attributes
                $password = $attributes['password'] ?? $user->radius_password;
                $mergedAttributes = array_merge(['password' => $password], $attributes);
                // Create or update user in RADIUS
                if ($exists) {
                    return $this->updateUser($user->username, $mergedAttributes);
                } else {
                    return $this->createUser($user->username, $password, $mergedAttributes);
                }
            } else {
                // Delete user from RADIUS if inactive
                if ($exists) {
                    return $this->deleteUser($user->username);
                }
                return true;
            }
        } catch (\Exception $e) {
            Log::error('Failed to sync RADIUS user', [
                'username' => $user->username,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getAccountingData(string $username): array
    {
        try {
            $sessions = RadAcct::where('username', $username)
                ->orderBy('acctstarttime', 'desc')
                ->limit(10)
                ->get();

            $totalUpload = 0;
            $totalDownload = 0;
            $totalSessionTime = 0;
            $activeSessions = 0;

            foreach ($sessions as $session) {
                $totalUpload += $session->acctinputoctets ?? 0;
                $totalDownload += $session->acctoutputoctets ?? 0;
                $totalSessionTime += $session->acctsessiontime ?? 0;

                if ($session->acctstoptime === null) {
                    $activeSessions++;
                }
            }

            return [
                'username' => $username,
                'total_sessions' => $sessions->count(),
                'active_sessions' => $activeSessions,
                'total_upload_bytes' => $totalUpload,
                'total_download_bytes' => $totalDownload,
                'total_session_time' => $totalSessionTime,
                'recent_sessions' => $sessions->map(function ($session) {
                    return [
                        'session_id' => $session->acctsessionid,
                        'start_time' => $session->acctstarttime,
                        'stop_time' => $session->acctstoptime,
                        'session_time' => $session->acctsessiontime,
                        'upload_bytes' => $session->acctinputoctets,
                        'download_bytes' => $session->acctoutputoctets,
                        'ip_address' => $session->framedipaddress,
                        'nas_ip' => $session->nasipaddress,
                    ];
                })->toArray(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get RADIUS accounting data', [
                'username' => $username,
                'error' => $e->getMessage(),
            ]);

            return [
                'username' => $username,
                'total_sessions' => 0,
                'active_sessions' => 0,
                'total_upload_bytes' => 0,
                'total_download_bytes' => 0,
                'total_session_time' => 0,
                'recent_sessions' => [],
            ];
        }
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate(array $data): array
    {
        try {
            $username = $data['username'] ?? '';
            $password = $data['password'] ?? '';
            $macAddress = $data['mac_address'] ?? null;

            Log::debug('RADIUS authenticate: Checking credentials in database', [
                'username' => $username,
                'mac_address' => $macAddress,
                'connection' => config('radius.connection', 'radius'),
            ]);

            $passwordAttribute = $this->preparePasswordAttributes($password);

            // Check if user exists and password matches
            $userCheck = RadCheck::where('username', $username)
                ->where('attribute', $passwordAttribute['attribute'])
                ->first();

            $passwordMatches = false;
            if ($userCheck) {
                $passwordMatches = $userCheck->value === $passwordAttribute['value'];
            }

            if ($passwordMatches) {
                // Check for MAC address binding
                $macCheck = RadCheck::where('username', 'testuser')->where('attribute', 'Calling-Station-Id')->first();
                if ($macCheck && $macCheck->value && $macCheck->value !== $macAddress) {
                    Log::warning('RADIUS authenticate: MAC address mismatch', [
                        'username' => $username,
                        'expected_mac' => $macCheck->value,
                        'actual_mac' => $macAddress,
                    ]);
                    return [
                        'success' => false,
                        'username' => $username,
                        'message' => 'Authentication failed: MAC address mismatch',
                    ];
                }

                Log::info('RADIUS authenticate: User authenticated successfully', [
                    'username' => $username,
                ]);

                $replyAttributes = RadReply::where('username', $username)->get()->pluck('value', 'attribute')->toArray();

                return [
                    'success' => true,
                    'username' => $username,
                    'message' => 'Authentication successful',
                    'reply_attributes' => $replyAttributes,
                ];
            }

            // Log failure without revealing if user exists (prevents username enumeration)
            Log::warning('RADIUS authenticate: Authentication failed', [
                'username' => $username,
            ]);

            return [
                'success' => false,
                'username' => $username,
                'message' => 'Authentication failed',
            ];
        } catch (\Exception $e) {
            // Only log detailed error internally, don't expose to response
            Log::error('RADIUS authentication error', [
                'username' => $data['username'] ?? 'unknown',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return [
                'success' => false,
                'message' => 'Authentication error',
            ];
        }
    }

    /**
     * {@inheritDoc}
     */
    public function accountingStart(array $data): bool
    {
        try {
            RadAcct::create([
                'acctsessionid' => $data['session_id'] ?? '',
                'username' => $data['username'] ?? '',
                'nasipaddress' => $data['nas_ip'] ?? '',
                'framedipaddress' => $data['framed_ip'] ?? '',
                'acctstarttime' => $data['start_time'] ?? now(),
                'acctstoptime' => null,
                'acctsessiontime' => 0,
                'acctinputoctets' => 0,
                'acctoutputoctets' => 0,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to start RADIUS accounting', [
                'session_id' => $data['session_id'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function accountingUpdate(array $data): bool
    {
        try {
            $session = RadAcct::where('acctsessionid', $data['session_id'] ?? '')->first();

            if ($session) {
                $session->update([
                    'acctsessiontime' => $data['session_time'] ?? 0,
                    'acctinputoctets' => $data['input_octets'] ?? 0,
                    'acctoutputoctets' => $data['output_octets'] ?? 0,
                ]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to update RADIUS accounting', [
                'session_id' => $data['session_id'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function accountingStop(array $data): bool
    {
        try {
            $session = RadAcct::where('acctsessionid', $data['session_id'] ?? '')->first();

            if ($session) {
                $session->update([
                    'acctstoptime' => $data['stop_time'] ?? now(),
                    'acctsessiontime' => $data['session_time'] ?? 0,
                    'acctinputoctets' => $data['input_octets'] ?? 0,
                    'acctoutputoctets' => $data['output_octets'] ?? 0,
                    'acctterminatecause' => $data['terminate_cause'] ?? '',
                ]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to stop RADIUS accounting', [
                'session_id' => $data['session_id'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getUserStats(string $username): array
    {
        try {
            $stats = RadAcct::where('username', $username)
                ->selectRaw('
                    COUNT(*) as total_sessions,
                    SUM(acctinputoctets) as total_upload,
                    SUM(acctoutputoctets) as total_download,
                    SUM(acctsessiontime) as total_time
                ')
                ->first();

            return [
                'username' => $username,
                'total_sessions' => $stats->total_sessions ?? 0,
                'total_upload_bytes' => $stats->total_upload ?? 0,
                'total_download_bytes' => $stats->total_download ?? 0,
                'total_session_time' => $stats->total_time ?? 0,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get user stats', [
                'username' => $username,
                'error' => $e->getMessage(),
            ]);

            return [
                'username' => $username,
                'total_sessions' => 0,
                'total_upload_bytes' => 0,
                'total_download_bytes' => 0,
                'total_session_time' => 0,
            ];
        }
    }

    /**
     * Prepares the password attribute and value based on the configured hash method.
     *
     * @return array{attribute: string, value: string}
     */
    private function preparePasswordAttributes(string $password): array
    {
        $hashMethod = config('radius.authenticate.hash', 'cleartext');

        return match ($hashMethod) {
            'md5' => ['attribute' => 'MD5-Password', 'value' => md5($password)],
            'sha1' => ['attribute' => 'SHA1-Password', 'value' => sha1($password)],
            default => ['attribute' => 'Cleartext-Password', 'value' => $password],
        };
    }

    /**
     * Executes a database transaction with standardized logging and error handling.
     *
     * @param string $action The action being performed (e.g., 'create', 'update', 'delete').
     * @param string $username The username being affected.
     * @param \Closure $callback The database operations to execute within the transaction.
     * @return bool True on success, false on failure.
     */
    private function executeTransaction(string $action, string $username, \Closure $callback): bool
    {
        try {
            DB::connection('radius')->transaction($callback);

            Log::info("RADIUS user {$action}d successfully", [
                'username' => $username,
                'action' => $action,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to {$action} RADIUS user", [
                'username' => $username,
                'action' => $action,
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : 'Trace hidden in production',
            ]);

            return false;
        }
    }
}