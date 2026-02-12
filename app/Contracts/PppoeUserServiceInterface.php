<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\User;
use App\Models\MikrotikRouter;
use App\Models\MikrotikProfile;

interface PppoeUserServiceInterface
{
    public function provisionPppoeUser(User $customer, MikrotikRouter $router, ?MikrotikProfile $profile = null, ?string $staticIp = null): bool;

    public function deprovisionPppoeUser(User $customer, MikrotikRouter $router, bool $delete = false): bool;

    public function bulkProvisionPppoeUsers(array $customers, MikrotikRouter $router): array;
}
