const CACHE_NAME = "kamerakita-pwa-v2";
const urlsToCache = [
    "/",
    "/vendor-assets/kamerakita/logo-mark.svg",
    "/images/app-icon.png"
];

self.addEventListener("install", event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(urlsToCache))
    );
    self.skipWaiting();
});

self.addEventListener("activate", event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

self.addEventListener("fetch", event => {
    if (event.request.mode === "navigate") {
        event.respondWith(
            fetch(event.request).catch(() => {
                return caches.match(event.request).then(response => {
                    return response || new Response(
                        "<html><head><title>Offline - KameraKita</title><meta name=\"viewport\" content=\"width=device-width,initial-scale=1\"><style>body{font-family:sans-serif;display:flex;flex-direction:column;align-items:center;justify-content:center;height:100vh;margin:0;background:#f8fafc;color:#334155;text-align:center;padding:1rem;}h1{color:#4f46e5;}</style></head><body><h1>Anda Sedang Offline</h1><p>Koneksi internet terputus. Silakan periksa jaringan Anda lalu muat ulang halaman untuk mengakses KameraKita.</p><button onclick=\"window.location.reload()\" style=\"margin-top:1rem;padding:0.5rem 1rem;background:#4f46e5;color:white;border:none;border-radius:0.5rem;font-weight:bold;\">Muat Ulang</button></body></html>",
                        { headers: { "Content-Type": "text/html" } }
                    );
                });
            })
        );
    } else {
        event.respondWith(
            caches.match(event.request).then(response => {
                return response || fetch(event.request);
            })
        );
    }
});
