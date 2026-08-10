<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    /**
     * Real menu data transcribed from the physical Kafeign menu board,
     * keyed by category slug (must match CategorySeeder's Str::slug output).
     * "(New)" and "VDT" badges from the board are NOT part of the stored
     * name — they become the is_new / is_vdt boolean flags instead.
     *
     * NOTE: "Ice Yakult Espresso" price was partly obscured in the source
     * photo. Seeded as 25000 (best guess, matching neighboring items) —
     * confirm the real price with the cafe owner and correct it later via
     * the admin dashboard (Phase 4) if it's wrong.
     */
    public function run(): void
    {
        $menu = [
            'signature' => [
                ['name' => 'Kopi Susu Dekap', 'price' => 23000],
                ['name' => 'Kopi Susu Erat', 'price' => 23000],
                ['name' => 'Kopi Susu Rindu', 'price' => 23000],
                ['name' => 'Kopi Susu Strawberry', 'price' => 23000],
            ],
            'espresso-based' => [
                ['name' => 'Mont Blanc', 'price' => 30000],
                ['name' => 'Butter Scotch Sea Salt Latte', 'price' => 25000],
                ['name' => 'Chocolate Sea Salt Latte', 'price' => 25000],
                ['name' => 'Cinnamon Caramel Latte', 'price' => 25000],
                ['name' => 'Ice Yakult Espresso', 'price' => 25000],
                ['name' => 'Coffee Latte', 'price' => 23000],
                ['name' => 'Cappuccino', 'price' => 23000],
                ['name' => 'Mochacino', 'price' => 23000],
                ['name' => 'Vanilla Latte', 'price' => 23000],
                ['name' => 'Caramel Latte', 'price' => 23000],
                ['name' => 'Hazelnut Latte', 'price' => 23000],
            ],
            'non-coffee' => [
                ['name' => 'Chocolate', 'price' => 23000],
                ['name' => 'Choco Berry', 'price' => 23000, 'is_new' => true],
                ['name' => 'Red Velvet', 'price' => 23000],
                ['name' => 'Red Velvet Klava', 'price' => 28000, 'is_new' => true],
                ['name' => 'Pink Lava', 'price' => 23000],
                ['name' => 'Vanilla Choco', 'price' => 23000],
                ['name' => 'Caramel Choco', 'price' => 23000],
                ['name' => 'Hazelnut Choco', 'price' => 23000],
                ['name' => 'Cookies & Cream', 'price' => 23000],
                ['name' => 'Vanilla Milkshake', 'price' => 23000],
                ['name' => 'Caramel Milkshake', 'price' => 23000],
                ['name' => 'Hazelnut Milkshake', 'price' => 23000],
                ['name' => 'White Strawberry', 'price' => 23000],
                ['name' => 'Lemon Tea', 'price' => 23000],
                ['name' => 'Lychee Tea', 'price' => 23000],
                ['name' => 'Mineral Water', 'price' => 10000],
            ],
            'matcha-series' => [
                ['name' => 'Matcha', 'price' => 23000],
                ['name' => 'Matcha Berry', 'price' => 23000],
                ['name' => 'Butter Scotch Sea Salt Matcha', 'price' => 25000],
            ],
            'manual-brew' => [
                ['name' => 'V60', 'price' => 25000],
                ['name' => 'Japanese', 'price' => 25000],
            ],
            'mocktail' => [
                ['name' => 'Blue Ocean', 'price' => 25000],
                ['name' => 'Lychee Yakult', 'price' => 25000],
                ['name' => 'Mojito Blue', 'price' => 25000],
                ['name' => 'Mojito Sakura', 'price' => 25000],
                ['name' => 'Mojito Americano', 'price' => 25000],
            ],
            'americano-series' => [
                ['name' => 'Long Black', 'price' => 20000],
                ['name' => 'Americano', 'price' => 20000],
                ['name' => 'Americano Stevia', 'price' => 20000],
                ['name' => 'Americano Creamy', 'price' => 23000],
                ['name' => 'Americano Coconut Water', 'price' => 23000],
                ['name' => 'Americano Lemon Soda', 'price' => 23000],
            ],
            'signature-steak' => [
                ['name' => 'Steak Grill', 'price' => 35000],
                ['name' => 'Mix Platter Steak', 'price' => 35000],
                ['name' => 'Sabana Chicken Steak', 'price' => 30000],
            ],
            'snack' => [
                ['name' => 'Nasi Goreng VDT', 'price' => 30000, 'is_new' => true, 'is_vdt' => true],
                ['name' => 'Ayam Betutu VDT', 'price' => 40000, 'is_new' => true, 'is_vdt' => true],
                ['name' => 'Beef Blackpaper VDT', 'price' => 40000, 'is_new' => true, 'is_vdt' => true],
                ['name' => 'Beef Roll VDT', 'price' => 39000, 'is_new' => true, 'is_vdt' => true],
                ['name' => 'Chicken Roll VDT', 'price' => 39000, 'is_new' => true, 'is_vdt' => true],
                ['name' => 'Cireng', 'price' => 22000],
                ['name' => 'Cassava Stick', 'price' => 22000],
                ['name' => 'Donat Kentang', 'price' => 22000],
                ['name' => 'Egg Chicken Roll', 'price' => 22000],
                ['name' => 'French Fries', 'price' => 22000],
                ['name' => 'Kebab Mini', 'price' => 22000],
                ['name' => 'Mix Platter', 'price' => 22000],
                ['name' => 'Pisang Kafeign', 'price' => 22000],
                ['name' => 'Risoles Kafeign', 'price' => 22000],
                ['name' => 'Singkong Geprek', 'price' => 22000],
                ['name' => 'Indomie Nyemek', 'price' => 25000],
                ['name' => 'Indomie Carbonara', 'price' => 25000],
                ['name' => 'Indomie Goreng', 'price' => 22000],
                ['name' => 'Indomie Rebus', 'price' => 22000],
            ],
        ];

        foreach ($menu as $slug => $items) {
            $category = Category::where('slug', $slug)->first();

            if (! $category) {
                $this->command?->warn("Skipping unknown category slug: {$slug}");

                continue;
            }

            foreach ($items as $index => $item) {
                MenuItem::updateOrCreate(
                    ['category_id' => $category->id, 'name' => $item['name']],
                    [
                        'price' => $item['price'],
                        'is_new' => $item['is_new'] ?? false,
                        'is_vdt' => $item['is_vdt'] ?? false,
                        'is_available' => true,
                        'sort_order' => $index + 1,
                    ]
                );
            }
        }
    }
}
