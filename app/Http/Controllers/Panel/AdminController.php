// Inside AdminController: replace existing OLT store/update method signatures/bodies with the following:

use App\Http\Requests\OltRequest;
use App\Models\Olt;

// Store new OLT
public function oltStore(OltRequest $request)
{
    $data = $request->validated();

    // Ensure sensible defaults
    if (empty($data['port']) && in_array($data['management_protocol'], ['ssh', 'telnet'])) {
        $data['port'] = $data['management_protocol'] === 'ssh' ? 22 : 23;
    }
    if (empty($data['snmp_port']) && $data['management_protocol'] === 'snmp') {
        $data['snmp_port'] = 161;
    }

    // Create and persist
    $olt = Olt::create($data);

    return redirect()->route('panel.admin.network.olt')
        ->with('success', 'OLT device created successfully.');
}

// Update existing OLT
public function oltUpdate(int $id, OltRequest $request)
{
    $olt = Olt::findOrFail($id);

    $data = $request->validated();

    if (in_array($data['management_protocol'], ['ssh', 'telnet']) && empty($data['port'])) {
        $data['port'] = $data['management_protocol'] === 'ssh' ? 22 : 23;
    }

    $olt->update($data);

    return redirect()->route('panel.admin.network.olt.edit', $olt->id)
        ->with('success', 'OLT updated successfully.');
}