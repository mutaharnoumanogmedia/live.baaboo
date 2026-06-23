// Service worker for Badabing Admin PWA.
// Script lives under /assets/admin/; scope /admin/ is allowed via Service-Worker-Allowed header.

self.addEventListener("install", function (event) {
    self.skipWaiting();
});

self.addEventListener("activate", function (event) {
    event.waitUntil(clients.claim());
});
