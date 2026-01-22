<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PerformanceMonitoringService
{
    private static int $queryCount = 0;

    private static array $slowQueries = [];

    private static float $startTime;

    /**
     * Start monitoring performance.
     */
    public static function start(): void
    {
        self::$startTime = microtime(true);
        self::$queryCount = 0;
        self::$slowQueries = [];

        // Listen for queries
        DB::listen(function ($query) {
            self::$queryCount++;

            // Log slow queries (>100ms)
            if ($query->time > 100) {
                self::$slowQueries[] = [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time,
                ];

                Log::warning('Slow query detected', [
                    'sql' => $query->sql,
                    'time' => $query->time . 'ms',
                    'bindings' => $query->bindings,
                ]);
            }
        });
    }

    /**
     * Stop monitoring and return stats.
     */
    public static function stop(): array
    {
        $endTime = microtime(true);
        $duration = round(($endTime - self::$startTime) * 1000, 2);

        return [
            'duration_ms' => $duration,
            'query_count' => self::$queryCount,
            'slow_queries' => count(self::$slowQueries),
            'slow_query_details' => self::$slowQueries,
        ];
    }

    /**
     * Get current query count.
     */
    public static function getQueryCount(): int
    {
        return self::$queryCount;
    }

    /**
     * Get slow queries.
     */
    public static function getSlowQueries(): array
    {
        return self::$slowQueries;
    }

    /**
     * Log performance metrics.
     */
    public static function logMetrics(string $endpoint, array $metrics): void
    {
        Log::info('Performance metrics', [
            'endpoint' => $endpoint,
            'metrics' => $metrics,
        ]);

        // Check for performance issues
        if ($metrics['duration_ms'] > 500) {
            Log::warning('Slow endpoint detected', [
                'endpoint' => $endpoint,
                'duration' => $metrics['duration_ms'] . 'ms',
            ]);
        }

        if ($metrics['query_count'] > 20) {
            Log::warning('High query count detected (possible N+1)', [
                'endpoint' => $endpoint,
                'query_count' => $metrics['query_count'],
            ]);
        }
    }

    /**
     * Enable query logging for debugging.
     */
    public static function enableQueryLogging(): void
    {
        DB::enableQueryLog();
    }

    /**
     * Get query log.
     */
    public static function getQueryLog(): array
    {
        return DB::getQueryLog();
    }

    /**
     * Disable query logging.
     */
    public static function disableQueryLogging(): void
    {
        DB::disableQueryLog();
    }
}
