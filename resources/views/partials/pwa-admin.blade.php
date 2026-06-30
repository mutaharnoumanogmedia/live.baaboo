{{--
    Admin PWA: manifest, meta tags, scoped service worker, install prompt.
    Scoped to /admin/ — separate from the public Badabing PWA.
--}}

<link rel="manifest" href="{{ asset('/admin/manifest.webmanifest') }}">
<meta name="theme-color" content="#DC2626">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="Badabing Admin">
<link rel="apple-touch-icon" href="{{ asset('/admin/icons/apple-touch-icon.png') }}">

<script>
    (function () {
        'use strict';

        if (!('serviceWorker' in navigator)) {
            return;
        }

        window.baabooAdminServiceWorkerReady = navigator.serviceWorker.register(
            @json(asset('/admin/sw.js')),
            { scope: "{{ ('/admin') }}/" }
        ).catch(function (error) {
            console.warn('Admin service worker registration failed:', error);
        });
    })();
</script>

@push('styles')
<style>
    #baabooAdminPwaBanner {
        position: fixed;
        left: 50%;
        bottom: 18px;
        transform: translateX(-50%) translateY(150%);
        z-index: 2147482999;
        width: calc(100% - 24px);
        max-width: 460px;
        background: #ffffff;
        color: #1f2937;
        border-radius: 18px;
        box-shadow: 0 12px 40px rgba(220, 38, 38, 0.22);
        padding: 16px 18px;
        display: flex;
        gap: 14px;
        align-items: flex-start;
        font-family: 'Nunito', system-ui, sans-serif;
        opacity: 0;
        transition: transform .35s ease, opacity .35s ease;
        border: 1px solid rgba(220, 38, 38, 0.15);
    }

    #baabooAdminPwaBanner.is-visible {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
    }

    #baabooAdminPwaBanner .baaboo-admin-pwa-icon {
        flex: 0 0 auto;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #DC2626;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    #baabooAdminPwaBanner .baaboo-admin-pwa-title {
        font-weight: 800;
        font-size: 1rem;
        margin: 0 0 2px;
        line-height: 1.2;
        color: #DC2626;
    }

    #baabooAdminPwaBanner .baaboo-admin-pwa-text {
        font-size: .85rem;
        margin: 0 0 10px;
        color: #4b5563;
    }

    #baabooAdminPwaBanner .baaboo-admin-pwa-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    #baabooAdminPwaBanner .baaboo-admin-pwa-install {
        background: #DC2626;
        color: #fff;
        border: none;
        font-weight: 800;
        border-radius: 50px;
        padding: 8px 18px;
        font-size: .85rem;
        cursor: pointer;
    }

    #baabooAdminPwaBanner .baaboo-admin-pwa-install:disabled {
        opacity: .7;
        cursor: default;
    }

    #baabooAdminPwaBanner .baaboo-admin-pwa-later {
        background: transparent;
        color: #6b7280;
        border: none;
        font-weight: 700;
        font-size: .85rem;
        cursor: pointer;
        padding: 8px 12px;
    }

    #baabooAdminPwaBanner .baaboo-admin-pwa-close {
        position: absolute;
        top: 8px;
        right: 12px;
        background: none;
        border: none;
        font-size: 1.1rem;
        line-height: 1;
        color: #9ca3af;
        cursor: pointer;
    }
</style>
@endpush

@push('scripts')
<div id="baabooAdminPwaBanner" role="dialog" aria-live="polite" aria-label="Admin-App installieren" hidden>
    <button type="button" class="baaboo-admin-pwa-close" id="baabooAdminPwaClose" aria-label="Schließen">&times;</button>
    <div class="baaboo-admin-pwa-icon" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 16 16" fill="currentColor">
            <path d="M8.5 1.5A1.5 1.5 0 0 0 7 3v1H3.5A1.5 1.5 0 0 0 2 5.5v7A1.5 1.5 0 0 0 3.5 14h9a1.5 1.5 0 0 0 1.5-1.5v-7A1.5 1.5 0 0 0 12.5 4H9V3a1.5 1.5 0 0 0-1.5-1.5zM8 4.5a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5z"/>
        </svg>
    </div>
    <div>
        <p class="baaboo-admin-pwa-title">Badabing Admin installieren</p>
        <p class="baaboo-admin-pwa-text">
            Füge das Admin-Panel deinem Startbildschirm hinzu und öffne das Dashboard mit einem Tipp.
        </p>
        <div class="baaboo-admin-pwa-actions">
            <button type="button" class="baaboo-admin-pwa-install" id="baabooAdminPwaInstall">
                Installieren
            </button>
            <button type="button" class="baaboo-admin-pwa-later" id="baabooAdminPwaLater">
                Vielleicht später
            </button>
        </div>
    </div>
</div>

<script>
    (function () {
        'use strict';

        var DISMISS_KEY = 'baaboo_admin_pwa_install_dismissed_at';
        var DISMISS_TTL_MS = 30 * 24 * 60 * 60 * 1000;
        var deferredPrompt = null;

        var banner = document.getElementById('baabooAdminPwaBanner');
        var installBtn = document.getElementById('baabooAdminPwaInstall');
        var laterBtn = document.getElementById('baabooAdminPwaLater');
        var closeBtn = document.getElementById('baabooAdminPwaClose');

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

        window.baabooAdminInstallPwa = function () {
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
                window.baabooAdminInstallPwa().finally(function () {
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
