
@extends('layout.layout')
@section('content')
<form class="form-signin"  action="/index" method="post" >
    <h1>登录</h1>
    <table border="1">
        {{csrf_field()}}
        <tr>
            <td><label for="inputNickName">名称</label></td>
            <td><input type="text" name="u_name" id="inputNickName" placeholder="名称" required autofocus></td>
        </tr>
        <tr>
            <td><label for="inputPassword" >密码</label></td>
            <td><input type="password" name="u_pwd" id="inputPassword" placeholder="***" required></td>
        </tr>
        <tr>
            <td><button type="submit">登录</button></td>
            <td><a href="/update">修改密码</a></td>
        </tr>
    </table>
</form>
@endsection
