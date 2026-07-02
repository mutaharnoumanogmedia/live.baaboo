@extends('emails.template')
@section('content')
    <div style="text-align: center; margin-bottom: 32px;">
        <h1
            style="color: #FC6902; font-size: 2.1rem; margin: 0 0 8px 0; font-family: 'Montserrat', Arial, sans-serif; letter-spacing: -1px;">
            Hallo {{ $user->user->name }},
        </h1>
        <p style="font-size: 1.25rem; color: #5A10AC; margin: 0 0 20px 0;">
            herzlichen Glückwunsch! 🎉 Du hast bei der Badabing Gameshow
            {{ $user->liveShow->start_time ? 'am' . $user->liveShow->start_time->format('d.m.Y') : 'N/A' }} einen
            {{ (int) $user->winnerPrize->voucher_amount }}€ baaboo Gutschein gewonnen 🥳
        </p>
    </div>

    <div
        style="background: #f6f1ff; border-radius: 12px; box-shadow: 0 1px 8px 1px #ece9f5; padding: 20px 24px 8px 24px; margin: 0 auto 28px auto; max-width: 440px;">
        <div style="font-weight: bold; color: #5A10AC; margin-bottom: 10px; font-size: 1.1rem;">
            Hier ist dein persönlicher Gutscheincode:<br>
            👉 {{ $user->discount_code }}
        </div>
        <p>
            Du kannst deinen Gutschein ganz einfach hier einlösen:<br>
            🌐 <a href="https://baaboo.com/" target="_blank">https://baaboo.com/</a>
        </p>

        <p>
            Wichtige Infos:<br>
            ✨ Der Gutschein ist 1 Monat gültig<br>
            ✨ Keine Barauszahlung möglich
        </p>

    </div>

    <div>
        Viel Spaß beim Shoppen! 🛍️<br>
        Liebe Grüße<br>
        <br>
        Dein Badabing Team

    </div>
@endsection
