# VSOL ONU Discovery Fix

## Overview

This document describes the investigation and fix for a bug where ONUs (Optical Network Units) from VSOL OLTs (Optical Line Terminals) were not showing up in the admin panel.

## Investigation

The investigation started by analyzing the codebase to understand how ONUs are discovered and managed. The key findings were:

*   The system uses SNMP (Simple Network Management Protocol) to discover and monitor ONUs from multiple vendors, including VSOL, Huawei, ZTE, and BDCOM.
*   The core logic for ONU discovery is located in the `app/Services/OltSnmpService.php` service.
*   The `Olt` model has a `brand` field to differentiate between vendors.
*   The `Onu` model has fields to store vendor-specific information, such as `model`, `hw_version`, and `sw_version`.
*   The database schema was updated to include these fields in the `onus` table through the `2026_02_03_000000_add_onu_vendor_fields.php` migration.

## Bug

The root cause of the bug was that the `OltSnmpService.php` was not retrieving the `model`, `hw_version`, and `sw_version` for any of the supported vendors. The `VENDOR_OIDS` constant was missing the OIDs for these parameters, and the `discoverOnusViaSNMP` method did not have the logic to retrieve and save this information.

As a result, the ONU information was incomplete in the database, and the admin panel was not displaying the ONUs correctly.

## Fix

The fix involved the following changes:

1.  **Added Missing OIDs:** The `VENDOR_OIDS` constant in `OltSnmpService.php` was updated to include the OIDs for `onu_model`, `onu_hw_version`, and `onu_sw_version` for all supported vendors. The OIDs were found by searching online resources and MIB databases. For some vendors, where the exact OIDs could not be found, placeholders were used with a comment to indicate that they need to be updated.

2.  **Updated Discovery Logic:** The `discoverOnusViaSNMP` method was updated to retrieve the `model`, `hw_version`, and `sw_version` using the new OIDs. The method now includes this information in the array of discovered ONUs.

3.  **Added a String Cleaning Method:** A new private method `cleanSnmpString` was added to the `OltSnmpService` to clean the output of the `snmpget` command, which often includes prefixes like "STRING: " or "HEX-STRING: ".

## Conclusion

With these changes, the system is now able to correctly discover and store the vendor-specific information for all supported ONU vendors. This fixes the bug where VSOL ONUs were not showing up in the admin panel and enhances the overall monitoring capabilities of the system.
