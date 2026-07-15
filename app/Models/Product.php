<?php

namespace App\Models;

use App\Support\Branding;
use App\Support\ImageStorage;
use App\Support\MediaLimits;
use App\Support\MediaStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Product extends Model
{
    use BelongsToTenant;

    public const UNIT_PIECE = 'piece';

    public const UNIT_KG = 'kg';

    public const UNIT_LITER = 'liter';

    public const UNIT_PORTION = 'portion';

    protected $fillable = [
        'tenant_id',
        'category_id',
        'sort_order',
        'name',
        'code',
        'description',
        'barcode',
        'unit_type',
        'price',
        'purchase_price',
        'vat_rate',
        'image_path',
        'extras',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'purchase_price' => 'decimal:2',
            'vat_rate' => 'integer',
            'extras' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /** @return array<string, string> */
    public static function unitTypes(): array
    {
        return [
            self::UNIT_PIECE => __('menu.product_unit_piece'),
            self::UNIT_KG => __('menu.product_unit_kg'),
            self::UNIT_LITER => __('menu.product_unit_liter'),
            self::UNIT_PORTION => __('menu.product_unit_portion'),
        ];
    }

    public function extra(string $key, mixed $default = null): mixed
    {
        return data_get($this->extras, $key, $default);
    }

    public function imageUrl(): string
    {
        return ImageStorage::url($this->image_path, MediaLimits::variantForContext(MediaLimits::CONTEXT_PRODUCT))
            ?? Branding::defaultLogoUrl();
    }

    public function hasMenuImage(): bool
    {
        return is_string($this->image_path) && trim($this->image_path) !== '';
    }

    public function menuImageUrl(): ?string
    {
        if (! $this->hasMenuImage()) {
            return null;
        }

        return ImageStorage::url($this->image_path, MediaLimits::variantForContext(MediaLimits::CONTEXT_PRODUCT));
    }

    public function videoPlaybackUrl(): ?string
    {
        $ref = $this->extra('video_ref');

        return is_string($ref) && $ref !== ''
            ? MediaStorage::streamPlaybackUrl($ref)
            : null;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
