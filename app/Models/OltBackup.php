<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * OLT Backup Model
 *
 * Represents a backup file for an OLT device.
 *
 * @property int $id
 * @property int $olt_id
 * @property string $file_path
 * @property int $file_size
 * @property string $backup_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class OltBackup extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'olt_id',
        'file_path',
        'file_size',
        'backup_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'olt_id' => 'integer',
        'file_size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the OLT that owns the backup.
     */
    public function olt(): BelongsTo
    {
        return $this->belongsTo(Olt::class);
    }

    /**
     * Get human-readable file size.
     */
    public function getSize(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    /**
     * Download the backup file.
     */
    public function download(): ?\Symfony\Component\HttpFoundation\StreamedResponse
    {
        if (! Storage::exists($this->file_path)) {
            return null;
        }

        $filename = basename($this->file_path);

        return Storage::download($this->file_path, $filename);
    }

    /**
     * Check if backup file exists.
     */
    public function exists(): bool
    {
        return Storage::exists($this->file_path);
    }

    /**
     * Delete backup file from storage.
     */
    public function deleteFile(): bool
    {
        if (Storage::exists($this->file_path)) {
            return Storage::delete($this->file_path);
        }

        return true;
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Delete file when model is deleted
        static::deleting(function (OltBackup $backup): void {
            $backup->deleteFile();
        });
    }
}
