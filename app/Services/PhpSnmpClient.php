<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\SnmpClientInterface;
use App\Models\Olt;

class PhpSnmpClient implements SnmpClientInterface
{
    public function walk(Olt $olt, string $oid): array
    {
        // Use PHP SNMP extension if available
        if (! function_exists('snmp2_walk')) {
            throw new \RuntimeException('PHP SNMP extension is not available');
        }

        $community = $olt->snmp_community;
        $host = $olt->ip_address;
        $result = @snmp2_walk($host, $community, $oid, 1000000, 2);

        if ($result === false) {
            return [];
        }

        $parsed = [];
        foreach ($result as $entry) {
            $parsed[] = (string) $entry;
        }

        return $parsed;
    }

    public function get(Olt $olt, string $oid)
    {
        if (! function_exists('snmp2_get')) {
            throw new \RuntimeException('PHP SNMP extension is not available');
        }

        $community = $olt->snmp_community;
        $host = $olt->ip_address;

        $result = @snmp2_get($host, $community, $oid);

        if ($result === false) {
            return null;
        }

        return $result;
    }
}
