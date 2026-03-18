@extends('emails.template')
@section('content')
    <div style="text-align: center; margin-bottom: 32px;">
        <h1
            style="color: #FC6902; font-size: 2.1rem; margin: 0 0 8px 0; font-family: 'Montserrat', Arial, sans-serif; letter-spacing: -1px;">
            🎉 Hallo, {{ $user->name }}! 🎉
        </h1>
        <p style="font-size: 1.25rem; color: #5A10AC; margin: 0 0 20px 0;">
            Du hast <span style="color: #FC6902; font-weight:bold;">"{{ $prizeWon != 'n/a' ? $prizeWon : '' }}"</span>
            bei der <span style="color:#FC6902; font-weight:bold;">Badabing Game Show</span> gewonnen!
        </p>
    </div>

    <div
        style="background: #f6f1ff; border-radius: 12px; box-shadow: 0 1px 8px 1px #ece9f5; padding: 20px 24px 8px 24px; margin: 0 auto 28px auto; max-width: 440px;">
        <div style="font-weight: bold; color: #5A10AC; margin-bottom: 10px; font-size: 1.1rem;">
            Herzlichen Glückwunsch, du hast bei der Badabing Game Show gewonnen!
            Dein Preis:
            {{ $prizeWon }}
        </div>
        <p>
            Wir melden uns in Kürze mit allen weiteren Details bei dir.
        </p>

        <p>
            📸 Zeig's der Welt:<br>
            Du hast bei der Badabing-Quizshow gewonnen! Das muss doch jemand erfahren, oder?
            Mach einen Screenshot von dieser Mail und poste ihn in deiner Story bei Instagram!
            Vergiss nicht <a href="https://www.instagram.com/badabing.show/" target="_blank">@badabing.show</a> zu taggen!

        </p>

    </div>

    <div>
        Viele Grüße<br>
        Euer Badabing-Team

    </div>
@endsection
