<?php

namespace App\Http\Controllers\Order;

use App\Model\CartModel;
use App\Model\GoodsModel;
use App\Model\OrderModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    public $u_id;                    // 登录UID
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->u_id = session()->get('u_id');
            return $next($request);
        });
    }

    /**
     * 订单展示
     *liruixiang
     */
    public function orderList(){
        $list=OrderModel::where(['uid'=>$this->u_id])->get();
        $data=[
            'title'=>'订单展示',
            'list'=>$list
        ];
        return view('order.order',$data);
    }

    /**
     * 提交订单添加
     * liruixaing
     */
    public function orderAdd()
    {
        //查询购物车商品
        $cart_goods = CartModel::where(['uid'=>$this->uid])->orderBy('c_id','desc')->get()->toArray();
        //var_dump($cart_goods);exit;
        if(empty($cart_goods)){
            die("购物车中无商品");
        }
        $order_amount = 0;
        foreach($cart_goods as $k=>$v){
            $goods_info = GoodsModel::where(['goods_id'=>$v['goods_id']])->first()->toArray();
            $goods_info['c_num'] = $v['c_num'];
            $list[] = $goods_info;

            //计算订单价格 = 商品数量 * 单价
            $order_amount += $goods_info['goods_price'] * $v['c_num'];
        }

        //生成订单号
        $order_name = OrderModel::generateOrderSN();
        $data = [
            'o_name'      => $order_name,
            'uid'           => session()->get('u_id'),
            'o_ctime'      => time(),
            'o_amount'  => $order_amount
        ];

        $oid = OrderModel::insertGetId($data);
        if(!$oid){
            echo '生成订单失败';
        }

        $o_name=OrderModel::where(['o_id'=>$oid])->first();
        echo '下单成功,订单号：'.$o_name['o_name'] .' 跳转支付';

        //清空购物车
        CartModel::where(['uid'=>session()->get('u_id')])->delete();
        //header('refresh:2,url=');
    }
}
