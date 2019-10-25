@extends('layouts.app')

@section('header-style')
<style>
    .card {
        margin-bottom: 20px;
    }
    .buttons{
        margin: 5px;
    }
</style>
@endsection

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-6">
            <actions-component auth="{{auth()->user()->isAdmin()}}"></actions-component>
        </div>
        <div class="col-md-6">
            @if(auth()->user()->isAdmin())
            <monitor-component></monitor-component>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Links</div>
                    <div class="card-body">
                            <a class="btn btn-md btn-outline-secondary buttons" href="{{route('requests')}}">Requests</a>
                            <a class="btn btn-md btn-outline-secondary buttons" href="{{route('responses')}}">Responses</a>
                            <a class="btn btn-md btn-outline-secondary buttons" href="{{route('settings')}}">Settings</a>
                            <a class="btn btn-md btn-outline-secondary buttons" href="{{route('magic-logins')}}">Magic Logins</a>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
     @if(auth()->user()->isAdmin())
    <div class="clearfix"></div>
    <div class="row responses">
        <responses-component limit="4" hide="queued,working,jobs,login,logout" home="true" responses_link="{{route('responses')}}"></responses-component>
    </div>
    @endif
</div>

@endsection
