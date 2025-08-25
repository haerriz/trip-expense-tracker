// Haerriz Expenses Service Worker
// This makes the app work offline and load faster

const CACHE_NAME = 'haerriz-expenses-v2';
const urlsToCache = [
  '/',
  '/index.php',
  '/dashboard.php',
  '/admin.php',
  '/splash.html',
  '/css/style.css',
  '/js/dark-mode.js',
  '/js/trip-dashboard.js',
  '/js/enhanced-chat.js',
  '/js/pwa-install.js',
  '/js/admin.js',
  '/js/avatar.js',
  '/favicon.svg',
  '/manifest.json',
  // External CDN files
  'https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css',
  'https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js',
  'https://fonts.googleapis.com/icon?family=Material+Icons',
  'https://code.jquery.com/jquery-3.6.0.min.js',
  'https://cdn.jsdelivr.net/npm/chart.js'
];

// Install Service Worker - Cache files
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('Caching app files');
        return cache.addAll(urlsToCache);
      })
      .catch((error) => {
        console.log('Cache failed:', error);
      })
  );
});

// Activate Service Worker - Clean old caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            console.log('Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

// Fetch - Serve cached files when offline
self.addEventListener('fetch', (event) => {
  // Only exclude logout and session check from caching
  if (event.request.url.includes('logout.php') || 
      event.request.url.includes('check_session.php')) {
    event.respondWith(fetch(event.request));
    return;
  }

  event.respondWith(
    fetch(event.request)
      .then((response) => {
        // Clone the response for caching
        const responseClone = response.clone();
        
        // Cache successful responses
        if (response.status === 200) {
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(event.request, responseClone);
          });
        }
        
        return response;
      })
      .catch(() => {
        // If network fails, try cache
        return caches.match(event.request)
          .then((cachedResponse) => {
            if (cachedResponse) {
              return cachedResponse;
            }
            // If both fail and it's a document, show index
            if (event.request.destination === 'document') {
              return caches.match('/index.php');
            }
          });
      })
  );
});