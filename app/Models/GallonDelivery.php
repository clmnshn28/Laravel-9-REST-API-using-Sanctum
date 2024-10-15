<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GallonDelivery extends Model
{
    use HasFactory;

    protected $table = 'gallon_delivery';

    protected $fillable = [
        'request_type_id',
        'request_type',
        'status',
        'reason',
    ];


    public static function refill_gallon_delivery(){
        $results = DB::table('gallon_delivery')
        ->leftJoin('refill', function ($join) {
            $join->on('refill.id', '=', 'gallon_delivery.request_type_id');
        })  
        ->leftJoin('customers', function ($join) {
            $join->on('customers.id', '=', 'refill.customer_id');
        })
        ->select(
        DB::raw('gallon_delivery.id as gallon_delivery_id'),
        DB::raw('refill.id as refill_id'), 
        'gallon_delivery.*',  
        'refill.*',
        'customers.*', 
        DB::raw('(
            SELECT CONCAT(
                "1: ", 
                COALESCE(SUM(CASE WHEN shop_gallon_id = 1 THEN quantity ELSE 0 END), 0), 
                ", 2: ", 
                COALESCE(SUM(CASE WHEN shop_gallon_id = 2 THEN quantity ELSE 0 END), 0)
            ) 
            FROM refill_details 
            WHERE refill_details.refill_gallon_id = refill.id
        ) AS quantities'),
        'gallon_delivery.updated_at',
        DB::raw('gallon_delivery.status as gallon_delivery_status'),
        )
        ->where('gallon_delivery.request_type', '=', 'refill')  
        ->get();
        return $results;
    }

    public static function borrow_gallon_delivery(){
        $results = DB::table('gallon_delivery')
        ->leftJoin('borrow', function ($join) {
            $join->on('borrow.id', '=', 'gallon_delivery.request_type_id');
        })
        ->leftJoin('customers', function ($join) {
            $join->on('customers.id', '=', 'borrow.customer_id');
        })
        ->select('gallon_delivery.*', 
        DB::raw('gallon_delivery.id as gallon_delivery_id'), 
        DB::raw('borrow.id as borrow_id'), 
        'borrow.*',
        'customers.*', 
        DB::raw('(
            SELECT CONCAT(
                "1: ", 
                COALESCE(SUM(CASE WHEN shop_gallon_id = 1 THEN quantity ELSE 0 END), 0), 
                ", 2: ", 
                COALESCE(SUM(CASE WHEN shop_gallon_id = 2 THEN quantity ELSE 0 END), 0)
            ) 
            FROM borrow_details 
            WHERE borrow_details.borrowed_gallon_id = borrow.id
        ) AS quantities'),
        'gallon_delivery.updated_at',
        DB::raw('gallon_delivery.status as gallon_delivery_status'),
        DB::raw('gallon_delivery.reason as reason'),
        )
        ->where('gallon_delivery.request_type', '=', 'borrow')
        ->get();
        return $results;
    }

    public static function return_gallon_delivery(){
        $results = DB::table('gallon_delivery')
        ->leftJoin('returned', function ($join) {
            $join->on('returned.id', '=', 'gallon_delivery.request_type_id');
        })
        ->leftJoin('customers', function($join){
            $join->on('customers.id', '=', 'returned.customer_id');
        })
        ->select('gallon_delivery.*', 
        DB::raw('gallon_delivery.id as gallon_delivery_id'), 
        DB::raw('returned.id as returned_id'), 
        'returned.*',
        'customers.*', 
        DB::raw('(
            SELECT CONCAT(
                "1: ", 
                COALESCE(SUM(CASE WHEN shop_gallon_id = 1 THEN quantity ELSE 0 END), 0), 
                ", 2: ", 
                COALESCE(SUM(CASE WHEN shop_gallon_id = 2 THEN quantity ELSE 0 END), 0)
            ) 
            FROM returned_details 
            WHERE returned_details.returned_gallon_id = returned.id
        ) AS quantities'),
        'gallon_delivery.updated_at',
        DB::raw('gallon_delivery.status as gallon_delivery_status'),
        )
        ->where('gallon_delivery.request_type', '=', 'return')
        ->get();
        return $results;
    }
}
