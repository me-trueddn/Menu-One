<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class OkcSale extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'okc_device_id',
        'order_id',
        'amount',
        'payment_method',
        'status',
        'response_message',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(OkcDevice::class, 'okc_device_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}

