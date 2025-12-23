@extends('emails.template')
@section('content')
    <h1>Congratulations {{ $user->name }}!</h1>

    <p>
        You have won <strong>{{ $prizeWon }}</strong> in our live show.
    </p>
    <div>
        <strong>Your Details:</strong><br>
        Name: {{ $user->name }}<br>
        Email: {{ $user->email }}<br>
    </div>
    <div>
    <table style="border-collapse: collapse; width: 100%;">
        <tr>
            <td><strong>Show Title:</strong></td>
            <td>{{ $liveShow->title }}</td>
        </tr>
        <tr>
            <td><strong>Description:</strong></td>
            <td>{{ $liveShow->description }}</td>
        </tr>
        <tr>
            <td><strong>Scheduled At:</strong></td>
            <td>{{ $liveShow->scheduled_at->format('F j, Y, g:i a') }}</td>
        </tr>
        <tr>
            <td><strong>Status:</strong></td>
            <td>{{ $liveShow->status }}</td>
        </tr>
        <tr>
            <td><strong>Host Name:</strong></td>
            <td>{{ $liveShow->host_name }}</td>
        </tr>
    </table>
    </div>
    <div>
        <strong>Next Steps:</strong><br>
        Our team will contact you shortly with more information on how to claim your prize.
    </div>

    <p>
        Thank you for participating!
    </p>
@endsection
