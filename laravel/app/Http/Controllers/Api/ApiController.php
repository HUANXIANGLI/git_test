<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ApiController extends Controller
{
    public function encrypt(){
        $url = 'http://ig.anjingdehua.cn/api/decrypt';
        $data = [
            'user_name' => 'test001',
            'pass' => '123456abc',
            'email' => 'zhangsan@qq.com'
        ];
        $method = 'AES-128-CBC';
        $now = time();
//var_dump($now);
        $key = 'password';
        $salt = 'xxxxx';
        $iv = substr(md5($now . $salt), 5, 16);
        $json_str = json_encode($data);
        $nec_data = openssl_encrypt($json_str, $method, $key, OPENSSL_RAW_DATA, $iv);
        //echo $nec_data;die;

        $post_data = base64_encode($nec_data);
        //echo $post_data;die;
        $data2=[
            't'=>$now,
            'post_data'=>$post_data
        ];
        //向服务器发送输数据
        $ch = curl_init();
        //var_dump($ch);die;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $rs = curl_exec($ch);
        //echo $rs;exit;
        $response = json_decode($rs, true);
        //var_dump($response);exit;


        //解密响应数据
        $iv2 = substr(md5($response['t'] . $salt), 5, 16);
        $dec_data = openssl_decrypt(base64_decode($response['data']), $method, $key, OPENSSL_RAW_DATA, $iv2);
        var_dump($dec_data);
    }

    public function decrypt(Request $request){
        $timestamp=$request->input('t');
        $key='password';
        $salt = 'xxxxx';
        $method = 'AES-128-CBC';
        $iv = substr(md5($timestamp . $salt), 5, 16);

        //接受加密数据
        $post_data=base64_decode($request->input('post_data'));
        $dec_str = openssl_decrypt($post_data, $method, $key, OPENSSL_RAW_DATA, $iv);

        if(1) {
            $now = time();
            $response = [
                'errno' => 0,
                'msg' => 'ok',
                'data' => 'this is secret'
            ];
            $iv2 = substr(md5($now . $salt), 5, 16);
            $net_data = openssl_encrypt(json_encode($response), $method, $key, OPENSSL_RAW_DATA, $iv2);
            $arr = [
                't' => $now,
                'data' => base64_encode($net_data)
            ];
            return json_encode($arr);
        }
    }

    public function encryption(){
        // $now = time();
        $url = 'http://ig.anjingdehua.cn/api/decryption';
        $data = [
            'aaa' => 'aaa',
            'bbb' => 'bbb',
            'ccc' => 'ccc'
        ];
        $method = 'AES-128-CBC';
        $now = time();
        $key = 'password';
        $salt = 'zzzzzz';
        $iv = substr(md5($now,$salt),0,16);
        $json_str = json_encode($data);
        $enc_data = openssl_encrypt($json_str,$method,$key,OPENSSL_RAW_DATA,$iv);
        $post_data = base64_encode($enc_data);

        //计算签名
        $priKey = file_get_contents('./key/priv.key');
        $res = openssl_get_privatekey($priKey);
        ($res) or die('您使用的私钥格式错误，请检查RSA私钥配置');

        openssl_sign($post_data, $sign, $res, OPENSSL_ALGO_SHA256);
        $sign = base64_encode($sign);

        $ch =  curl_init();

        curl_setopt($ch,CURLOPT_URL,$url);

        curl_setopt($ch,CURLOPT_POST,1);

        curl_setopt($ch,CURLOPT_POSTFIELDS,['data'=>$post_data,'sign' => $sign,'now' => $now]);

        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

        curl_setopt($ch,CURLOPT_HEADER,0);

        $rs = curl_exec($ch);

        var_dump($rs);

    }

    public function decryption(){
        $data = $request->input('data');
        $now = $request->input('now');
        $sign = $request->input('sign');
        $pubkey = file_get_contents('./key/pub.pem');
        $res = openssl_get_publickey($pubkey);
        ($res) or die('您使用的公钥格式错误，请检查RSA私钥配置');
        $result = openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_SHA256);
        //   openssl_free_key($result);
        if ($result == 1) {
            $key = "password";
            $iv = substr(md5($now), 1, 16);
            $en = openssl_decrypt(base64_decode($data), 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);

            return '成功';
        }

    }

}
