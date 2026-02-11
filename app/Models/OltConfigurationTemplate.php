<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OltConfigurationTemplate extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'vendor',
        'model',
        'description',
        'template_content',
        'variables',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByVendor($query, string $vendor)
    {
        return $query->where('vendor', $vendor);
    }

    public function renderTemplate(array $values = []): string
    {
        $content = $this->template_content;

        // Validate that values is an associative array
        if (! is_array($values) || array_values($values) === $values) {
            throw new \InvalidArgumentException('Template values must be an associative array');
        }

        foreach ($values as $key => $value) {
            // Validate key format (alphanumeric and underscore only)
            if (! preg_match('/^[a-zA-Z0-9_]+$/', $key)) {
                throw new \InvalidArgumentException("Invalid template variable key: {$key}");
            }

            // Do not use htmlspecialchars for CLI templates.
            // Just replace the placeholder with the string value.
            $content = str_replace('{{' . $key . '}}', (string) $value, $content);
        }

        return $content;
    }
}
