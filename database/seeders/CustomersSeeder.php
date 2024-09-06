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
            'username' => 'beckett',
            'password' => Hash::make('password'),
            'fname'=> 'beckett',
            'lname'=> 'quizon',
            'contact_number'=>'09671212123',
            'house_number' => '466',
            'street' => 'Maharlika St.',
            'barangay' => 'Inaon',
            'municipality_city' => 'Pulilan',
            'province' => 'Bulacan',
            'postal_code' => '3005',
        ]);
        
    }
}
