<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator;
use App\Rules\UniqueForUser;

class ProfileController extends BaseController
{
       
    // Get the current authenticated user data
    public function show(Request $request)
    {
        $user = $request->user();
        return $this->sendResponse($user, 'Current user retrieved successfully.');
    }


    
    // Update the current authenticated user's information
    public function update(Request $request)
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users,username,'.$user->id,
            'fname' => 'required',
            'lname' => 'required',
            'email' => 'required|email|unique:users,email,'.$user->id,
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

        $user->username = $request->username;
        $user->fname = $request->fname;
        $user->lname = $request->lname;
        $user->email = $request->email;
        $user->contact_number = $request->contact_number ?? '';
        $user->house_number = $request->house_number ?? $user->house_number;
        $user->street = $request->street ?? $user->street;
        $user->barangay = $request->barangay ?? $user->barangay;
        $user->municipality_city = $request->municipality_city ?? $user->municipality_city;
        $user->province = $request->province ?? $user->province;
        $user->postal_code = $request->postal_code ?? $user->postal_code;

        $user->save();

        return $this->sendResponse($user, 'User updated successfully.');
    }



    // Update the current user's profile picture
    public function updateImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Validation rules
        ]);

        $user = $request->user();

        // Handle file upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('storage/images'), $imageName); // Move the image to the public directory

            // Update the user profile picture path in the database
            $user->image = $imageName;
            $user->save();
        }

        return response()->json(['success' => 'Profile picture updated successfully.']);
    }



    // Change the current user's password
    public function changePassword(Request $request)
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:8',
            'confirm_new_password' => 'required|same:new_password',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors());
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->sendError('Error', ['current_password' => 'Current password is incorrect']);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return $this->sendResponse([], 'Password changed successfully.');
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


}
