<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index()
    {
        $users = User::where('parent_id', Auth::id())->get();
        return view('panels.admin.index', compact('users'));
    }

    public function create()
    {
        return view('panels.admin.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        User::create(array_merge($request->all(), ['parent_id' => Auth::id()]));

        return redirect()->route('admin.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        return view('panels.admin.edit', compact('user'));
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

        return redirect()->route('admin.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.index')->with('success', 'User deleted successfully.');
    }
}
