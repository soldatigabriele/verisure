@extends('layouts.app')

@section('header-style')
<style>
    .headers {
    }
</style>
@endsection

@section('content')
    <div class="row justify-content-center">
        
        <div class="card" style="margin:0px 20px;">
            <h5 class="card-header"><i class="fas fa-angle-double-down"></i> Responses</h5>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Type</th>
                            <th>Request</th>
                            <th>Status</th>
                            <th>Body</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($responses as $response)
                        <tr>
                            <td>
                                <a href="{{ route('response', $response->id) }}">
                                    {{ $response->id }}
                                </a>
                            </td>
                            <td>
                                {{ \Illuminate\Support\Str::title($response->request_type) }}
                            </td>
                            <td>
                                <a href="{{ route('request', $response->request->id) }}">{{ $response->request->id }}</a>
                            </td>
                            <td>
                                {{ $response->status }}
                            </td>
                            <td>
                                {{ \Illuminate\Support\Str::limit($response->body, 40) }}
                            </td>
                            <td>
                                {{ $response->created_at->format('d/m/Y H:i:s') }}
                                ({{ $response->created_at->diffForHumans() }})
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>                
            </div>
        </div>

    </div>
@endsection
