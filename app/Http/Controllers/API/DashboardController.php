<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\Returned;
use App\Models\Refill;
use App\Models\Borrow;
use App\Models\ReturnedDetails;
use App\Models\RefillDetails;
use App\Models\BorrowDetails;
use App\Models\ShopGallon;
use App\Models\GallonDelivery;
use Carbon\Carbon;
use Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;  

class DashboardController extends BaseController
{
    
    public function getDashboardData(){
       
        $returnedGallons = Returned::select(
            DB::raw('CASE 
                WHEN returned_details.shop_gallon_id = 1 THEN "Slim"
                WHEN returned_details.shop_gallon_id = 2 THEN "Round"
                ELSE "Unknown"
            END as type'),
            DB::raw('SUM(returned_details.quantity) as total')
        )
        ->join('returned_details', 'returned.id', '=', 'returned_details.returned_gallon_id')
        ->where('returned.status', '=', 'completed') 
        ->groupBy('returned_details.shop_gallon_id')
        ->get();


        $refilledGallons = Refill::select(
            DB::raw('MONTH(refill.created_at) as month'),
            DB::raw('SUM(refill_details.quantity) as total')
        )
        ->join('refill_details', 'refill.id', '=', 'refill_details.refill_gallon_id')
        ->where('refill.status', '=', 'completed') 
        ->whereYear('refill.created_at', Carbon::now()->year)
        ->groupBy(DB::raw('MONTH(refill.created_at)'))
        ->orderBy('month')
        ->get();


        $borrowedGallons = Borrow::select(
            DB::raw('MONTH(borrow.created_at) as month'),
            DB::raw('SUM(borrow_details.quantity) as total')
        )
        ->join('borrow_details', 'borrow.id', '=', 'borrow_details.borrowed_gallon_id')
        ->where('borrow.status', '=', 'completed') 
        ->whereYear('borrow.created_at', Carbon::now()->year)
        ->groupBy(DB::raw('MONTH(borrow.created_at)'))
        ->orderBy('month')
        ->get();
        
        $data = [
            'returnedGallons' => $returnedGallons,
            'refilledGallons' => $refilledGallons,
            'borrowedGallons' => $borrowedGallons,
        ];

        return $this->sendResponse($data, 'Dashboard data retrieved successfully.');
    }


    public function getCustomerDashboardData()
    {
        $customer = Auth::guard('customer')->user();

        if (!$customer) {
            return $this->sendError('Unauthenticated.');
        }

        $returnedTotal = Returned::where('customer_id', $customer->id)
        ->where('status', '=', 'completed')
        ->with('returned_details') 
        ->get()
        ->sum(function ($returned) {
            return $returned->returned_details->sum('quantity');
        });

        $refilledTotal = Refill::where('customer_id', $customer->id)
        ->where('status', '=', 'completed')
        ->with('refill_details') 
        ->get()
        ->sum(function ($refill) {
            return $refill->refill_details->sum('quantity');
        });
       
        $borrowedTotal = Borrow::where('customer_id', $customer->id)
        ->where('borrow.status', '=', 'completed') 
        ->with('borrow_details') 
        ->get()
        ->sum(function ($borrow) {
            return $borrow->borrow_details->sum('quantity');
        });
    
        $gallonTotals = [
            'returned' => $returnedTotal,
            'refilled' => $refilledTotal,
            'borrowed' => $borrowedTotal,
        ];

        $refilledGallonsByMonth = Refill::select(
            DB::raw('MONTH(refill.created_at) as month'),
            DB::raw('refill_details.shop_gallon_id'),
            DB::raw('SUM(refill_details.quantity) as total')
        )
        ->join('refill_details', 'refill.id', '=', 'refill_details.refill_gallon_id')
        ->where('refill.customer_id', $customer->id)
        ->where('refill.status', '=', 'completed') 
        ->whereYear('refill.created_at', Carbon::now()->year)
        ->groupBy(DB::raw('MONTH(refill.created_at), refill_details.shop_gallon_id'))
        ->orderBy('month')
        ->get();


        $customerDashboardData = [
            'gallonTotals' => $gallonTotals,
            'refilledGallonsByMonth' => $refilledGallonsByMonth,
        ];

        return $this->sendResponse($customerDashboardData, 'Customer dashboard data retrieved successfully.');

    }

}
