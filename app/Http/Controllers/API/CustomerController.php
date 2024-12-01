<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\GallonDelivery;
use App\Models\User;
use Validator;
use Illuminate\Support\Facades\Auth;
use App\Rules\UniqueForUser;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

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
            'contact_number' => empty($customer->contact_number),
            'house_number' => empty($customer->house_number),
            'street' => empty($customer->street),
            'barangay' => empty($customer->barangay),
        ]);

        if ($missingFields) {
            return $this->sendError('Missing address fields.', array_keys($missingFields));
        }

        return $this->sendResponse(['hasAddress' => true], 'Address check completed successfully.');
    }


    public function showRequestsTransaction(){
        
        $customer_id = Auth::guard('customer')->user()->id;

        $refill_gallon_delivery = GallonDelivery::refill_gallon_delivery()->toArray();
        $borrow_gallon_delivery = GallonDelivery::borrow_gallon_delivery()->toArray();
        $return_gallon_delivery = GallonDelivery::return_gallon_delivery()->toArray();

        $result = array_merge($refill_gallon_delivery,  $borrow_gallon_delivery, $return_gallon_delivery);
        $collection = collect($result);
        $sorted = $collection->filter(function ($value) use ($customer_id) {
            return strtolower($value->customer_id) == $customer_id;
        })->sortBy('updated_at')->values()->toArray();

        return $this->sendResponse( $sorted, ' All Transactions created successfully.');
    }

     /**
     * Download QR code for the customer.
     *
     * @param  string  $qrCode
     * @return \Illuminate\Http\Response
     */
    public function downloadQRCode($qrCode)
    {
        // Define the path to the QR code image in the storage folder
        $filePath = storage_path("app/public/qrcodes/{$qrCode}");

        // Check if the file exists
        if (!file_exists($filePath)) {
            return $this->sendError('File not found.', [], 404);
        }

        // Return the file as a download
        return response()->download($filePath);
    }


}
