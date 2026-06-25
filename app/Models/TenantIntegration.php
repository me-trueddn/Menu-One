<?php

namespace App\Models;

use App\Enums\IntegrationProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class TenantIntegration extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'provider',
        'is_enabled',
        'config',
        'webhook_secret',
        'last_synced_at',
        'last_error',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'config' => 'array',
            'last_synced_at' => 'datetime',
        ];
    }

    public function providerEnum(): IntegrationProvider
    {
        return IntegrationProvider::from($this->provider);
    }

    public function configValue(string $key, mixed $default = null): mixed
    {
        $config = $this->config ?? [];

        if (! array_key_exists($key, $config)) {
            return $default;
        }

        $value = $config[$key];

        if (is_string($value) && str_starts_with($value, 'enc:')) {
            try {
                return Crypt::decryptString(substr($value, 4));
            } catch (\Throwable) {
                return $default;
            }
        }

        return $value;
    }

    public function setConfigValue(string $key, mixed $value, bool $secret = false): void
    {
        $config = $this->config ?? [];

        if ($secret && is_string($value) && $value !== '') {
            $config[$key] = 'enc:'.Crypt::encryptString($value);
        } else {
            $config[$key] = $value;
        }

        $this->config = $config;
    }

    public function webhookSecretPlain(): ?string
    {
        if (! $this->webhook_secret) {
            return null;
        }

        try {
            return Crypt::decryptString($this->webhook_secret);
        } catch (\Throwable) {
            return null;
        }
    }

    public function setWebhookSecret(?string $plain): void
    {
        if ($plain === null || trim($plain) === '') {
            return;
        }

        $this->webhook_secret = Crypt::encryptString(trim($plain));
    }

    public function hasWebhookSecret(): bool
    {
        return is_string($this->webhook_secret) && $this->webhook_secret !== '';
    }

    public static function forProvider(IntegrationProvider $provider): ?self
    {
        return static::query()
            ->where('provider', $provider->value)
            ->first();
    }

    public static function upsertForProvider(IntegrationProvider $provider, array $attributes): self
    {
        return static::query()->updateOrCreate(
            ['provider' => $provider->value],
            $attributes,
        );
    }
}
