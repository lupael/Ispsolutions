# OLT SNMP MIB Updates and Vendor OIDs

This document tracks vendor-specific SNMP OIDs that were added or updated in the project.

## V‑SOL (V1600D series) — Added 2026-02-03 ✅

- Enterprise tree: `.1.3.6.1.4.1.11606`
- PON Port Table (nmsEponOltPonTable): `.1.3.6.1.4.1.11606.10.101.6.1`
- ONU LLID Sequence: `.1.3.6.1.4.1.11606.10.101.6.1.1.2`
- ONU Authentication Method: `.1.3.6.1.4.1.11606.10.101.6.1.1.3`
- Check ONU MAC: `.1.3.6.1.4.1.11606.10.101.6.1.1.4`
- Symbolic MIB fallback (FD-OLT-MIB): `FD-OLT-MIB::nmsEponOltPonTable`

Notes:

- Many V‑SOL OLTs require SNMP to be enabled via CLI (some firmwares block SNMP/Telnet by default). Use the OLT CLI to enable SNMP or central management (BS-EMS) before running discovery. If SNMP is not available, the `olt:snmp-test` diagnostic command (recommended) can help verify which OIDs are exposed by a device.

- The code now tries the EPON standard OID first, then the FD-OLT (`nmsEponOltPonTable`) enterprise table, then V1600D numeric fallbacks and symbolic fallbacks when MIBs are present.

---

If you have a short `snmpwalk` sample from a V‑SOL OLT, paste it here and I will finalize index parsing and, if needed, add further numeric OID fallbacks.
