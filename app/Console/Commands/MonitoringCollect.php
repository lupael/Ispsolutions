<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\MonitoringServiceInterface;
use App\Models\MikrotikRouter;
use App\Models\Olt;
use App\Models\Onu;
use Illuminate\Console\Command;

class MonitoringCollect extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitoring:collect
                            {--type= : Device type to monitor (router/olt/onu)}
                            {--id= : Specific device ID to monitor}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collect metrics from all network devices';

    /**
     * Execute the console command.
     */
    public function handle(MonitoringServiceInterface $monitoringService): int
    {
        $type = $this->option('type');
        $id = $this->option('id');

        $this->info('Starting device monitoring...');

        if ($type && $id) {
            return $this->monitorSpecificDevice($monitoringService, $type, (int) $id);
        }

        if ($type) {
            return $this->monitorDeviceType($monitoringService, $type);
        }

        return $this->monitorAllDevices($monitoringService);
    }

    /**
     * Monitor a specific device
     */
    private function monitorSpecificDevice(MonitoringServiceInterface $service, string $type, int $id): int
    {
        try {
            $this->info("Monitoring {$type} #{$id}...");
            $metrics = $service->monitorDevice($type, $id);

            $this->line("  Status: {$metrics['status']}");
            if (isset($metrics['cpu_usage'])) {
                $this->line("  CPU: {$metrics['cpu_usage']}%");
            }
            if (isset($metrics['memory_usage'])) {
                $this->line("  Memory: {$metrics['memory_usage']}%");
            }

            $this->info('✓ Monitoring completed');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to monitor {$type} #{$id}: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    /**
     * Monitor all devices of a specific type
     */
    private function monitorDeviceType(MonitoringServiceInterface $service, string $type): int
    {
        $devices = match ($type) {
            'router' => MikrotikRouter::where('status', 'active')->get(),
            'olt' => Olt::active()->get(),
            'onu' => Onu::online()->get(),
            default => throw new \InvalidArgumentException("Invalid device type: {$type}"),
        };

        if ($devices->isEmpty()) {
            $this->warn("No active {$type}s found");

            return self::SUCCESS;
        }

        $this->info("Monitoring {$devices->count()} {$type}(s)...");
        $bar = $this->output->createProgressBar($devices->count());

        $success = 0;
        $failed = 0;

        foreach ($devices as $device) {
            try {
                $service->monitorDevice($type, $device->id);
                $success++;
            } catch (\Exception $e) {
                $failed++;
                $this->newLine();
                $this->error("Failed {$type} #{$device->id}: {$e->getMessage()}");
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Monitoring completed: {$success} successful, {$failed} failed");

        return self::SUCCESS;
    }

    /**
     * Monitor all devices
     */
    private function monitorAllDevices(MonitoringServiceInterface $service): int
    {
        $deviceTypes = ['router', 'olt', 'onu'];
        $overallResult = self::SUCCESS;

        foreach ($deviceTypes as $type) {
            $result = $this->monitorDeviceType($service, $type);
            if ($result === self::FAILURE) {
                $overallResult = self::FAILURE;
            }
            $this->newLine();
        }

        $this->info("All device monitoring completed.");
        return $overallResult;
    }
}
