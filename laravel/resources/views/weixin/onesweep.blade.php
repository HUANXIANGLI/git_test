@extends('layout.goods')
@section('title') {{$title}}    @endsection
@section('content')
    <a href="">微信二维码登录</a>
    <div class="container">
        <h2>微信登录</h2>
        <h3>
            <a href="https://open.weixin.qq.com/connect/qrconnect?appid=wxe24f70961302b5a5&amp;redirect_uri=http%3A%2F%2Fmall.77sc.com.cn%2Fweixin.php%3Fr1%3Dhttp%3A%2F%2Fig.anjingdehua.cn%2FcodeAdd&amp;response_type=code&amp;scope=snsapi_login&amp;state=STATE#wechat_redirect">Login</a>
        </h3>
    </div>
@endsection
@section('footer')
    @parent
    <script src="{{URL::asset('/js/weixin/chat.js')}}"></script>
@endsection