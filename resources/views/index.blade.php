<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>baaboo Live - Live Game Show</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {

            --baaboo-orange: #ff5f00;
            --dark-bg: #0F0F0F;
            --darker-bg: #0A0A0A;
            --card-bg: #1A1A1A;
            --hover-bg: #272727;
            --text-primary: #FFFFFF;
            --text-secondary: #AAAAAA;
            --border-color: #3A3A3A;
            --success: #00D25B;
            --warning: #FFB900;
            --danger: #FF4742;
            --info: #0078D4;
        }



        body {
            background: var(--dark-bg);
            color: var(--text-primary);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            overflow-x: hidden;
        }

        /* Animated Background */
        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: radial-gradient(ellipse at 20% 30%, rgba(255, 0, 0, 0.1) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 70%, rgba(0, 210, 91, 0.1) 0%, transparent 50%),
                var(--dark-bg);
        }

        /* Live Show Banner */
        .live-show-banner {
            background: linear-gradient(135deg, var(--baaboo-orange) 0%, var(--baaboo-orange) 100%);
            padding: 3.5rem 0;
            box-shadow: 0 4px 20px rgba(255, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }

        .live-show-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            animation: shine 3s infinite;
        }

        .btn-primary {
            background-color: var(--baaboo-orange);
            color: var(--text-primary);
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #e55400;
        }

        .btn-primary:focus {
            background-color: #e55400;
            outline: none;
            box-shadow: none;
        }

        .text-primary {
            color: var(--baaboo-orange) !important;
        }

        @keyframes shine {
            0% {
                left: -100%;
            }

            100% {
                left: 100%;
            }
        }

        .live-badge {
            background: var(--text-primary);
            color: var(--baaboo-orange);
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.8;
            }
        }

        .live-dot {
            width: 8px;
            height: 8px;
            background: var(--baaboo-orange);
            border-radius: 50%;
            animation: blink 1.5s infinite;
        }

        @keyframes blink {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.3;
            }
        }

        /* Header */
        .header {
            padding: 1.5rem 0;
            background: #ffcd42;
            border-bottom: 1px solid var(--border-color);
        }

        .logo {
            font-size: 2rem;
            font-weight: bold;
            background: linear-gradient(135deg, var(--baaboo-orange) 0%, #FF6B6B 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Hero Section */
        .hero {
            padding: 5rem 0;
            text-align: left;
            position: relative;
        }

        .hero h1 {
            font-size: 4rem;
            font-weight: 900;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, var(--text-primary) 0%, var(--text-secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.2;
            text-align: left;
        }

        .hero p {
            font-size: 1.5rem;
            color: var(--text-secondary);
            margin-bottom: 2rem;
        }

        .cta-button {
            background: linear-gradient(135deg, var(--baaboo-orange) 0%, var(--baaboo-orange) 100%);
            color: var(--text-primary);
            padding: 1rem 3rem;
            font-size: 1.25rem;
            font-weight: bold;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(255, 0, 0, 0.3);
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(255, 0, 0, 0.5);
        }

        /* Features Section */
        .features {
            padding: 5rem 0;
        }

        .feature-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
            height: 100%;
        }

        .feature-card:hover {
            background: var(--hover-bg);
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(255, 0, 0, 0.2);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--baaboo-orange) 0%, var(--baaboo-orange) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 1.5rem;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .feature-card p {
            color: var(--text-secondary);
            line-height: 1.6;
        }

        /* Prize Section */
        .prize-section {
            background: var(--card-bg);
            padding: 5rem 0;
            border-top: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
        }

        .prize-amount {
            font-size: 5rem;
            font-weight: 900;
            background: linear-gradient(135deg, var(--success) 0%, #00FF87 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }

        /* Footer */
        .footer {
            padding: 3rem 0;
            background: var(--darker-bg);
            border-top: 1px solid var(--border-color);
            text-align: center;
            color: var(--text-secondary);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1.2rem;
            }

            .prize-amount {
                font-size: 3rem;
            }
        }
    </style>
</head>

<body>
    <div class="animated-bg"></div>

    <!-- Live Show Banner -->
    @if ($currentLiveShow)
        <div class="live-show-banner">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center text-md-start mb-3 mb-md-0">
                        <span class="live-badge">
                            <span class="live-dot"></span>
                            LIVE NOW
                        </span>
                    </div>
                    <div class="col-md-7 text-center text-md-start mb-3 mb-md-0">
                        <h5 class="mb-1 fw-bold">{{ $currentLiveShow->title ?? 'Live Show' }} - Win
                            {{ $currentLiveShow->currency ?? '€' }} {{ $currentLiveShow->prize_amount ?? '10,000' }}!
                        </h5>
                        <p class="mb-0 opacity-75">{{ $currentLiveShow->users->count() ?? 0 }} players competing now</p>
                    </div>
                    <div class="col-md-3 text-center text-md-end">
                        <a href="{{ route('live-show', $currentLiveShow->id) }}" class="btn btn-light btn-lg fw-bold">
                            <i class="fas fa-play me-2"></i>JOIN NOW
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="logo">
                    <img src="{{ asset('assets/images/logo.png') }}" class="img-fluid" width="150" alt="">
                    live
                </div>
                <nav>
                    <a href="#" class="btn btn-outline-dark me-2">How It Works</a>
                    <a href="#" class="btn btn-primary">Download App</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="row">
                <div class="col-md-6">

                    <h1>Play Live. Win Real Money.</h1>
                    <p>Join thousands of players in live trivia game shows</p>
                    <button class="cta-button">
                        <i class="fas fa-rocket me-2"></i>Get Started Free
                    </button>
                    <div class="mt-5 text-secondary">
                        <i class="fas fa-users me-2"></i>500K+ Active Players
                        <span class="mx-3">|</span>
                        <i class="fas fa-trophy me-2"></i>$2M+ Won This Month
                    </div>

                </div>
                <div class="col-lg-6">
                    <div class="card shadow rounded p-4 bg-light" style="max-width: 480px; margin: 2rem auto;">
                        <h3 class="mb-3 text-dark fw-bold"><i class="fas fa-user-plus me-2 text-primary"></i>
                            Register to Play</h3>
                        <form method="POST" action="{{ route('live-show.user.register', $currentLiveShow->id ?? 1) }}">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label text-dark fw-semibold">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    placeholder="John Doe" required autocomplete="off">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label text-dark fw-semibold">Email address</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    placeholder="you@email.com" required autocomplete="off">
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="agree" required>
                                <label class="form-check-label" for="agree">I agree to the <a href="#"
                                        class="text-danger text-decoration-underline">Terms & Conditions</a></label>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary fw-bold btn-lg">
                                    <i class="fas fa-arrow-right"></i> Register & Join Now
                                </button>
                            </div>
                        </form>
                        <div class="mt-3 text-center text-secondary">
                            Already have an account? <a href="#"
                                class="text-danger text-decoration-underline">Login</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-4 fw-bold">How It Works</h2>
                <p class="lead text-secondary">Three simple steps to winning</p>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <h3>Get Notified</h3>
                        <p>Receive push notifications when live shows are about to start. Never miss a game!</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-gamepad"></i>
                        </div>
                        <h3>Play Live</h3>
                        <p>Answer trivia questions in real-time with players around the world. 12 questions to victory!
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <h3>Win Cash</h3>
                        <p>Split the prize pool with winners. Money is deposited directly to your account instantly!</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Prize Section -->
    <section class="prize-section">
        <div class="container text-center">
            <h2 class="display-5 fw-bold mb-4">This Week's Prize Pool</h2>
            <div class="prize-amount">$50,000</div>
            <p class="lead text-secondary mb-4">Join the next show and compete for your share!</p>
            <div class="row justify-content-center mt-5">
                <div class="col-md-3 col-6 mb-3">
                    <h3 class="text-warning">Daily</h3>
                    <p class="text-secondary">2 shows per day</p>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <h3 class="text-info">12 Questions</h3>
                    <p class="text-secondary">Answer all correctly</p>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <h3 class="text-danger">10 Seconds</h3>
                    <p class="text-secondary">Per question</p>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <h3 class="text-success">Free</h3>
                    <p class="text-secondary">Always free to play</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="hero py-5">
        <div class="container text-center">
            <h2 class="display-4 fw-bold mb-4">Ready to Play?</h2>
            <p class="lead text-secondary mb-4">Download the app and join the next live show</p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <button class="btn btn-light btn-lg">
                    <i class="fab fa-apple me-2"></i>App Store
                </button>
                <button class="btn btn-light btn-lg">
                    <i class="fab fa-google-play me-2"></i>Google Play
                </button>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <div class="logo mb-3">baaboo.live</div>
                    <p>The world's most exciting live trivia game show.</p>
                </div>
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="text-white mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-secondary text-decoration-none">About Us</a>
                        </li>
                        <li class="mb-2"><a href="#" class="text-secondary text-decoration-none">How to
                                Play</a>
                        </li>
                        <li class="mb-2"><a href="#" class="text-secondary text-decoration-none">Prize
                                Winners</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5 class="text-white mb-3">Connect</h5>
                    <div class="d-flex gap-3 justify-content-center justify-content-md-start">
                        <a href="#" class="text-secondary"><i class="fab fa-facebook fa-2x"></i></a>
                        <a href="#" class="text-secondary"><i class="fab fa-twitter fa-2x"></i></a>
                        <a href="#" class="text-secondary"><i class="fab fa-instagram fa-2x"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-4 border-secondary">
            <p class="mb-0">&copy; 2025 Baaboo Live. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>

</html>
