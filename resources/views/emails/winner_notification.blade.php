@extends('emails.template')
@section('content')
    <div style="text-align: center; margin-bottom: 32px;">
        <h1 style="color: #FC6902; font-size: 2.1rem; margin: 0 0 8px 0; font-family: 'Montserrat', Arial, sans-serif; letter-spacing: -1px;">
            🎉 Glückwunsch, {{ $user->name }}! 🎉
        </h1>
        <p style="font-size: 1.25rem; color: #5A10AC; margin: 0 0 20px 0;">
            Du hast <span style="color: #FC6902; font-weight:bold;">{{ $prizeWon }}</span> 
            bei der <span style="color:#FC6902; font-weight:bold;">Badabing Game Show</span> gewonnen!
        </p>
    </div>

    <div
        style="background: #f6f1ff; border-radius: 12px; box-shadow: 0 1px 8px 1px #ece9f5; padding: 20px 24px 8px 24px; margin: 0 auto 28px auto; max-width: 440px;">
        <div style="font-weight: bold; color: #5A10AC; margin-bottom: 10px; font-size: 1.1rem;">
            Deine Gewinner-Details
        </div>
        <table style="border-collapse: collapse; width: 100%; background: none;">
            <tr>
                <td style="padding: 8px 0; color: #9E136D; width: 42%"><strong>Name:</strong></td>
                <td style="padding: 8px 0; color: #3d4852;">{{ $user->name }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #9E136D;"><strong>E-Mail:</strong></td>
                <td style="padding: 8px 0; color: #3d4852;">{{ $user->email }}</td>
            </tr>
        </table>
    </div>

    <div style="background: #faf0e5; border-radius: 12px; box-shadow: 0 1px 8px 1px #f8ede6; padding: 20px 24px 8px 24px; margin: 0 auto 28px auto; max-width: 440px;">
        <div style="font-weight: bold; color: #FC6902; margin-bottom: 10px; font-size: 1.1rem;">
            Infos zur Show
        </div>
        <table style="border-collapse: collapse; width: 100%; background: none;">
            <tr>
                <td style="padding: 8px 0; color: #FC6902; width: 42%"><strong>Titel:</strong></td>
                <td style="padding: 8px 0; color: #3d4852;">{{ $liveShow->title }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #FC6902;"><strong>Beschreibung:</strong></td>
                <td style="padding: 8px 0; color: #3d4852;">{{ $liveShow->description }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #FC6902;"><strong>Datum & Uhrzeit:</strong></td>
                <td style="padding: 8px 0; color: #3d4852;">
                    {{ $liveShow->scheduled_at->format('d.m.Y, H:i') }}
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #FC6902;"><strong>Status:</strong></td>
                <td style="padding: 8px 0; color: #3d4852;">{{ ucfirst($liveShow->status) }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #FC6902;"><strong>Moderator:</strong></td>
                <td style="padding: 8px 0; color: #3d4852;">{{ $liveShow->host_name }}</td>
            </tr>
        </table>
    </div>

    <div style="background: #e8fcf1; border-radius: 10px; padding: 14px 20px; margin-bottom: 24px; max-width: 430px; margin:auto;">
        <span style="color: #14A57C; font-weight: bold;">Nächste Schritte:</span>
        <p style="color: #3d4852; margin: 8px 0 0 0;">
            Unser Team wird dich in Kürze kontaktieren, um dir alle Infos zur Gewinnübergabe zu senden.
            Bitte behalte deine E-Mails im Blick – dein Preis wartet auf dich!
        </p>
    </div>

    <p style="color: #5A10AC; margin-top: 24px; font-weight: bold; font-size: 1.08rem; text-align: center;">
        Danke für deine Teilnahme und viel Spaß mit deinem Gewinn!<br>
        Dein <span style="color:#FC6902;">Badabing Game Show</span> Team
    </p>
@endsection
