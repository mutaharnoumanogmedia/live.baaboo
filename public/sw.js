self.addEventListener("push", function (event) {
    if (!event.data) return;

    const payload = event.data.json();

    event.waitUntil(
        self.registration.showNotification(payload.title, {
            body: payload.body,
            icon: "/icon.png",
            data: payload.url,
        })
    );
});

self.addEventListener("notificationclick", function (event) {
    event.notification.close();

    event.waitUntil(clients.openWindow(event.notification.data || "/"));
});
