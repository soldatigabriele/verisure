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
        <div class="col-12" style="margin:20px;">
            <div class="card">
                <div class="card-header">Monitor</div>
                    <div class="card-body">
                        <a href="{{route('requests')}}">Requests list</a>
                        <br>
                        <a href="{{route('responses')}}">Responses list</a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
