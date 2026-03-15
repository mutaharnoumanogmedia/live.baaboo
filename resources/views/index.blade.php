<x-guest-layout>
    <!-- Live Show Banner -->
    {{-- @if (isset($currentLiveShow) && $currentLiveShow)
        <style>
            /* Non-Bootstrap classes used in this banner */
            .live-show-banner {
                background: linear-gradient(90deg, #fae7ff 0%, #e9e2fe 100%);
                border-radius: 32px;
                box-shadow: 0 4px 20px rgba(160, 130, 255, 0.08);
                margin-bottom: 36px;
                margin-top: 36px;
            }

            .live-badge {
                background: #ffd600;
                color: #5a189a;
                padding: 6px 18px;
                border-radius: 21px 6px 21px 21px;
                letter-spacing: 1.5px;
                box-shadow: 0 2px 8px rgba(255,167,38,0.06);
            }

            .live-dot {
                display: inline-block;
                width: 12px;
                height: 12px;
                border-radius: 50%;
                background: radial-gradient(ellipse at center, #ff3358 80%, #ff6347 100%);
                box-shadow: 0 0 8px 2px #ff335888;
                margin-right: 7px;
                vertical-align: middle;
            }

            .text-gradient {
                background: linear-gradient(90deg, #a100ff 0%, #ef008f 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                text-fill-color: transparent;
            }

            .b-highlight {
                color: #ff3366;
                font-weight: bold;
                background: #fff0f6;
                padding: 0.15em 0.6em;
                border-radius: 6px;
            }

            .border-purple {
                border: 2px solid #bb5ff5 !important;
            }

            .text-orange {
                color: #ff8800 !important;
            }
        </style>
        <div class="live-show-banner position-relative" style="overflow:visible;">
            <div class="text-center position-absolute w-100" style="top:-30px; left:0;">
                <span class="px-4 py-2 shadow badge bg-warning fs-5" style="border-radius:30px; letter-spacing:1px;">
                    ✨ It's Show Time! Lass dir den Spaß nicht entgehen! ✨
                </span>
            </div>
            <div class="container">
                <div class="py-3 row align-items-center" style="min-height:80px;">
                    <div class="mb-3 text-center col-md-2 text-md-start mb-md-0 d-flex align-items-center">
                        <span class="live-badge fs-6 fw-bolder d-inline-flex align-items-center">
                            <span class="live-dot me-2"></span>
                            @if (isset($currentLiveShow) && in_array($currentLiveShow->status, ['live', 'scheduled']))
                                <span class="d-none d-md-inline">
                                    {{ strtoupper($currentLiveShow->status) }}
                                    @if ($currentLiveShow->scheduled_at)
                                        &ndash;
                                        {{ \Carbon\Carbon::parse($currentLiveShow->scheduled_at)->format('d.m.Y H:i') }}
                                    @endif
                                </span>
                                <span class="d-inline d-md-none">
                                    {{ strtoupper(Str::limit($currentLiveShow->status, 4, '')) }}
                                    @if ($currentLiveShow->scheduled_at)
                                        &ndash;
                                        {{ \Carbon\Carbon::parse($currentLiveShow->scheduled_at)->format('d.m.Y H:i') }}
                                    @endif
                                </span>
                            @else
                                <span class="d-none d-md-inline">LIVE NOW</span>
                                <span class="d-inline d-md-none">LIVE</span>
                            @endif
                        </span>
                    </div>
                    <div class="mb-3 text-center col-md-7 text-md-start mb-md-0">
                        <h5 class="mb-1 fw-bold text-gradient" style="font-size:1.2rem;">
                            {{ $currentLiveShow->title ?? 'Live Show' }}
                            <span class="mx-2">·</span>

                        </h5>
                        <p class="mb-0 opacity-75 fs-6">
                            <i class="bi bi-people-fill text-purple"></i>
                            {{ $currentLiveShow->users->count() ?? 0 }}
                            {{ $currentLiveShow->users->count() == 1 ? 'Mitspieler ist' : 'Mitspieler sind' }} gerade
                            dabei
                        </p>
                    </div>
                    <div
                        class="text-center col-md-3 text-md-end d-flex justify-content-center justify-content-md-end align-items-center">
                        <a href="{{ route('live-show', $currentLiveShow->id) }}"
                            class="px-4 shadow-sm btn btn-light btn-lg fw-bold border-purple"
                            style="border-radius: 24px;">
                            <i class="fas fa-play me-2 text-orange"></i>Jetzt mitspielen
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif --}}

    <!-- ══════════════════════════════════════════
     HERO
══════════════════════════════════════════ -->
    <section class="hero-section">
        <div class="container">
            <!-- Logo placeholder -->
            <div class="text-center row justify-content-center">
                <div class="">
                    <a href="{{ route('index') }}">
                        <img src="{{ asset('images/badabing-logo.webp') }}" alt="Badabing Game Show" width="200"
                            class="mx-auto mb-3 img-fluid hero-logo">
                    </a>
                </div>
            </div>

            <p class="hero-eyebrow">Die erste interaktive Online-Game-Show Deutschlands </p>
            <h1 class="hero-title">
                Zuschauen war gestern.
                <br>
                <span class="highlight">Jetzt spielst Du mit. </span>
            </h1>
            <div class="alert-next-show" style="">
                Nächste Show: 19.03 um 20:00 Uhr</div>

            <p class="hero-subtitle">
                Quiz, Challenges, Überraschungsspiele – live und interaktiv. Bei der Badabing Game Show wird jeder
                Zuschauer automatisch zum Teilnehmer. Echte Gewinne, echte Spannung, echte Chancen.
            </p>

            <div class="w-100">
                <center>
                    <div class="col-md-4">
                        <a href="#form-section" class="text-decoration-none d-block">
                            <button class="mb-3 btn-orange w-100">
                                Zur nächsten Game Show anmelden <i class="bi bi-bell-fill"></i>
                            </button>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="#how-it-works-section" class="text-decoration-none d-block">
                            <button class="mt-2 btn-outline-purple w-100">
                                So funktioniert's
                                <i class="bi bi-arrow-up-right"></i></button>
                        </a>
                    </div>
                </center>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════
       STATS BAR
  ══════════════════════════════════════════ -->
    <section class="stats-bar">
        <div class="container">
            <div class="text-center row justify-content-center g-3">
                <div class="col-6 col-md-3">
                    <div class="stat-value text-orange">100%</div>
                    <div class="stat-label">Kostenlose Teilnahme </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-value text-purple">LIVE</div>
                    <div class="stat-label">Interaktives Mitspielen </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-value ">
                        <i class="bi bi-infinity text-purple-plum fs-5"></i>
                    </div>
                    <div class="stat-label">
                        Unbegrenzt Mitspielen
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-value text-pink">ECHT</div>
                    <div class="stat-label">Gewinne &amp; Preise</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════
       BEREIT FÜR DIE SHOW (CTA strip)
  ══════════════════════════════════════════ -->
    <section class="cta-banner" id="form-section">
        <div class="container ">
            <div class="fs-6 fw-bold text-purple" style="text-transform: uppercase;">Sei Dabei</div>
            <h2 class="mb-2 section-title">Bereit für die <span class="text-orange">Show?</span></h2>
            <p class="mb-1 text-muted" style="">Trag Dich ein und schnapp Dir Dein Ticket zur nächsten
            </p>
            <p class="mb-3 text-muted" style="">Game Show am 19.03 um 20:00 Uhr. </p>
            <x-registeration-form :referredByUser="$referredByUser ?? null" />

            <p class="mb-3 text-purple" style="">
                Kostenlos & unverbindlich. Du kannst Dich jederzeit abmelden.
            </p>
        </div>
    </section>

    <!-- ══════════════════════════════════════════
       3 SCHRITTE ZUM GEWINN
  ══════════════════════════════════════════ -->
    <section class="steps-section" id="how-it-works-section">
        <div class="container">
            <div class="mb-5 text-center">
                <div class="section-eyebrow" style="text-transform: uppercase;">So funktioniert's</div>
                <h2 class="section-title">Drei Schritte zum <span class="text-orange">Gewinn.</span></h2>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card step-card purple">
                        <div class="card-body">
                            <div class="step-number">01</div>
                            <h4 class="step-title">Einschalten</h4>
                            <p class="step-text">Öffne die Badabing Game Show - live im Browser, ohne App, ohne
                                Anmeldehürden. Einfach reinschalten und dabei sein. </p>
                        </div>
                    </div>

                </div>
                <div class="col-md-4">
                    <div class="card step-card orange">
                        <div class="card-body">
                            <div class="step-number">02</div>
                            <h4 class="step-title">Mitspielen</h4>
                            <p class="step-text">Quiz und Challenges - klicke, rate, entscheide. Du bist nicht nur
                                Zuschauer, Du bist Teil der Show.</p>
                        </div>
                    </div>

                </div>
                <div class="col-md-4">
                    <div class="card step-card pink">
                        <div class="card-body">
                            <div class="step-number">03</div>
                            <h4 class="step-title">Abräumen</h4>
                            <p class="step-text">Jede Runde echte Preise – von Geldgewinnen bis hin zu traumhaften
                                Reisen und aufregenden Gewinnchancen.</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════
       JEDE SHOW EIN NEUES SPIEL
  ══════════════════════════════════════════ -->
    <section class="features-section gradient-bg">
        <div class="container">
            <div class="mb-5 text-center">
                <p class="fs-6 fw-bold text-orange" style="text-transform: uppercase;">
                    Spielmodi
                </p>
                <h2 class="section-title">Jede Show ein <span class="accent">neues Spiel.</span></h2>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">🧠</div>
                        <h5 class="feature-title">Diverse Kategorien</h5>
                        <p class="feature-text">Teste Dein Wissen in kniffligen Quizrunden gegen andere Teilnehmer.
                        </p>
                        <div>
                            <span class="badge badge-purple">Wissen</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">⚡</div>
                        <h5 class="feature-title">Sei schnell</h5>
                        <p class="feature-text">Schnelligkeit entscheidet. Schnell denken, schnell antworten - und
                            gewinnen.</p>
                        <div>
                            <span class="badge badge-orange">Geschick</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">👥</div>
                        <h5 class="feature-title">Spiele mit Freunden</h5>
                        <p class="feature-text">Spiel mit Freunden zusammen und habt ein unvergessliches Quiz-Erlebnis.
                            Gemeinsam gewinnen doppelt geil.</p>
                        <div>
                            <span class="badge badge-purple-plum">Teamplay</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════
       NÄCHSTE SHOW (CTA strip 2)
  ══════════════════════════════════════════ -->
    <section class="bg-white cta-banner">
        <div class="container text-center">
            <div class="text-purple fs-6 fw-bold" style="text-transform: uppercase;">Nicht verpassen!

            </div>
            <h2 class="mb-2 section-title">Die nächste <span class="text-orange">Show</span> startet <span
                    class="text-orange">bald</span>!</h2>
            <p class="mb-1 text-muted" style="">Melde dich jetzt kostenlos an und sichere Dir dein Ticket & den Teilnahmelink</p>
            {{-- <p class="mb-3 text-muted" style="">Game Show am (Mo) um 20:00 Uhr.</p> --}}
            <center>
                <div class="col-lg-4">
                    <a href="#form-section" class="text-decoration-none d-block">
                        <button class="mb-3 btn-orange w-100">
                            Zur nächsten Game Show anmelden <i class="bi bi-bell-fill"></i>
                        </button>
                    </a>
                </div>
            </center>
        </div>
    </section>

    <!-- ══════════════════════════════════════════
       WAS LÄUFT / SCHEDULE
  ══════════════════════════════════════════ -->
    <section class="schedule-section gradient-bg">
        <div class="container">
            <div class="mb-4 text-center">
                <div class="fs-6 fw-bold text-orange" style="text-transform: uppercase;">Showtime</div>
                <h2 class="section-title">Was läuft, was <span class="text-purple">kommt</span>.</h2>
            </div>

            <div id="schedule-carousel">


            </div>
        </div>
    </section>

    <section class="container p-4 bg-whtie">
        <div class="row">
            <div class="col-md-3">
                <img src="{{ asset('images/money-sack-icon.png') }}" alt="Schedule 1" class="img-fluid"
                    width="20">
                Geldpreise
            </div>
            <div class="col-md-3">
                <img src="{{ asset('images/aero-plane-icon.png') }}" alt="Schedule 2" class="img-fluid"
                    width="20">
                Traumreisen
            </div>
            <div class="col-md-3">
                <img src="{{ asset('images/shop-bag-icon.png') }}" alt="Schedule 3" class="img-fluid"
                    width="20">
                Shopping-Gutscheine
            </div>
            <div class="col-md-3">
                <img src="{{ asset('images/gift-icon.png') }}" alt="Schedule 4" class="img-fluid" width="20">
                Sachpreise
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════
       PREISE
  ══════════════════════════════════════════ -->
    <section class="bg-white w-100">
        <div class="container d-flex justify-content-center align-items-center">
            <div class="col-lg-8">
                <div class="container">
                    <div class="py-5 mb-5 text-center">
                        <div class=" fs-6 fw-bold text-pink">Was gibt's zu gewinnen?</div>
                        <h2 class="section-title">Preise, die sich <span class="accent-orange">lohnen.</span></h2>
                    </div>
                    <div class="mb-4 row justify-content-center g-4">
                        <div class="col-6 col-md-4">
                            <center>
                                <div class="prize-card">
                                    <div class="prize-icon">
                                        <img src="{{ asset('images/winner.png') }}" alt="Prize 1" class="img-fluid"
                                            width="50">
                                    </div>
                                    <div class="prize-place">1. Platz</div>
                                    <div class="prize-amount">300 € Bargeld</div>
                                </div>
                            </center>
                        </div>
                        <div class="col-6 col-md-4">
                            <center>
                                <div class="prize-card">
                                    <div class="prize-icon">
                                        <img src="{{ asset('images/2nd.png') }}" alt="Prize 1" class="img-fluid"
                                            width="50">
                                    </div>
                                    <div class="prize-place">2. Platz</div>
                                    <div class="prize-amount">100 € Bargeld</div>
                                </div>
                            </center>
                        </div>
                        <div class="col-6 col-md-4">
                            <center>
                                <div class="prize-card">
                                    <div class="prize-icon">
                                        <img src="{{ asset('images/3rd.png') }}" alt="Prize 1" class="img-fluid"
                                            width="50">
                                    </div>
                                    <div class="prize-place">3. Platz</div>
                                    <div class="prize-amount">50 € Bargeld</div>
                                </div>
                            </center>
                        </div>
                        <div class="col-6 col-md-4">
                            <center>
                                <div class="prize-card">
                                    <div class="prize-place">4 - 10. Platz</div>
                                    <p class="text-center fs-4">
                                        🤫
                                    </p>
                                </div>
                            </center>
                        </div>
                        <div class="col-6 col-md-4">
                            <center>
                                <div class="prize-card">
                                    <div class="prize-place">11- 20. Platz</div>
                                    <p class="text-center fs-4">
                                        🤫
                                    </p>
                                </div>
                            </center>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════
       TESTIMONIALS
  ══════════════════════════════════════════ -->
    <section class="testimonials-section gradient-bg">
        <div class="container">
            <div class="mb-5 text-center">
                <div class="section-eyebrow">DAS SAGEN UNSERE SPIELERINNEN &amp; SPIELER</div>
                <h2 class="section-title">Echte Spieler, echte <span class="text-orange">Begeisterung.</span></h2>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="testi-card">
                        <div class="stars">★★★★★</div>
                        <p class="testi-text">Ich war skeptisch - eine Game Show online, kostenlos, echte Gewinne? Aber dann hab ich mitgemacht und tatsächlich 100€ gewonnen. Bin absolut begeistert!</p>
                        <div class="testi-author">Lisa M.</div>
                        <div class="testi-date">Badabing Gewinner</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testi-card">
                        <div class="stars">★★★★★</div>
                        <p class="testi-text">Endlich eine Show, bei der ich nicht nur zuschaue. Die Quiz-Battles machen mega Spaß und ich freue mich jede Woche auf die nächste Runde. Mein Highlight am Abend!</p>
                        <div class="testi-author">Markus T.</div>
                        <div class="testi-date">Quiz-Battle-Fan</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testi-card">
                        <div class="stars">★★★★★</div>
                        <p class="testi-text">Ich dachte zuerst, ich schaue nur kurz rein – und plötzlich habe ich selbst mitgespielt. Die Show ist super unterhaltsam und man fiebert bei jeder Runde mit. Wirklich ein cooles Konzept.</p>
                        <div class="testi-author">Sandra K.</div>
                        <div class="testi-date">Badabing Player</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════
       HOST
  ══════════════════════════════════════════ -->
    <section class="host-section gradient-bg-reverse">
        <div class="container">
            <div class="text-center w-100">
                <div class="host-eyebrow fs-6 text-orange">DEINE GASTGEBERIN</div>
                <h1 class="fs-1 fw-bold">Die Stimme der Show: <span class="text-purple">Tanja
                        Müller</span>
                </h1>
            </div>



            <div class="p-2 bg-white border rounded col-12 p-lg-5 border-light">
                <div class="row w-100 align-items-center justify-content-center">
                    <div class="mb-4 text-center col-md-4">

                        <img src="{{ asset('images/tanjamueller_portrait.webp') }}" alt="Tanja Müller"
                            class="img-fluid circle" style="border-radius: 50%; max-width: 200px; width: 100%;">
                        <div class="fs-4 text-pink">
                            <i class="bi bi-instagram"></i>
                            tanja__mueller
                        </div>
                    </div>
                    <div class="p-4 col-md-8 d-flex flex-column justify-content-center align-items-center">

                        <p class="text-dark fs-5">
                            Live-Shows leben von Persönlichkeit – und genau die bringt Tanja Müller mit auf
                            die
                            Badabing Bühne.
                            <br><br>


                            Als erfahrene Moderatorin war sie bereits Teil verschiedener TV-Produktionen und
                            weiß genau, wie man Spannung aufbaut und Zuschauer mitreißt.
                            <br><br>


                            Mit ihrer Energie, ihrem Gespür für Unterhaltung und ihrer spontanen Art macht
                            sie
                            aus jeder Sendung ein Erlebnis – und sorgt dafür, dass aus Zuschauern echte
                            Mitspieler werden.
                            <br><br>


                            Denn bei Badabing gilt:
                            Du schaust nicht nur zu – du spielst mit.


                        </p>

                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- ══════════════════════════════════════════
       HOW IT WORKS (Von der Anmeldung zum Gewinn)
  ══════════════════════════════════════════ -->
    <section class="howitworks-section">
        <div class="container">
            <div class="mb-5 text-center">
                <div class="section-eyebrow">DER WEG ZUM ERFOLG</div>
                <h2 class="section-title">Von der Anmeldung zum <span class="text-orange">Gewinn.</span></h2>
            </div>
            <div class="mb-2 text-center row g-4">
                <div class="col-6 col-md-3">
                    <div class="hw-step orange">
                        <div class="hw-icon">
                            <div class="hw-step-counter">1</div>

                            📝
                        </div>
                        <div class="hw-title">Registrieren</div>
                        <div class="hw-text">Erstelle Dein kostenloses Konto auf badabing.show - in unter 60 Sekunden,
                            ohne Kreditkarte.
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="hw-step pink">
                        <div class="hw-icon">
                            <div class="hw-step-counter">2</div>
                            🌐
                        </div>
                        <div class="hw-title">Show öffnen</div>
                        <div class="hw-text">Gehe zur Show-Seite auf badabing.show oder klicke den Link in Deiner Erinnerungs-Nachricht.</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="hw-step purple">
                        <div class="hw-icon">
                            <div class="hw-step-counter">3</div>

                            🎮
                        </div>
                        <div class="hw-title">Live mitspielen</div>
                        <div class="hw-text">Zur Showtime einfach einschalten und direkt im Browser mitspielen - am Handy. Tablet oder Laptop.
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="hw-step purple-plum">
                        <div class="hw-icon">
                            <div class="hw-step-counter">4</div>
                            🏆
                        </div>
                        <div class="hw-title">Gewinne kassieren</div>
                        <div class="hw-text">Gewonnen? Dann geht alles automatisch. Gutscheine werden direkt verschickt, Geldgewinne zeitnah ausgezahlt.
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-2 mx-auto mt-5 text-center rounded alert col-lg-6 fw-bold text-dark "
                style="background: var(--purple-light);">Alles passiert auf badabing.show - Du brauchst keine extra App und kein Abo. Einfach registrieren, einschalten, mitspielen. So einfach ist das.

</div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════
       CTA BANNER (final)
  ══════════════════════════════════════════ -->
    {{-- <section class="cta-banner gradient-bg">
        <div class="container text-center">
            <div class="text-banner">
                Jetzt Ticket sichern
            </div>
            <h2 class="mb-2 section-title">Bereit für die <span class="text-orange">Show?</span></h2>
            <p class="mb-1 text-dark" style="">Trag Dich ein und schnapp Dir Dein Ticket zur nächsten </p>
            <p class="mb-3 text-dark" style="">Game Show am 19.03 um 20:00 Uhr.
                .</p>
            <div id="form-section">
                <x-registeration-form />


            </div>

        </div>
    </section> --}}

    <!-- ══════════════════════════════════════════
       FAQ
  ══════════════════════════════════════════ -->
    <section class="faq-section">
        <div class="container">
            <div class="mb-5 text-center">
                <div class="section-eyebrow">FAQ</div>
                <h2 class="section-title">Noch Fragen?</h2>
                <p style=";color:var(--text-muted);max-width:560px;margin:0 auto">Alles, was Du über
                    die Badabing Game Show wissen musst – kurz und bündig beantwortet.</p>
            </div>
            <div class="accordion" id="faqAccordion" style="max-width:760px;margin:0 auto">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faqHeadingOne">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faqCollapseOne" aria-expanded="true" aria-controls="faqCollapseOne">
                            Muss ich was zahlen, um an der Show teilzunehmen?
                        </button>
                    </h2>
                    <div id="faqCollapseOne" class="accordion-collapse collapse show" aria-labelledby="faqHeadingOne"
                        data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Nein, Du kannst direkt über die Website oder einen Link teilnehmen, ohne eine App herunterzuladen oder ein kostenpflichtiges Abo abzuschließen. Die Teilnahme an der Game Show ist komplett kostenlos.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faqHeadingTwo">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faqCollapseTwo" aria-expanded="false" aria-controls="faqCollapseTwo">
                            Welche Preise kann ich gewinnen?
                        </button>
                    </h2>
                    <div id="faqCollapseTwo" class="accordion-collapse collapse" aria-labelledby="faqHeadingTwo"
                        data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Von Geld- und Sachpreisen über Traumreisen bis hin zu exklusiven Shopping-Deals ist alles möglich. Je nach Show gibt es unterschiedliche Belohnungen!
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faqHeadingThree">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faqCollapseThree" aria-expanded="false"
                            aria-controls="faqCollapseThree">
                            Gibt es eine Altersbeschränkung für die Teilnahme?
                        </button>
                    </h2>
                    <div id="faqCollapseThree" class="accordion-collapse collapse" aria-labelledby="faqHeadingThree"
                        data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Ja, Du musst mindestens 18 Jahre alt sein, um an den Spielen teilzunehmen und Preise zu
                            gewinnen.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faqHeadingFour">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faqCollapseFour" aria-expanded="false" aria-controls="faqCollapseFour">
                            Wie oft kann ich mitspielen?
                        </button>
                    </h2>
                    <div id="faqCollapseFour" class="accordion-collapse collapse" aria-labelledby="faqHeadingFour"
                        data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Es gibt keine Begrenzung! Du kannst so oft teilnehmen, wie Du möchtest, und deine Gewinnchancen immer wieder aufs Neue nutzen.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faqHeadingFive">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faqCollapseFive" aria-expanded="false" aria-controls="faqCollapseFive">
                            Wie werden die Gewinner ermittelt?
                        </button>
                    </h2>
                    <div id="faqCollapseFive" class="accordion-collapse collapse" aria-labelledby="faqHeadingFive"
                        data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Die Gewinner werden je nach Spielmodus durch Wissen und Schnelligkeit bestimmt. Alle Gewinne werden fair vergeben und transparent angezeigt.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faqHeadingSix">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faqCollapseSix" aria-expanded="false" aria-controls="faqCollapseSix">
                            Gibt es feste Sendezeiten oder kann ich jederzeit spielen?
                        </button>
                    </h2>
                    <div id="faqCollapseSix" class="accordion-collapse collapse" aria-labelledby="faqHeadingSix"
                        data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Es gibt feste Live-Show-Zeiten, aber auch spontane Special-Events. Die genauen Termine
                            findest Du immer aktuell auf der Website.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faqHeadingSeven">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#faqCollapseSeven" aria-expanded="false"
                            aria-controls="faqCollapseSeven">
                            Kann ich auch mit Freunden oder als Team spielen?
                        </button>
                    </h2>
                    <div id="faqCollapseSeven" class="accordion-collapse collapse" aria-labelledby="faqHeadingSeven"
                        data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Ja! Du kannst jeden Deiner Freunde zu Badabing einladen und gemeinsam mitfiebern. Aktuell ist jedoch eine Teilnahme nur als Einzelspieler möglich. Möchtest Du oder Deine Freunde teilnehmen, loggt euch bitte einzeln zur Show ein.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>




    @push('scripts')
        <script>
            var assetPath = "{{ asset('images/') }}";
            var scheduleData = {
                "carousel_items": [{
                        "badge": "BALD",
                        "date": "19.03.2026 um 20:00 Uhr",
                        "title": "Die große Premiere",
                        "description": "Die allererste Badabing Game Show - sei von Anfang an dabei und sichere Dir die ersten Gewinne!",
                        "meta": [{
                                "icon": "bulls-eye-icon.png",
                                "label": "Wissen + Speed"
                            },
                            {
                                "icon": "gift-icon.png",
                                "label": "Premieren-Preise"
                            }
                        ]
                    },
                    {
                        "badge": "BALD",
                        "date": "02.04.2026 um 20:00 Uhr",
                        "title": "Quiz&Speed Game Show",
                        "description": "Teste Dein Wissen und Deine Schnelligkeit und sichere Dir die Chance auf echte Gewinne!",
                        "meta": [{
                                "icon": "bulls-eye-icon.png",
                                "label": "Wissen + Speed"
                            },
                            {
                                "icon": "gift-icon.png",
                                "label": "Geldpreise & Gutscheine"
                            }
                        ]
                    },
                    {
                        "badge": "BALD",
                        "date": "09.04.2026 um 20:00 Uhr",
                        "title": "Quiz&Speed Game Show",
                        "description": "Teste Dein Wissen und Deine Schnelligkeit und sichere Dir die Chance auf echte Gewinne!",
                        "meta": [{
                                "icon": "bulls-eye-icon.png",
                                "label": "Wissen + Speed"
                            },
                            {
                                "icon": "gift-icon.png",
                                "label": "Geldpreise & Gutscheine"
                            }
                        ]
                    },
                    {
                        "badge": "BALD",
                        "date": "16.04.2026 um 20:00 Uhr",
                        "title": "Quiz&Speed Game Show",
                        "description": "Teste Dein Wissen und Deine Schnelligkeit und sichere Dir die Chance auf echte Gewinne!",
                        "meta": [{
                                "icon": "bulls-eye-icon.png",
                                "label": "Wissen + Speed"
                            },
                            {
                                "icon": "gift-icon.png",
                                "label": "Geldpreise & Gutscheine"
                            }
                        ]
                    },
                    {
                        "badge": "BALD",
                        "date": "23.04.2026 um 20:00 Uhr",
                        "title": "Quiz&Speed Game Show",
                        "description": "Teste Dein Wissen und Deine Schnelligkeit und sichere Dir die Chance auf echte Gewinne!",
                        "meta": [{
                                "icon": "bulls-eye-icon.png",
                                "label": "Wissen + Speed"
                            },
                            {
                                "icon": "gift-icon.png",
                                "label": "Geldpreise & Gutscheine"
                            }
                        ]
                    }
                ]
            }

            // Initialize Slick Slider for the Schedule Carousel
            $(document).ready(function() {
                // Append schedule data and generate dynamic items inside the #schedule-carousel div

                // Ensure #schedule-carousel is empty
                $('#schedule-carousel').empty();

                // Loop through scheduleData
                if (window.scheduleData && Array.isArray(scheduleData.carousel_items)) {
                    scheduleData.carousel_items.forEach(function(show) {
                        var $card = $(
                            '<div class="show-card-container"> <div class="mb-3 show-card"></div></div>');
                        // Top: badge and date
                        var $badge = $('<span class="mb-2 badge badge-orange" style="font-size:0.75rem;">' + (
                            show.badge || '') + '</span>');
                        var $date = $('<div class="mb-1 show-date text-pink">' + (show.date || '') + '</div>');

                        // Title + desc
                        var $title = $('<div class="my-2 show-title fs-4 fw-bold">' + (show.title || '') +
                            '</div>');
                        var $desc = $('<div class="show-desc">' + (show.description || '') + '</div>');

                        // Meta section
                        var $meta = $('<div class="flex-wrap gap-2 mt-2 show-meta d-flex"></div>');
                        if (Array.isArray(show.meta)) {
                            show.meta.forEach(function(meta) {
                                var $item = $(
                                    '<span class="gap-1 d-inline-flex align-items-center"></span>');
                                if (meta.icon) {
                                    $item.append('<img src="' + assetPath + '/' + meta.icon +
                                        '" alt="" width="16" height="16">');
                                }
                                $item.append('<span>' + meta.label + '</span>');
                                $meta.append($item);
                            });
                        }

                        // Compose card body
                        var $body = $('<div class="show-card-body text-start"></div>');
                        $body.append($badge);
                        $body.append($date);
                        $body.append($title);
                        $body.append($desc);
                        $body.append($meta);
                        $card.append($body);

                        $('#schedule-carousel').append($card);
                    });

                    $('#schedule-carousel').slick({
                        dots: true,
                        arrows: true,
                        infinite: false,
                        slidesToShow: 3,
                        slidesToScroll: 1,
                        responsive: [{
                                breakpoint: 992,
                                settings: {
                                    slidesToShow: 2
                                }
                            },
                            {
                                breakpoint: 768,
                                settings: {
                                    slidesToShow: 1
                                }
                            }
                        ]
                    });
                }
            });
        </script>
    @endpush
</x-guest-layout>
