<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Auth\Events\Verified;

class VerificationController extends BaseController
{
    public function verify(Request $request, $id, $hash)
    {
        $customer = Customer::findOrFail($id);

            
        if (!hash_equals((string) $hash, (string) sha1($customer->email))) {
            return $this->sendError('Invalid verification link.');
        }

        // Check if the email is already verified
        if ($customer->hasVerifiedEmail()) {
            $url = env('APP_URL') . '/customer/sign-in';
            return redirect($url)->with('status', 'Email verified successfully. You can now log in.');
        }

        // Mark the email as verified and trigger the Verified event
        $customer->markEmailAsVerified();
        event(new Verified($customer));

        return $this->sendResponse([], 'Email verified successfully.');
    }

    
    public function send(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();

        return $this->sendResponse([], 'Verification link sent.');
    }
}
