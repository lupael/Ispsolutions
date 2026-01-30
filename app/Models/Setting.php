<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Settings Model
 *
 * Generic key-value settings storage for system configuration.
 *
 * @property int $id
 * @property string $key
 * @property mixed $value
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Setting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'value' => 'array',
    ];

    /**
     * Get a setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value.
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public static function set(string $key, $value): self
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Check if a setting exists.
     *
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        return self::where('key', $key)->exists();
    }

    /**
     * Delete a setting.
     *
     * @param string $key
     * @return bool
     */
    public static function remove(string $key): bool
    {
        return self::where('key', $key)->delete() > 0;
    }
}
