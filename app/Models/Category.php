<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    /**
     * The fixed inline-SVG icon set <x-icon> knows how to render (see
     * resources/views/components/icon.blade.php) — the only valid values
     * for the `icon` column. Centralized here so the admin category form
     * and its validation rule can't drift out of sync with each other.
     */
    public const ICONS = [
        'mug' => 'Mug',
        'coffee-cup' => 'Cangkir Kopi',
        'glass' => 'Gelas',
        'leaf' => 'Daun (Matcha)',
        'drip' => 'Dripper (Manual Brew)',
        'mocktail-glass' => 'Gelas Mocktail',
        'tall-glass' => 'Gelas Tinggi (Americano)',
        'steak' => 'Steak',
        'snack-plate' => 'Piring (Snack)',
    ];

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    /**
     * Scope: categories in menu-board display order.
     */
    public function scopeOrderedBySort($query)
    {
        return $query->orderBy('sort_order');
    }
}
