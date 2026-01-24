<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImportPppCustomersRequested
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public int $operatorId,
        public int $nasId,
        public array $options = []
    ) {
    }

    /**
     * Get import options.
     */
    public function getOptions(): array
    {
        return array_merge([
            'filter_disabled' => true,
            'generate_bills' => false,
            'package_id' => null,
        ], $this->options);
    }
}
