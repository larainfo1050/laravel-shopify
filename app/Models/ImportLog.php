<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportLog extends Model
{
     protected $fillable = [
        'upload_id',
        'product_id',
        'level',
        'action',
        'message',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public function upload(): BelongsTo
    {
        return $this->belongsTo(Upload::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public static function log(
        int $uploadId,
        string $level,
        string $action,
        string $message,
        ?int $productId = null,
        ?array $context = null
    ): self {
        return static::create([
            'upload_id' => $uploadId,
            'product_id' => $productId,
            'level' => $level,
            'action' => $action,
            'message' => $message,
            'context' => $context,
        ]);
    }
}
