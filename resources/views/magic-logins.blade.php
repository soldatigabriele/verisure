@extends('layouts.app')

@section('header-style')
<style>
    .headers {
    }
    .card{
        margin-top: 20px;
    }
</style>
@endsection

@section('content')

<div id="app" class="container">

    <magic-logins-component url="{{env('APP_URL')}}/ml"></magic-logins-component>

</div>

@endsection
