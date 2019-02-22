<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>微信素材添加页面</title>
</head>
<body>
<form action="{{url('/admin/weixin/sendmsg')}}" method="post">
    {{csrf_field()}}
    <h1>微信群发消息页面</h1>
    <table border="1">
        <tr>
            <td><h2>发送内容</h2></td>
            <td><input type="text" name="msg"></td>
        </tr>
        <tr>
            <td></td>
            <td><input type="submit" value="发送"></td>
        </tr>
    </table>
</form>
</body>
</html>