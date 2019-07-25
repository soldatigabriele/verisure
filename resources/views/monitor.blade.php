@extends('layouts.app')

@section('header-style')
<style>
    .headers {
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        
        <div class="card col-12" style="margin-bottom:20px;">
            <div class="card-body">
                <a href="{{route('requests')}}">Requests list</a>
            </div>
        </div>

        <div class="card col-12">
            <div class="card-body">
                <a href="{{route('responses')}}">Responses list</a>
            </div>
        </div>


    </div>
</div>
@endsection
