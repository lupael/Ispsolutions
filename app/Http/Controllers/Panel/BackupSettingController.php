<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\BackupSetting;
use App\Models\Nas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Backup Settings Controller
 *
 * Manages backup settings for operator authentication.
 * Defines the primary router for authentication.
 */
class BackupSettingController extends Controller
{
    /**
     * Display backup settings.
     */
    public function index()
    {
        $operator = Auth::user();
        $backupSetting = BackupSetting::where('operator_id', $operator->id)->first();
        $routers = Nas::where('tenant_id', $operator->tenant_id)->get();

        if ($backupSetting) {
            return view('panel.backup-settings.edit', [
                'backupSetting' => $backupSetting,
                'routers' => $routers,
            ]);
        }

        return view('panel.backup-settings.create', [
            'routers' => $routers,
        ]);
    }

    /**
     * Show the form for creating backup settings.
     */
    public function create()
    {
        $operator = Auth::user();
        $routers = Nas::where('tenant_id', $operator->tenant_id)->get();

        if ($routers->isEmpty()) {
            return redirect()->route('panel.admin.network.routers.create')
                ->with('warning', 'Please add at least one router before configuring backup settings.');
        }

        return view('panel.backup-settings.create', [
            'routers' => $routers,
        ]);
    }

    /**
     * Store backup settings.
     */
    public function store(Request $request)
    {
        $operator = Auth::user();
        
        $request->validate([
            'nas_id' => [
                'required',
                'exists:nas,id,tenant_id,' . $operator->tenant_id,
            ],
        ]);

        BackupSetting::updateOrCreate(
            ['operator_id' => $operator->id],
            [
                'nas_id' => $request->nas_id,
                'primary_authenticator' => 'Radius',
            ]
        );

        return redirect()->route('panel.admin.backup-settings.index')
            ->with('success', 'Backup settings configured successfully.');
    }

    /**
     * Show the form for editing backup settings.
     */
    public function edit()
    {
        $operator = Auth::user();
        $backupSetting = BackupSetting::where('operator_id', $operator->id)->firstOrFail();
        $routers = Nas::where('tenant_id', $operator->tenant_id)->get();

        return view('panel.backup-settings.edit', [
            'backupSetting' => $backupSetting,
            'routers' => $routers,
        ]);
    }

    /**
     * Update backup settings.
     */
    public function update(Request $request)
    {
        $operator = Auth::user();
        
        $request->validate([
            'nas_id' => [
                'required',
                'exists:nas,id,tenant_id,' . $operator->tenant_id,
            ],
        ]);

        $backupSetting = BackupSetting::where('operator_id', $operator->id)->firstOrFail();

        $backupSetting->update([
            'nas_id' => $request->nas_id,
        ]);

        return redirect()->route('panel.admin.backup-settings.index')
            ->with('success', 'Backup settings updated successfully.');
    }
}
