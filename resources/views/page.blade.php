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

    <example-component></example-component>

    <div class="card">
        <div class="card-header">test</div>
        <div class="card-body">
            <pre>
                alarm:
                    - enable
                    - disable

                garage:
                    - enable
                    - disable

                schedule:
                    - activation
                    - deactivation

                keep session alive:
                    - enabled
                    - ttl

                censure responses

                api auth active
                api auth token:
                    - generate and set new random token

                notification:
                    - enabled
                    - channel

                status jobs max calls: 5

                status jobs sleep: 3

                invalidate session (logout)
            </pre>
        </div>
    </div>


</div>

@endsection
