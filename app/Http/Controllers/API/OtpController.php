<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Otp;
use App\Models\Customer; 
use App\Models\User; 
use Validator;
use Mail;

class OtpController extends BaseController
{
    public function sendOtp(Request $request){

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
   
        $email = $request->input('email');

        $customer = Customer::where('email', $email)->first();
        $admin = User::where('email', $email)->first();
        if (!$customer && !$admin) {
            return $this->sendError('Email not found.');
        }

        $otpCode = rand(100000, 999999);

        Otp::create([
            'email' => $email,
            'otp_code' => $otpCode,
        ]);

        Mail::send('otp', ['otp' => $otpCode], function ($message) use ($email) {
            $message->to($email)
                    ->subject('Your OTP Code');
        });

        return $this->sendResponse([], 'Customer register successfully.');
    }


    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp_code' => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $email = $request->input('email');
        $otpCode = $request->input('otp_code');

        // Check if the OTP exists and matches
        $otp = Otp::where('email', $email)
                    ->where('otp_code', $otpCode)
                    ->first();

        if (!$otp) {
            return $this->sendError('Invalid OTP.');
        }

        if ($otp->created_at->diffInSeconds(now()) > 180) {
            $otp->delete(); 
            return $this->sendError('OTP has expired.');
        }

        $otp->delete();

        return $this->sendResponse([], 'OTP verified successfully.');
    }




    public function deleteOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $email = $request->input('email');

        // Delete the OTP for the given email
        Otp::where('email', $email)->delete();

        return $this->sendResponse([], 'OTP deleted successfully.');
    }



    public function resetPassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email', 
            'password' => 'required|string|confirmed', 
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $email = $request->input('email');
        
        // Check if the email belongs to a customer or an admin
        $customer = Customer::where('email', $email)->first();
        $admin = User::where('email', $email)->first();

        
        if ($customer) {
            $customer->password = Hash::make($request->input('password'));
            $customer->save();
        } elseif ($admin) {
            $admin->password = Hash::make($request->input('password'));
            $admin->save();
        } else {
            return $this->sendError('User not found.');
        }

        return $this->sendResponse([], 'Password reset successfully.');

    }


}
