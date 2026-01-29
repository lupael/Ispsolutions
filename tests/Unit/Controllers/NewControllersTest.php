<?php

declare(strict_types=1);

namespace Tests\Unit\Controllers;

use App\Http\Controllers\Panel\CustomerBackupController;
use App\Http\Controllers\Panel\NasNetWatchController;
use App\Services\MikrotikApiService;
use Tests\TestCase;

class NewControllersTest extends TestCase
{
    public function test_nas_netwatch_controller_can_be_instantiated(): void
    {
        $apiService = $this->createMock(MikrotikApiService::class);
        $controller = new NasNetWatchController($apiService);

        $this->assertInstanceOf(NasNetWatchController::class, $controller);
    }

    public function test_customer_backup_controller_can_be_instantiated(): void
    {
        $apiService = $this->createMock(MikrotikApiService::class);
        $controller = new CustomerBackupController($apiService);

        $this->assertInstanceOf(CustomerBackupController::class, $controller);
    }
}
