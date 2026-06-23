@props(['liveShow'])

@php
    use App\Models\LiveShow;
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Auth;

    $updateMessage = '';
    $nextScheduledLiveShow = null;

    if ($liveShow->status == 'completed') {
        $nextScheduledLiveShow = LiveShow::where('status', 'scheduled')
            ->orderBy('scheduled_at', 'asc')
            ->notTestShow()
            ->first();
        if ($nextScheduledLiveShow) {
            $updateMessage =
                'Die Show ist vorbei – danke fürs Mitmachen! Die nächste Runde startet am ' .
                Carbon::parse($nextScheduledLiveShow->scheduled_at)->locale('de')->translatedFormat('d.F Y \u\m H:i') .
                ' Uhr.';
        }
    }

    if ($liveShow->status == 'scheduled') {
        $updateMessage =
            'Die Show ist geplant und startet am ' .
            Carbon::parse($liveShow->scheduled_at)->locale('de')->translatedFormat('d.F Y \u\m H:i') .
            ' Uhr.';
    }

    $countdownTarget =
        $liveShow->status === 'scheduled' ? $liveShow->scheduled_at : $nextScheduledLiveShow?->scheduled_at;

    $prize = $liveShow->winnerPrizes->first()->prize ?? 'n/a';

    $waitingCount = $liveShow->users()->count();
    if ($waitingCount >= 1000) {
        $waitingLabel = number_format($waitingCount / 1000, $waitingCount >= 10000 ? 0 : 1) . 'K';
    } else {
        $waitingLabel = number_format($waitingCount);
    }

    $eligibleLabel =
        !$liveShow->max_players || $liveShow->max_players <= 0 ? 'ALLE' : number_format($liveShow->max_players);

   

    

@endphp

<div class="waiting-lobby" id="waitingLobby"
    @if ($countdownTarget) data-countdown-target="{{ $countdownTarget->toIso8601String() }}" @endif>
    <div class="waiting-lobby__inner">
        {{-- Header --}}
        <header class="waiting-lobby__header">
            <div class="waiting-lobby__brand">
                <img src="{{ asset('/images/badabing-logo.webp') }}" alt="Badabing Show" class="waiting-lobby__brand-logo">
                <span class="waiting-lobby__brand-text"> </span>
            </div>
            <div class="waiting-lobby__wallet" title="Dein Guthaben">
                <i class="fas fa-coins waiting-lobby__wallet-icon" aria-hidden="true"></i>
                <span class="waiting-lobby__wallet-amount">{{ $prize }}</span>
            </div>
        </header>

        @if ($updateMessage)
            <p class="waiting-lobby__status-message">{{ $updateMessage }}</p>
        @endif

        {{-- Countdown --}}
        <section class="waiting-lobby__countdown-section" aria-label="Countdown bis zum nächsten Spiel">
            <div class="waiting-lobby__ring">
                <div class="waiting-lobby__ring-outer"></div>
                <div class="waiting-lobby__ring-inner">
                    <span class="waiting-lobby__countdown-label">NÄCHSTES SPIEL IN</span>
                    <span class="waiting-lobby__countdown-timer" id="waitingLobbyCountdown">--:--:--</span>
                </div>
            </div>
             
        </section>

        {{-- Stats cards --}}
        <section class="waiting-lobby__stats" aria-label="Spielstatistik">
            <div class="waiting-lobby__stat-card">
                <i class="fas fa-trophy waiting-lobby__stat-icon" aria-hidden="true"></i>
                <span class="waiting-lobby__stat-label">PREISPOOL</span>
                <span class="waiting-lobby__stat-value">{{ $prize }}</span>
            </div>
            <div class="waiting-lobby__stat-card">
                <i class="fas fa-user-friends waiting-lobby__stat-icon" aria-hidden="true"></i>
                <span class="waiting-lobby__stat-label">WARTEN</span>
                <span class="waiting-lobby__stat-value waiting-lobby__stat-value--accent">
                <span class="waiting-lobby__waiting-dot" aria-hidden="true"></span>
                    
                    {{ $waitingLabel }}</span>
            </div>
        </section>

        {{-- CTA --}}
        <section class="waiting-lobby__cta-section">
            <button type="button" class="waiting-lobby__notify-btn" id="waitingLobbyNotifyBtn">
                <i class="fas fa-bell" aria-hidden="true"></i>
                <span class="waiting-lobby__notify-label">Benachrichtigung an</span>
            </button>
            {{-- <p class="waiting-lobby__notify-caption">Du kriegst 2 Minuten vor Start Bescheid.</p> --}}
        </section>

    <p class="waiting-lobby__info" style="text-align:center; margin-top: 1rem;">
        Für mehr Informationen besuche <a href="https://badabing.show" target="_blank" rel="noopener" style="color: var(--wl-accent); text-decoration: underline;">badabing.show</a>
    </p>


    </div>

    {{-- Bottom toolbar --}}
    <nav class="waiting-lobby__toolbar" aria-label="Lobby-Aktionen">

        <button type="button" class="waiting-lobby__toolbar-btn" aria-label="Teilen" id="waitingLobbyShareBtn">
            <i class="fas fa-share-alt" aria-hidden="true"></i>
        </button>
        <button type="button" class="waiting-lobby__toolbar-btn" aria-label="Schließen"
            onclick="window.location.href='{{ url('/') }}'">
            <i class="fas fa-times" aria-hidden="true"></i>
        </button>
    </nav>
</div>

@once
    <style>
        .waiting-lobby {
            --wl-bg-deep: #1a0a2e;
            --wl-bg-mid: #2d1457;
            --wl-card: #3a1a6e;
            --wl-accent: #ff5f00;
            --wl-white: #ffffff;
            --wl-muted: #a89bc4;
            --wl-purple-glow: rgba(200, 224, 0, 0.12);

            min-height: 100dvh;
            background: linear-gradient(180deg, var(--wl-bg-deep) 0%, var(--wl-bg-mid) 100%);
            color: var(--wl-white);
            font-family: 'Outfit', system-ui, sans-serif;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        .waiting-lobby__brand-logo {
            width: 100px;
            height: 100px;
        }

        .waiting-lobby__inner {
            flex: 1;
            width: 100%;
            max-width: 480px;
            margin: 0 auto;
            padding: 1.25rem 1rem 5.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .waiting-lobby__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .waiting-lobby__brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .waiting-lobby__brand-icon {
            color: var(--wl-accent);
            font-size: 1.1rem;
        }

        .waiting-lobby__brand-text {
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: 0.08em;
            color: var(--wl-accent);
        }

        .waiting-lobby__wallet {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(0, 0, 0, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 999px;
            padding: 0.35rem 0.75rem;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .waiting-lobby__wallet-icon {
            color: #f5c842;
            font-size: 0.8rem;
        }

        .waiting-lobby__status-message {
            margin: 0;
            font-size: 0.82rem;
            line-height: 1.45;
            color: var(--wl-muted);
            text-align: center;
            padding: 0 0.25rem;
        }

        .waiting-lobby__countdown-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.85rem;
            padding: 0.5rem 0;
        }

        .waiting-lobby__ring {
            position: relative;
            width: min(72vw, 260px);
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .waiting-lobby__ring-outer,
        .waiting-lobby__ring-inner {
            position: absolute;
            border-radius: 50%;
            border: 2px solid rgba(200, 224, 0, 0.25);
        }

        .waiting-lobby__ring-outer {
            inset: 0;
            box-shadow: 0 0 40px var(--wl-purple-glow);
        }

        .waiting-lobby__ring-inner {
            inset: 14%;
            border-color: rgba(255, 255, 255, 0.12);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            background: radial-gradient(circle at center, rgba(58, 26, 110, 0.6) 0%, transparent 70%);
        }

        .waiting-lobby__countdown-label {
            font-size: 0.68rem;
            letter-spacing: 0.14em;
            color: var(--wl-muted);
            font-weight: 600;
        }

        .waiting-lobby__countdown-timer {
            font-size: clamp(1.15rem, 5.5vw, 1.55rem);
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: 0.02em;
            font-variant-numeric: tabular-nums;
            text-align: center;
            white-space: nowrap;
        }

        .waiting-lobby__waiting-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 999px;
            padding: 0.35rem 0.85rem;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            color: var(--wl-muted);
        }

        .waiting-lobby__waiting-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #3ddc84;
            box-shadow: 0 0 8px rgba(61, 220, 132, 0.7);
            animation: wl-pulse 1.8s ease-in-out infinite;
        }

        @keyframes wl-pulse {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: 0.6;
                transform: scale(0.85);
            }
        }

        .waiting-lobby__stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }

        .waiting-lobby__stat-card {
            background: var(--wl-card);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            padding: 0.85rem 0.75rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.25rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .waiting-lobby__stat-icon {
            color: var(--wl-accent);
            font-size: 1rem;
            margin-bottom: 0.15rem;
        }

        .waiting-lobby__stat-label {
            font-size: 0.62rem;
            letter-spacing: 0.1em;
            color: var(--wl-muted);
            font-weight: 600;
        }

        .waiting-lobby__stat-value {
            font-size: 1.15rem;
            font-weight: 700;
        }

        .waiting-lobby__stat-value--accent {
            color: var(--wl-accent);
        }

        .waiting-lobby__cta-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .waiting-lobby__notify-btn {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            background: var(--wl-accent);
            color: #0a0612;
            border: none;
            border-radius: 14px;
            padding: 0.95rem 1.25rem;
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            box-shadow: 0 4px 24px rgba(200, 224, 0, 0.25);
        }

        .waiting-lobby__notify-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 28px rgba(200, 224, 0, 0.35);
        }

        .waiting-lobby__notify-btn:active {
            transform: translateY(0);
        }

        .waiting-lobby__notify-btn:disabled {
            cursor: default;
        }

        .waiting-lobby__notify-btn--subscribed {
            background: rgba(61, 220, 132, 0.2);
            color: #3ddc84;
            border: 1px solid rgba(61, 220, 132, 0.45);
            box-shadow: none;
        }

        .waiting-lobby__notify-btn--subscribed:hover {
            transform: none;
            box-shadow: none;
        }

        .waiting-lobby__notify-caption {
            margin: 0;
            font-size: 0.72rem;
            color: var(--wl-muted);
            text-align: center;
        }

        .waiting-lobby__chat {
            flex: 1;
            min-height: 0;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 16px;
            padding: 0.85rem;
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
        }

        .waiting-lobby__chat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
        }

        .waiting-lobby__chat-title {
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.08em;
        }

        .waiting-lobby__chat-subtitle {
            font-size: 0.65rem;
            letter-spacing: 0.06em;
            color: var(--wl-muted);
            font-weight: 600;
        }

        .waiting-lobby__chat-list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 0.55rem;
            overflow-y: auto;
            max-height: 180px;
        }

        .waiting-lobby__chat-item {
            font-size: 0.82rem;
            line-height: 1.4;
        }

        .waiting-lobby__chat-user {
            font-weight: 700;
        }

        .waiting-lobby__chat-user--purple {
            color: #b794f6;
        }

        .waiting-lobby__chat-user--accent {
            color: var(--wl-accent);
        }

        .waiting-lobby__chat-sep {
            color: var(--wl-muted);
            margin-right: 0.2rem;
        }

        .waiting-lobby__chat-message {
            color: rgba(255, 255, 255, 0.88);
        }

        .waiting-lobby__toolbar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            display: flex;
            align-items: center;
            justify-content: space-around;
            gap: 0.5rem;
            background: rgba(10, 4, 20, 0.92);
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding: 0.65rem 1rem calc(0.65rem + env(safe-area-inset-bottom, 0px));
            backdrop-filter: blur(12px);
            z-index: 100;
        }

        .waiting-lobby__toolbar-btn {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: none;
            background: transparent;
            color: var(--wl-muted);
            font-size: 1.1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: color 0.15s ease, background 0.15s ease;
        }

        .waiting-lobby__toolbar-btn:hover {
            color: var(--wl-white);
        }

        .waiting-lobby__toolbar-btn--active {
            background: var(--wl-accent);
            color: #0a0612;
            box-shadow: 0 0 16px rgba(200, 224, 0, 0.4);
        }

        @media (min-width: 481px) {
            .waiting-lobby__inner {
                padding-top: 1.75rem;
            }
        }
    </style>

    <script>
        (function() {
            'use strict';

            var lobby = document.getElementById('waitingLobby');
            if (!lobby) return;

            var countdownEl = document.getElementById('waitingLobbyCountdown');
            var targetIso = lobby.dataset.countdownTarget;
            var countdownInterval = null;

            function pad(n) {
                return String(n).padStart(2, '0');
            }

            function formatCountdown(totalSec) {
                var days = Math.floor(totalSec / 86400);
                var hours = Math.floor((totalSec % 86400) / 3600);
                var minutes = Math.floor((totalSec % 3600) / 60);
                var seconds = totalSec % 60;

                if (totalSec >= 86400) {
                    return days + 'T ' + pad(hours) + 'Std ' + pad(minutes) + 'Min ' + pad(seconds) + 'Sek';
                }
                if (totalSec >= 3600) {
                    return pad(hours) + ':' + pad(minutes) + ':' + pad(seconds);
                }
                if (totalSec >= 60) {
                    return pad(minutes) + ':' + pad(seconds);
                }
                return pad(seconds);
            }

            function updateCountdown() {
                if (!countdownEl || !targetIso) {
                    if (countdownEl) countdownEl.textContent = '--:--:--';
                    return;
                }

                var diffMs = new Date(targetIso).getTime() - Date.now();
                if (diffMs <= 0) {
                    countdownEl.textContent = 'Startet gleich';
                    if (countdownInterval) clearInterval(countdownInterval);
                    return;
                }

                var totalSec = Math.floor(diffMs / 1000);
                countdownEl.textContent = formatCountdown(totalSec);
            }

            updateCountdown();
            if (targetIso) {
                countdownInterval = setInterval(updateCountdown, 1000);
            }

            var notifyBtn = document.getElementById('waitingLobbyNotifyBtn');
            var notifyLabel = notifyBtn ? notifyBtn.querySelector('.waiting-lobby__notify-label') : null;
            var notifyIcon = notifyBtn ? notifyBtn.querySelector('i') : null;

            function pushSupported() {
                return ('serviceWorker' in navigator) &&
                    ('PushManager' in window) &&
                    ('Notification' in window);
            }

            function isPushSubscribed() {
                if (!pushSupported() || Notification.permission !== 'granted') {
                    return Promise.resolve(false);
                }

                return navigator.serviceWorker.ready
                    .then(function(registration) {
                        return registration.pushManager.getSubscription();
                    })
                    .then(function(subscription) {
                        return !!subscription;
                    })
                    .catch(function() {
                        return false;
                    });
            }

            function setNotifySubscribed(subscribed) {
                if (!notifyBtn) return;

                if (subscribed) {
                    notifyBtn.disabled = true;
                    notifyBtn.classList.add('waiting-lobby__notify-btn--subscribed');
                    notifyBtn.setAttribute('aria-pressed', 'true');
                    if (notifyLabel) notifyLabel.textContent = 'Benachrichtigung abonniert';
                    if (notifyIcon) notifyIcon.className = 'fas fa-check';
                } else {
                    notifyBtn.disabled = false;
                    notifyBtn.classList.remove('waiting-lobby__notify-btn--subscribed');
                    notifyBtn.setAttribute('aria-pressed', 'false');
                    if (notifyLabel) notifyLabel.textContent = 'Benachrichtigung an';
                    if (notifyIcon) notifyIcon.className = 'fas fa-bell';
                }
            }

            function updateNotifyButtonState() {
                isPushSubscribed().then(function(subscribed) {
                    setNotifySubscribed(subscribed);
                });
            }

            function requestPushEnable() {
                if (typeof window.baabooEnablePush === 'function') {
                    return window.baabooEnablePush();
                }

                return new Promise(function(resolve) {
                    var attempts = 0;
                    var timer = setInterval(function() {
                        attempts++;
                        if (typeof window.baabooEnablePush === 'function') {
                            clearInterval(timer);
                            window.baabooEnablePush().then(resolve);
                        } else if (attempts >= 30) {
                            clearInterval(timer);
                            resolve(false);
                        }
                    }, 100);
                });
            }

            if (notifyBtn) {
                notifyBtn.addEventListener('click', function() {
                    if (notifyBtn.disabled || notifyBtn.classList.contains('waiting-lobby__notify-btn--subscribed')) {
                        return;
                    }

                    notifyBtn.disabled = true;
                    if (notifyLabel) notifyLabel.textContent = 'Wird aktiviert…';

                    requestPushEnable().then(function(ok) {
                        if (ok) {
                            setNotifySubscribed(true);
                            return;
                        }

                        notifyBtn.disabled = false;
                        if (notifyLabel) notifyLabel.textContent = 'Benachrichtigung an';

                        var pushBanner = document.getElementById('baabooPushBanner');
                        if (pushBanner) {
                            pushBanner.classList.add('is-visible');
                        }
                    });
                });

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', updateNotifyButtonState);
                } else {
                    updateNotifyButtonState();
                }
            }

            var shareBtn = document.getElementById('waitingLobbyShareBtn');
            if (shareBtn && navigator.share) {
                shareBtn.addEventListener('click', function() {
                    navigator.share({
                        title: document.title,
                        url: window.location.href
                    }).catch(function() {});
                });
            }
        })
        ();
    </script>
@endonce
