@extends('emails.template')
@section('content')
    <div style="text-align: center; margin-bottom: 32px;">
        <h1
            style="color: #FC6902; font-size: 2.1rem; margin: 0 0 8px 0; font-family: 'Montserrat', Arial, sans-serif; letter-spacing: -1px;">
            🎁 Herzlichen Glückwunsch, {{ $user->name }}!
        </h1>
        <p style="font-size: 1.25rem; color: #5A10AC; margin: 0 0 20px 0;">
            Du hast im <span style="color: #FC6902; font-weight:bold;">Special Quiz</span> der Badabing Game Show
            <span style="color:#FC6902; font-weight:bold;">"{{ $prizeWon != 'n/a' ? $prizeWon : '' }}"</span> gewonnen!
        </p>
    </div>

    <div
        style="background: #f6f1ff; border-radius: 12px; box-shadow: 0 1px 8px 1px #ece9f5; padding: 20px 24px 8px 24px; margin: 0 auto 28px auto; max-width: 440px;">
        <div style="font-weight: bold; color: #5A10AC; margin-bottom: 10px; font-size: 1.1rem;">
            Dein Gewinn: {{ $prizeWon }}
        </div>
        <p>
            Wir melden uns in Kürze bei dir, um die Übergabe deines Gewinns zu organisieren.
            Bitte antworte auf diese E-Mail mit deinem vollständigen Namen und deiner Adresse.
        </p>
    </div>

    <div>
        Viele Grüße<br>
        Euer Badabing-Team
    </div>
@endsection
