/**
 * Created by li828428 on 2019.02.23.
 */
    // ...长按弹出菜单
$("#J__chatMsgList").on("longTap", "li .msg", function(e){
    var that = $(this), menuTpl, menuNode = $("<div class='wc__chatTapMenu animated anim-fadeIn'></div>");
    that.addClass("taped");
    that.parents("li").siblings().find(".msg").removeClass("taped");
    var isRevoke = that.parents("li").hasClass("me");
    var _revoke = isRevoke ? "<a href='#'><i class='ico i4'></i>撤回</a>" : "";

    if(that.hasClass("picture")){
        console.log("图片长按");
        menuTpl = "<div class='menu menu-picture'><a href='#'><i class='ico i1'></i>复制</a><a href='#'><i class='ico i2'></i>收藏</a><a href='#'><i class='ico i3'></i>另存为</a>"+ _revoke +"<a href='#'><i class='ico i5'></i>删除</a></div>";
    }else if(that.hasClass("video")){
        console.log("视频长按");
        menuTpl = "<div class='menu menu-video'><a href='#'><i class='ico i3'></i>另存为</a>" + _revoke +"<a href='#'><i class='ico i5'></i>删除</a></div>";
    }else{
        console.log("文字长按");
        menuTpl = "<div class='menu menu-text'><a href='#'><i class='ico i1'></i>复制</a><a href='#'><i class='ico i2'></i>收藏</a>" + _revoke +"<a href='#'><i class='ico i5'></i>删除</a></div>";
    }

    if(!$(".wc__chatTapMenu").length){
        $(".wc__chatMsg-panel").append(menuNode.html(menuTpl));
        autoPos();
    }else{
        $(".wc__chatTapMenu").hide().html(menuTpl).fadeIn(250);
        autoPos();
    }

    function autoPos(){
        console.log(that.position().top)
        var _other = that.parents("li").hasClass("others");
        $(".wc__chatTapMenu").css({
            position: "absolute",
            left: that.position().left + parseInt(that.css("marginLeft")) + (_other ? 0 : that.outerWidth() - $(".wc__chatTapMenu").outerWidth()),
            top: that.position().top - $(".wc__chatTapMenu").outerHeight() - 8
        });
    }
});

// ...表情、选择区切换
$(".wc__editor-panel").on("click", ".btn", function(){
    var that = $(this);
    $(".wc__choose-panel").show();
    if (that.hasClass("btn-emotion")) {
        $(".wc__choose-panel .wrap-emotion").show();
        $(".wc__choose-panel .wrap-choose").hide();
        // 初始化swiper表情
        !emotionSwiper && $("#J__emotionFootTab ul li.cur").trigger("click");
    } else if (that.hasClass("btn-choose")) {
        $(".wc__choose-panel .wrap-emotion").hide();
        $(".wc__choose-panel .wrap-choose").show();
    }
    wchat_ToBottom();
});

// ...处理编辑器信息
var $editor = $(".J__wcEditor"), _editor = $editor[0];
function surrounds(){
    setTimeout(function () { //chrome
        var sel = window.getSelection();
        var anchorNode = sel.anchorNode;
        if (!anchorNode) return;
        if (sel.anchorNode === _editor ||
            (sel.anchorNode.nodeType === 3 && sel.anchorNode.parentNode === _editor)) {

            var range = sel.getRangeAt(0);
            var p = document.createElement("p");
            range.surroundContents(p);
            range.selectNodeContents(p);
            range.insertNode(document.createElement("br")); //chrome
            sel.collapse(p, 0);

            (function clearBr() {
                var elems = [].slice.call(_editor.children);
                for (var i = 0, len = elems.length; i < len; i++) {
                    var el = elems[i];
                    if (el.tagName.toLowerCase() == "br") {
                        _editor.removeChild(el);
                    }
                }
                elems.length = 0;
            })();
        }
    }, 10);
}
// 格式化编辑器包含标签
_editor.addEventListener("click", function () {
    //$(".wc__choose-panel").hide();
}, true);
_editor.addEventListener("focus", function(){
    surrounds();
}, true);
_editor.addEventListener("input", function(){
    surrounds();
}, false);
// 点击表情
$("#J__swiperEmotion").on("click", ".face-list span img", function(){
    var that = $(this), range;

    if(that.hasClass("face")){ //小表情
        var img = that[0].cloneNode(true);
        _editor.focus();
        _editor.blur(); //输入表情时禁止输入法

        setTimeout(function(){
            if(document.selection && document.selection.createRange){
                document.selection.createRange().pasteHTML(img);
            }else if(window.getSelection && window.getSelection().getRangeAt){
                range = window.getSelection().getRangeAt(0);
                range.insertNode(img);
                range.collapse(false);

                var sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(range);
            }
        }, 10);
    }else if(that.hasClass("del")){ //删除
        _editor.focus();
        _editor.blur(); //输入表情时禁止输入法

        setTimeout(function(){
            range = window.getSelection().getRangeAt(0);
            range.collapse(false);

            var sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
            document.execCommand("delete");
        }, 10);
    }
});
var openid = $("#openid").val();

setInterval(function(){
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url     :   '/chat?openid=' + openid + '&pos=' + $("#msg_pos").val(),
        type    :   'get',
        dataType:   'json',
        success :   function(d){
            if(d.errno==0){     //服务器响应正常
                //数据填充
                if(d.data.type=='0'){
                    var msg_str="<p class='time' align='center'><span>"+d.data.ctime+
                        "</span></p><li class='others' style='width:2000px;height: 100px;' align='left'> <div><img style='width:50px;height:50px;' src='"+ d.res.headimgurl +
                        "' alt=''></div><div class='content'><p class='author'>" + d.res.nickname+
                        "</p><div class='msg'>" + d.data.text+
                        "</div></div></li>"
                }else{
                    var msg_str="<p class='time' align='center'><span>" +d.data.ctime+
                        "</span></p><li class='me'  style='height: 200px;' align='right'> <div><img style='width:50px;height:50px;' src='" +d.res.headimgurl+
                        "' alt=''></div> <div class='content'> <p class='author'>" + d.res.nickname+
                        "</p> <div class='msg'>" + d.data.text+
                        "</div></div></li>"
                }


                $("#J__chatMsgList").append(msg_str);
                $("#msg_pos").val(d.res.id)
            }else{

            }
        }
    });
},5000);

// 客服发送消息 begin
$("#send_msg_btn").click(function(e){
    e.preventDefault();
    var send_msg = $("#send_msg").val().trim();
    var msg_str = '<p style="color: mediumorchid"> >>>>> '+send_msg+'</p>';
    $("#J__chatMsgList").append(msg_str);
    $("#send_msg").val("");
});