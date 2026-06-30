// Service worker for Badabing PWA installability and web push notifications.
// v2 — lifecycle handlers for install; push + notificationclick unchanged.

self.addEventListener("install", function (event) {
    self.skipWaiting();
});

self.addEventListener("activate", function (event) {
    event.waitUntil(clients.claim());
});

self.addEventListener("push", function (event) {
    if (!event.data) return;

    // The server sends a JSON payload: { title, body, url, data }.
    let payload = {};
    try {
        payload = event.data.json();
    } catch (e) {
        // Fallback for plain-text payloads.
        payload = { title: "Badabing", body: event.data.text() };
    }

    // Where the user should land when they click the notification.
    const targetUrl =
        payload.url || (payload.data && payload.data.url) || "/";

    event.waitUntil(
        self.registration.showNotification(payload.title || "Badabing", {
            body: payload.body || "",
            icon: "/assets/images/badabing-logo.png",
            badge: "/assets/images/favicon.png",
            // Stash the destination so notificationclick can read it back.
            data: { url: targetUrl },
            // Re-using a tag keeps repeated alerts from stacking endlessly.
            tag: (payload.data && payload.data.tag) || "badabing-notification",
            renotify: true,
        })
    );
});

self.addEventListener("notificationclick", function (event) {
    event.notification.close();

    const targetUrl =
        (event.notification.data && event.notification.data.url) || "/";

    // If a Badabing tab is already open, focus it instead of opening a new one.
    event.waitUntil(
        clients
            .matchAll({ type: "window", includeUncontrolled: true })
            .then(function (clientList) {
                for (const client of clientList) {
                    if ("focus" in client && client.url.indexOf(targetUrl) !== -1) {
                        return client.focus();
                    }
                }
                if (clients.openWindow) {
                    return clients.openWindow(targetUrl);
                }
            })
    );
});
