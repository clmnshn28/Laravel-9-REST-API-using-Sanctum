<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BusinessHour;
use App\Models\TimeSlot;

class TimeSlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $defaultTimeSlots = [
            ['start' => '00:00', 'end' => '12:00']
        ];

        $businessHours = BusinessHour::all();

        foreach ($businessHours as $businessHour) {
            foreach ($defaultTimeSlots as $slot) {
                TimeSlot::create([
                    'business_hour_id' => $businessHour->id,
                    'start' => $slot['start'],
                    'end' => $slot['end'],
                ]);
            }
        }
    }
}
