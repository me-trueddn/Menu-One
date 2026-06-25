<?php

namespace App\Models;

use App\Enums\IntegrationProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class IntegrationProductMapping extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'provider',
        'external_id',
        'external_name',
        'product_id',
    ];

    public function providerEnum(): IntegrationProvider
    {
        return IntegrationProvider::from($this->provider);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
