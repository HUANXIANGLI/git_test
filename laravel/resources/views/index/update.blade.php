@extends('layout.layout')
@section('content')
    <form class="form-signin"  action="/update" method="post" >
        <h1>修改密码</h1>
        <table border="1">
            {{csrf_field()}}
            <tr>
                <td><label for="inputNickName">名称</label></td>
                <td><input type="text" name="u_name" id="inputNickName" placeholder="名称" required autofocus></td>
            </tr>
            <tr>
                <td>
                    <label for="inputPassword" >原密码</label></td>
                <td>
                    <input type="password" name="pwd" placeholder="***" required></td>
            </tr>
            <tr>
                <td>
                    <label for="inputPassword" >修改密码</label></td>
                <td>
                    <input type="password" name="pwd1" placeholder="***" required></td>
            </tr>
            <tr>
                <td>
                    <label for="inputPassword" >确认密码</label></td>
                <td>
                <input type="password" name="pwd2" placeholder="***" required></td>
            </tr>
            <tr>
                <td><button type="submit">修改密码</button></td>
                <td><a href="/index">登录页面</a></td>
            </tr>


        </table>
    </form>
@endsection
