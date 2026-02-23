// Names for caches
const CACHE_NAME = 'property-inspection-v2';

// List of files to cache (add your actual file paths here)
const FILES_TO_CACHE = [
    //'/app/',
    //'/app/index.php',
    //'/app/manifest.json',
    //'/app/css/styles.css',
    //'/app/assets/js/app.js',
    '/app/assets/icons/favicon.ico',
    '/app/assets/icons/web-app-manifest-192x192.png',
    '/app/assets/icons/web-app-manifest-512x512.png'
];

// Install event — cache files
self.addEventListener('install', (event) => {
    console.log('[ServiceWorker] Install');
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[ServiceWorker] Caching app shell');
                return cache.addAll(FILES_TO_CACHE);
            })
    );
    self.skipWaiting();
});

// Activate event — clean up old caches
self.addEventListener('activate', (event) => {
    console.log('[ServiceWorker] Activate');
    event.waitUntil(
        caches.keys().then((keyList) => {
            return Promise.all(keyList.map((key) => {
                if (key !== CACHE_NAME) {
                    console.log('[ServiceWorker] Removing old cache:', key);
                    return caches.delete(key);
                }
            }));
        })
    );
    self.clients.claim();
});

// Fetch event — serve cached files if offline
self.addEventListener('fetch', (event) => {
    console.log('[ServiceWorker] Fetch', event.request.url);
    event.respondWith(
        caches.match(event.request)
            .then((response) => {
                // Return from cache if found, else fetch from network
                return response || fetch(event.request);
            })
    );
});