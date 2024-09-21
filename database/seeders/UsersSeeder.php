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
            'username' => 'admin',
            'password' => Hash::make('password'),
            'fname'=> 'Celmin Shane',
            'lname'=> 'Quizon',
            'email'=>'celminshanequizon@gmail.com',
            'contact_number'=>'09671212123',
            'house_number' => '366',
            'street' => 'Gitna',
            'barangay' => 'Tinejero',
            'municipality_city' => 'Malolos',
            'province' => 'Bulacan',
            'postal_code' => '3000',
        ]);
    }
}
