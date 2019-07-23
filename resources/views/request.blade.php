@extends('layouts.app')

@section('content')
        
<div class="container">
    <div class="card">
    <h5 class="card-header"><i class="fas fa-angle-double-up"></i>  Request {{ $request->id }}</h5>
    <div class="card-body">
        <div class="row">
            <div class="col-2">Id</div>
            <div class="col-10">{{ $request->id }}</div>
        </div>
        <hr>
        <div class="row">
            <div class="col-2">Method</div>
            <div class="col-10">{{ $request->method }}</div>
        </div>
        <hr>
        <div class="row">
            <div class="col-2">Uri</div>
            <div class="col-10">
                {{ str_replace('https://customers.verisure.co.uk/', '', $request->uri) }}
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-2">Headers</div>
            <div class="col-10">
                    @foreach($request->headers as $key => $val)
                        {{$key}}: {{ json_encode($val) }}
                        <br>
                    @endforeach
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-2">Body</div>
            <div class="col-10">{{$request->body}}</div>
        </div>
        <hr>
        <div class="row">
            <div class="col-2">Created at</div>
            <div class="col-10">
                {{$request->created_at->format('d/m/Y H:i:s')}}
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-2">Updated at</div>
            <div class="col-10">
                {{$request->updated_at->format('d/m/Y H:i:s')}}
            </div>
        </div>
    </div>
    </div>

</div>

@endsection
