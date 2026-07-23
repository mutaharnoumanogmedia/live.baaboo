{{--
    Reusable Web-Push opt-in for visitors (German copy).

    Drop this partial into any guest-facing page (e.g. index / live-show).
    It renders a friendly bottom banner that convinces the visitor to enable
    browser notifications, then registers the service worker and stores the
    push subscription on the server via /api/push/subscribe.

    The whole script lives inside an IIFE and uses unique IDs, so it is safe to
    include alongside other page scripts without clashing with global variables.
--}}

<style>
    /* Slide-up opt-in banner shown at the bottom of the screen. */
    #baabooPushBanner {
        position: fixed;
        left: 50%;
        bottom: 18px;
        transform: translateX(-50%) translateY(150%);
        z-index: 2147483000;
        width: calc(100% - 24px);
        max-width: 460px;
        background: #ffffff;
        color: #140b63;
        border-radius: 18px;
        box-shadow: 0 12px 40px rgba(20, 11, 99, 0.22);
        padding: 16px 18px;
        display: flex;
        gap: 14px;
        align-items: flex-start;
        font-family: 'Nunito', system-ui, sans-serif;
        opacity: 0;
        transition: transform .35s ease, opacity .35s ease;
    }

    #baabooPushBanner.is-visible {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
    }

    #baabooPushBanner .baaboo-push-bell {
        flex: 0 0 auto;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: linear-gradient(135deg, #5A10AC, #FC6902);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    #baabooPushBanner .baaboo-push-title {
        font-weight: 800;
        font-size: 1rem;
        margin: 0 0 2px;
        line-height: 1.2;
    }

    #baabooPushBanner .baaboo-push-text {
        font-size: .85rem;
        margin: 0 0 10px;
        color: #4b4070;
    }

    #baabooPushBanner .baaboo-push-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    #baabooPushBanner .baaboo-push-allow {
        background: #FC6902;
        color: #fff;
        border: none;
        font-weight: 800;
        border-radius: 50px;
        padding: 8px 18px;
        font-size: .85rem;
        cursor: pointer;
    }

    #baabooPushBanner .baaboo-push-allow:disabled {
        opacity: .7;
        cursor: default;
    }

    #baabooPushBanner .baaboo-push-later {
        background: transparent;
        color: #6b6080;
        border: none;
        font-weight: 700;
        font-size: .85rem;
        cursor: pointer;
        padding: 8px 12px;
    }

    #baabooPushBanner .baaboo-push-close {
        position: absolute;
        top: 8px;
        right: 12px;
        background: none;
        border: none;
        font-size: 1.1rem;
        line-height: 1;
        color: #b3acc7;
        cursor: pointer;
    }
</style>
@if (!request()->boolean('debug_bot'))
    <div id="baabooPushBanner" role="dialog" aria-live="polite" aria-label="Benachrichtigungen aktivieren">
        <button type="button" class="baaboo-push-close" id="baabooPushClose" aria-label="Schließen">&times;</button>
        <div class="baaboo-push-bell" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 16 16"
                fill="currentColor">
                <path
                    d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zm.995-14.901a1 1 0 1 0-1.99 0A5.002 5.002 0 0 0 3 6c0 1.098-.5 6-2 7h14c-1.5-1-2-5.902-2-7 0-2.42-1.72-4.44-4.005-4.901z" />
            </svg>
        </div>
        <div>
            <p class="baaboo-push-title">Verpasse keine Game-Show mehr!</p>
            <p class="baaboo-push-text">
                Aktiviere Benachrichtigungen und wir erinnern dich rechtzeitig,
                bevor die nächste Live-Show startet &ndash; so sicherst du dir deine Gewinnchance.
            </p>
            <div class="baaboo-push-actions">
                <button type="button" class="baaboo-push-allow" id="baabooPushAllow">
                    Benachrichtigungen aktivieren
                </button>
                <button type="button" class="baaboo-push-later" id="baabooPushLater">
                    Vielleicht später
                </button>
            </div>
        </div>
    </div>
@endif
<script>
    (function() {
        'use strict';

        // --- Configuration injected from the server ----------------------------
        var VAPID_PUBLIC_KEY = @json(env('VAPID_PUBLIC_KEY'));
        var SUBSCRIBE_URL = @json(url('/api/push/subscribe'));
        var CSRF_TOKEN = @json(csrf_token());

        // Key used to remember that the visitor dismissed the banner, so we do
        // not nag them on every page load.
        var DISMISS_KEY = 'baaboo_push_dismissed_at';
        // How long a "later" dismissal is respected before we ask again (7 days).
        var DISMISS_TTL_MS = 7 * 24 * 60 * 60 * 1000;

        var banner = document.getElementById('baabooPushBanner');
        var allowBtn = document.getElementById('baabooPushAllow');
        var laterBtn = document.getElementById('baabooPushLater');
        var closeBtn = document.getElementById('baabooPushClose');

        // Bail out early if the browser cannot do web push at all.
        function pushSupported() {
            return ('serviceWorker' in navigator) &&
                ('PushManager' in window) &&
                ('Notification' in window);
        }

        function showBanner() {
            if (!banner) return;
            // Force a reflow before adding the class so the transition runs.
            void banner.offsetWidth;
            banner.classList.add('is-visible');
        }

        function hideBanner() {
            if (!banner) return;
            banner.classList.remove('is-visible');
        }

        function rememberDismissal() {
            try {
                localStorage.setItem(DISMISS_KEY, String(Date.now()));
            } catch (e) {
                /* localStorage may be unavailable in private mode; ignore. */
            }
        }

        function recentlyDismissed() {
            try {
                var ts = parseInt(localStorage.getItem(DISMISS_KEY) || '0', 10);
                return ts > 0 && (Date.now() - ts) < DISMISS_TTL_MS;
            } catch (e) {
                return false;
            }
        }

        // Convert the base64 VAPID public key into the Uint8Array the Push API
        // expects as the applicationServerKey.
        function urlBase64ToUint8Array(base64String) {
            var padding = '='.repeat((4 - base64String.length % 4) % 4);
            var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
            var rawData = atob(base64);
            var output = new Uint8Array(rawData.length);
            for (var i = 0; i < rawData.length; i++) {
                output[i] = rawData.charCodeAt(i);
            }
            return output;
        }

        // Register the SW, request permission, subscribe and persist on server.
        function enablePush() {
            if (!pushSupported()) {
                console.log('Push notifications are not supported in this browser.');
                return Promise.resolve(false);
            }
            if (!VAPID_PUBLIC_KEY) {
                console.warn('VAPID public key is missing; cannot subscribe to push.');
                return Promise.resolve(false);
            }

            return Notification.requestPermission().then(function(permission) {
                if (permission !== 'granted') {
                    console.log('Push permission was not granted:', permission);
                    return false;
                }

                var swReady = window.baabooServiceWorkerReady || navigator.serviceWorker.register(
                    @json(asset('sw.js')));
                return Promise.resolve(swReady).then(function() {
                        return navigator.serviceWorker.ready;
                    })
                    .then(function(registration) {
                        return registration.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY),
                        });
                    })
                    .then(function(subscription) {
                        return fetch(SUBSCRIBE_URL, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': CSRF_TOKEN,
                                'Accept': 'application/json',
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify(subscription),
                        });
                    })
                    .then(function() {
                        console.log('Push notifications enabled.');
                        return true;
                    });
            }).catch(function(error) {
                console.error('Failed to enable push notifications:', error);
                return false;
            });
        }

        // --- Wire up the banner buttons ----------------------------------------
        if (allowBtn) {
            allowBtn.addEventListener('click', function() {
                allowBtn.disabled = true;
                allowBtn.textContent = 'Wird aktiviert…';
                enablePush().then(function(ok) {
                    hideBanner();
                    rememberDismissal();
                });
            });
        }

        if (laterBtn) {
            laterBtn.addEventListener('click', function() {
                rememberDismissal();
                hideBanner();
            });
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                rememberDismissal();
                hideBanner();
            });
        }

        // --- Decide whether to prompt on this page load ------------------------
        function maybePrompt() {
            if (!pushSupported()) return;
            // Already granted: silently make sure the subscription is stored,
            // and never show the banner.
            if (Notification.permission === 'granted') {
                enablePush();
                return;
            }
            // The user previously blocked notifications, or recently dismissed
            // the banner: respect that and stay quiet.
            if (Notification.permission === 'denied') return;
            if (recentlyDismissed()) return;

            // Give the page a moment to settle, then slide the banner in.
            setTimeout(showBanner, 3500);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', maybePrompt);
        } else {
            maybePrompt();
        }

        // Expose a manual trigger so other buttons can open the prompt if needed.
        window.baabooEnablePush = enablePush;
    })();
</script>
