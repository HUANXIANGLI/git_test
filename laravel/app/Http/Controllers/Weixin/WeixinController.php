<?php

namespace App\Http\Controllers\Weixin;

use App\Model\userModel;
use App\Model\WeixinChat;
use App\Model\WeixinMaterial;
use App\Model\WeixinMedia;
use App\Model\WeixinUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp;
use Illuminate\Support\Facades\Storage;
use PhpParser\Node\Scalar\String_;

class WeixinController extends Controller
{
    protected $redis_weixin_access_token = 'str:weixin_access_token';     //微信 access_token
    protected $redis_weixin_jsapi_ticket = 'str:weixin_jsapi_ticket';     //微信  获取jsapi_ticket

    /**
     * 首次接入
     */
    public function validToken1()
    {
        //$get = json_encode($_GET);
        //$str = '>>>>>' . date('Y-m-d H:i:s') .' '. $get . "<<<<<\n";
        //file_put_contents('logs/weixin.log',$str,FILE_APPEND);
        echo $_GET['echostr'];
    }

    /**
     * 接收微信服务器事件推送
     */
    public function wxEvent()
    {
        $data = file_get_contents("php://input");
        //var_dump($data);exit;

        //解析XML
        $xml = simplexml_load_string($data);        //将 xml字符串 转换成对象
        //var_dump($xml);echo '<hr>';

        //记录日志
        $log_str = date('Y-m-d H:i:s') . "\n" . $data . "\n<<<<<<<";
        file_put_contents('logs/wx_event.log',$log_str,FILE_APPEND);

        $event = $xml->Event;                       //事件类型
        $openid = $xml->FromUserName;               //用户openid

        //用户发送文本消息
        if(isset($xml->MsgType)){
            if($xml->MsgType=='text'){
                $msg = $xml->Content;
                $xml_response = '<xml><ToUserName><![CDATA['.$openid.']]></ToUserName><FromUserName><![CDATA['.$xml->ToUserName.']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['. $msg. date('Y-m-d H:i:s') .']]></Content></xml>';
                echo $xml_response;

                //写入数据库
                $data = [
                    'openid' => $openid,
                    'type'=>0,
                    'text'=>$msg,
                    'ctime'=>time()
                ];

                WeixinChat::insertGetId($data);
            }elseif($xml->MsgType=='image'){       //用户发送图片信息
                //视业务需求是否需要下载保存图片
                if(1){  //下载图片素材
                    $file_name = $this->dlWxImg($xml->MediaId);
                    $xml_response = '<xml><ToUserName><![CDATA['.$openid.']]></ToUserName><FromUserName><![CDATA['.$xml->ToUserName.']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['. str_random(10) . ' >>> ' . date('Y-m-d H:i:s') .']]></Content></xml>';
                    echo $xml_response;

                    //写入数据库
                    $data = [
                        'openid'    => $openid,
                        'add_time'  => time(),
                        'msg_type'  => 'image',
                        'media_id'  => $xml->MediaId,
                        'format'    => $xml->Format,
                        'msg_id'    => $xml->MsgId,
                        'local_file_name'   => $file_name
                    ];

                    $m_id = WeixinMedia::insertGetId($data);
                    var_dump($m_id);
                }
            }elseif($xml->MsgType=='voice'){        //处理语音信息
                $this->dlVoice($xml->MediaId);
                $xml_response = '<xml><ToUserName><![CDATA['.$openid.']]></ToUserName><FromUserName><![CDATA['.$xml->ToUserName.']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['. str_random(10) . ' >>> ' . date('Y-m-d H:i:s') .']]></Content></xml>';
                echo $xml_response;
            }elseif($xml->MsgType=='video'){        //处理视频信息
                $this->dlVideo($xml->MediaId);
                $xml_response = '<xml><ToUserName><![CDATA['.$openid.']]></ToUserName><FromUserName><![CDATA['.$xml->ToUserName.']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['. str_random(10) . ' >>> ' . date('Y-m-d H:i:s') .']]></Content></xml>';
                echo $xml_response;
            }elseif($xml->MsgType=='event') {        //判断事件类型
                if($event=='subscribe'){
                    $openid = $xml->FromUserName;               //用户openid
                    $sub_time = $xml->CreateTime;               //扫码关注时间

                    //echo 'openid: '.$openid;echo '</br>';
                    //echo '$sub_time: ' . $sub_time;

                    //获取用户信息
                    $user_info = $this->getUserInfo($openid);
                    //echo '<pre>';print_r($user_info);echo '</pre>';

                    //保存用户信息
                    $u = WeixinUser::where(['openid'=>$openid])->first();
                    //var_dump($u);die;
                    if($u){       //用户不存在
                        echo '用户已存在';
                    }else{
                        $user_data = [
                            'openid'            => $openid,
                            'add_time'          => time(),
                            'nickname'          => $user_info['nickname'],
                            'sex'               => $user_info['sex'],
                            'headimgurl'        => $user_info['headimgurl'],
                            'subscribe_time'    => $sub_time,
                        ];

                        $id = WeixinUser::insertGetId($user_data);      //保存用户信息
                        var_dump($id);
                    }
                    $xml_response = '<xml><ToUserName><![CDATA['.$openid.']]></ToUserName><FromUserName><![CDATA['.$xml->ToUserName.']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['.'您好，谢谢您的关注'.']]></Content></xml>';
                    echo $xml_response;
                }elseif($event=='CLICK'){               //click 菜单
                    if($xml->EventKey=='click'){
                        $this->click($openid,$xml->ToUserName);
                    }
                }
            }
        }

    }

    /**
     * 文本消息
     */
    public function click($openid,$from){

        $xml_response = '<xml><ToUserName><![CDATA['.$openid.']]></ToUserName><FromUserName><![CDATA['.$from.']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['. '您好, 现在时间'. date('Y-m-d H:i:s') .']]></Content></xml>';
        echo $xml_response;
    }

    /**
     * 下载图片素材
     * @param $media_id
     */
    public function dlWxImg($media_id)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token='.$this->getWXAccessToken().'&media_id='.$media_id;
        //echo $url;echo '</br>';

        //保存图片
        $client = new GuzzleHttp\Client();
        $response = $client->get($url);
        //$h = $response->getHeaders();

        //获取文件名
        $file_info = $response->getHeader('Content-disposition');
        $file_name = substr(rtrim($file_info[0],'"'),-20);

        $wx_image_path = 'wx/images/'.$file_name;
        //保存图片
        $r = Storage::disk('local')->put($wx_image_path,$response->getBody());
        if($r){     //保存成功

        }else{      //保存失败

        }
        return $file_name;
    }

    /**
     * 下载语音文件
     * @param $media_id
     */
    public function dlVoice($media_id)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token='.$this->getWXAccessToken().'&media_id='.$media_id;

        $client = new GuzzleHttp\Client();
        $response = $client->get($url);
        //$h = $response->getHeaders();
        //echo '<pre>';print_r($h);echo '</pre>';die;
        //获取文件名
        $file_info = $response->getHeader('Content-disposition');
        $file_name = substr(rtrim($file_info[0],'"'),-20);

        $wx_image_path = 'wx/voice/'.$file_name;
        //保存语言
        $r = Storage::disk('local')->put($wx_image_path,$response->getBody());
        if($r){     //保存成功

        }else{      //保存失败

        }
        return $file_name;
    }

    /**
     * 下载视频文件
     * @param $media_id
     */
    public function dlVideo($media_id)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token='.$this->getWXAccessToken().'&media_id='.$media_id;

        $client = new GuzzleHttp\Client();
        $response = $client->get($url);
        //$h = $response->getHeaders();
        //echo '<pre>';print_r($h);echo '</pre>';die;
        //获取文件名
        $file_info = $response->getHeader('Content-disposition');
        $file_name = substr(rtrim($file_info[0],'"'),-20);

        $wx_image_path = 'wx/video/'.$file_name;
        //保存视频
        $r = Storage::disk('local')->put($wx_image_path,$response->getBody());
        if($r){     //保存成功

        }else{      //保存失败

        }

        return $file_name;
    }

    /**
     * 接收事件推送
     */
    public function validToken()
    {
        //$get = json_encode($_GET);
        //$str = '>>>>>' . date('Y-m-d H:i:s') .' '. $get . "<<<<<\n";
        //file_put_contents('logs/weixin.log',$str,FILE_APPEND);
        //echo $_GET['echostr'];
        $data = file_get_contents("php://input");
        $log_str = date('Y-m-d H:i:s') . "\n" . $data . "\n<<<<<<<";
        file_put_contents('logs/wx_event.log',$log_str,FILE_APPEND);
    }

    /**
     * 获取微信AccessToken
     */
    public function getWXAccessToken()
    {
        //获取缓存
        $token = Redis::get($this->redis_weixin_access_token);
        if(!$token){        // 无缓存 请求微信接口
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WEIXIN_APPID').'&secret='.env('WEIXIN_APPSECRET');
            $data = json_decode(file_get_contents($url),true);

            //记录缓存
            $token = $data['access_token'];
            Redis::set($this->redis_weixin_access_token,$token);
            Redis::setTimeout($this->redis_weixin_access_token,3600);
        }
        return $token;

    }

    /**
     * 获取用户信息
     * @param $openid
     */
    public function getUserInfo($openid)
    {
        //$openid = 'oGtn31fkd3Rfb-M72O_t8jXXDFzw';
        $access_token = $this->getWXAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';

        $data = json_decode(file_get_contents($url),true);
        return $data;
        //echo '<pre>';print_r($data);echo '</pre>';
    }

    /**
     * 修改菜单一 二 级
     */
    public function createMenu(){
        //echo __METHOD__;die;
        // 1 获取access_token 拼接请求接口
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$this->getWXAccessToken();
        //echo $url;echo '</br>';
        //2 请求微信接口
        $client = new GuzzleHttp\Client(['base_uri' => $url]);
        //var_dump($client);exit;

        $data= [
             "button"=>[
                [
                    "name"=>"欢迎你",
                    "sub_button"=>[
                        [
                            "type"=>"view",
                            "name"=>"百度搜索",
                            "url"=>"https://www.baidu.com/"
                        ],
                        [
                            "type"=>"view",
                            "name"=>"GITHUB",
                            "url"=>"http://github.com"
                        ],
                        [
                            "type"=>"view",
                            "name"=>"中国天气",
                            "url"=>"http://www.weather.com.cn/"
                        ],
                        [
                            "type"=>"view",
                            "name"=>"王者官方",
                            "url"=>"https://pvp.qq.com/"
                        ],
                        [
                            "type"=>"view",
                            "name"=>"炉石传说",
                            "url"=>"http://hs.blizzard.cn/landing"
                        ]
                    ]
                ],
                [
                    "name"=>"学习",
                    "sub_button"=>[
                        [
                            "type"=>"view",
                            "name"=>"搜索",
                            "url"=>"http://www.soso.com/"
                        ],
                        [
                            "type"=>"view",
                            "name"=>"掘金",
                            "url"=>"https://juejin.im/"
                        ],
                        [
                            "type"=>"view",
                            "name"=>"码云",
                            "url"=>"https://gitee.com/"
                        ],
                        [
                            "type"=>"view",
                            "name"=>"阿里云",
                            "url"=>"https://www.aliyun.com/"
                        ],
                        [
                            "type"=>"view",
                            "name"=>"腾讯云",
                            "url"=>"https://cloud.tencent.com/"
                        ],
                    ]
                ],
                 [
                     "type"=>"click",
                      "name"=>"现在时间",
                      "key"=>"click"
                  ]
//                [
//                    "name"=>"个人网页",
//                    "sub_button"=>[
//                        [
//                            "type"=>"view",
//                            "name"=>"主页",
//                            "url"=>"http://www.soso.com/"
//                        ]
//                    ]
//                ]
             ]
        ];

        $r = $client->request('POST', $url, [
            'body' => json_encode($data,JSON_UNESCAPED_UNICODE)
        ]);
        //var_dump($r);exit;
        // 3 解析微信接口返回信息

        $response_arr = json_decode($r->getBody(),true);
        //echo '<pre>';print_r($response_arr);echo '</pre>';die;

        if($response_arr['errcode'] == 0){
            echo "菜单创建成功";
        }else{
            echo "菜单创建失败，请重试";echo '</br>';
            echo $response_arr['errmsg'];

        }
    }

    /**
     * 群发
     */
    public function all()
    {
        $access_token = $this->getWXAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token='.$access_token;
        //var_dump($url);exit;
        $client = new GuzzleHttp\Client(['base_url' => $url]);
        $param = [
            "filter"=>[
                "is_to_all"=>true
            ],
            "text"=>[
                "content"=>"as."
            ],
            "msgtype"=>"text"
        ];
        ///var_dump($param);exit;
        $r = $client->Request('POST', $url, [
            'body' => json_encode($param, JSON_UNESCAPED_UNICODE)
        ]);
        //var_dump($r);exit;
        $response_arr = json_decode($r->getBody(), true);
        //echo '<pre>';
        //print_r($response_arr);
        // echo '</pre>';

        if ($response_arr['errcode'] == 0) {
            echo "发送成功";
        } else {
            echo "发送失败";
            echo '</br>';
            echo $response_arr['errmsg'];

        }
    }

    /**
     * 上传素材
     */
    public function upMaterial()
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/material/add_material?access_token='.$this->getWXAccessToken().'&type=image';
        $client = new GuzzleHttp\Client();
        $response = $client->request('POST',$url,[
            'multipart' => [
                [
                    'name'     => 'username',
                    'contents' => 'zhangsan'
                ],
                [
                    'name'     => 'media',
                    'contents' => fopen('QMAzRcNh3MGWS8A.png', 'r')
                ],
            ]
        ]);

        $body = $response->getBody();
        echo $body;echo '<hr>';
        $d = json_decode($body,true);
        echo '<pre>';print_r($d);echo '</pre>';


    }

    /**
     * 获取永久素材列表
     */
    public function materialList()
    {
        $client = new GuzzleHttp\Client();
        $type = $_GET['type'];
        $offset = $_GET['offset'];

        $url = 'https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token='.$this->getWXAccessToken();

        $body = [
            "type"      => $type,
            "offset"    => $offset,
            "count"     => 20
        ];
        $response = $client->request('POST', $url, [
            'body' => json_encode($body)
        ]);

        $body = $response->getBody();
        echo $body;echo '<hr>';
        $arr = json_decode($response->getBody(),true);
        echo '<pre>';print_r($arr);echo '</pre>';


    }

    /**
     * 上传至微信永久素材
     * @param $file_path
     */
    public function upMaterialTest($file_path)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/material/add_material?access_token='.$this->getWXAccessToken().'&type=image';
        $client = new GuzzleHttp\Client();
        $response = $client->request('POST',$url,[
            'multipart' => [
                [
                    'name'     => 'media',
                    'contents' => fopen($file_path, 'r')
                ],
            ]
        ]);

        $body = $response->getBody();
        echo $body;echo '<hr>';
        $d = json_decode($body,true);
        echo '<pre>';print_r($d);echo '</pre>';


    }

    /**
     * 添加永久素材页面
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function WxMediaShow(){
        return view('weixin.wxshow');
    }

    /**
     * 素材添加
     * @param Request $request
     */
    public function WxMediaAdd(Request $request){
        //echo '<pre>';print_r($_POST);echo '</pre>';echo '<hr>';
        //echo '<pre>';print_r($_FILES);echo '</pre>';echo '<hr>';
        //var_dump($_POST);
        //exit;
        //保存文件
        $img_file = $request->file('media');
        //echo '<pre>';print_r($img_file);echo '</pre>';echo '<hr>';

        $img_origin_name = $img_file->getClientOriginalName();
        echo 'originName: '.$img_origin_name;echo '</br>';
        $file_ext = $img_file->getClientOriginalExtension();          //获取文件扩展名
        echo 'ext: '.$file_ext;echo '</br>';

        //重命名
        $new_file_name = str_random(15). '.'.$file_ext;
        echo 'new_file_name: '.$new_file_name;echo '</br>';

        //文件保存路径


        //保存文件
        $save_file_path = $request->media->storeAs('form_test',$new_file_name);       //返回保存成功之后的文件路径

        echo 'save_file_path: '.$save_file_path;echo '<hr>';

        $material_data = [
            'test'=>$request->input('msg'),
            'url'=>$save_file_path,
            'atime'=>time()
        ];

        $id = WeixinMaterial::insertGetId($material_data);      //保存用户信息

        if($id){
            echo '添加成功';
        }else{
            echo '添加失败';
        }

        //上传至微信永久素材
        $this->upMaterialTest($save_file_path);
    }

    /**
     * 微信扫一扫登录添加
     * @return string
     */
    public function getCode(){
        $code = $_GET['code'];          // code

        //2 用code换取access_token 请求接口

        $token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=wxe24f70961302b5a5&secret=0f121743ff20a3a454e4a12aeecef4be&code='.$code.'&grant_type=authorization_code';
        $token_json = file_get_contents($token_url);
        $token_arr = json_decode($token_json,true);
        echo '<hr>';
        echo '<pre>';print_r($token_arr);echo '</pre>';

        $access_token = $token_arr['access_token'];
        $openid = $token_arr['openid'];

        // 3 携带token  获取用户信息
        $user_info_url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
        $user_json = file_get_contents($user_info_url);

        $user_arr = json_decode($user_json,true);
        $unionid=$user_arr['unionid'];
        $name=$user_arr['nickname'];
        $res = WeixinUser::where(['unionid' => $unionid])->first();

        if($res){
            return '登陆成功';
        }else {
            $data = [
                'u_name' => $name
            ];
            $id = userModel::insertGetId($data);
            if ($id) {
                //var_dump($id);
                $arr = [
                    'uid' => $id,
                    'openid' => $user_arr['openid'],
                    'add_time' => time(),
                    'nickname' => $name,
                    'sex' => $user_arr['sex'],
                    'headimgurl' => $user_arr['headimgurl'],
                    'subscribe_time' => time(),
                    'unionid' => $unionid
                ];
                $res = WeixinUser::insertGetId($arr);
                // var_dump($r);
                if ($res) {
                    return '成功';
                } else {
                    return '失败';
                }
            } else {
                return '第一条数据入库失败';
            }
            }
    }

    /**
     * 微信扫一扫展示页面
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function codeAdd(){
        $data=[
            'title'=>'微信二维码登录'
        ];
        return view('weixin.onesweep',$data);
    }

    /**
     * 获取jsapi_ticket
     * [getJsapiTicket description]
     * @return [type] [description]
     */
    public function getJsapiTicket() {
        $access_token=$this->getWXAccessToken();
        $jsapi_ticket = Redis::get($this->redis_weixin_jsapi_ticket);
        if(!$jsapi_ticket){
            $url = "http://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$access_token."&type=jsapi";

            $data = json_decode(file_get_contents($url),true);
            var_dump($data);
            //记录缓存
            $jsapi_ticket = $data['ticket'];
            Redis::set($this->redis_weixin_jsapi_ticket,$jsapi_ticket);
            Redis::setTimeout($this->redis_weixin_jsapi_ticket,7200);
        }
        return $jsapi_ticket;
    }

    /**
     * 微信jssdk 调试
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function jssdkTest()
    {
        //计算签名
        $jsconfig = [
            'appid' => env('WEIXIN_APPID'),        //APPID
            'timestamp' => time(),
            'noncestr'    => str_random(10),
            //'sign'      => $this->wxJsConfigSign()
        ];
        $sign = $this->wxJsConfigSign($jsconfig);
        $jsconfig['sign'] = $sign;
        $data = [
            'title'=>'JSSDK',
            'jsconfig'  => $jsconfig
        ];
        return view('weixin.jssdk',$data);
    }


    /**
     * 计算JSSDK sign
     */
    public function wxJsConfigSign($param)
    {
        $current_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];     //当前调用 jsapi的 url
        $ticket = $this->getJsapiTicket();
        $str =  'jsapi_ticket='.$ticket.'&noncestr='.$param['noncestr']. '&timestamp='. $param['timestamp']. '&url='.$current_url;
        $signature=sha1($str);
        return $signature;
    }

}
