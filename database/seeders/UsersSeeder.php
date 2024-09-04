<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            'username' => 'clmnshn',
            'password' => Hash::make('password'),
            'fname'=> 'celmin',
            'lname'=> 'quizon',
            'contact_number'=>'09671212123',
            'house_number' => '366',
            'street' => 'Gitna St.',
            'barangay' => 'Tinejero',
            'municipality_city' => 'Pulilan',
            'province' => 'Bulacan',
            'postal_code' => '3005',
        ]);
    }
}
