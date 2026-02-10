<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\Olt;
use Illuminate\Support\Str;

/**
 * Helper class for OLT vendor detection.
 *
 * Centralizes vendor detection logic to ensure consistency across
 * SNMP, SSH, and command generation paths.
 */
class OltVendorDetector
{
    /**
     * Supported OLT vendors.
     */
    public const VENDOR_VSOL = 'vsol';
    public const VENDOR_HUAWEI = 'huawei';
    public const VENDOR_ZTE = 'zte';
    public const VENDOR_FIBERHOME = 'fiberhome';
    public const VENDOR_BDCOM = 'bdcom';
    public const VENDOR_CDATA = 'cdata';
    public const VENDOR_GENERIC = 'generic';

    /**
     * Map of vendor constants to their identifying keywords.
     */
    private static array $vendorKeywords = [
        self::VENDOR_VSOL => ['vsol', 'v-sol'],
        self::VENDOR_HUAWEI => ['huawei'],
        self::VENDOR_ZTE => ['zte'],
        self::VENDOR_FIBERHOME => ['fiberhome', 'fiber home'],
        self::VENDOR_BDCOM => ['bdcom'],
        self::VENDOR_CDATA => ['c-data', 'cdata'],
    ];

    /**
     * Detect vendor from OLT model.
     */
    public static function detect(Olt $olt): string
    {
        // Build a single searchable string from available OLT attributes
        $parts = [];

        if (!empty($olt->brand)) {
            $parts[] = (string) $olt->brand;
        }

        if (!empty($olt->model)) {
            $parts[] = (string) $olt->model;
        }

        if (!empty($olt->name)) {
            $parts[] = (string) $olt->name;
        }

        $searchText = trim(implode(' ', $parts));

        return self::detectFromText($searchText);
    }

    /**
     * Core vendor detection logic based on free-form identification text.
     *
     * This helper can be reused to keep vendor detection consistent across
     * different discovery paths (e.g. SNMP vs SSH command generation).
     */
    public static function detectFromText(string $searchText): string
    {
        $searchText = strtolower(trim($searchText));

        if ($searchText === '') {
            return self::VENDOR_GENERIC;
        }

        // Iterate through the keyword map to find a match
        foreach (self::$vendorKeywords as $vendor => $keywords) {
            if (Str::contains($searchText, $keywords)) {
                return $vendor;
            }
        }

        return self::VENDOR_GENERIC;
    }

    /**
     * Get list of all supported vendors.
     */
    public static function getSupportedVendors(): array
    {
        return [
            self::VENDOR_VSOL,
            self::VENDOR_HUAWEI,
            self::VENDOR_ZTE,
            self::VENDOR_FIBERHOME,
            self::VENDOR_BDCOM,
            self::VENDOR_CDATA,
            self::VENDOR_GENERIC,
        ];
    }
}
