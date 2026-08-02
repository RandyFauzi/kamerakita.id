self.addEventListener('push', function (e) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    if (e.data) {
        var msg = e.data.json();
        e.waitUntil(self.registration.showNotification(msg.title, {
            body: msg.body,
            icon: msg.icon || '/favicon.ico',
            actions: msg.actions || [],
            data: msg.data || {}
        }));
    }
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    if (event.action) {
        // Handle action clicks
        if (event.action === 'explore') {
            clients.openWindow('/');
        }
    } else {
        // Handle normal clicks
        const urlToOpen = event.notification.data.url || '/dashboard';
        event.waitUntil(
            clients.matchAll({ type: 'window' }).then(function(windowClients) {
                // Check if there is already a window/tab open with the target URL
                for (var i = 0; i < windowClients.length; i++) {
                    var client = windowClients[i];
                    if (client.url === urlToOpen && 'focus' in client) {
                        return client.focus();
                    }
                }
                // If not, then open the target URL in a new window/tab.
                if (clients.openWindow) {
                    return clients.openWindow(urlToOpen);
                }
            })
        );
    }
});
