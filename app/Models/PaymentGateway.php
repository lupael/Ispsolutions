<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use BelongsToTenant, HasFactory;

    // Gateway type constants
    public const TYPE_STRIPE = 'stripe';
    public const TYPE_BKASH = 'bkash';
    public const TYPE_NAGAD = 'nagad';
    public const TYPE_SSLCOMMERZ = 'sslcommerz';
    public const TYPE_PAYPAL = 'paypal';
    public const TYPE_RAZORPAY = 'razorpay';

    protected $fillable = [
        'tenant_id',
        'name',
        'slug', // bkash, nagad, stripe, paypal, sslcommerz, razorpay
        'is_active',
        'configuration', // JSON field for API keys, secrets, etc.
        'test_mode',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'test_mode' => 'boolean',
        'configuration' => 'encrypted:array',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
