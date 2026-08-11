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
        'stock',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'is_new' => 'boolean',
            'is_vdt' => 'boolean',
            'is_available' => 'boolean',
            'stock' => 'integer',
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
     * The single source of truth for "may this be ordered right now".
     *
     *     is_available = true  AND  (stock IS NULL OR stock > 0)
     *
     * THE ONLY SCOPE PERMITTED TO GATE A WRITE. Every money path already
     * calls it, so they all inherit the stock rule for free. If you find
     * yourself re-expressing this condition anywhere else — a FormRequest
     * `Rule::exists(...)->where('is_available', true)`, a raw builder
     * clause — that copy will silently keep the old semantics the next
     * time this rule changes. Call the scope instead.
     *
     * For deciding what to *show*, use scopeListedOnMenu().
     */
    public function scopeAvailable($query)
    {
        return $query
            ->where('is_available', true)
            ->where(fn ($q) => $q->whereNull('stock')->orWhere('stock', '>', 0));
    }

    /**
     * Scope: what appears on the customer menu at all. DISPLAY ONLY —
     * never use this to gate a write.
     *
     * Deliberately does NOT filter on stock. A sold-out item stays on the
     * menu, greyed out and badged "Habis": a customer holding the tent
     * card deserves an answer rather than a mystery, and it advertises the
     * item for tomorrow. Only `is_available = false` (the admin's "not on
     * the menu today" switch) actually hides a row.
     */
    public function scopeListedOnMenu($query)
    {
        return $query->where('is_available', true);
    }

    /** Tracked and exhausted — shown, but cannot be ordered. */
    public function isSoldOut(): bool
    {
        return $this->stock !== null && $this->stock <= 0;
    }

    public function isOrderable(): bool
    {
        return $this->is_available && ! $this->isSoldOut();
    }

    /** Whether this item's stock is counted at all. */
    public function tracksStock(): bool
    {
        return $this->stock !== null;
    }

    /**
     * Scope: items in menu-board display order within their category.
     */
    public function scopeOrderedBySort($query)
    {
        return $query->orderBy('sort_order');
    }
}
