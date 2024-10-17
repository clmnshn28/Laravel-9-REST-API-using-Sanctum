<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\AnnouncementReadStatus;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Validator;

class AnnouncementController extends BaseController
{
    
    public function getAllAnnouncementsForAdmin()
    {
        $announcement = Announcement::all();
        return $this->sendResponse($announcement, "Announcements Admin retrieved successfully.");
    }



    public function getAnnouncementsWithReadStatus()
    {
        $announcement = Announcement::all();
        return $this->sendResponse($announcement->load(['readStatus']), "Announcements Customer retrieved successfully.");
    }



    public function store(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'title' => 'required',
            'content' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Validator Error.', $validator->errors());
        }

        $announcement = Announcement::create($input);

        $customers = Customer::all(); 

        foreach ($customers as $customer) {
            AnnouncementReadStatus::create([
                'announcement_id' => $announcement->id,
                'admin_id' =>  Auth::guard('admin')->user()->id,
                'customer_id' => $customer->id,
                'is_read' => false, 
            ]);
        }

        return $this->sendResponse($announcement, 'Announcement created successfully.');
    }



    public function update(Request $request, $id)
    {
        $input = $request->all();

        $validator = Validator::make($input,[
            'title' => 'required',
            'content' => 'required',
        ]);
        
        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->error());
        }
        $announcement = Announcement::find($id);
        $announcement->title = $input['title'];
        $announcement->content = $input['content'];
        $announcement->save();

        AnnouncementReadStatus::where('announcement_id', $id)
        ->update(['is_read' => false]);

        return $this->sendResponse($announcement, 'Announcement updated successfully.');
    }



    public function destroy($id)
    {
        $announcement = Announcement::find($id);
        if (!$announcement) {
            return $this->sendError('Announcement not found.');
        }
        $announcement->delete();

        return $this->sendResponse($announcement, 'Announcement deleted successfully.');
    }



    public function markAsRead(Request $request, $announcementId)
    {
        $customerId = Auth::guard('customer')->user()->id;

        $readStatus = AnnouncementReadStatus::updateOrCreate(
            ['announcement_id' => $announcementId, 'customer_id' => $customerId],
            ['is_read' => true]
        );

        return $this->sendResponse($readStatus, 'Announcement marked as read successfully.');
    }



}
