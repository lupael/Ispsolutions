<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ImportPppCustomersRequested;
use App\Jobs\ImportPppCustomersJob;
use Illuminate\Support\Facades\Log;

class ImportPppCustomersListener
{
    /**
     * Handle the event.
     */
    public function handle(ImportPppCustomersRequested $event): void
    {
        Log::info('PPP customers import requested', [
            'operator_id' => $event->operatorId,
            'nas_id' => $event->nasId,
            'options' => $event->options,
        ]);

        // Dispatch the import job
        ImportPppCustomersJob::dispatch(
            $event->operatorId,
            $event->nasId,
            $event->getOptions()
        )->onQueue('imports');
    }
}
