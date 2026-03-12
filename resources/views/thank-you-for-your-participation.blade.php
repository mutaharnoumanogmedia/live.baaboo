<x-guest-layout>

    <style>
        :root {
            --purple: #2d1b6e;

            --teal: #29b6d8;
            --bg-lavender: #e8e0f5;
            --bg-peach: #f5e0d0;
            --social-bg: #d8d8e8;
            --dark-footer: #1a1a2e;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background: #f5f3fb;
            color: #1a1230;
        }

        /* ── HERO ── */
        .hero {
            background: linear-gradient(150deg, #ddd4f0 0%, #e8ddf5 35%, #f5e0d0 100%);
            padding: 60px 20px 80px;
            text-align: center;
        }

        .logo-placeholder {
            width: 110px;
            height: 110px;
            background: linear-gradient(135deg, #7b4fc4, #f4802a);
            border-radius: 22px;
            margin: 0 auto 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: #fff;
            font-weight: 900;
            box-shadow: 0 8px 32px rgba(123, 79, 196, 0.25);
        }

        .hero h1 {
            font-family: 'Poppins', sans-serif;
            font-size: clamp(2rem, 5vw, 3rem);
            font-weight: 900;
            color: var(--purple);
            line-height: 1.15;
            margin-bottom: 24px;
        }

        .hero h1 .accent {
            color: var(--orange);
        }

        .hero p {
            font-size: 1rem;
            color: #3a2860;
            line-height: 1.7;
            max-width: 580px;
            margin: 0 auto 10px;
        }

        .hero p strong {
            font-weight: 800;
        }

        .hero .hinweis {
            font-size: 0.88rem;
            color: #5a4880;
            margin-top: 18px;
        }

        .hero .hinweis strong {
            font-weight: 800;
        }

        /* ── TELEGRAM CARD ── */
        .telegram-section {
            background: #f7f5ff;
            padding: 50px 20px;
        }

        .telegram-card {
            background: #fff;
            border-radius: 20px;
            padding: 44px 36px;
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
            border: 1px solid #ede8f8;
        }

        .telegram-icon {
            width: 60px;
            height: 60px;
            background: var(--teal);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 1.7rem;
            color: #fff;
        }

        .telegram-card h2 {
            font-family: 'Poppins', sans-serif;
            font-weight: 900;
            font-size: clamp(1.5rem, 3vw, 1.9rem);
            color: var(--purple);
            margin-bottom: 14px;
        }

        .telegram-card h2 .accent {
            color: var(--teal);
        }

        .telegram-card p {
            font-size: 0.93rem;
            color: #5a4880;
            line-height: 1.65;
            margin-bottom: 20px;
        }

        .telegram-features {
            display: flex;
            justify-content: center;
            gap: 22px;
            flex-wrap: wrap;
            margin-bottom: 28px;
            color: #000 !important;
        }

        .telegram-features span {
            font-size: 0.88rem;
            font-weight: 700;

        }

        .telegram-features span i {
            margin-right: 5px;
        }

        .btn-telegram {
            background: var(--teal);
            color: #fff;
            font-weight: 800;
            font-size: 1rem;
            border: none;
            border-radius: 50px;
            padding: 14px 36px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s;
        }

        .btn-telegram:hover {
            background: #1fa0be;
            color: #fff;
        }

        /* ── SOCIAL MEDIA ── */
        .social-section {
            background: var(--social-bg);
            padding: 0;
        }

        .social-inner {
            display: flex;
            align-items: stretch;
            max-width: 100%;
        }

        .social-label {
            background: var(--social-bg);
            padding: 26px 36px;
            font-size: 0.82rem;
            font-weight: 900;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--purple);
            display: flex;
            align-items: center;
            border-right: 1px solid #c8c0de;
            min-width: 220px;
        }

        .social-icons {
            display: flex;
            flex: 1;
        }

        .social-icon-item {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 26px;
            border-right: 1px solid #c8c0de;
            color: var(--purple);
            font-size: 1.7rem;
            cursor: pointer;
            transition: background 0.15s, color 0.15s;
            text-decoration: none;
        }

        .social-icon-item:last-child {
            border-right: none;
        }

        .social-icon-item:hover {
            background: #c0b8d8;
            color: var(--purple);
        }

        /* ── FOOTER ── */
        .footer-section {
            background: var(--dark-footer);
            padding: 28px 20px;
        }

        .footer-left h3 {
            font-family: 'Poppins', sans-serif;
            font-weight: 900;
            font-size: 1.3rem;
            color: #fff;
            margin-bottom: 4px;
        }

        .footer-left h3 .accent {
            color: var(--orange);
        }

        .email-label {
            font-size: 0.8rem;
            font-weight: 700;
            color: #aaa;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }

        .email-input-row {
            display: flex;
            gap: 0;
        }

        .email-input-row input {
            flex: 1;
            padding: 10px 16px;
            border: none;
            border-radius: 8px 0 0 8px;
            font-size: 0.9rem;
            outline: none;
            font-family: 'Nunito', sans-serif;
        }

        .email-input-row button {
            background: var(--orange);
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 0 8px 8px 0;
            font-weight: 800;
            font-size: 0.9rem;
            cursor: pointer;
            white-space: nowrap;
            transition: background 0.2s;
        }

        .email-input-row button:hover {
            background: #d96b18;
        }

        @media (max-width: 767px) {
            .social-inner {
                flex-direction: column;
            }

            .social-label {
                border-right: none;
                border-bottom: 1px solid #c8c0de;
                min-width: unset;
                text-align: center;
                justify-content: center;
            }

            .social-icons {
                justify-content: center;
            }
        }
    </style>


    <!-- ══════════════════════════════════════════
         HERO – DANKE
    ══════════════════════════════════════════ -->
    <section class="hero">
        <div class="text-center row justify-content-center">
            <div class="">
                <a href="{{ route('index') }}">
                    <img src="{{ asset('images/badabing-logo.webp') }}" alt="BadaBing Game Show" width="200"  
                        class="img-fluid hero-logo mb-3 mx-auto">
                </a>
            </div>
        </div>

        <h1>
            Deine <span class="accent">Anmeldung</span> war<br>erfolgreich!
        </h1>

        <p>
            Danke für Deine Anmeldung zur <strong>Badabing Game Show</strong> – wir freuen uns riesig, dass Du dabei
            bist!<br>
            In den nächsten Minuten erhältst Du eine <strong>Bestätigungs-Mail mit allen wichtigen Informationen zur
                Show.</strong>
        </p>
        <div class="alert  fs-4 my-4" style="max-width: 600px; margin: 0 auto; background:var(--bg-lavender); border:1px solid var(--purple); border-radius: 10px; padding: 10px;">
            Dein Empfehlungslink:
            <a id="referral-link" href="{{ $user->referral_link }}" target="_blank">{{ $user->referral_link }}</a>
            <button id="copy-referral-link-btn" class="btn btn-link btn-sm    " style="vertical-align: baseline;"
                onclick="copyReferralLink()">
                <i class="bi bi-clipboard"></i>
            </button>
            <script>
                function copyReferralLink() {
                    const link = document.getElementById('referral-link').innerText;
                    navigator.clipboard.writeText(link).then(function() {
                        const btn = document.getElementById('copy-referral-link-btn');
                        btn.innerHTML = '<i class="bi bi-clipboard-check text-success"></i>';
                        setTimeout(() => {
                            btn.innerHTML = '<i class="bi bi-clipboard"></i>';
                        }, 1300);
                    });
                }
            </script>
        </div>

        <p class="hinweis">
            <strong>Hinweis:</strong> Falls Du keine E-Mail findest, schau bitte auch in Deinem <strong>Spam- oder
                Werbeordner</strong> nach.
        </p>
    </section>

    <!-- ══════════════════════════════════════════
         TELEGRAM CARD
    ══════════════════════════════════════════ -->
    <section class="telegram-section">
        <div class="telegram-card">
            <div class="telegram-icon">
                <img src="{{ asset('images/telegramlogo.webp') }}" alt="Telegram" class="img-fluid">
            </div>
            <h2>Direkt auf Dein <span class="accent">Handy.</span></h2>
            <p>
                Erhalte Show-Termine, exklusive Vorab-Infos und Gewinn-Benachrichtigungen<br>
                direkt per Telegram - schneller als jeder Newsletter.
            </p>
            <div class="telegram-features">
                <span><i class="bi bi-check2 text-orange"></i>Show-Reminder</span>
                <span><i class="bi bi-check2 text-orange"></i>Exklusive Vorab-Infos</span>
                <span><i class="bi bi-check2 text-orange"></i>Gewinn-Alerts</span>
            </div>
            <a href="https://t.me/badabingQuiz" target="_blank" class="btn-telegram">
                <i class="bi bi-telegram"></i>
                Jetzt Telegram-Kanal abonnieren
            </a>
        </div>
    </section>

    <!-- ══════════════════════════════════════════
         SOCIAL MEDIA BAR
    ══════════════════════════════════════════ -->
    <section class="social-section">
        <div class="social-inner">
            <div class="social-label">FOLGE UNS AUCH AUF<br>SOCIAL MEDIA</div>
            <div class="social-icons">
                <a href="https://www.instagram.com/badabing.show/" target="_blank" class="social-icon-item"><i
                        class="bi bi-instagram"></i></a>
                <a href="https://www.tiktok.com/@badabingshow" target="_blank" class="social-icon-item"><i
                        class="bi bi-tiktok"></i></a>
                <a href="https://www.facebook.com/profile.php?id=61585035375167" target="_blank"
                    class="social-icon-item"><i class="bi bi-facebook"></i></a>
            </div>
        </div>
    </section>



</x-guest-layout>
