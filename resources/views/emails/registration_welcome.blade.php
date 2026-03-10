@extends('emails.template')
@section('content')
    <h1>Welcome, {{ $user->name }}!</h1>

    <p>Thank you for registering. Here are your account details and important links.</p>

    <div style="margin: 24px 0;">
        <strong>Your registration details</strong>
        <table style="border-collapse: collapse; width: 100%; margin-top: 8px;">
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #e8e5ef;"><strong>Name:</strong></td>
                <td style="padding: 8px 0; border-bottom: 1px solid #e8e5ef;">{{ $user->name }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #e8e5ef;"><strong>Email:</strong></td>
                <td style="padding: 8px 0; border-bottom: 1px solid #e8e5ef;">{{ $user->email }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #e8e5ef;"><strong>Username:</strong></td>
                <td style="padding: 8px 0; border-bottom: 1px solid #e8e5ef;">{{ $user->user_name }}</td>
            </tr>
        </table>
    </div>

    <div style="margin: 24px 0;">
        <strong>Your referral link</strong>
        <p style="margin: 8px 0;">Share this link so others can register and you can earn rewards:</p>
        <p style="margin: 8px 0; word-break: break-all;">
            <a href="{{ $user->referralLink() }}" style="color: #3d4852;">{{ $user->referralLink() }}</a>
        </p>
    </div>

    <div style="margin: 24px 0;">
        <strong>Your magic link (one-click login)</strong>
        <p style="margin: 8px 0;">Use this link to sign in directly and join live shows:</p>
        <p style="margin: 8px 0; word-break: break-all;">
            <a href="{{ $user->magicLink() }}" style="color: #3d4852;">{{ $user->magicLink() }}</a>
        </p>
    </div>

    <p>Keep this email safe so you can access your referral link and magic link anytime.</p>

    <p>Thank you for joining!</p>
@endsection
