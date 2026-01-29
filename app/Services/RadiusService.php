<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\RadiusServiceInterface;
use App\Models\NetworkUser;
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
        try {
            DB::connection('radius')->transaction(function () use ($username, $password, $attributes) {
                // Create password check entry
                RadCheck::create([
                    'username' => $username,
                    'attribute' => 'Cleartext-Password',
                    'op' => ':=',
                    'value' => $password,
                ]);

                // Create reply attributes
                foreach ($attributes as $attribute => $value) {
                    RadReply::create([
                        'username' => $username,
                        'attribute' => $attribute,
                        'op' => '=',
                        'value' => $value,
                    ]);
                }
            });

            Log::info('RADIUS user created', ['username' => $username]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to create RADIUS user', [
                'username' => $username,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function updateUser(string $username, array $attributes): bool
    {
        try {
            DB::connection('radius')->transaction(function () use ($username, $attributes) {
                // Update password if provided
                if (isset($attributes['password'])) {
                    RadCheck::where('username', $username)
                        ->where('attribute', 'Cleartext-Password')
                        ->update(['value' => $attributes['password']]);
                    unset($attributes['password']);
                }

                // Update reply attributes
                foreach ($attributes as $attribute => $value) {
                    RadReply::updateOrCreate(
                        [
                            'username' => $username,
                            'attribute' => $attribute,
                        ],
                        [
                            'op' => '=',
                            'value' => $value,
                        ]
                    );
                }
            });

            Log::info('RADIUS user updated', ['username' => $username]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update RADIUS user', [
                'username' => $username,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function deleteUser(string $username): bool
    {
        try {
            DB::connection('radius')->transaction(function () use ($username) {
                RadCheck::where('username', $username)->delete();
                RadReply::where('username', $username)->delete();
            });

            Log::info('RADIUS user deleted', ['username' => $username]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete RADIUS user', [
                'username' => $username,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function syncUser(NetworkUser $user, array $attributes = []): bool
    {
        try {
            // Check if user exists in RADIUS
            $exists = RadCheck::where('username', $user->username)->exists();

            if ($user->status === 'active') {
                // Prepare attributes
                $password = $attributes['password'] ?? $user->password;
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

            Log::debug('RADIUS authenticate: Checking credentials in database', [
                'username' => $username,
                'connection' => config('radius.connection', 'radius'),
            ]);

            // Check if user exists and password matches
            $user = RadCheck::where('username', $username)
                ->where('attribute', 'Cleartext-Password')
                ->where('value', $password)
                ->first();

            if ($user) {
                Log::info('RADIUS authenticate: User found and authenticated', [
                    'username' => $username,
                ]);
                
                return [
                    'success' => true,
                    'username' => $username,
                    'message' => 'Authentication successful',
                ];
            }

            // Check if user exists at all
            $userExists = RadCheck::where('username', $username)->exists();
            
            if ($userExists) {
                Log::warning('RADIUS authenticate: User exists but password mismatch', [
                    'username' => $username,
                ]);
            } else {
                Log::warning('RADIUS authenticate: User not found in database', [
                    'username' => $username,
                ]);
            }

            return [
                'success' => false,
                'username' => $username,
                'message' => $userExists ? 'Invalid password' : 'User not found',
            ];
        } catch (\Exception $e) {
            Log::error('RADIUS authentication error', [
                'username' => $data['username'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Authentication error: ' . $e->getMessage(),
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
}
