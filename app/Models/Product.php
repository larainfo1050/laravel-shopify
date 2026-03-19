<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
     protected $fillable = [
        'upload_id',
        // From CSV columns
        'handle',
        'title',
        'body_html',
        'vendor',
        'product_type',
        'tags',
        'published',
        'variant_sku',
        'variant_price',
        'variant_compare_at_price',
        'variant_requires_shipping',
        'variant_taxable',
        'variant_inventory_tracker',
        'variant_inventory_qty',
        'variant_inventory_policy',
        'variant_fulfillment_service',
        'variant_weight',
        'variant_weight_unit',
        'image_src',
        'image_position',
        'image_alt_text',
        // Status fields
        'import_status',
        'error_message',
    ];

    protected $casts = [
        'published' => 'boolean',
        'variant_price' => 'decimal:2',
        'variant_compare_at_price' => 'decimal:2',
        'variant_requires_shipping' => 'boolean',
        'variant_taxable' => 'boolean',
        'variant_inventory_qty' => 'integer',
        'variant_weight' => 'decimal:2',
        'image_position' => 'integer',
    ];

    public function upload(): BelongsTo
    {
        return $this->belongsTo(Upload::class);
    }

    public function getIsSuccessfulAttribute(): bool
    {
        return $this->import_status === 'successful';
    }

    public function getIsFailedAttribute(): bool
    {
        return $this->import_status === 'failed';
    }
}
