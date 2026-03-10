<x-guest-layout>
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
                    <x-registeration-form />
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
</x-guest-layout>