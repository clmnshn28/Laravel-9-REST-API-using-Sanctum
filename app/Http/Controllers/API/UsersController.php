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
                'username' => 'required|unique:customers',
                'password' => 'required|min:8',
                'c_password' => 'required|same:password', 
                'fname' => 'required',
                'lname' => 'required',
                'contact_number' => 'nullable',
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

            unset($input['c_password']);

            $customer = Customer::create([
                'username' => $input['username'],
                'password' => Hash::make($input['password']),
                'fname' => $input['fname'],
                'lname' => $input['lname'],
                'contact_number' => $input['contact_number'],
                'house_number' => $input['house_number'],
                'street' => $input['street'],
                'barangay' => $input['barangay'],
                'municipality_city' => $input['municipality_city'],
                'province' => $input['province'],
                'postal_code' => $input['postal_code'],
            ]);

            return $this->sendResponse($customer, 'Customer created successfully.');
        }



    public function update(Request $request, Customer $customer){

        $input = $request->all();

        $validator = Validator::make($input, [
            'username' => 'required|unique:customers,username,'. $customer->id,
            'password' => 'nullable|min:8',
            'fname' => 'required',
            'lname' => 'required',
            'contact_number' => 'required',
            'house_number' => 'required',
            'street' => 'required',
            'barangay' => 'required',
            'municipality_city' => 'required',
            'province' => 'required',
            'postal_code' => 'required',
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
        $customer->contact_number = $input['contact_number'];
        $customer->house_number = $input['house_number'];
        $customer->street = $input['street'];
        $customer->barangay = $input['barangay'];
        $customer->municipality_city = $input['municipality_city'];
        $customer->province = $input['province'];
        $customer->postal_code = $input['postal_code'];

        $customer->save();

        return $this->sendResponse($customer, 'Customer updated successfully.');

    }


}
