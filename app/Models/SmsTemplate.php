<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'message',
        'variables',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Template slug constants
     */
    public const SLUG_INVOICE_GENERATED = 'invoice_generated';

    public const SLUG_PAYMENT_RECEIVED = 'payment_received';

    public const SLUG_INVOICE_EXPIRING = 'invoice_expiring';

    public const SLUG_INVOICE_OVERDUE = 'invoice_overdue';

    public const SLUG_OTP = 'otp';

    public const SLUG_WELCOME = 'welcome';

    public const SLUG_PASSWORD_RESET = 'password_reset';

    /**
     * Render the template with variables
     */
    public function render(array $data): string
    {
        $message = $this->message;

        foreach ($data as $key => $value) {
            $message = str_replace('{' . $key . '}', $value, $message);
        }

        return $message;
    }

    /**
     * Scope to filter active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by slug
     */
    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }
}
