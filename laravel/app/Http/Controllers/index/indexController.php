<?php

namespace App\Http\Controllers\index;

use App\Http\Controllers\Controller;
use App\Model\userModel;

class indexController extends Controller
{
    public function index($id){
        $user=userModel::where(['user_id'=>$id])->first()->toArray();
        print_r($user);exit;
    }

    public function add(){
        $data= [
            'user_tel'=>str_random(10),
        ];

        $default=userModel::insert($data);
        var_dump($default);
    }

    public function delete($id){
        $where=[
            'user_id'=>$id
        ];
        $default=userModel::where($where)->delete();
        var_dump($default);
    }

    public function update($id){
        $data= [
            'user_tel'=>str_random(10),
        ];
        $where=[
            'user_id'=>$id
        ];
        $default=userModel::where($where)->update($data);
        var_dump($default);
    }

    public function select(){
        $default=userModel::all();
        $data=[
            'default'=>$default,
            'page'=>99
        ];
        return view('index.index',$data);
    }

    public function gitAdd(){
        echo 'liRUIxiang';
    }


}
