<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\MikrotikRouter;
use App\Models\RouterConfigurationBackup;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * BackupCreated Event
 *
 * Fired when a router configuration backup is created.
 */
class BackupCreated
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public MikrotikRouter $router,
        public RouterConfigurationBackup $backup,
        public string $backupType,
        public ?string $reason = null
    ) {
    }

    /**
     * Get backup details.
     */
    public function getBackupDetails(): array
    {
        return [
            'router_id' => $this->router->id,
            'router_name' => $this->router->name,
            'backup_id' => $this->backup->id,
            'backup_type' => $this->backupType,
            'reason' => $this->reason,
            'created_by' => $this->backup->created_by,
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}
