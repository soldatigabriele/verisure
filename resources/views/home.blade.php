@extends('layouts.app')

@section('header-style')
<style>
    .card {
        margin-bottom: 20px;
    }
</style>
@endsection

@section('content')

<div class="container">
    @if(auth()->user()->isAdmin())
    <div class="row">
        <responses-component limit="4" hide="queued,working,jobs,login,logout" home="true" responses_link="{{route('responses')}}"></responses-component>
    </div>
    @endif
    <div class="row">
        <actions-component auth="{{auth()->user()->isAdmin()}}"></actions-component>
        @if(auth()->user()->isAdmin())
        <monitor-component></monitor-component>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Links</div>
                <div class="card-body">
                    <a href="{{route('requests')}}">Requests list</a>
                    <br>
                    <a href="{{route('responses')}}">Responses list</a>
                    <br>
                    <a href="{{route('settings')}}">Settings</a>
                    <br>
                    <a href="{{route('magic-logins')}}">Magic Logins</a>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@endsection
