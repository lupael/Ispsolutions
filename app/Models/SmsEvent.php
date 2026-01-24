<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsEvent extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'event_name',
        'event_label',
        'message_template',
        'is_active',
        'available_variables',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'available_variables' => 'array',
    ];

    /**
     * Replace variables in template with actual values
     */
    public function renderMessage(array $data): string
    {
        $message = $this->message_template;

        foreach ($data as $key => $value) {
            $message = str_replace('{' . $key . '}', $value, $message);
        }

        return $message;
    }

    /**
     * Get all active SMS events
     */
    public static function active()
    {
        return static::where('is_active', true)->get();
    }
}
