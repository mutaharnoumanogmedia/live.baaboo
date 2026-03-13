@extends('emails.template')
@section('content')
    <h1
        style="color: #5A10AC; font-size: 2.1rem; margin-bottom: 12px; margin-top: 0; font-family: 'Montserrat', Arial, sans-serif; letter-spacing: -1px;">
        Willkommen, {{ $user->name }}!</h1>

    <p style="color: #3d4852; font-size: 1.2rem; margin-bottom: 24px;">
        Schön, dass du bei der <span style="color: #FC6902; font-weight: bold;">Badabing Game Show</span> dabei bist! 🎉<br>
        Hier findest du deine persönlichen Daten und wichtige Links für deine Teilnahme.
    </p>

    <div
        style="background: #f0e8fc; border-radius: 12px; padding: 20px 24px 1px 24px; margin-bottom: 32px; box-shadow: 0 2px 10px 0 #ece9f5; max-width: 450px; margin-left: auto; margin-right: auto;">
        <div style="font-weight: bold; color: #5A10AC; margin-bottom: 10px; font-size: 1.07rem;">Deine Anmeldedaten</div>
        <table style="border-collapse: collapse; width: 100%; background: none;">
            <tr>
                <td style="padding: 10px 0; color: #5A10AC;"><strong>Name:</strong></td>
                <td style="padding: 10px 0; color: #3d4852;">{{ $user->name }}</td>
            </tr>
            <tr>
                <td style="padding: 10px 0; color: #5A10AC;"><strong>E-Mail:</strong></td>
                <td style="padding: 10px 0; color: #3d4852;">{{ $user->email }}</td>
            </tr>
            <tr>
                <td style="padding: 10px 0; color: #5A10AC;"><strong>Username:</strong></td>
                <td style="padding: 10px 0; color: #3d4852;">{{ $user->user_name }}</td>
            </tr>
        </table>
    </div>

    <div style="margin: 32px 0;">
        <div style="font-weight: bold; color: #FC6902; font-size: 1.07rem; margin-bottom: 4px;">Dein Referral Link</div>
        <p style="margin: 6px 0 10px 0; color: #555;">
            Teile diesen Link mit deinen Freunden:
        </p>
        <div style="background: #f3eefb; border-radius: 8px; padding: 10px 12px;">
            <a href="{{ $user->referralLink() }}"
                style="color: #5A10AC; text-decoration: none; word-break: break-all;">{{ $user->referralLink() }}</a>
        </div>
    </div>

    <div style="margin: 32px 0;">
        <div style="font-weight: bold; color: #9E136D; font-size: 1.07rem; margin-bottom: 4px;">Dein Magic Link <span
                style="font-weight: normal; color: #6e6e6e;">(Ein-Klick-Login)</span></div>
        <p style="margin: 6px 0 10px 0; color: #555;">
            Mit diesem Link loggst du dich direkt ein und bist sofort live dabei:
        </p>
        <div style="background: #f3eefb; border-radius: 8px; padding: 10px 12px;">
            <a href="{{ $user->magicLink() }}"
                style="color: #9E136D; text-decoration: none; word-break: break-all;">{{ $user->magicLink() }}</a>
        </div>
    </div>

    <div style="background: #f0e8fc; border-radius: 10px; padding: 16px 22px; margin-bottom: 26px;">
        <strong style="color: #FC6902;">Hinweis:</strong>
        <span style="color: #3d4852;">
            Bewahre diese E-Mail sicher auf, damit du jederzeit auf deinen Referral Link und Magic Link zugreifen kannst.
        </span>
    </div>

    <p>
        Erhalte Show-Termine, exklusive Vorab-Infos und Gewinn-Benachrichtigungen
        direkt per Telegram - schneller als jeder Newsletter.
        <a href="https://t.me/badabingQuiz" target="_blank">https://t.me/badabingQuiz</a>
    </p>

    <p style="color: #5A10AC; margin-top: 0; font-weight: bold; font-size: 1.07rem;">
        Danke fürs Anmelden und willkommen in der Show!
    </p>
@endsection
