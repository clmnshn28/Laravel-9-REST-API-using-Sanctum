<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\Concern;
use App\Models\Customer;
use App\Models\Reply;
use App\Models\Notification; 
use Illuminate\Support\Facades\Auth;
use App\Events\NotificationEvent;
use App\Events\NotificationAdminEvent;
use Validator;

class ConcernController extends BaseController
{

    public function getCustomerConcerns(){
        
        $customerId = Auth::guard('customer')->user()->id;

        $customer = Customer::with(['concerns'])->find($customerId);
        if (!$customer) {
            return $this->sendError('Customer not found.');
        }

        return $this->sendResponse($customer, 'Specific customer concern retrieve successfully.');
    }




    public function getAllConcerns(){
        
        $concerns = Concern::with(['admin', 'customer', 'replies'])->get();   

        return $this->sendResponse($concerns, 'All customer concern retrieve successfully.');
    }




    public function store(Request $request){

        $input = $request->all();

        $validator = Validator::make($input,[
            'subject' => 'required',
            'concern_type' => 'required',
            'content' => 'required',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        if ($request->hasFile('images')) {
            $images = $request->file('images');
            $imageNames = []; 
        
            foreach ($images as $image) {
                $timestamp = date('YmdHis') . uniqid(); 
                $imageName = $timestamp . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/images', $imageName); 
                $imageNames[] = $imageName; 
            }
        
            $input['images'] = json_encode($imageNames); 
        }

        $concern = Concern::create([
            'admin_id' => 1,
            'customer_id' => Auth::guard('customer')->user()->id,
            'subject' => $input['subject'],
            'concern_type' => $input['concern_type'],
            'content' => $input['content'],
            'images' => $input['images'] ?? null, 
        ]);

        $notification = Notification::create([
            'customer_id' => Auth::guard('customer')->user()->id,
            'admin_id' => 1, 
            'type' => 'Concern',
            'subject' => 'Concern Submitted',
            'description' =>  Auth::guard('customer')->user()->fname .' '. Auth::guard('customer')->user()->lname . 
                             ' has submitted a concern: "' . $input['subject'] . 
                             '" of type "' . $input['concern_type'] . '".',
            'is_admin' => true,
        ]);

        event(new NotificationAdminEvent($notification));

        return $this->sendResponse($concern, 'Concern created successfully.');
    }

    

    public function markConcernAsRead($id) {
        
        $concern = Concern::find($id);
        if (!$concern) {
            return $this->sendError('Concern not found.');
        }
       
        $concern->is_read = 1;
        $concern->save();
    
        return $this->sendResponse($concern, 'Concern marked as read successfully.');
    }


    public function storeReply(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $concern = Concern::find($id);
        if (!$concern) {
            return $this->sendError('Concern not found.');
        }

        $reply = Reply::create([
            'content' => $request->input('content'),
            'customer_id' =>  $concern->customer_id,
            'admin_id' => Auth::guard('admin')->user()->id,
            'concern_id' => $id,
        ]);
            
        $notification = Notification::create([
            'customer_id' => $concern->customer_id,
            'admin_id' => Auth::guard('admin')->user()->id, 
            'type' => 'Concern Reply',
            'subject' => 'Your Concern has a new reply',
            'description' => 'Your concern titled "' . $concern->subject . '" has received a new reply.',
            'is_admin' => false,
        ]);

        event(new NotificationEvent($notification));
    

        return $this->sendResponse($reply, 'Response concern successfully.');
    }


    public function getRepliesForConcern($id) {
        $concern = Concern::find($id);
        if (!$concern) {
            return $this->sendError('Concern not found.');
        }
    
        $replies = Concern::with(['replies'])->where('id', $id)->first();
    
        return $this->sendResponse($replies, 'Replies retrieved successfully.');
    }
    
}
