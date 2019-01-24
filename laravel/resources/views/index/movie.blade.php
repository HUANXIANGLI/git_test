@extends('layout.layout')
@section('content')
    <h1>Movie</h1>
    @foreach($seat as $k=>$v)
        @if($v==1)
            <a href="/buy/{{$k}}" class="btn-default btn-danger">座位{{$k+1}}</a>
        @else
            <a href="/buy/{{$k}}" class="btn-default btn-info">座位{{$k+1}}</a>
        @endif
    @endforeach

@endsection

@section('footer')
@endsection


