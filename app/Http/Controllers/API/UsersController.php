<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\UnRegisteredCustomer;
use App\Models\Refill;
use App\Models\RefillDetails;
use App\Models\GallonDelivery;
use Illuminate\Support\Facades\Hash;
use Validator;
use App\Rules\UniqueForUser;
use Carbon\Carbon; 
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;

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
                'email_verified_at' => Carbon::now(),
            ]);

            // Prepare customer data for the QR code
            $qrContent = [
                'ID' => $customer->id,
                'Name' => $customer->fname . ' ' . $customer->lname,
                'Contact' => $customer->contact_number ?? ' - ',
                'Address' => trim(
                    ($customer->house_number ? $customer->house_number . ', ' : ' - ') .
                    ($customer->street ? $customer->street . ', ' : ' - ') .
                    ($customer->barangay ? $customer->barangay . ', ' : ' - ') .
                    ($customer->municipality_city ? $customer->municipality_city . ', ' : ' - ') .
                    ($customer->province ? $customer->province . ', ' : ' - ') .
                    ($customer->postal_code ? $customer->postal_code : ' - ')
                ) ?: '-',
            ];

            $qrString = json_encode($qrContent);

            // Generate the QR code
            $result = Builder::create()
                ->data($qrString)
                ->encoding(new Encoding('UTF-8'))
                ->size(300)
                ->margin(10)
                ->build();

                
            // Ensure the qrcodes directory exists
            $qrCodeDirectory = storage_path('app/public/qrcodes');
            if (!is_dir($qrCodeDirectory)) {
                mkdir($qrCodeDirectory, 0775, true);  // Create the directory if it doesn't exist
            }

            // Handle saving the QR code
            $timestamp = date('YmdHis');  // Create a timestamp for uniqueness
            $qrImageName = "{$timestamp}_{$customer->id}.png";  // Naming the file
                
            // Save the QR code image
            $result->saveToFile(storage_path("app/public/qrcodes/{$qrImageName}"));

            // Save the path in the database
            $customer->qr_code = $qrImageName;
            $customer->save();


            return $this->sendResponse($customer, 'Customer created successfully.');
        }


    // show all
    public function index()
    {
        $customer = Customer::all();
        return $this->sendResponse($customer->load(['inactive_gallons']), 'Customers retrieved successfully.');
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

        $qrContent = [
            'Name' => $customer->fname . ' ' . $customer->lname,
            'Contact' => $customer->contact_number ?? ' - ',
            'Address' => trim(
                ($customer->house_number ? $customer->house_number . ', ' : ' - ') .
                ($customer->street ? $customer->street . ', ' : ' - ') .
                ($customer->barangay ? $customer->barangay . ', ' : ' - ') .
                ($customer->municipality_city ? $customer->municipality_city . ', ' : ' - ') .
                ($customer->province ? $customer->province . ', ' : ' - ') .
                ($customer->postal_code ? $customer->postal_code : ' - ')
            ) ?: '-',
        ];

        $qrString = json_encode($qrContent);

        // Generate the new QR code
        $result = Builder::create()
        ->data($qrString)
        ->encoding(new Encoding('UTF-8'))
        ->size(300)
        ->margin(10)
        ->build();

        // Ensure the qrcodes directory exists
        $qrCodeDirectory = storage_path('app/public/qrcodes');
        if (!is_dir($qrCodeDirectory)) {
            mkdir($qrCodeDirectory, 0775, true);  // Create the directory if it doesn't exist
        }

        // Handle saving the new QR code
        $timestamp = date('YmdHis');  // Create a timestamp for uniqueness
        $qrImageName = "{$timestamp}_{$customer->id}.png";  // Naming the file

        // Save the new QR code image
        $result->saveToFile(storage_path("app/public/qrcodes/{$qrImageName}"));

        // Save the new QR code path in the database
        $customer->qr_code = $qrImageName;
        $customer->save();


        return $this->sendResponse($customer, 'Customer updated successfully.');

    }


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


    // For Unregistered Customer
    public function storeWalkInRequest(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'fname' => 'required',
            'lname' => 'required',
            'contact_number' => 'required|digits:11',
            'house_number' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'barangay' => 'required|string|max:255',
            'municipality_city' => 'sometimes|string|max:255',
            'province' => 'sometimes|string|max:255',
            'postal_code' => 'sometimes|string|max:10',
            'data' => 'required|array',
            'data.*.gallon_id' => 'required',
            'data.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors());
        }

        $unregisteredCustomer = UnregisteredCustomer::create([
            'fname' => $input['fname'],
            'lname' => $input['lname'],
            'contact_number' => $input['contact_number'],
            'house_number' => $input['house_number'],
            'street' => $input['street'],
            'barangay' => $input['barangay'],
            'municipality_city' => $input['municipality_city'] ?? 'Malolos',
            'province' => $input['province'] ?? 'Bulacan',
            'postal_code' => $input['postal_code'] ?? '3000'
        ]);

        
        $refill = Refill::create([
            'unregistered_customer_id' => $unregisteredCustomer->id,
            'admin_id' => 1,
            'status' => 'completed',
        ]);

        $gallon_delivery_request = GallonDelivery::create([
            'request_type_id' => $refill->id,
            'request_type' => 'refill',
            'status' => 'completed',
        ]);

        foreach( $input['data'] as $refill_request_data ){
            
            $refill->refill_details()->create([
                'shop_gallon_id' => $refill_request_data['gallon_id'],
                'refill_gallon_id' => $refill->id,
                'quantity' => $refill_request_data['quantity'],
            ]);
        }

        return $this->sendResponse($refill, 'Walk-in request processed successfully.');
    }
 
    
}
