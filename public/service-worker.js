importScripts("https://js.pusher.com/beams/service-worker.js");

const CACHE_VERSION = 'v1';
const CACHE_NAME = 'app-cache-' + CACHE_VERSION;

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((name) => {
                    if (name !== CACHE_NAME) {
                        return caches.delete(name);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

self.addEventListener('push', (event) => {
    let options = {
        body: event.data.text(),
        icon: '/img/192x192.png',
        badge: '/icons/badge-72x72.png'
    };

    event.waitUntil(
        self.registration.showNotification('New Notification', options)
    );
});

self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    // Skip non-GET requests
    if (event.request.method !== 'GET') {
        return;
    }

    // Skip Minio/S3 host
    if (url.hostname === 'herramientas-minio.z55ugh.easypanel.host') {
        return;
    }

    // Skip Livewire, API, and dynamic requests
    if (url.pathname.startsWith('/livewire') || url.pathname.startsWith('/api')) {
        return;
    }

    // Vite hashed assets (e.g. /build/assets/app-BLvSENOF.js) — cache-first since hash changes on rebuild
    if (url.pathname.startsWith('/build/assets/')) {
        event.respondWith(
            caches.match(event.request).then((cached) => {
                if (cached) {
                    return cached;
                }
                return fetch(event.request).then((response) => {
                    if (response && response.status === 200) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(event.request, clone);
                        });
                    }
                    return response;
                });
            })
        );
        return;
    }

    // Static assets (images, fonts, vendor JS) — network-first with cache fallback
    const isStaticAsset = /\.(png|jpg|jpeg|gif|svg|ico|woff2?|ttf|eot)$/.test(url.pathname)
        || url.pathname.startsWith('/vendor/');

    if (isStaticAsset) {
        event.respondWith(
            fetch(event.request).then((response) => {
                if (response && response.status === 200 && response.type === 'basic') {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, clone);
                    });
                }
                return response;
            }).catch(() => caches.match(event.request))
        );
        return;
    }

    // Navigation & HTML — always network-first, no caching
    event.respondWith(
        fetch(event.request).catch(() => caches.match(event.request))
    );
});
