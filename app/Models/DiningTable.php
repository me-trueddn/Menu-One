<?php

namespace App\Models;

use App\Enums\TableStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class DiningTable extends Model
{
    use BelongsToTenant;

    protected $table = 'cafe_tables';

    protected $fillable = [
        'tenant_id',
        'name',
        'capacity',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => TableStatus::class,
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'cafe_table_id');
    }

    public function activeOrder(): ?Order
    {
        return $this->orders()
            ->whereIn('status', ['open', 'sent'])
            ->latest()
            ->first();
    }
}
