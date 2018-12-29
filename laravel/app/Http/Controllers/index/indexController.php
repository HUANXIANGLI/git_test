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
}
