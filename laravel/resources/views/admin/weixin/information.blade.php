<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>聊天页面</title>
</head>
<body>
<!-- //微聊消息上墙面板 -->
<div class="wc__chatMsg-panel flex1" style="border: 1px red solid;">
    <div class="wc__slimscroll2">
        <div class="chatMsg-cnt">
            <ul class="clearfix" id="J__chatMsgList">
                <p align="center"><a href="">﹀</a></p>


            </ul>
        </div>
    </div>
</div>

<!-- //微聊底部功能面板 -->
<div class="wc__footTool-panel" align="bottom">
    <input type="hidden" value="1" id="msg_pos">
    <!-- 输入框模块 -->
    <form class="wc__editor-panel wc__borT flexbox">
        <input type="hidden" value="{{$openid}}" id="openid">
        <div style="float: right;"><button style="height:32px;" id="send_msg_btn">发送</button></div>
        <div class="wrap-editor flex1" style="width:1000px;float: right;"><div class="editor J__wcEditor" style="border:1px black solid" contenteditable="true" id="send_msg"></div></div>
        <i class="btn btn-emotion"></i>
        <i class="btn btn-choose"></i>
    </form>

    <!-- 表情、选择模块 -->
    <div class="wc__choose-panel wc__borT" style="display: none;">
        <!-- 表情区域 -->
        <div class="wrap-emotion" style="display: none;">
            <div class="emotion__cells flexbox flex__direction-column">
                <div class="emotion__cells-swiper flex1" id="J__swiperEmotion">
                    <div class="swiper-container">
                        <div class="swiper-wrapper"></div>
                        <div class="pagination-emotion"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>