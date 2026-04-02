<x-guest-layout>
    <style>
        .login-split {
            display: flex;
            min-height: 100vh;
        }

        .login-brand {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: linear-gradient(160deg, #3a0078 0%, #5A10AC 40%, #9E136D 80%, #FC6902 100%);
            position: relative;
            overflow: hidden;
            padding: 3rem 2rem;
        }

        .login-brand::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.04);
            top: -120px;
            left: -120px;
        }

        .login-brand::after {
            content: '';
            position: absolute;
            width: 350px;
            height: 350px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.03);
            bottom: -80px;
            right: -80px;
        }

        .login-brand-content {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .login-brand-logo {
            max-width: 220px;
            width: 100%;
            margin-bottom: 2rem;
            filter: drop-shadow(0 4px 24px rgba(0, 0, 0, 0.25));
        }

        .login-brand-tagline {
            color: rgba(255, 255, 255, 0.92);
            font-family: 'Poppins', sans-serif;
            font-size: 1.4rem;
            font-weight: 700;
            line-height: 1.4;
            max-width: 320px;
        }

        .login-brand-sub {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.95rem;
            margin-top: 0.75rem;
            max-width: 280px;
        }

        .login-form-side {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #faf8ff;
            padding: 3rem 2rem;
        }

        .login-form-wrapper {
            width: 100%;
            max-width: 420px;
        }

        .login-form-header {
            margin-bottom: 2rem;
        }

        .login-form-header h2 {
            font-family: 'Poppins', sans-serif;
            font-weight: 800;
            font-size: 1.75rem;
            color: #140B63;
            margin-bottom: 0.25rem;
        }

        .login-form-header p {
            color: #6b6080;
            font-size: 0.95rem;
            margin: 0;
        }

        .login-card {
            background: #fff;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 32px rgba(90, 16, 172, 0.08);
            border: 1px solid #f0e8fc;
        }

        .login-card .form-label {
            font-weight: 700;
            font-size: 0.85rem;
            color: #140B63;
            margin-bottom: 0.4rem;
        }

        .login-card .form-control {
            border-radius: 12px;
            padding: 0.7rem 1rem;
            border: 1.5px solid #e0d8f0;
            font-size: 0.95rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .login-card .form-control:focus {
            border-color: #5A10AC;
            box-shadow: 0 0 0 3px rgba(90, 16, 172, 0.1);
        }

        .login-card .form-check-input:checked {
            background-color: #5A10AC;
            border-color: #5A10AC;
        }

        .login-btn {
            background: linear-gradient(135deg, #5A10AC, #9E136D);
            border: none;
            color: #fff;
            font-weight: 700;
            border-radius: 50px;
            padding: 0.75rem 2rem;
            font-size: 1rem;
            width: 100%;
            transition: transform 0.15s, box-shadow 0.2s;
        }

        .login-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(90, 16, 172, 0.3);
            color: #fff;
        }

        .login-forgot {
            color: #5A10AC;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
        }

        .login-forgot:hover {
            text-decoration: underline;
            color: #9E136D;
        }

        /* Decorative dots */
        .brand-dots {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-top: 2.5rem;
        }

        .brand-dots span {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
        }

        .brand-dots span:nth-child(2) {
            background: rgba(255, 255, 255, 0.5);
        }

        .brand-dots span:nth-child(3) {
            background: rgba(255, 255, 255, 0.7);
        }

        @media (max-width: 767.98px) {
            .login-split {
                flex-direction: column;
            }

            .login-brand {
                padding: 2.5rem 1.5rem 2rem;
                min-height: auto;
            }

            .login-brand-logo {
                max-width: 150px;
                margin-bottom: 1rem;
            }

            .login-brand-tagline {
                font-size: 1.1rem;
            }

            .login-brand-sub {
                font-size: 0.85rem;
            }

            .brand-dots {
                margin-top: 1.5rem;
            }

            .login-form-side {
                padding: 2rem 1.25rem 3rem;
            }

            .login-card {
                padding: 1.5rem;
            }

            .login-form-header h2 {
                font-size: 1.5rem;
            }
        }
    </style>

    <div class="login-split">
        {{-- Left: Brand Pane --}}
        <div class="login-brand">
            <div class="login-brand-content">
                <a href="{{ route('index') }}">
                    <img src="{{ asset('images/badabing-logo.webp') }}" alt="Badabing Logo" class="login-brand-logo">
                </a>
                <div class="login-brand-tagline d-none d-lg-block">
                    Die interaktive<br>Live Game Show
                </div>
                <div class="login-brand-sub d-none d-lg-block">
                    Quiz, Challenges & Preise – live im Browser.
                </div>
                <div class="brand-dots">
                    <span></span><span></span><span></span>
                </div>
            </div>
        </div>

        {{-- Right: Login Form --}}
        <div class="login-form-side">
            <div class="login-form-wrapper">
                @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="login-form-header">
                    <h2>{{ __('Willkommen zurück') }}</h2>
                    <p>{{ __('Melde dich an, um fortzufahren') }}</p>
                </div>

                <div class="login-card">
                    <form method="POST" action="{{ route('login') }}" autocomplete="off">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('Email') }}</label>
                            <input id="email" type="email"
                                class="form-control @error('email') is-invalid @enderror" name="email"
                                value="{{ old('email') }}" required autofocus autocomplete="username"
                                placeholder="name@example.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">{{ __('Passwort') }}</label>
                            <input id="password" type="password"
                                class="form-control @error('password') is-invalid @enderror" name="password" required
                                autocomplete="current-password" placeholder="••••••••">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
                                <label class="form-check-label" for="remember_me" style="font-size:0.85rem;">
                                    {{ __('Angemeldet bleiben') }}
                                </label>
                            </div>
                            @if (Route::has('password.request'))
                                <a class="login-forgot" href="{{ route('password.request') }}">
                                    {{ __('Passwort vergessen?') }}
                                </a>
                            @endif
                        </div>

                        <button type="submit" class="btn login-btn">
                            {{ __('Anmelden') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
