{{--
    Progressive Web App support: manifest, meta tags, service worker registration,
    and an optional install prompt banner (German copy).
--}}

<link rel="manifest" href="{{ asset('/manifest.webmanifest') }}">
<meta name="theme-color" content="#5A10AC">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="Badabing">
<link rel="apple-touch-icon" href="{{ asset('/icons/apple-touch-icon.png') }}">

<script>
    (function () {
        'use strict';

        if (!('serviceWorker' in navigator)) {
            return;
        }

        window.baabooServiceWorkerReady = navigator.serviceWorker.register('{{ asset('sw.js') }}')
            .catch(function (error) {
                console.warn('Service worker registration failed:', error);
            });
    })();
</script>

@push('styles')
<style>
    #baabooPwaBanner {
        position: fixed;
        left: 50%;
        bottom: 18px;
        transform: translateX(-50%) translateY(150%);
        z-index: 2147482999;
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

    #baabooPwaBanner.is-visible {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
    }

    #baabooPwaBanner .baaboo-pwa-icon {
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

    #baabooPwaBanner .baaboo-pwa-title {
        font-weight: 800;
        font-size: 1rem;
        margin: 0 0 2px;
        line-height: 1.2;
    }

    #baabooPwaBanner .baaboo-pwa-text {
        font-size: .85rem;
        margin: 0 0 10px;
        color: #4b4070;
    }

    #baabooPwaBanner .baaboo-pwa-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    #baabooPwaBanner .baaboo-pwa-install {
        background: #FC6902;
        color: #fff;
        border: none;
        font-weight: 800;
        border-radius: 50px;
        padding: 8px 18px;
        font-size: .85rem;
        cursor: pointer;
    }

    #baabooPwaBanner .baaboo-pwa-install:disabled {
        opacity: .7;
        cursor: default;
    }

    #baabooPwaBanner .baaboo-pwa-later {
        background: transparent;
        color: #6b6080;
        border: none;
        font-weight: 700;
        font-size: .85rem;
        cursor: pointer;
        padding: 8px 12px;
    }

    #baabooPwaBanner .baaboo-pwa-close {
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
@endpush

@push('scripts')
<div id="baabooPwaBanner" role="dialog" aria-live="polite" aria-label="App installieren" hidden>
    <button type="button" class="baaboo-pwa-close" id="baabooPwaClose" aria-label="Schließen">&times;</button>
    <div class="baaboo-pwa-icon" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 16 16" fill="currentColor">
            <path d="M8.5 1.5A1.5 1.5 0 0 0 7 3v1H3.5A1.5 1.5 0 0 0 2 5.5v7A1.5 1.5 0 0 0 3.5 14h9a1.5 1.5 0 0 0 1.5-1.5v-7A1.5 1.5 0 0 0 12.5 4H9V3a1.5 1.5 0 0 0-1.5-1.5zM8 4.5a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5z"/>
        </svg>
    </div>
    <div>
        <p class="baaboo-pwa-title">Badabing als App installieren</p>
        <p class="baaboo-pwa-text">
            Füge Badabing deinem Startbildschirm hinzu und starte die Game Show mit einem Tipp.
        </p>
        <div class="baaboo-pwa-actions">
            <button type="button" class="baaboo-pwa-install" id="baabooPwaInstall">
                Installieren
            </button>
            <button type="button" class="baaboo-pwa-later" id="baabooPwaLater">
                Vielleicht später
            </button>
        </div>
    </div>
</div>

<script>
    (function () {
        'use strict';

        var DISMISS_KEY = 'baaboo_pwa_install_dismissed_at';
        var DISMISS_TTL_MS = 30 * 24 * 60 * 60 * 1000;
        var deferredPrompt = null;

        var banner = document.getElementById('baabooPwaBanner');
        var installBtn = document.getElementById('baabooPwaInstall');
        var laterBtn = document.getElementById('baabooPwaLater');
        var closeBtn = document.getElementById('baabooPwaClose');

        function isStandalone() {
            return window.matchMedia('(display-mode: standalone)').matches ||
                window.navigator.standalone === true;
        }

        function rememberDismissal() {
            try {
                localStorage.setItem(DISMISS_KEY, String(Date.now()));
            } catch (e) {
                /* ignore */
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

        function showBanner() {
            if (!banner) return;
            banner.hidden = false;
            void banner.offsetWidth;
            banner.classList.add('is-visible');
        }

        function hideBanner() {
            if (!banner) return;
            banner.classList.remove('is-visible');
            banner.hidden = true;
        }

        window.baabooInstallPwa = function () {
            if (!deferredPrompt) {
                return Promise.resolve(false);
            }

            deferredPrompt.prompt();
            return deferredPrompt.userChoice.then(function (choice) {
                deferredPrompt = null;
                hideBanner();
                rememberDismissal();
                return choice.outcome === 'accepted';
            });
        };

        window.addEventListener('beforeinstallprompt', function (event) {
            event.preventDefault();
            deferredPrompt = event;

            if (isStandalone() || recentlyDismissed()) {
                return;
            }

            setTimeout(showBanner, 4000);
        });

        window.addEventListener('appinstalled', function () {
            deferredPrompt = null;
            hideBanner();
        });

        if (installBtn) {
            installBtn.addEventListener('click', function () {
                installBtn.disabled = true;
                installBtn.textContent = 'Wird installiert…';
                window.baabooInstallPwa().finally(function () {
                    installBtn.disabled = false;
                    installBtn.textContent = 'Installieren';
                });
            });
        }

        if (laterBtn) {
            laterBtn.addEventListener('click', function () {
                rememberDismissal();
                hideBanner();
            });
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                rememberDismissal();
                hideBanner();
            });
        }
    })();
</script>
@endpush
