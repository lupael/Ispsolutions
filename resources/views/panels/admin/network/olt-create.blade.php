<div x-data="{ protocol: '{{ old('management_protocol', $olt->management_protocol ?? 'ssh') }}' }">
    <label for="management_protocol" class="block text-sm font-medium text-gray-700">Management Protocol</label>
    <select id="management_protocol" name="management_protocol" x-model="protocol" class="mt-1 block w-full rounded-md">
        <option value="ssh">SSH</option>
        <option value="telnet">Telnet</option>
        <option value="snmp">SNMP</option>
    </select>

    <div x-show="protocol === 'ssh' || protocol === 'telnet'" class="mt-3">
        <label for="port" class="block text-sm font-medium text-gray-700">SSH/Telnet Port</label>
        <input type="number" id="port" name="port" value="{{ old('port', $olt->port ?? 22) }}" class="mt-1 block w-full rounded-md">
    </div>

    <div x-show="protocol === 'snmp'" class="mt-3">
        <label for="snmp_port" class="block text-sm font-medium text-gray-700">SNMP Port</label>
        <input type="number" id="snmp_port" name="snmp_port" value="{{ old('snmp_port', $olt->snmp_port ?? 161) }}" class="mt-1 block w-full rounded-md">

        <label for="snmp_community" class="block text-sm font-medium text-gray-700 mt-2">SNMP Community</label>
        <input type="text" id="snmp_community" name="snmp_community" value="{{ old('snmp_community', $olt->snmp_community ?? '') }}" class="mt-1 block w-full rounded-md">
    </div>
</div>