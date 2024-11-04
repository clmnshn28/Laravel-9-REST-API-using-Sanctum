<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Customer;

class CustomersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $customer = Customer::create([
            'username' => 'havis',
            'password' => Hash::make('password'),
            'fname'=> 'Francis Harvey',
            'lname'=> 'Soriano',
            'email'=>'francissoriano43@gmail.com',
            'contact_number'=>'09352338425',
            'house_number' => '914',
            'street' => 'Hilerang Kawayan',
            'barangay' => 'Lawa',
            'municipality_city' => 'Malolos',
            'province' => 'Bulacan',
            'postal_code' => '3000',
        ]);
        
    }
}
