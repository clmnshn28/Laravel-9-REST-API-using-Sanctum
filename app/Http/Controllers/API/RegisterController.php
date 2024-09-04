<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Validator;
   
class RegisterController extends BaseController
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
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
            return $this->sendError('Validation Error.', $validator->errors());       
        }
   
        $input = $request->all();
        unset($input['c_password']);
        $input['password'] = Hash::make($input['password']);

        $customer = Customer::create($input);
        $success['token'] =  $customer->createToken('MyApp')->plainTextToken;
        $success['fname'] = $customer->fname;
        $success['lname'] = $customer->lname;
   
        return $this->sendResponse($success, 'User register successfully.');
    }
   
    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {

        $admin = User::where('username', $request->username)->first();
        $customer = Customer::where('username', $request->username)->first();

        if($admin && Hash::check($request->password, $admin->password)){
            $success['token'] =  $admin->createToken('Admin')->plainTextToken; 
            $success['fname'] = $admin->fname;
            $success['lname'] = $admin->lname;
            $success['role'] = 'admin';

            return $this->sendResponse($success, 'Admin login successfully.');
        } 

        if($customer && Hash::check($request->password, $customer->password)){
            $success['token'] = $customer->createToken('Customer')->plainTextToken;
            $success['fname'] = $customer->fname;
            $success['lname'] = $customer->lname;
            $success['role'] = 'customer';
    
            return $this->sendResponse($success, 'Customer login successfully.');
        }
    
       
        return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        
    }
}