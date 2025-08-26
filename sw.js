// Haerriz Expenses Service Worker
// This makes the app work offline and load faster

const CACHE_NAME = 'haerriz-expenses-v2';

// Push notification handling
self.addEventListener('push', (event) => {
  console.log('Push event received:', event);
  
  let notificationData = {
    title: 'Haerriz Trip Finance',
    body: 'New notification from Haerriz Expenses'
  };
  
  if (event.data) {
    try {
      const data = event.data.json();
      console.log('Push data:', data);
      notificationData.title = data.title || notificationData.title;
      notificationData.body = data.body || data.message || notificationData.body;
    } catch (e) {
      console.log('Push data as text:', event.data.text());
      notificationData.body = event.data.text();
    }
  }
  
  const options = {
    body: notificationData.body,
    icon: '/favicon.svg',
    badge: '/favicon.svg',
    vibrate: [200, 100, 200],
    requireInteraction: false,
    data: {
      dateOfArrival: Date.now(),
      url: '/dashboard.php'
    },
    actions: [
      {
        action: 'open',
        title: 'Open App'
      },
      {
        action: 'close',
        title: 'Close'
      }
    ]
  };

  event.waitUntil(
    self.registration.showNotification(notificationData.title, options)
  );
});

// Handle notification clicks
self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  if (event.action === 'open') {
    event.waitUntil(
      clients.openWindow('/dashboard.php')
    );
  } else if (event.action === 'close') {
    // Just close the notification
    return;
  } else {
    // Default action - open app
    event.waitUntil(
      clients.openWindow('/dashboard.php')
    );
  }
});

// Handle messages from main thread (for testing)
self.addEventListener('message', (event) => {
  console.log('SW received message:', event.data);
  
  if (event.data.type === 'SIMULATE_PUSH') {
    const data = event.data.data;
    
    const options = {
      body: data.body,
      icon: data.icon || '/favicon.svg',
      badge: '/favicon.svg',
      vibrate: [200, 100, 200],
      requireInteraction: false,
      tag: 'simulated-push'
    };
    
    self.registration.showNotification(data.title, options);
    console.log('Simulated push notification shown');
  }
});
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
  '/js/push-notifications.js',
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
        
        // Cache successful GET responses only (exclude POST, chrome-extension, etc.)
        if (response.status === 200 && 
            event.request.method === 'GET' &&
            event.request.url.startsWith('http') && 
            !event.request.url.includes('chrome-extension')) {
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