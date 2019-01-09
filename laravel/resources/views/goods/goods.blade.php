{{-- 购物车页面--}}
@extends('layout.goods')
@section('title') {{$title}}    @endsection
@section('content')
    <table class="table table-hover">
        <h2>商品页面</h2>
        <tr>
            <td>ID</td>
            <td>名称</td>
            <td>数量</td>
            <td>时间</td>
            <td>操作</td>
        </tr>
        @foreach($data as $k)
        <tr>
            <td>{{$k->goods_id}}</td>
            <td>{{$k->goods_name}}</td>
            <td>{{$k->goods_store}}</td>
            <td>{{date("Y-m-d H:i:s",$k->goods_ctime)}}</td>
            <td><li class="btn"><a href="/goodsDetails/{{$k['goods_id']}}">加入购物车</a></li></td>
        </tr>
        @endforeach
    </table>
@endsection