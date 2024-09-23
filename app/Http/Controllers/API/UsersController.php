<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;
use Validator;

class UsersController extends BaseController
{
        public function store(Request $request){
            
            $input = $request->all();

            $validator = Validator::make($input, [
                'username' => 'required|unique:customers,username',
                'password' => 'required|min:8',
                'c_password' => 'required|same:password', 
                'fname' => 'required',
                'lname' => 'required',
                'email' => 'required|unique:customers,email',
                'contact_number' => 'nullable',
                'house_number' => 'nullable',
                'street' => 'nullable',
                'barangay' => 'nullable',
                'municipality_city' => 'nullable',
                'province' => 'nullable',
                'postal_code' => 'nullable',
                'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            if($validator->fails()){
                return $this->sendError('Validation Error', $validator->errors());
            }

            unset($input['c_password']);

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $timestamp = date('YmdHis');
                $imageName =  $timestamp . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/images', $imageName);
                $input['image'] = $imageName;
            }

            $customer = Customer::create([
                'username' => $input['username'],
                'password' => Hash::make($input['password']),
                'fname' => $input['fname'],
                'lname' => $input['lname'],
                'email' => $input['email'],
                'contact_number' => $input['contact_number'],
                'house_number' => $input['house_number'] ?? '',
                'street' => $input['street'] ?? '',
                'barangay' => $input['barangay'] ?? '',
                'municipality_city' => $input['municipality_city'],
                'province' => $input['province'],
                'postal_code' => $input['postal_code'],
                'image' => $input['image'] ?? null,
            ]);

            return $this->sendResponse($customer, 'Customer created successfully.');
        }


    // show all
    public function index()
    {
        $customer = Customer::all();
        return $this->sendResponse($customer, 'Customers retrieved successfully.');
    }
    
    // show specific
    public function show(Customer $customer)
    {
        return $this->sendResponse($customer, 'Customer retrieved successfully.');
    }

    // update
    public function update(Request $request, Customer $customer){

        $input = $request->all();

        $validator = Validator::make($input, [
            'username' => 'required|unique:customers,username,'. $customer->id,
            'password' => 'nullable|min:8',
            'fname' => 'required',
            'lname' => 'required',
            'email' => 'required|unique:customers,email,'. $customer->id,
            'contact_number' => 'required',
            'house_number' => 'nullable',
            'street' => 'nullable',
            'barangay' => 'nullable',
            'municipality_city' => 'nullable',
            'province' => 'nullable',
            'postal_code' => 'nullable',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors());
        }

        $customer->username = $input['username'];
        if (isset($input['password']) && !empty($input['password'])) {
            $customer->password = Hash::make($input['password']);
        }
        $customer->fname = $input['fname'];
        $customer->lname = $input['lname'];
        $customer->email = $input['email'];
        $customer->contact_number = $input['contact_number'];
        $customer->house_number = $input['house_number'] ?? $customer->house_number;
        $customer->street = $input['street'] ?? $customer->street;
        $customer->barangay = $input['barangay'] ?? $customer->barangay;
        $customer->municipality_city = $input['municipality_city'] ?? $customer->municipality_city;
        $customer->province = $input['province'] ?? $customer->province;;
        $customer->postal_code = $input['postal_code'] ?? $customer->postal_code;

        $customer->save();

        return $this->sendResponse($customer, 'Customer updated successfully.');

    }


    // check if email || username exists
    public function validateUser(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'username' => 'required|string',
            'currentEmail' => 'required|email',
            'currentUsername' => 'required|string',
        ]);
    
        // Check if the email exists for other users (excluding the current user)
        $emailExists = Customer::where('email', $request->email)
            ->where('email', '!=', $request->currentEmail) // Exclude current user's email
            ->exists();
            
        // Check if the username exists for other users (excluding the current user)
        $usernameExists = Customer::where('username', $request->username)
            ->where('username', '!=', $request->currentUsername) // Exclude current user's username
            ->exists();
    

        return response()->json([
            'emailExists' => $emailExists,
            'usernameExists' => $usernameExists,
        ]);
    }



    // reset password 
    public function resetPassword(Request $request, Customer $customer) {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:8',
            'c_password' => 'required|same:password', // Ensure the confirmation password matches
        ]);
    
        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors());
        }
    
        // Hash the new password
        $customer->password = Hash::make($request->password);
        $customer->save(); // Save the updated pass
    
        return $this->sendResponse($customer, 'Password updated successfully.');
    }




    // deactivate 
    public function deactivate(Request $request, $id)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors());
        }

        // Find the customer by ID
        $customer = Customer::find($id);

        // Check if the customer exists
        if (!$customer) {
            return $this->sendError('Customer not found.', [], 404);
        }

        // Prepare the deactivation information
        $deactivationInfo = [
            'title' => $request->title,
            'description' => $request->description,
        ];
       
        // Store deactivation info
        $customer->deactivation_info = $deactivationInfo; 
        
        $customer->delete(); // Set the deleted_at timestamp
        $customer->save();

        return $this->sendResponse($customer, 'Customer deactivated successfully.');
    }


    // see all have delete_at
    public function trashed()
    {
        $customers = Customer::onlyTrashed()->select('id', 'fname', 'lname', 'email','image', 'deleted_at', 'deactivation_info')->get();
        return $this->sendResponse($customers, 'Soft-deleted customers retrieved successfully.');
    }



     // Activate (Restore) a Customer
     public function reactivate($id)
     {
         $customer = Customer::withTrashed()->find($id);
 
         if ($customer) {
             $customer->restore(); // Restore the user
             $customer->deactivation_info = null;
             $customer->save(); // Save the changes to the database
             return $this->sendResponse([], 'User activated successfully.');
         }
 
         return $this->sendError('User not found.', [], 404);
     }
 
    
}
