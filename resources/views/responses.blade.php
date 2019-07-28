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
                            <th>Request</th>
                            <th>Status</th>
                            <th>Body</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $badges = [
                                'completed' => 'badge-success',
                                'working' => 'badge-light',
                                'queued' => 'badge-light',
                                'failed' => 'badge-danger',
                            ];
                        @endphp
                    
                        @foreach($responses as $response)
                        <tr>
                            <td>
                                <a href="{{ route('response', $response->id) }}">
                                    {{ $response->id }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('request', $response->request->id) }}">{{ \Illuminate\Support\Str::title($response->request_type) }}</a>
                            </td>
                            <td>
                                {{ $response->status }}
                            </td>
                            <td>
                                @isset($response->body['job_id'])

                                    <span class="badge badge-info">
                                        requested 
                                    </span>
                                    <span class="badge badge-light">
                                        job_id: {{ $response->body['job_id'] }}
                                    </span>
                                @elseif (isset($response->body['status']))

                                    <span class="badge {{$badges[$response->body['status']] ?? 'badge-danger'}}">{{ $response->body['status'] }}</span>
                                    <span class="badge badge-light">
                                        @if($response->body['status'] == 'completed')
                                            {{ $response->body['message']['message'] }}
                                        @endif
                                        @if($response->body['status'] == 'failed')
                                            {{ $response->body['message'] }}
                                        @endif
                                    </span>
                                @else
                                    <span class="badge badge-light">
                                        {{ \Illuminate\Support\Str::limit(json_encode($response->body), 100) }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                {{ $response->created_at->diffForHumans() }} - 
                                {{ $response->created_at->format('d/m/Y H:i:s') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>                
                {{ $responses->links() }}
            </div>
        </div>

    </div>
@endsection
