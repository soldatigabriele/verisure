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
            <h5 class="card-header"><i class="fas fa-angle-double-up"></i>  Requests </h5>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Type</th>
                            <th>Method</th>
                            <th>Uri</th>
                            <th>Body</th>
                            <th>Date</th>
                            <th>Response</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $request)
                        <tr>
                            <td>
                                <a href="{{ route('request', $request->id) }}">
                                    {{ $request->id }}
                                </a>
                            </td>
                            <td>
                                {{ \Illuminate\Support\Str::title(optional($request->response)->request_type) }}
                            </td>
                            <td>
                                {{ $request->method }}
                            </td>
                            <td>
                                {{ str_replace('https://customers.verisure.co.uk/', '', $request->uri) }}
                            </td>
                            <td>
                                {{ \Illuminate\Support\Str::limit($request->body, 40) }}
                            </td>
                            <td>
                                {{ $request->created_at->format('d/m/Y H:i:s') }}
                                ({{ $request->created_at->diffForHumans() }})
                            </td>
                            <td>
                                <a href="{{route('response', ['response' => optional($request->response)->id] )}}">{{ optional($request->response)->status }}</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>                
                {{ $requests->links() }}
            </div>
        </div>

    </div>
@endsection
