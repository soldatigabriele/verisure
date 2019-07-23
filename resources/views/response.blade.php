@extends('layouts.app')

@section('content')
        
<table class="table">
    <thead>
        <tr>
            <th>#</th>
            <th>Status</th>
            <th>Request</th>
            <th>Headers</th>
            <th>Body</th>
            <th>Created at</th>
            <th>Updated at</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                {{ $response->id }}
            </td>
            <td>
                {{ \Illuminate\Support\Str::title($response->request_type) }}
            </td>
            <td>
                {{ $response->status }}
            </td>
            <td>
                @foreach($response->headers as $key => $val)
                    {{$key}}: {{ json_encode($val) }}
                    <br>
                @endforeach
            </td>
            <td>
                {{ $response->body}}
            </td>
            <td>
                {{$response->created_at->format('d/m/Y H:i:s')}}
            </td>
            <td>
                {{$response->updated_at->format('d/m/Y H:i:s')}}
            </td>
        </tr>
    </tbody>
</table>                

@endsection
