<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Auth;
use App\Rules\UniqueForUser;

class CustomerController extends BaseController
{
    
    // Customer::find(1)->feedbacks()
    
    // check if email || username exists
    public function validateUser(Request $request)
    {
        $currentEmail = $request->input('currentEmail');
        $currentUsername = $request->input('currentUsername');

        // Use custom validation rules
        $request->validate([
            'email' => ['required', 'email', new UniqueForUser('email', $currentEmail)],
            'username' => ['required', new UniqueForUser('username', $currentUsername)],
        ]);

         // If validation passes, return success response
        return response()->json([
            'emailExists' => false, 
            'usernameExists' => false,
        ]);
    }

    public function checkUserAddress(Request $request)
    {   
        $customer = Auth::guard('customer')->user();
        
        $missingFields = array_filter([
            'house_number' => empty($customer->house_number),
            'street' => empty($customer->street),
            'barangay' => empty($customer->barangay),
        ]);

        if ($missingFields) {
            return $this->sendError('Missing address fields.', array_keys($missingFields));
        }

        return $this->sendResponse(['hasAddress' => true], 'Address check completed successfully.');
    }


}
