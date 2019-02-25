<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>聊天页面</title>
    <meta name="csrf-token" content="{{csrf_token()}}">
</head>
<body>
<!-- //微聊消息上墙面板 -->
<div class="wc__chatMsg-panel flex1" style="border: 2px red solid;">
    <div class="wc__slimscroll2">
        <div class="chatMsg-cnt">
            <ul class="clearfix" id="J__chatMsgList">
                <p align="center"><a href="">﹀</a></p>


            </ul>
            <!-- //微聊底部功能面板 -->
            <div class="wc__footTool-panel" align="bottom">
                {{csrf_field()}}
                <input type="hidden" value="1" id="msg_pos">
                <!-- 输入框模块 -->
                <form action="" class="form-inline" align="right">
                    <input type="hidden" value="{{$openid}}" id="openid">
                    <input type="hidden" value="1" id="msg_pos">
                    <textarea name="" id="send_msg" cols="100" rows="5"></textarea>
                    <button class="btn btn-info" id="send_msg_btn">发送</button>
                </form>
            </div>
        </div>
    </div>
</div>
<hr>
</body>
</html>
<script src="{{URL::asset('/js/weixin/chat.js')}}"></script>
<script src="{{URL::asset('/js/jquery-1.12.4.min.js')}}"></script>