<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VatCollection extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'invoice_id',
        'payment_id',
        'vat_profile_id',
        'base_amount',
        'vat_amount',
        'total_amount',
        'collection_date',
        'tax_period',
    ];

    protected $casts = [
        'base_amount' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'collection_date' => 'date',
    ];

    /**
     * Get the invoice associated with this VAT collection.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the payment associated with this VAT collection.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get the VAT profile used for this collection.
     */
    public function vatProfile(): BelongsTo
    {
        return $this->belongsTo(VatProfile::class);
    }
}
