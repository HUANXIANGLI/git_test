{{-- 购物车页面--}}
@extends('layout.goods')
@section('title') {{$title}}    @endsection
@section('content')
    <div class="container">
        <form action="/upload" method="post" enctype="multipart/form-data">
            {{csrf_field()}}
            <input type="file" name="pdf">
            <input type="submit" value="UPLOAD">
        </form>
    </div>
@endsection