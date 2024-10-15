<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\Refill;
use App\Models\RefillDetails;
use App\Models\GallonDelivery;
use Illuminate\Support\Facades\Auth;
use Validator;

class RefillController extends BaseController
{
    public function index(){
        $refill = Refill::all();
        return $this->sendResponse($refill, 'Refill retrieved successfully.');     
    }

    public function store(Request $request){
        \Log::info(Auth::guard('customer')->user());    
        $input = $request->all();  

        $validator = Validator::make($input['data'], [
            '*.gallon_id' => 'required',
            '*.quantity' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors());
        }

        $refill = Refill::create([
            'customer_id' => Auth::guard('customer')->user()->id,
            'admin_id' => 1,
            'status' => 'pending',
        ]);

        $gallon_delivery_request = GallonDelivery::create([
            'request_type_id' => $refill->id,
            'request_type' => 'refill',
            'status' => 'pending',
        ]);

        foreach( $input['data'] as $refill_request_data ){
            $refill->refill_details()->create([
                'shop_gallon_id' => $refill_request_data['gallon_id'],
                'refill_gallon_id' => $refill->id,
                'quantity' => $refill_request_data['quantity'],
            ]);
        }

        return $this->sendResponse($refill->load(['refill_details']), 'Refill request created successfully.');
    }
}
