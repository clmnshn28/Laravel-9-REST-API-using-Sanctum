<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UsersSeeder::class,
            CustomersSeeder::class,
            ProductSeeder::class,
            BusinessHoursSeeder::class,
            TimeSlotSeeder::class,
            BorrowLimitsSeeder::class,
        ]);
    }
}
