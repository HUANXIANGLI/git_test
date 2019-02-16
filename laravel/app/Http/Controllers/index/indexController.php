<?php

namespace App\Http\Controllers\index;

use App\Model\userModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class indexController extends Controller
{
    /**
     * 登录密码
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * liruixiang
     */
    public function index(){
        if(request()->isMethod('post')){
            $user=request()->input('u_name');
            $pwd=request()->input('u_pwd');
            if(empty($user)){
                header('refresh:2,url=/index');
                die('用户名不能为空');
            }else if(empty($pwd)){
                header('refresh:2,url=/index');
                die('密码不能为空');
            }
            $where=[
                'u_name'=>$user,
                'u_pwd'=>$pwd
            ];

            $app=userModel::where($where)->first();
            if($app){
                //Redis::get('dl',$where);
                header('refresh:2,url=/index');
                echo '登录成功';
            }else{
                header('refresh:2,url=/index');
                echo '登录失败';
            }
        }else{
            $data=[
                'title'=> '考试登录缓存'
            ];
            return view('index.index',$data);
        }
    }

    /**
     * 修改
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * liruixiang
     */
    public function update(){
        if(request()->isMethod('post')){
            $u_name=request()->input('u_name');
            $pwd=request()->input('pwd');
            $pwd1=request()->input('pwd1');
            $pwd2=request()->input('pwd2');

            $where=[
                'u_name'=>$u_name,
                'u_pwd'=>$pwd
            ];
            
            $id=userModel::where($where)->first()->toArray();

            if(empty($u_name)){
                header('refresh:2,url=/index');
                die('用户名不能为空');
            }else if(empty($pwd)){
                header('refresh:2,url=/update');
                die('原密码不能为空');
            }else if(empty($pwd1)){
                header('refresh:2,url=/update');
                die('修改密码不能为空');
            }else if(empty($pwd2)){
                header('refresh:2,url=/update');
                die('确认密码不能为空');
            }else if($pwd1!=$pwd2){
                header('refresh:2,url=/update');
                die('修改密码与确认密码不一致');
            }

            $where=[
                'u_id'=>$id['u_id']
            ];

            $app=userModel::where($where)->update(['u_pwd'=>$pwd2]);
            if($app){
                header('refresh:2,url=/index');
                echo '修改成功';
            }else{
                header('refresh:2,url=/update');
                echo '用户名或密码不正确';
            }
        }else{
            $data=[
                'title'=> '修改缓存修改密码'
            ];
            return view('index.update',$data);
        }
    }

    /**
     * 坐位
     * liruixiang
     */
    public function movie(){
        $key = 'test_bit';      // redis key

        $seat_status = [];
        for($i=0;$i<=30;$i++){
            $status = Redis::getBit($key,$i);   //判断当前位 为0 或者 为1
            $seat_status[$i] = $status;
        }

        $data=[
            'seat'  => $seat_status,
            'title'=>'坐位展示'
        ];
        return view('index.movie',$data);
    }


    public function movieBuy($pos){
        $key = 'test_bit';
        Redis::setbit($key,$pos,1);
        header('Refresh:2;url=/movie');
        echo '抢座成功';
    }

}
