<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OltConfigurationTemplate extends Model
{
    protected $fillable = [
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
        if (!is_array($values) || array_values($values) === $values) {
            throw new \InvalidArgumentException('Template values must be an associative array');
        }
        
        foreach ($values as $key => $value) {
            // Validate key format (alphanumeric and underscore only)
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $key)) {
                throw new \InvalidArgumentException("Invalid template variable key: {$key}");
            }
            
            // Escape the value to prevent injection
            $escapedValue = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
            $content = str_replace("{{" . $key . "}}", $escapedValue, $content);
        }
        
        return $content;
    }
}
