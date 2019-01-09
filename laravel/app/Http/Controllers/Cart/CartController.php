<?php

namespace App\Http\Controllers\Cart;

use App\Model\CartModel;
use App\Model\GoodsModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CartController extends Controller
{
    /**
     *购物车添加
     *liruixiang
     */
    public function cartAdd($goods_id){
        //取存的cart_goods判断有无在数据库当中
        $cart_goods = session()->get('cart_goods');
        //print_r($cart_goods);exit;
        //是否已在购物车中   如果不是空的判断
        if(!empty($cart_goods)){
            if(in_array($goods_id,$cart_goods)){
                echo '已存在购物车中';
                exit;
            }
        }

        //存session 之后判断存在数据库当中
        session()->push('cart_goods',$goods_id);

        //减库存
        $where = ['goods_id'=>$goods_id];
        $goods_store = GoodsModel::where($where)->value('goods_store');
        //print_r($goods_store);exit;
        if($goods_store<=0){
            echo '库存不足';
            exit;
        }
        $rs = GoodsModel::where(['goods_id'=>$goods_id])->decrement('goods_store');

        if($rs){
            $where=[
                'goods_id'=> $goods_id
            ];
            $goods_name=GoodsModel::where($where)->first();
            echo '成功添加'."<p style='color:red'>".$goods_name['goods_name']."</p>".'一件，谢谢您的光临';
        }else{
            echo '添加失败';
        }
    }

    /**
     * 购物车删除
     * liruixiang1
     */
    public function cartDel($goods_id){
        //判断 商品是否在 购物车中
        $goods = session()->get('cart_goods');
        //var_dump($goods);exit;
        //echo '<pre>';print_r($goods);echo '</pre>';die;
        $where=[
             'goods_id'=> $goods_id
        ];


        if(in_array($goods_id,$goods)){
            //执行删除
            foreach($goods as $k=>$v){
                if($goods_id == $v){
                    session()->pull('cart_goods.'.$k);
                    $goods_name=GoodsModel::where($where)->first();
                    echo '删除购物车成功----  成功减少 '."<p style='color:red'>".$goods_name['goods_name']."</p>".'一件';
                }
            }
        }else{
            //不在购物车中
            die("商品不在购物车中");
        }
    }

    /**
     * 购物车添加
     * @return array
     * liruixiang
     */
    public function cartAdd2(){
        $goods_id = request()->input('goods_id');
        $num = request()->input('c_num');
        //var_dump($goods_id);
        //var_dump($num);
        //检查库存
        $store_num = GoodsModel::where(['goods_id'=>$goods_id])->value('goods_store');
        if($store_num<=0){
            $response = [
                'errno' => 5001,
                'msg'   => '库存不足'
            ];
            return $response;
        }

        //写入购物车表
        $data = [
            'goods_id'  => $goods_id,
            'c_num'       => $num,
            'c_ctime'  => time(),
            'uid'       => session()->get('u_id'),
            'session_token' => session()->get('u_token')
        ];

        $cid = CartModel::insertGetId($data);
        if(!$cid){
            $response = [
                'errno' => 5002,
                'msg'   => '添加购物车失败，请重试'
            ];
            return $response;
        }


        $response = [
            'error' => 0,
            'msg'   => '添加成功'
        ];
        return $response;
    }

}
