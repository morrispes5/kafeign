<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * The 9 categories from the physical Kafeign menu board, in the exact
     * order they appear there. `icon` is a key into the fixed inline-SVG
     * set rendered by the <x-icon> Blade component (built in Phase 1).
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Signature', 'icon' => 'mug'],
            ['name' => 'Espresso Based', 'icon' => 'coffee-cup'],
            ['name' => 'Non Coffee', 'icon' => 'glass'],
            ['name' => 'Matcha Series', 'icon' => 'leaf'],
            ['name' => 'Manual Brew', 'icon' => 'drip'],
            ['name' => 'Mocktail', 'icon' => 'mocktail-glass'],
            ['name' => 'Americano Series', 'icon' => 'tall-glass'],
            ['name' => 'Signature Steak', 'icon' => 'steak'],
            ['name' => 'Snack', 'icon' => 'snack-plate'],
        ];

        foreach ($categories as $index => $category) {
            Category::updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'icon' => $category['icon'],
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}
