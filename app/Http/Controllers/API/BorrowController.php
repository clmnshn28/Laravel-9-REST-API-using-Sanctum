<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Borrow;
use App\Models\BorrowDetails;
use App\Models\GallonDelivery;
use App\Models\Notification; 
use Illuminate\Support\Facades\Auth;
use Validator;

class BorrowController extends BaseController
{
    public function index(){
        $borrow = Borrow::all();
        return $this->sendResponse($borrow, 'Borrow retrieved successfully.');     
    }


    public function store(Request $request){
        \Log::info(Auth::guard('customer')->user());    
        $input = $request->all();  

        $validator = Validator::make($input['data'], [
            '*.gallon_id' => 'required',
            '*.quantity' => 'required|lte:*.available_stock',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors());
        }

        $borrow = Borrow::create([
            'customer_id' => Auth::guard('customer')->user()->id,
            'admin_id' => 1,
            'status' => 'pending',
        ]);

         GallonDelivery::create([
            'request_type_id' => $borrow->id,
            'request_type' => 'borrow',
            'status' => 'pending',
        ]);

        $gallonDescription = [];
        foreach( $input['data'] as $borrow_request_data ){

            $gallonType = ($borrow_request_data['gallon_id'] == 1) ? 'Slim' : 
                        (($borrow_request_data['gallon_id'] == 2) ? 'Round' : 'Unknown');

            // Prepare the description
            $gallonDescription[] = "{$borrow_request_data['quantity']} {$gallonType} Gallon" .  
                                ($borrow_request_data['quantity'] > 1 ? 's' : '');

            $borrow->borrow_details()->create([
                'shop_gallon_id' => $borrow_request_data['gallon_id'],
                'borrowed_gallon_id' => $borrow->id,
                'quantity' => $borrow_request_data['quantity'],
            ]);

            // $product = Product::find($borrow_request_data['gallon_id']);
            // $product->decrement('available_stock', $borrow_request_data['quantity']);
            // $product->increment('borrowed', $borrow_request_data['quantity']);
            // if ($product->available_stock > 0) {
            //     $product->update(['status' => 'Available']);
            // } else {
            //     $product->update(['status' => 'Out of Stock']);
            // }
        }

        $gallonDescriptionString = implode(', ', $gallonDescription);

        $customer = Auth::guard('customer')->user();
        Notification::create([
            'customer_id' => $customer->id, 
            'admin_id' => 1,
            'type' => 'Borrow', 
            'subject' => 'Borrow Request', 
            'description' => $customer->fname .' '. $customer->lname .' has requested to borrow ' . $gallonDescriptionString,
            'is_admin' => true, 
        ]);

        return $this->sendResponse($borrow->load(['borrow_details']), 'Borrow request created successfully.');
    }


    
    public function getBorrowedGallons() {
        $customerId = Auth::guard('customer')->user()->id;
    
        $borrowedGallons = Borrow::where('customer_id', $customerId)
            ->where('status', '=', 'completed') 
            ->with('borrow_details')
            ->get();
    
        return $this->sendResponse($borrowedGallons, 'Borrowed gallons retrieved successfully.');
    }

}
