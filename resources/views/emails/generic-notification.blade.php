@extends('emails.template')
@section('content')
    <h2>{{ $payload->title }}</h2>

    <p>{{ $payload->message }}</p>

    @if (isset($payload->data['url']))
        <p>
            <a href="{{ url($payload->data['url']) }}">
                View details
            </a>
        </p>
    @endif
@endsection
