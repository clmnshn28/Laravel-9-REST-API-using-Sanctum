<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\BusinessHour;
use App\Models\TimeSlot;
use Illuminate\Support\Facades\Auth;
use Validator;

class BusinessHourController extends BaseController
{
    
    public function index(){
        $businessHours = BusinessHour::with(['timeSlots'])->get();
        return $this->sendResponse($businessHours, 'Business hours retrieved successfully.');
    }



    public function update(Request $request){

        $input = $request->all();

        $validator = Validator::make($input['data'], [
            '*.day' => 'required|string',
            '*.is_open' => 'required|boolean',
            '*.time_slots' => 'required|array',
            '*.time_slots.*.start' => 'required|date_format:H:i',
            '*.time_slots.*.end' => 'required|date_format:H:i|after:time_slots.*.start',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors());
        }


        foreach ($input['data'] as $hourData) {
          
            $businessHour = BusinessHour::where('day', $hourData['day'])->firstOrFail();
    
            
            $businessHour->update(['is_open' => $hourData['is_open']]);
    
           
            $existingTimeSlots = $businessHour->timeSlots;
    
        
            foreach ($hourData['time_slots'] as $slot) {
                // Find the existing time slot by ID
                $timeSlot = $existingTimeSlots->firstWhere('id', $slot['id'] ?? null);
                
                $timeSlot->update([
                    'start' => $slot['start'],
                    'end' => $slot['end'],
                ]);
            
            }
        }
        return $this->sendResponse($businessHour->load('timeSlots'), 'Business hour created successfully.');
    }
    
}
