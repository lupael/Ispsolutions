<?php

namespace App\Http\Controllers;

use App\Models\Nas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RouterController extends Controller
{
    public function index()
    {
        $routers = Nas::all();
        return view('routers.index', compact('routers'));
    }

    public function create()
    {
        // This view doesn't exist yet. I will create it later.
        return view('routers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nasname' => 'required|ip',
            'shortname' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'secret' => 'required|string|min:16',
            'api_username' => 'required|string|max:255',
            'api_password' => 'required|string|max:255',
            'api_port' => 'required|integer',
            'community' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'server' => 'nullable|string|max:255',
            'ports' => 'nullable|integer',
        ]);

        $nas = new Nas($request->all());
        $nas->tenant_id = Auth::user()->tenant_id;
        $nas->admin_id = Auth::user()->admin_id;
        $nas->operator_id = Auth::id();
        $nas->save();

        return redirect()->route('panel.admin.routers.index')->with('success', 'Router added successfully.');
    }

    public function edit(Nas $router)
    {
        return view('routers.edit', compact('router'));
    }

    public function update(Request $request, Nas $router)
    {
        $request->validate([
            'nasname' => 'required|ip',
            'shortname' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'secret' => 'nullable|string|min:16',
            'api_username' => 'required|string|max:255',
            'api_password' => 'required|string|max:255',
            'api_port' => 'required|integer',
            'community' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $data = $request->except('secret');
        if ($request->filled('secret')) {
            $data['secret'] = $request->secret;
        }

        $router->update($data);

        return redirect()->route('panel.admin.routers.index')->with('success', 'Router updated successfully.');
    }

    public function destroy(Nas $router)
    {
        $router->delete();
        return redirect()->route('panel.admin.routers.index')->with('success', 'Router deleted successfully.');
    }
}
