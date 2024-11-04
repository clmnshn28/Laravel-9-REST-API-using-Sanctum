<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\GallonDelivery;
use App\Models\Refill;
use App\Models\Borrow;
use App\Models\Returned;
use App\Models\Product;
use App\Models\Notification; 
use Illuminate\Support\Facades\Auth;
use Validator;

class GallonDeliveryController extends BaseController
{
    public function index(){

        $refill_gallon_delivery = GallonDelivery::refill_gallon_delivery()->toArray();
        $borrow_gallon_delivery = GallonDelivery::borrow_gallon_delivery()->toArray();
        $return_gallon_delivery = GallonDelivery::return_gallon_delivery()->toArray();

        $result = array_merge($refill_gallon_delivery,  $borrow_gallon_delivery, $return_gallon_delivery);
        $collection = collect($result);
        $sorted = $collection->sortBy('updated_at')->values()->toArray();
        return $this->sendResponse( $sorted, ' All Queue created successfully.');
    }

    public function showRequests($delivery_status){

        $refill_gallon_delivery = GallonDelivery::refill_gallon_delivery()->toArray();
        $borrow_gallon_delivery = GallonDelivery::borrow_gallon_delivery()->toArray();
        $return_gallon_delivery = GallonDelivery::return_gallon_delivery()->toArray();

        $result = array_merge($refill_gallon_delivery,  $borrow_gallon_delivery, $return_gallon_delivery);
        $collection = collect($result);
        $sorted = $collection->filter(function ($value) use ($delivery_status) {
            return strtolower($value->gallon_delivery_status) === $delivery_status;
        })->sortBy('updated_at')->values()->toArray();

        return $this->sendResponse( $sorted, ' All Queue created successfully.');
    }



    public function declineRequest(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:255', 
            'refill_id' => 'nullable', 
            'borrow_id' => 'nullable', 
            'returned_id' => 'nullable', 
            'gallon_type' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        // Find the GallonDelivery by ID
        $gallonDelivery = GallonDelivery::find($id);
        if (!$gallonDelivery) {
            return $this->sendError('Delivery request not found.');
        }

        $gallonDescriptionString = ''; 
        $customerId = null;

        switch ($request->gallon_type) {
            case 'refill':
                $gallonRefill = Refill::find($request->refill_id);
                if (!$gallonRefill) {
                    return $this->sendError('Refill request not found.');
                }
                $gallonRefill->status = 'cancelled';
                $gallonRefill->save();
                $customerId = $gallonRefill->customer_id;
                $gallonDescriptionString = 'refill request';
                break;

            case 'borrow':
                $gallonBorrow = Borrow::find($request->borrow_id);
                if (!$gallonBorrow) {
                    return $this->sendError('Borrow request not found.');
                }
                $gallonBorrow->status = 'cancelled';
                $gallonBorrow->save();
                $customerId = $gallonBorrow->customer_id;
                $gallonDescriptionString = 'borrow request';
                break;

            case 'return':
                $gallonReturn = Returned::find($request->returned_id);
                if (!$gallonReturn) {
                    return $this->sendError('Returned request not found.');
                }
                $gallonReturn->status = 'cancelled';
                $gallonReturn->save();
                $customerId = $gallonReturn->customer_id;
                $gallonDescriptionString = 'return request';
                break;
        }

        $gallonDelivery->status = 'cancelled'; 
        $gallonDelivery->reason = $request->reason; 
        $gallonDelivery->save(); 

        Notification::create([
            'customer_id' => $customerId,
            'admin_id' => Auth::guard('admin')->user()->id, 
            'type' => ucfirst($request->gallon_type),
            'subject' => ucfirst($request->gallon_type) . ' Request Declined', 
            'description' => 'Your ' . $gallonDescriptionString . ' has been declined. Reason: ' . $request->reason,
            'is_admin' => false, 
        ]);

        return $this->sendResponse($gallonDelivery, 'Delivery request declined successfully.');
    }



    public function acceptRequest(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'gallon_type' => 'required', 
            'refill_id' => 'nullable', 
            'borrow_id' => 'nullable', 
            'returned_id' => 'nullable', 
            'data' => 'required|array',
            'data.*.gallon_id' => 'required',
            'data.*.quantity' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        // Find the GallonDelivery by ID
        $gallonDelivery = GallonDelivery::find($id);
        if (!$gallonDelivery) {
            return $this->sendError('Delivery request not found.');
        }

        $customerId = null; 

        if( $request->gallon_type  === 'refill'){
            $gallonRefill = Refill::find( $request->refill_id);
            if (!$gallonRefill) {
                return $this->sendError('Refill request not found.');
            }
            $gallonDelivery->status = 'pickup'; 
            $gallonRefill->status = 'pickup' ;
            $gallonRefill->save();
            $customerId = $gallonRefill->customer_id;

        }else if($request->gallon_type  === 'borrow'){
            $gallonBorrow = Borrow::find( $request->borrow_id);
            if (!$gallonBorrow) {
                return $this->sendError('Borrow request not found.');
            }
            $gallonDelivery->status = 'deliver';
            $gallonBorrow->status = 'deliver' ;
            $gallonBorrow->save();
            $customerId = $gallonBorrow->customer_id;

            foreach( $request->data as $borrow_request_data ){
                $product = Product::find($borrow_request_data['gallon_id']);
                $product->decrement('available_stock', $borrow_request_data['quantity']);
                $product->increment('borrowed', $borrow_request_data['quantity']);
                if ($product->available_stock > 0) {
                    $product->update(['status' => 'Available']);
                } else {
                    $product->update(['status' => 'Out of Stock']);
                }
            }


        }else if($request->gallon_type  === 'return'){
            $gallonReturn = Returned::find( $request->returned_id);
            if (!$gallonReturn) {
                return $this->sendError('Returned request not found.');
            }
            $gallonDelivery->status = 'pickup'; 
            $gallonReturn->status = 'pickup' ;
            $gallonReturn->save();
            $customerId = $gallonReturn->customer_id; 
        }

        $gallonDelivery->save(); 

        Notification::create([
            'customer_id' => $customerId, // Use the retrieved customer ID
            'admin_id' => 1, // Assuming the admin ID is fixed for this example
            'type' => ucfirst($request->gallon_type),
            'subject' => ucfirst($request->gallon_type) . ' Request Accepted',
            'description' => 'Your ' . ucfirst($request->gallon_type) . ' request has been accepted.',
            'is_admin' => false,
        ]);

        return $this->sendResponse($gallonDelivery, 'Delivery request accepted successfully.');
    }






    public function completedRequest(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'gallon_type' => 'required|string', 
            'gallon_status' => 'required|string', 
            'refill_id' => 'nullable', 
            'borrow_id' => 'nullable', 
            'returned_id' => 'nullable',
            'customer_id' => 'required', 
            'data' => 'required|array',
            'data.*.gallon_id' => 'required',
            'data.*.quantity' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        // Find the GallonDelivery by ID
        $gallonDelivery = GallonDelivery::find($id);
        if (!$gallonDelivery) {
            return $this->sendError('Delivery request not found.');
        }

        if ($request->gallon_type === 'refill') {
         
            if($request->gallon_status === 'deliver' ){
                $gallonRefill = Refill::find( $request->refill_id);
                if (!$gallonRefill) {
                    return $this->sendError('Refill request not found.');
                }
                $gallonDelivery->status = 'completed';
                $gallonRefill->status = 'completed' ;
                $gallonRefill->save();

            }else{
                $gallonRefill = Refill::find( $request->refill_id);
                if (!$gallonRefill) {
                    return $this->sendError('Refill request not found.');
                }
                $gallonDelivery->status = 'deliver';
                $gallonRefill->status = 'deliver' ;
                $gallonRefill->save();
            }


        }else if($request->gallon_type === 'borrow'){
          
            $gallonBorrow = Borrow::find($request->borrow_id);
            if (!$gallonBorrow) {
                return $this->sendError('Borrow request not found.');
            }
            $gallonDelivery->status = 'completed';  
            $gallonBorrow->status = 'completed' ;
            $gallonBorrow->save();


        }else if($request->gallon_type === 'return'){ 
            $gallonReturn = Returned::find( $request->returned_id);
            if (!$gallonReturn) {
                return $this->sendError('Returned request not found.');
            }
            $gallonDelivery->status = 'completed';  
            $gallonReturn->status = 'completed' ;
            $gallonReturn->save();


            $borrow = Borrow::where([
                'customer_id' => $request->customer_id,
                'admin_id' => 1,
            ])->first();

            foreach( $request->data as $returned_request_data ){
                $product = Product::find($returned_request_data['gallon_id']);
                $product->increment('available_stock', $returned_request_data['quantity']);
                $product->decrement('borrowed', $returned_request_data['quantity']);
                if ($product->available_stock > 0) {
                    $product->update(['status' => 'Available']);
                } else {
                    $product->update(['status' => 'Out of Stock']);
                }

                $borrow->borrow_details()->create([
                    'shop_gallon_id' => $returned_request_data['gallon_id'],
                    'borrowed_gallon_id' => $borrow->id,
                    'quantity' => -$returned_request_data['quantity'],
                ]);
            }
        }
       
        $gallonDelivery->save(); 

        return $this->sendResponse($gallonDelivery, 'Delivery request accepted successfully.');
    }



}
