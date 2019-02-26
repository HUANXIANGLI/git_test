<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');

    $router->resource('/goods',GoodsController::class);
    $router->resource('/users',UsersController::class);
    $router->resource('/wxuser',WeixinController::class);
    $router->resource('/wxmedia',WeixinMediaController::class);
    $router->resource('/material',WeixinMaterialController::class);

    $router->get('/information','WeixinController@information');      //展示页面
    $router->get('/chat','WeixinController@getChatMsg');      //定时刷新
    $router->get('/textMsg','WeixinController@textMsg');      //添加进入

    $router->get('/weixin/sendmsg','WeixinController@sendMsgView');      //
    $router->post('/weixin/sendmsg','WeixinController@sendMsg');
});