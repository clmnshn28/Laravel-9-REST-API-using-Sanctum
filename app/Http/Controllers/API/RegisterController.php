<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Validation\Rule;
use Illuminate\Auth\Events\Registered;

class RegisterController extends BaseController
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'username' => 'required|unique:customers,username',
            'password' => 'required|min:8',
            'c_password' => 'required|same:password',
            'fname' => 'required',
            'lname' => 'required',
            'email' => [
                'required',
                'email',
                Rule::unique('customers', 'email'),
                Rule::unique('users', 'email'),
            ],
            'contact_number' => 'nullable',
            'house_number' => 'nullable',
            'street' => 'nullable',
            'barangay' => 'nullable',
            'municipality_city' => 'nullable',
            'province' => 'nullable',
            'postal_code' => 'nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
   

        unset($input['c_password']);
      
        $customer = Customer::create([
            'username' => $input['username'],
            'password' => Hash::make($input['password']),
            'fname' => $input['fname'],
            'lname' => $input['lname'],
            'email' => $input['email'],
            'contact_number' => $input['contact_number'] ?? '',
            'house_number' => $input['house_number'] ?? '',
            'street' => $input['street'] ?? '',
            'barangay' => $input['barangay'] ?? '',
            'municipality_city' => $input['municipality_city'] ?? 'Malolos',
            'province' => $input['province'] ?? 'Bulacan',                   
            'postal_code' => $input['postal_code'] ?? '3000', 
            'image' => $input['image'] ?? null,
        ]);

        event(new Registered($customer));
 
        return $this->sendResponse([], 'Customer register successfully.');
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
            
            $token = $admin->createToken('Admin')->plainTextToken;
            $admin->remember_token = $token;
            $success['token'] = $token;
            $success['id'] = $admin->id;
            $success['fname'] = $admin->fname;
            $success['lname'] = $admin->lname;
            $success['role'] = 'admin';
            $admin->is_online = 1; 
            $admin->save();
            
            Auth::login($admin);

            return $this->sendResponse($success, 'Admin login successfully.');
        }
      
        return $this->sendError('Invalid username or password', ['error'=>'Unauthorized']);  
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

            if (is_null($customer->email_verified_at)) {
                return $this->sendError('Email not verified.');
            }

            $token = $customer->createToken('Customer')->plainTextToken;
            $customer->remember_token = $token;
            $success['token'] = $token;
            $success['id'] = $customer->id;
            $success['fname'] = $customer->fname;
            $success['lname'] = $customer->lname;
            $success['role'] = 'customer';
            $customer->is_online = 1; 
            $customer->save(); 

     
            return $this->sendResponse($success, 'Customer login successfully.');
        }

        return $this->sendError('Invalid username or password.', ['error'=>'Unauthorized']);
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