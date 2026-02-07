<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class SuperAdminController extends Controller
{
    public function index()
    {
        $admins = User::role('admin')->where('parent_id', Auth::id())->get();
        return view('super-admin.index', compact('admins'));
    }

    public function create()
    {
        return view('super-admin.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        $user = User::create(array_merge($request->all(), ['parent_id' => Auth::id()]));
        $user->assignRole('admin');

        return redirect()->route('super-admin.index')->with('success', 'Admin created successfully.');
    }

    public function edit(User $user)
    {
        return view('super-admin.edit', compact('user'));
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

        return redirect()->route('super-admin.index')->with('success', 'Admin updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('super-admin.index')->with('success', 'Admin deleted successfully.');
    }
}
