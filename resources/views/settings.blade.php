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

    <settings-component></settings-component>

</div>

@endsection
