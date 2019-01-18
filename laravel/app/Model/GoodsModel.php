<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GoodsModel extends Model
{
    public $primaryKey = 'goods_id';
    public $table='laravel_goods';
    public $timestamps=false;

    //获取某字段时 格式化 该字段的值
    public function getPriceAttribute($goods_price)
    {
        return $goods_price / 100;
    }
}
