<?php

namespace Database\Seeders;

use App\Models\Table;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    /**
     * The cafe has 36 physical tables/seats, numbered 1 through 36.
     */
    public function run(): void
    {
        for ($number = 1; $number <= 36; $number++) {
            Table::updateOrCreate(['number' => $number]);
        }
    }
}
