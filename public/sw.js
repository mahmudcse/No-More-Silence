/**
 * Service Worker for No More Silence PWA
 * Provides offline resilience, Stale-While-Revalidate caching for civic statistics,
 * and background asset caching for mobile devices.
 */

const CACHE_NAME = 'nms-pwa-v1';
const STATS_CACHE = 'nms-stats-v1';

const PRECACHE_ASSETS = [
    '/offline.html',
    '/manifest.webmanifest',
    '/icons/icon.svg',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS);
        }).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((name) => {
                    if (name !== CACHE_NAME && name !== STATS_CACHE) {
                        return caches.delete(name);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Only handle same-origin GET requests
    if (request.method !== 'GET' || url.origin !== self.location.origin) {
        return;
    }

    // Skip caching for auth, internal dashboard, filament, or dynamic submissions
    if (
        url.pathname.startsWith('/admin') ||
        url.pathname.startsWith('/dashboard') ||
        url.pathname.startsWith('/api/v1/auth') ||
        url.pathname.startsWith('/api/v1/me') ||
        url.pathname.startsWith('/api/v1/submissions') ||
        url.pathname.startsWith('/api/v1/appeals')
    ) {
        return;
    }

    // 1. Navigation requests (HTML pages) -> Network first, offline.html fallback
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => {
                return caches.match('/offline.html');
            })
        );
        return;
    }

    // 2. Static Stats API -> Stale-While-Revalidate
    if (url.pathname.startsWith('/api/v1/stats') || url.pathname.startsWith('/api/v1/districts')) {
        event.respondWith(
            caches.open(STATS_CACHE).then((cache) => {
                return cache.match(request).then((cachedResponse) => {
                    const fetchPromise = fetch(request).then((networkResponse) => {
                        if (networkResponse && networkResponse.status === 200) {
                            cache.put(request, networkResponse.clone());
                        }
                        return networkResponse;
                    }).catch(() => cachedResponse);

                    return cachedResponse || fetchPromise;
                });
            })
        );
        return;
    }

    // 3. Static assets (Vite build chunks, images, icons, fonts) -> Cache-first
    if (
        url.pathname.startsWith('/build/') ||
        url.pathname.startsWith('/icons/') ||
        url.pathname.endsWith('.js') ||
        url.pathname.endsWith('.css') ||
        url.pathname.endsWith('.svg') ||
        url.pathname.endsWith('.png') ||
        url.pathname.endsWith('.json')
    ) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                if (cachedResponse) {
                    return cachedResponse;
                }
                return fetch(request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const responseToCache = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(request, responseToCache);
                        });
                    }
                    return networkResponse;
                });
            })
        );
        return;
    }
});
