<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Olt;

interface SnmpClientInterface
{
    /**
     * Walk an OID on the given OLT and return an array of OID index => value.
     *
     * @param Olt $olt
     * @param string $oid
     * @return array<int|string, string>
     */
    public function walk(Olt $olt, string $oid): array;

    /**
     * Get a single OID value from the given OLT.
     *
     * @param Olt $olt
     * @param string $oid
     * @return string|int|bool|null The value of the OID, or false/null on failure.
     */
    public function get(Olt $olt, string $oid): string|int|bool|null;
}
