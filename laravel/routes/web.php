<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
//用户注册
Route::any('userAdd', 'User\UserController@userAdd');

//用户登录
Route::any('loginAdd', 'User\UserController@loginAdd');

//个人中心
Route::any('center','User\UserController@center');

//商品主页删除
Route::any('loginQuit','User\UserController@loginQuit');



//商品主页
Route::any('goodsList','Goods\GoodsController@goodsList')->middleware('check.login.token');

//商品主页删除
Route::any('goodsDel/{goods_id}','Goods\GoodsController@goodsDel')->middleware('check.login.token');

//商品详情
Route::any('goodsDetails/{goods_id}','Goods\GoodsController@goodsDetails')->middleware('check.login.token');



//购物车添加
Route::any('cartAdd/{goods_id}','Cart\CartController@cartAdd')->middleware('check.login.token');

//购物车删除
Route::any('cartDel/{goods_id}','Cart\CartController@cartDel')->middleware('check.login.token');


