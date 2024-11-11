<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $product = Product::create([
            'item_name' => 'Blue Slim Gallon with Faucet Refill (20L/5gal)',
            'initial_stock' => 0,
            'price'=> 25,
            'borrowed'=> 0,
            'available_stock'=> 0,
            'status' => 'Out of Stock',
        ]);

        $product = Product::create([
            'item_name' => 'Round Gallon Dispenser Refill 18.9L',
            'initial_stock' => 0,
            'price'=> 25,
            'borrowed'=> 0,
            'available_stock'=> 0,
            'status' => 'Out of Stock',
        ]);
    }
}
