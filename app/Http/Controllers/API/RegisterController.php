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
     * Login for Admin
     *
     * @return \Illuminate\Http\Response
     */
    public function loginAdmin(Request $request)
    {

        $admin = User::where('username', $request->username)->first();


        if($admin && Hash::check($request->password, $admin->password)){
            
            $token = $admin->createToken('Customer')->plainTextToken;
            $admin->remember_token = $token;
            $success['token'] = $token;
            $success['fname'] = $admin->fname;
            $success['lname'] = $admin->lname;
            $success['role'] = 'admin';
            $admin->is_online = 1; 
            $admin->save(); 

            return $this->sendResponse($success, 'Admin login successfully.');
        }
      
        return $this->sendError('Unauthorized.', ['error'=>'Unauthorized']);  
    }

    /**
     *  Login for Customer
     * 
     * @return \Illuminate\Http\Response
     */
    public function loginCustomer(Request $request)
    {
        $customer = Customer::where('username', $request->username)->first();

        if($customer && Hash::check($request->password, $customer->password)){

            $token = $customer->createToken('Customer')->plainTextToken;
            $customer->remember_token = $token;
            $success['token'] = $token;
            $success['fname'] = $customer->fname;
            $success['lname'] = $customer->lname;
            $success['role'] = 'customer';
            $customer->is_online = 1; 
            $customer->save(); 

            return $this->sendResponse($success, 'Customer login successfully.');
        }

        return $this->sendError('Unauthorized.', ['error'=>'Unauthorized']);
    }


    /**
     * Logout for Admin and Customer
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $user = Auth::user(); // Get the currently authenticated user

        if ($user) {

            $user->remember_token = null;
            $user->is_online = 0;
            $user->save();

            return $this->sendResponse([], 'User logged out successfully.');
        }

        return $this->sendError('Unauthorized.', ['error' => 'Unauthorized']);
    }


}