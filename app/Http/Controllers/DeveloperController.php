<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class DeveloperController extends Controller
{
    public function index()
    {
        $superAdmins = User::role('super-admin')->get();
        return view('panels.developer.index', compact('superAdmins'));
    }

    public function create()
    {
        return view('panels.developer.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        $user = User::create($request->all());
        $user->assignRole('super-admin');

        return redirect()->route('developer.index')->with('success', 'Super Admin created successfully.');
    }

    public function edit(User $user)
    {
        return view('panels.developer.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8',
        ]);

        $user->update($request->except('password'));

        if ($request->password) {
            $user->update(['password' => $request->password]);
        }

        return redirect()->route('developer.index')->with('success', 'Super Admin updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('developer.index')->with('success', 'Super Admin deleted successfully.');
    }
}
