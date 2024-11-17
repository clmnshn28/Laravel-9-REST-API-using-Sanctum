<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\Notification; 
use Illuminate\Support\Facades\Auth;

class NotificationController extends BaseController
{
    public function indexForCustomer(){
        $customerId = Auth::guard('customer')->user()->id;

        $notifications = Notification::where('customer_id', $customerId)
            ->where('is_admin', false) 
            ->get();

        return $this->sendResponse($notifications, 'Customer notifications retrieved successfully.');
    }


    public function markAsReadCustomer($id){

        $customerId = Auth::guard('customer')->user()->id;

        $notification = Notification::where('id', $id)->where('customer_id', $customerId)->first();

        if (!$notification) {
            return $this->sendError('Notification not found or you do not have permission to access it.');
        }

        $notification->is_read = true;
        $notification->save();

        return $this->sendResponse($notification, 'Notification marked as read successfully.');
    }




    public function indexForAdmin(){
        
        $notifications = Notification::where('is_admin', true)
        ->orderBy('created_at', 'desc')
        ->get();

        $groupedNotifications = $notifications->groupBy(function ($notification) {
            return $notification->type . '-' . $notification->created_at->format('Y-m-d H:00');
        });
    
        $batchNotifications = [];

        foreach ($groupedNotifications as $key => $group) {
            $firstNotification = $group->first();
            $earliestNotification = $group->sortBy('created_at')->first();

            $description = '';
            if ($firstNotification->type === 'Concern') {
                $concernCount = $group->count();
                $description = 'There ' . ($concernCount == 1 ? 'is' : 'are') . ' ' . $concernCount . ' new customer concern' . ($concernCount == 1 ? '' : 's') . ' that may require attention.';
                
            } else {
                $requestCount = $group->count();
                $description = 'There ' . ($requestCount == 1 ? 'is' : 'are') . ' ' . $requestCount . ' new ' . strtolower($firstNotification->type) . ($requestCount == 1 ? ' request' : ' requests');
            }

            $batchNotifications[] = [
                'type' => $firstNotification->type,
                'subject' => $firstNotification->subject,
                'description' => $description,
                'time' => $earliestNotification->created_at->diffForHumans(),
                'is_read' => $group->every(function ($notif) {
                    return $notif->is_read;
                }) ? true : false,
                'count' => $group->count(),
                'batch_id' => $group,  
            ];
        }
    
        return $this->sendResponse($batchNotifications, 'Admin notifications batched successfully.');
    }



    public function markAsReadAdmin(Request $request){

        $validated = $request->validate([
            'notification_ids' => 'required|array'
        ]);

        Notification::whereIn('id', $validated['notification_ids'])
        ->where('is_admin', true)
        ->update(['is_read' => true]);

        return $this->sendResponse(null,"Selected notifications marked as read successfully.");
    }

}
