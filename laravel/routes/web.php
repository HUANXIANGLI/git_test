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

Route::get('user/{id}', 'index\IndexController@index');



Route::get('add', 'index\IndexController@add');
Route::get('delete/{id}', 'index\IndexController@delete');
Route::get('update/{id}', 'index\IndexController@update');
Route::get('select', 'index\IndexController@select');