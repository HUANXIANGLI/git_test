<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OrderModel extends Model
{
    public $table = 'laravel_order';
    public $timestamps = false;


    /**
     * 生成订单号
     */
    public static function generateOrderSN()
    {
        return 'liruixiang'.date('ymdHi') . rand(11111,99999) . rand(2222,9999);
    }
}
