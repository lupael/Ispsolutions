<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RouterConfigurationTemplate extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'template_type',
        'configuration',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'configuration' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function provisioningLogs(): HasMany
    {
        return $this->hasMany(RouterProvisioningLog::class, 'template_id');
    }

    /**
     * Replace placeholders in configuration with actual values.
     *
     * @param  array<string, mixed>  $variables
     * @return array<string, mixed>
     */
    public function interpolateConfiguration(array $variables): array
    {
        $config = $this->configuration;
        $json = json_encode($config);

        foreach ($variables as $key => $value) {
            $json = str_replace('{{ '.$key.' }}', (string) $value, $json);
        }

        return json_decode($json, true) ?? [];
    }
}
