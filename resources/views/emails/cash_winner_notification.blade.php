@extends('emails.template')
@section('content')
    <div style="text-align: center; margin-bottom: 32px;">
        <h1
            style="color: #FC6902; font-size: 2.1rem; margin: 0 0 8px 0; font-family: 'Montserrat', Arial, sans-serif; letter-spacing: -1px;">
            Herzlichen Glückwunsch 🎉
        </h1>
        <p style="font-size: 1.25rem; color: #5A10AC; margin: 0 0 20px 0;">
            Hallo {{ $user->name }},<br>
            herzlichen Glückwunsch! Du hast bei der Badabing Show am
            {{ $liveShow->start_time ? $liveShow->start_time->format('d.m.Y') : $liveShow->scheduled_at?->format('d.m.Y') }}
            einen Gewinn in Höhe von <span style="color:#FC6902; font-weight:bold;">{{ $amount }}</span> erzielt 🎉.
        </p>
    </div>

    <div
        style="background: #f6f1ff; border-radius: 12px; box-shadow: 0 1px 8px 1px #ece9f5; padding: 20px 24px 8px 24px; margin: 0 auto 28px auto; max-width: 440px;">
        <p>
            Wir hoffen sehr, dass du Spaß bei der Show hattest.
        </p>
        <p>
            Damit wir dir deinen Gewinn auszahlen können, sende uns bitte noch folgende Angaben zu:
        </p>
        <ul style="margin: 0 0 12px 0; padding-left: 20px; color: #5A10AC;">
            <li>deinen vollständigen Namen</li>
            <li>deine vollständige Adresse</li>
            <li>deine IBAN</li>
        </ul>
        <p>
            Bitte beachte, dass wir die Überweisung nur vornehmen können, wenn uns alle Informationen vollständig
            vorliegen.
        </p>
        <p>
            Im Anschluss an die Überweisung erhältst du von uns einen Auszahlungsbeleg. Mit diesem bestätigst du, dass du
            das Geld von uns erhalten hast.
        </p>
    </div>

    <div>
        Wir wünschen dir viel Spaß mit deinem Gewinn und freuen uns auf das nächste Mal.<br>
        <br>
        Viele liebe Grüße,<br>
        Dilara<br>
        Badabing-Team
    </div>
@endsection
