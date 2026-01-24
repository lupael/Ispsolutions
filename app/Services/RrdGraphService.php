<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\NetworkUser;
use App\Models\RadAcct;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RrdGraphService
{
    private const RRD_STEP = 300; // 5 minutes
    private const CACHE_TTL = 300; // 5 minutes
    private const GRAPH_WIDTH = 800;
    private const GRAPH_HEIGHT = 300;
    
    private bool $rrdAvailable;
    
    public function __construct()
    {
        $this->rrdAvailable = extension_loaded('rrd');
    }
    
    /**
     * Check if RRD extension is available
     */
    public function isAvailable(): bool
    {
        return $this->rrdAvailable;
    }
    
    /**
     * Get RRD file path for a customer
     */
    private function getRrdPath(int $customerId): string
    {
        $directory = storage_path('app/rrd');
        
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        
        return "{$directory}/{$customerId}.rrd";
    }
    
    /**
     * Create RRD database for a customer
     */
    public function createRrdDatabase(int $customerId): bool
    {
        if (!$this->rrdAvailable) {
            Log::warning('RRD extension not available');
            return false;
        }
        
        $rrdPath = $this->getRrdPath($customerId);
        
        // Don't recreate if already exists
        if (file_exists($rrdPath)) {
            return true;
        }
        
        try {
            $options = [
                '--step', (string) self::RRD_STEP,
                '--start', (string) (time() - 10),
                'DS:upload:COUNTER:600:0:U',
                'DS:download:COUNTER:600:0:U',
                // 1 hour (12 * 5min), avg & max
                'RRA:AVERAGE:0.5:1:12',
                'RRA:MAX:0.5:1:12',
                // 24 hours (288 * 5min), avg & max
                'RRA:AVERAGE:0.5:1:288',
                'RRA:MAX:0.5:1:288',
                // 1 week (2016 * 5min), avg & max
                'RRA:AVERAGE:0.5:1:2016',
                'RRA:MAX:0.5:1:2016',
                // 1 month (8640 * 5min), avg & max
                'RRA:AVERAGE:0.5:1:8640',
                'RRA:MAX:0.5:1:8640',
            ];
            
            $result = rrd_create($rrdPath, $options);
            
            if ($result === false) {
                Log::error('Failed to create RRD database', [
                    'customer_id' => $customerId,
                    'error' => rrd_error(),
                ]);
                return false;
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error('Exception creating RRD database', [
                'customer_id' => $customerId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
    
    /**
     * Update RRD database with bandwidth data
     */
    public function updateBandwidthData(int $customerId, int $upload, int $download): bool
    {
        if (!$this->rrdAvailable) {
            return false;
        }
        
        $rrdPath = $this->getRrdPath($customerId);
        
        // Create database if it doesn't exist
        if (!file_exists($rrdPath)) {
            if (!$this->createRrdDatabase($customerId)) {
                return false;
            }
        }
        
        try {
            $timestamp = time();
            $update = "{$timestamp}:{$upload}:{$download}";
            
            $result = rrd_update($rrdPath, [$update]);
            
            if ($result === false) {
                Log::error('Failed to update RRD database', [
                    'customer_id' => $customerId,
                    'error' => rrd_error(),
                ]);
                return false;
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error('Exception updating RRD database', [
                'customer_id' => $customerId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
    
    /**
     * Collect bandwidth data from radacct for a customer
     */
    public function collectCustomerBandwidth(NetworkUser $customer): bool
    {
        try {
            // Get the latest session data from radacct
            $session = RadAcct::where('username', $customer->username)
                ->whereNull('acctstoptime')
                ->orderByDesc('acctstarttime')
                ->first();
            
            if (!$session) {
                // No active session, update with zeros
                return $this->updateBandwidthData(
                    $customer->id,
                    0,
                    0
                );
            }
            
            // Update with current session data
            // Note: COUNTER type expects cumulative values
            return $this->updateBandwidthData(
                $customer->id,
                (int) $session->acctinputoctets,
                (int) $session->acctoutputoctets
            );
        } catch (\Exception $e) {
            Log::error('Failed to collect bandwidth data', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
    
    /**
     * Generate bandwidth graph
     */
    public function generateGraph(int $customerId, string $timeframe = 'hourly'): ?string
    {
        if (!$this->rrdAvailable) {
            return $this->generateFallbackGraph($timeframe);
        }
        
        // Check cache first
        $cacheKey = "bandwidth_graph_{$customerId}_{$timeframe}";
        $cached = Cache::get($cacheKey);
        
        if ($cached !== null) {
            return $cached;
        }
        
        $rrdPath = $this->getRrdPath($customerId);
        
        if (!file_exists($rrdPath)) {
            return $this->generateFallbackGraph($timeframe);
        }
        
        try {
            $graphPath = storage_path("app/graphs/{$customerId}_{$timeframe}.png");
            $graphDir = dirname($graphPath);
            
            if (!file_exists($graphDir)) {
                mkdir($graphDir, 0755, true);
            }
            
            $options = $this->getGraphOptions($rrdPath, $graphPath, $timeframe);
            
            try {
                $result = rrd_graph($graphPath, $options);
                
                // rrd_graph returns false on error, but PHPStan thinks it's always array
                // @phpstan-ignore-next-line
                if ($result === false || empty($result)) {
                    $error = rrd_error();
                    Log::error('Failed to generate RRD graph', [
                        'customer_id' => $customerId,
                        'timeframe' => $timeframe,
                        'error' => $error ?: 'Unknown RRD error',
                    ]);
                    return $this->generateFallbackGraph($timeframe);
                }
            } catch (\Throwable $e) {
                Log::error('Exception generating RRD graph', [
                    'customer_id' => $customerId,
                    'timeframe' => $timeframe,
                    'error' => $e->getMessage(),
                ]);
                return $this->generateFallbackGraph($timeframe);
            }
            
            // Read and encode the graph
            $imageData = file_get_contents($graphPath);
            $base64 = base64_encode($imageData);
            
            // Clean up temporary file
            @unlink($graphPath);
            
            // Cache the result
            Cache::put($cacheKey, $base64, self::CACHE_TTL);
            
            return $base64;
        } catch (\Exception $e) {
            Log::error('Exception generating RRD graph', [
                'customer_id' => $customerId,
                'timeframe' => $timeframe,
                'error' => $e->getMessage(),
            ]);
            return $this->generateFallbackGraph($timeframe);
        }
    }
    
    /**
     * Get graph options based on timeframe
     */
    private function getGraphOptions(string $rrdPath, string $graphPath, string $timeframe): array
    {
        $now = time();
        
        switch ($timeframe) {
            case 'hourly':
                $start = $now - 3600; // Last 1 hour
                $title = 'Bandwidth Usage - Last Hour';
                break;
            case 'daily':
                $start = $now - 86400; // Last 24 hours
                $title = 'Bandwidth Usage - Last 24 Hours';
                break;
            case 'weekly':
                $start = $now - 604800; // Last 7 days
                $title = 'Bandwidth Usage - Last 7 Days';
                break;
            case 'monthly':
                $start = $now - 2592000; // Last 30 days
                $title = 'Bandwidth Usage - Last 30 Days';
                break;
            default:
                $start = $now - 3600;
                $title = 'Bandwidth Usage';
        }
        
        return [
            '--start', (string) $start,
            '--end', (string) $now,
            '--title', $title,
            '--vertical-label', 'Bits per second',
            '--width', (string) self::GRAPH_WIDTH,
            '--height', (string) self::GRAPH_HEIGHT,
            '--lower-limit', '0',
            '--rigid',
            '--alt-autoscale',
            '--alt-autoscale-max',
            'DEF:upload=' . $rrdPath . ':upload:AVERAGE',
            'DEF:download=' . $rrdPath . ':download:AVERAGE',
            'CDEF:upload_bits=upload,8,*',
            'CDEF:download_bits=download,8,*',
            'AREA:upload_bits#00FF00:Upload',
            'LINE1:upload_bits#00CC00',
            'AREA:download_bits#0000FF:Download:STACK',
            'LINE1:download_bits#0000CC',
            'GPRINT:upload_bits:LAST:Current Upload\\: %6.2lf %Sbps',
            'GPRINT:upload_bits:AVERAGE:Average Upload\\: %6.2lf %Sbps',
            'GPRINT:upload_bits:MAX:Max Upload\\: %6.2lf %Sbps\\n',
            'GPRINT:download_bits:LAST:Current Download\\: %6.2lf %Sbps',
            'GPRINT:download_bits:AVERAGE:Average Download\\: %6.2lf %Sbps',
            'GPRINT:download_bits:MAX:Max Download\\: %6.2lf %Sbps\\n',
        ];
    }
    
    /**
     * Generate a fallback graph when RRD is not available
     */
    private function generateFallbackGraph(string $timeframe): string
    {
        // Create a simple placeholder image
        $width = self::GRAPH_WIDTH;
        $height = self::GRAPH_HEIGHT;
        
        $image = imagecreate($width, $height);
        
        // Colors
        $bgColor = imagecolorallocate($image, 255, 255, 255);
        $textColor = imagecolorallocate($image, 0, 0, 0);
        $borderColor = imagecolorallocate($image, 200, 200, 200);
        
        // Draw border
        imagerectangle($image, 0, 0, $width - 1, $height - 1, $borderColor);
        
        // Add text
        $text = "Bandwidth Graph ({$timeframe})";
        $text2 = "RRD extension not available";
        $text3 = "Please install php-rrd extension";
        
        imagestring($image, 5, 250, 120, $text, $textColor);
        imagestring($image, 3, 250, 140, $text2, $textColor);
        imagestring($image, 3, 220, 160, $text3, $textColor);
        
        // Capture image to buffer
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);
        
        return base64_encode($imageData);
    }
    
    /**
     * Get graph for customer by timeframe
     */
    public function getCustomerGraph(int $customerId, string $timeframe = 'hourly'): ?string
    {
        return $this->generateGraph($customerId, $timeframe);
    }
    
    /**
     * Clean up old RRD files
     */
    public function cleanup(int $daysOld = 90): int
    {
        $directory = storage_path('app/rrd');
        
        if (!file_exists($directory)) {
            return 0;
        }
        
        $count = 0;
        $cutoffTime = time() - ($daysOld * 86400);
        
        $files = glob("{$directory}/*.rrd");
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                @unlink($file);
                $count++;
            }
        }
        
        return $count;
    }
}
