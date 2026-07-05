<?php

namespace App\Models;

use App\Support\ImageStorage;
use App\Support\MediaLimits;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Category extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'sort_order',
        'image_path',
    ];

    public function imageUrl(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return ImageStorage::url($this->image_path, MediaLimits::variantForContext(MediaLimits::CONTEXT_PRODUCT));
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
