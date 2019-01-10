{{-- 订单页面--}}
@extends('layout.goods')
@section('title') {{$title}}    @endsection
@section('content')
    <table class="table table-hover">
        <h2>订单页面</h2>
        <tr class="success">
            <td>ID</td>
            <td>订单编号</td>
            <td>价格</td>
            <td>时间</td>
            <td>操作</td>
        </tr>
        @foreach($list as $v)
            <tr class="info">
                <td>{{$v->o_id}}</td>
                <td>{{$v->o_name}}</td>
                <td>￥{{$v->o_amount / 100}}元</td>
                <td>{{date("Y-m-d H:i:s",$v->o_ctime)}}</td>
                <td><li class="btn"><a href="">删除订单</a></li></td>
            </tr>
        @endforeach
    </table>
@endsection