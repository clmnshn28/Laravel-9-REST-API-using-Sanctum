<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Borrow;
use App\Models\Returned;
use App\Models\ReturnedDetails;
use App\Models\GallonDelivery;
use App\Models\Notification; 
use Illuminate\Support\Facades\Auth;
use Validator;

class ReturnController extends BaseController
{
    public function index(){
        $returned = Returned::all();
        return $this->sendResponse($returned, 'Returned retrieved successfully.');     
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

        $returned = Returned::create([
            'customer_id' => Auth::guard('customer')->user()->id,
            'admin_id' => 1,
            'status' => 'pending',
        ]);

        // $borrow = Borrow::where([
        //     'customer_id' => Auth::guard('customer')->user()->id,
        //     'admin_id' => 1,
        // ])->first();

        $gallon_delivery_request = GallonDelivery::create([
            'request_type_id' => $returned->id,
            'request_type' => 'return',
            'status' => 'pending',
        ]);

        $gallonDescription = [];
        foreach( $input['data'] as $returned_request_data ){

            $gallonType = ($returned_request_data['gallon_id'] == 1) ? 'Slim' : 
                        (($returned_request_data['gallon_id'] == 2) ? 'Round' : 'Unknown');

            // Prepare the description
            $gallonDescription[] = "{$returned_request_data['quantity']} {$gallonType} Gallon" .  
                                ($returned_request_data['quantity'] > 1 ? 's' : '');

            $returned->returned_details()->create([
                'shop_gallon_id' => $returned_request_data['gallon_id'],
                'returned_gallon_id' => $returned->id,
                'quantity' => $returned_request_data['quantity'],
            ]);

            // $product = Product::find($returned_request_data['gallon_id']);
            // $product->increment('available_stock', $returned_request_data['quantity']);
            // $product->decrement('borrowed', $returned_request_data['quantity']);
            // if ($product->available_stock > 0) {
            //     $product->update(['status' => 'Available']);
            // } else {
            //     $product->update(['status' => 'Out of Stock']);
            // }
            
            // $borrow->borrow_details()->create([
            //     'shop_gallon_id' => $returned_request_data['gallon_id'],
            //     'borrowed_gallon_id' => $borrow->id,
            //     'quantity' => -$returned_request_data['quantity'],
            // ]);

        }

        $gallonDescriptionString = implode(', ', $gallonDescription);

        $customer = Auth::guard('customer')->user();
        Notification::create([
            'customer_id' =>$customer->id, 
            'admin_id' => 1,
            'type' => 'Return', 
            'subject' => 'Return Request', 
            'description' =>$customer->fname .' '.$customer->lname .' has requested to return ' . $gallonDescriptionString,
            'is_admin' => true, 
        ]);

        return $this->sendResponse($returned->load(['returned_details']), 'Return request created successfully.');
    }
}
