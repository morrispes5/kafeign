<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'price',
        'image_path',
        'is_new',
        'is_vdt',
        'is_available',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'is_new' => 'boolean',
            'is_vdt' => 'boolean',
            'is_available' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Public URL for the item's photo, or null when none has been
     * uploaded yet. Views should treat null as "no photo" and fall back
     * to the typography-only card, not show a broken image.
     *
     * Deliberately root-relative ("/storage/...") instead of
     * Storage::disk('public')->url(), which builds an absolute URL from
     * APP_URL. APP_URL ships as "http://localhost" with no port, while
     * `php artisan serve` defaults to port 8000 — so every photo silently
     * failed to load in the browser (wrong port) even though the upload
     * and resize both succeeded. A root-relative path always resolves
     * against whatever host/port/protocol the page was actually loaded
     * from, so this can't drift out of sync with APP_URL again.
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(
            fn () => $this->image_path ? '/storage/'.$this->image_path : null
        );
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scope: only items the admin hasn't 86'd.
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope: items in menu-board display order within their category.
     */
    public function scopeOrderedBySort($query)
    {
        return $query->orderBy('sort_order');
    }
}
