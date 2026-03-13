<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Badabing – Game Show</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />

    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">
    <meta property="og:image" content="{{ asset('images/meta.png') }}">
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:image" content="{{ asset('images/meta.png') }}" />
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,400;0,600;0,700;0,800;0,900;1,700&family=Poppins:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css" />
    <style>
        :root {
            --purple: #5A10AC;
            --purple-light: #9b6ed6;
            --purple-plum: #9E136D;
            --purple-dark: #5a2fa0;
            --orange: #FC6902;
            --orange-light: #f9a55a;
            --yellow: #f5c842;
            --bg-lavender: #f3eefb;
            --bg-light: #faf8ff;
            --text-dark: #140B63;
            --text-muted: #6b6080;
            --white: #ffffff;
            --star-gold: #f4c430;
            --pink: #f73fae;

            --purple-light: #5a10ac1a;
            --orange-light: #fc69021a;
            --purple-plum-light: #9e136d1a;
            --pink-light: #f73fae1a;
        }

        

        body {
            font-family: 'Nunito', sans-serif;
            background: var(--bg-light);
            color: var(--text-dark);
            margin: 0;
            padding: 0;
            
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: 'Poppins', sans-serif;
        }

        .gradient-bg {
            background: linear-gradient(160deg, #eee6fc 0%, #f7f2ff 40%, #fce9d8 100%) !important;

        }
        .gradient-bg-reverse {
            background: linear-gradient(564deg, #fce9d8 0%, #f7f2ff 60%, #eee6fc 100%) !important;
        }

        .rounded {
            border-radius: 20px !important;
        }

        /* ── HERO ── */
        .hero-section {
            background: linear-gradient(160deg, #eee6fc 0%, #f7f2ff 40%, #fce9d8 100%);
            padding: 60px 0 50px;
            text-align: center;
        }

        .hero-logo-placeholder {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, var(--purple), var(--orange));
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero-eyebrow {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 2px;
            color: var(--purple);
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .hero-title {
            font-size: clamp(2rem, 5vw, 3.2rem);
            font-weight: 900;
            line-height: 1.1;
            color: var(--text-dark);
            margin-bottom: 6px;
        }

        .hero-title .highlight {
            color: var(--orange);
        }

        .hero-subtitle {
            font-size: 1.05rem;
            color: #000;
            max-width: 100%;
            margin: 0 auto 10px;
            padding: 0px 50px;
        }

        .alert-next-show {
            color: #F73FAE;
            font-weight: 700;
            font-size: 0.8rem;
            margin-bottom: 10px;
            background: var(--pink-light);
            border-radius: 20px;
            padding: 10px 20px;
            border: 1px solid #F73FAE;
            width: fit-content;
            margin: 20px auto;
        }

        .hero-sub-small {
            font-size: 0.82rem;
            color: var(--text-muted);
            margin-bottom: 18px;
        }

        .btn-orange {
            background: var(--orange);
            color: #fff;
            font-weight: 800;
            border: none;
            border-radius: 50px;
            padding: 14px 36px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-orange:hover {
            background: #e06d18;
            color: #fff;
        }

        .btn-outline-purple {
            border: 2px solid var(--purple);
            color: var(--purple);
            font-weight: 700;
            border-radius: 50px;
            padding: 10px 28px;
            background: transparent;
            font-size: 0.9rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
        }

        .btn-outline-purple:hover {
            background: var(--purple);
            color: #fff;
        }

        /* ── STATS BAR ── */
        .stats-bar {
            background: #fff;
            border-top: 1px solid #ede8f8;
            border-bottom: 1px solid #ede8f8;
            padding: 18px 0;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value,
        .stat-value i {
            font-size: 2rem !important;
            font-weight: 900;
            color: var(--purple);
        }


        .stat-label {
            font-size: 0.78rem;
            color: var(--text-muted);
            font-weight: 600;
        }

        .stat-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #4caf50;
            display: inline-block;
            margin-right: 4px;
        }

        /* ── SECTION COMMON ── */
        .section-eyebrow {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: var(--purple);
            margin-bottom: 6px;
        }

        .section-title {
            font-size: clamp(1.6rem, 3.5vw, 2.3rem);
            font-weight: 900;
            color: var(--text-dark);
        }

        .section-title .accent {
            color: var(--purple);
        }

        .section-title .accent-orange {
            color: var(--orange);
        }

        /* ── STEPS SECTION ── */
        .steps-section {
            background: #fff;
            padding: 60px 0;
        }

        .step-number {
            font-size: 3.5rem;
            font-weight: 900;
            color: #ede8f8;
            line-height: 1;
            opacity: 0.7;
        }

        .step-card {
            border-radius: 18px;
        }

        .step-card.purple.card {
            border-top: 5px solid var(--purple);
        }

        .step-card.orange.card {
            border-top: 5px solid var(--orange);
        }

        .step-card.pink.card {
            border-top: 5px solid var(--pink);
        }


        .step-card.purple .card-body .step-number {
            color: var(--purple);
        }

        .step-card.orange .card-body .step-number {
            color: var(--orange);
        }

        .step-card.pink .card-body .step-number {
            color: var(--pink);
        }

        .step-card .step-title {
            font-weight: 800
        }

        .step-card.purple .step-title {
            color: var(--purple);
        }

        .step-card.orange .step-title {
            color: var(--orange);
        }

        .step-card.pink .step-title {
            color: var(--pink);
        }

        .step-text {
            font-size: 1.2rem;
            color: var(--text-dark);
            font-weight: 600;
        }

        .badge {
            width: fit-content;
            padding: 8px 15px;
            border-radius: 20px;

            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-purple {
            background: var(--purple-light);
            color: var(--purple);
            border: 1px solid var(--purple);
        }

        .badge-orange {
            background: var(--orange-light);
            color: var(--orange);
            border: 1px solid var(--orange);
        }

        .badge-purple-plum {
            background: var(--purple-plum-light);
            color: var(--purple-plum);
            border: 1px solid var(--purple-plum);
        }





        /* ── FEATURES ── */
        .features-section {
            background: var(--bg-lavender);
            padding: 60px 0;
        }

        .feature-card {
            background: #fff;
            border-radius: 18px;
            padding: 28px 24px;
            height: 100%;
            box-shadow: 0 2px 16px rgba(123, 79, 196, 0.07);
        }

        .feature-icon {
            font-size: 1.6rem;
            margin-bottom: 12px;
        }

        .feature-title {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--text-dark);
        }

        .feature-text {
            font-size: 0.85rem;
            color: #000;
            margin-bottom: 14px;
        }

        .feature-link {
            color: var(--purple);
            font-weight: 700;
            font-size: 0.85rem;
            text-decoration: none;
        }

        .feature-link:hover {
            text-decoration: underline;
        }

        /* ── CTA BANNER ── */
        .cta-banner {

            padding: 50px 0;
            text-align: center;
        }

        /* ── SCHEDULE / WHAT'S ON ── */
        .schedule-section {
            background: #fff;
            padding: 60px 0;
        }

        .show-card {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 14px rgba(0, 0, 0, 0.07);
        }

        .show-card-img-placeholder {
            height: 160px;
            background: linear-gradient(135deg, #c8a8f0, #f0a868);
        }

        .show-card-body {
            padding: 16px;
            background: #fff;
        }

        .show-tag {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--purple);
        }

        .show-date {
            font-size: 0.78rem;
            color: var(--text-muted);
        }

        .show-title {
            font-weight: 800;
            font-size: 1rem;
            color: var(--text-dark);
        }

        .show-desc {
            font-size: 0.9rem;
            color: var(--text-dark);
            margin-bottom: 10px;
        }

        .show-meta {
            font-size: 0.78rem;
            color: var(--text-muted);
        }

        .schedule-nav {
            display: flex;
            gap: 20px;
            font-size: 0.85rem;
            color: var(--text-muted);
            font-weight: 600;
        }

        .schedule-nav span {
            cursor: pointer;
        }

        .schedule-nav span.active {
            color: var(--purple);
            border-bottom: 2px solid var(--purple);
        }

        /* ── PRIZES ── */
        .prizes-section {
            background: var(--bg-lavender);
            padding: 60px 0;
            text-align: center;
        }

        .prize-card {
            background: #fff;
            border-radius: 16px;
            padding: 28px 20px;
            border: 1px solid whitesmoke;

        }

        .prize-icon {
            font-size: 2rem;
            margin-bottom: 8px;
        }

        .prize-place {
            font-weight: 900;
            font-size: 1rem;
            color: var(--text-dark);
        }

        .prize-amount {
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .prize-small {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 12px;
        }

        /* ── TESTIMONIALS ── */
        .testimonials-section {
            background: #fff;
            padding: 60px 0;
        }

        .testi-card {
            background: var(--bg-lavender);
            border-radius: 16px;
            padding: 24px;
            height: 100%;
        }

        .stars {
            color: var(--star-gold);
            font-size: 1rem;
        }

        .testi-text {
            font-size: 0.88rem;
            color: var(--text-dark);
            margin: 10px 0;
        }

        .testi-author {
            font-weight: 800;
            font-size: 0.85rem;
        }

        .testi-date {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        /* ── HOST ── */
        .host-section {
            background: var(--bg-lavender);
            padding: 60px 0;
        }

        .host-img-placeholder {
            width: 200px;
            height: 220px;
            background: linear-gradient(135deg, #ddc8f8, #f8d8b8);
            border-radius: 120px 120px 80px 80px;
            margin: 0 auto;
        }

        .host-eyebrow {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 2px;
            color: var(--orange);
            text-transform: uppercase;
        }

        .host-name {
            font-size: 2rem;
            font-weight: 900;
            color: var(--text-dark);
        }

        .host-bio {
            font-size: 0.9rem;
            color: var(--text-muted);
            line-height: 1.7;
        }

        .host-sig {
            font-style: italic;
            font-size: 0.85rem;
            color: var(--purple);
            margin-top: 12px;
        }

        /* ── HOW IT WORKS ── */
        .howitworks-section {
            background: #fff;
            padding: 60px 0;
        }

        .hw-step {
            text-align: center;
            padding: 0 10px;
            position: relative;
        }

        .hw-step-counter {
            position: absolute;
            top: -10px;
            right: 0;
            width: 20px;
            height: 20px;
            background: var(--purple);
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hw-step.purple .hw-step-counter {
            background: var(--purple);
        }

        .hw-step.orange .hw-step-counter {
            background: var(--orange);
        }

        .hw-step.pink .hw-step-counter {
            background: var(--pink);
        }

        .hw-step.purple-plum .hw-step-counter {
            background: var(--purple-plum);
        }

        .hw-step.purple .hw-icon {
            background: var(--purple-light) !important;
        }

        .hw-step.orange .hw-icon {
            background: var(--orange-light) !important;
        }

        .hw-step.pink .hw-icon {
            background: var(--pink-light) !important;
        }

        .hw-step.purple-plum .hw-icon {
            background: var(--purple-plum-light) !important;
        }

        .hw-step.purple .hw-title {
            color: var(--purple) !important;
        }

        .hw-step.orange .hw-title {
            color: var(--orange) !important;
        }

        .hw-step.pink .hw-title {
            color: var(--pink) !important;
        }

        .hw-step.purple-plum .hw-title {
            color: var(--purple-plum) !important;
        }

        .hw-icon {
            position: relative;
            width: 56px;
            height: 56px;
            border-radius: 15px;
            background: linear-gradient(135deg, var(--purple), var(--purple-light));
            color: #fff;
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
        }



        .hw-title {
            font-weight: 800;
            font-size: 0.95rem;
            color: var(--text-dark);
        }

        .hw-text {
            font-size: 1rem;
            color: var(--text-dark);
            font-weight: 600;
        }

        .hw-connector {
            flex: 1;
            height: 2px;
            background: linear-gradient(90deg, var(--purple-light), var(--orange-light));
            margin-top: -28px;
        }

        /* ── FAQ ── */
        .faq-section {
            background: var(--bg-lavender);
            padding: 60px 0;
        }

        .faq-q {
            font-weight: 700;
            font-size: 0.95rem;
            color: var(--text-dark);
            padding: 18px 0;
            border-bottom: 1px solid #e0d8f0;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .faq-q:first-child {
            border-top: 1px solid #e0d8f0;
        }

        .faq-chevron {
            color: var(--purple);
            font-size: 1.1rem;
        }

        /* ── FOOTER ── */
        footer {
            background: #1a1230;
            color: #aaa;
            font-size: 0.8rem;
            padding: 24px 0;
            text-align: center;
        }

        /* ── DIVIDER LABEL ── */
        .divider-label {
            display: inline-block;
            background: var(--purple);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding: 4px 14px;
            border-radius: 20px;
            margin-bottom: 12px;
        }

        .text-purple {
            color: var(--purple) !important;
        }

        .text-orange {
            color: var(--orange) !important;
        }

        .text-purple-plum {
            color: var(--purple-plum) !important;
        }

        .text-purple-dark {
            color: var(--purple-dark) !important;
        }

        .bg-purple {
            background-color: var(--purple) !important;
        }

        .bg-orange {
            background-color: var(--orange) !important;
        }

        .bg-purple-plum {
            background-color: var(--purple-plum) !important;
        }

        .bg-purple-dark {
            background-color: var(--purple-dark) !important;
        }

        .text-pink {
            color: var(--pink) !important;
        }

        .bg-purple-soft {
            background: #f0e8fc;
            border-radius: 12px;
        }

        .show-card-container {
            margin-bottom: 10px;
            padding: 10px !important;
        }

        .show-card-container .show-card-body {
            background: #fff;
            border-radius: 12px;

            margin-bottom: 10px;
            padding: 30px;
        }
    </style>

    @include('partials.gtm', ['part' => 'head'])

</head>

<body>
    {{-- @include('partials.gtm', ['part' => 'body']) --}}

    <div class="animated-bg"></div>

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    {{ $slot }}


    <!-- ══════════════════════════════════════════
     FOOTER
══════════════════════════════════════════ -->
    <footer>
        <div class="container">
      
            <p class="mb-0" style="font-size:0.72rem">Die Badabing Game Show ist ein Unterhaltungsangebot. Teilnahme
                ab 18 Jahren. Alle Angaben ohne Gewähr. Preise können je nach Showformat variieren.</p>

            <p class="mb-1">
                © 2026 Badabing Game Show ·
                <a href="#" class="text-white" target="_blank">Impressum</a> ·
                <a href="#" class="text-white" target="_blank">Datenschutz</a> ·
                <a href="#" class="text-white" target="_blank">AGB</a> ·
                <a href="#" class="text-white" target="_blank">Kontakt</a>
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script>
        // Schedule nav tabs
        document.querySelectorAll('.schedule-nav span').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.schedule-nav span').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
            });
        });
    </script>

    @stack('scripts')
</body>

</html>
