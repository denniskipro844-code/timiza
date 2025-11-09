/* eslint-disable no-restricted-globals */
const APP_VERSION = 'v1.2';
const APP_CACHE = `timiza-app-${APP_VERSION}`;
const ASSETS_TO_CACHE = [
  '/',
  '/index.php',
  '/about.php',
  '/programs.php',
  '/get-involved.php',
  '/news.php',
  '/gallery.php',
  '/contact.php',
  '/assets/css/styles.css',
  '/assets/js/main.js',
  '/assets/images/logo.png',
  '/assets/images/logo-192.png',
  '/assets/images/logo-512.png',
  '/assets/images/Timiza-team.webp',
  '/offline.html',
  '/manifest.json',
  // External resources
  'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
  'https://cdn.tailwindcss.com',
  'https://unpkg.com/aos@2.3.1/dist/aos.css'
];

// Install event - cache resources
self.addEventListener('install', event => {
  console.log('Service Worker: Installing...');
  event.waitUntil(
    caches.open(APP_CACHE)
      .then(cache => {
        console.log('Service Worker: Caching files');
        return cache.addAll(ASSETS_TO_CACHE);
      })
      .then(() => {
        console.log('Service Worker: All files cached');
        return self.skipWaiting();
      })
      .catch(err => {
        console.error('Service Worker: Cache failed', err);
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
  console.log('Service Worker: Activating...');
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys
          .filter(key => key.startsWith('timiza-app-') && key !== APP_CACHE)
          .map(key => {
            console.log('Service Worker: Removing old cache', key);
            return caches.delete(key);
          })
      )
    ).then(() => {
      console.log('Service Worker: Activated');
      return self.clients.claim();
    })
  );
});

// Fetch event - serve cached content when offline
self.addEventListener('fetch', event => {
  // Skip non-GET requests
  if (event.request.method !== 'GET') return;
  
  // Skip Chrome extension requests
  if (event.request.url.startsWith('chrome-extension://')) return;
  
  event.respondWith(
    caches.match(event.request)
      .then(cacheResponse => {
        // Return cached version if available
        if (cacheResponse) {
          console.log('Service Worker: Serving from cache', event.request.url);
          return cacheResponse;
        }
        
        // Otherwise, fetch from network
        return fetch(event.request)
          .then(networkResponse => {
            // Don't cache if not a valid response
            if (!networkResponse || networkResponse.status !== 200 || networkResponse.type !== 'basic') {
              return networkResponse;
            }
            
            // Clone the response
            const responseToCache = networkResponse.clone();
            
            // Add to cache for future use
            caches.open(APP_CACHE)
              .then(cache => {
                // Only cache GET requests for same origin
                if (event.request.method === 'GET' && event.request.url.startsWith(self.location.origin)) {
                  cache.put(event.request, responseToCache);
                }
              });
            
            return networkResponse;
          })
          .catch(() => {
            // If both cache and network fail, show offline page
            console.log('Service Worker: Serving offline page');
            return caches.match('/offline.html');
          });
      })
  );
});

// Background sync for form submissions (when online)
self.addEventListener('sync', event => {
  if (event.tag === 'background-sync') {
    console.log('Service Worker: Background sync triggered');
    event.waitUntil(doBackgroundSync());
  }
});

function doBackgroundSync() {
  // Handle any queued form submissions or data sync
  return Promise.resolve();
}

// Push notification handling
self.addEventListener('push', event => {
  console.log('Service Worker: Push received');
  
  const options = {
    body: event.data ? event.data.text() : 'New update from Timiza Youth Initiative',
    icon: '/assets/images/logo-192.png',
    badge: '/assets/images/logo-192.png',
    vibrate: [200, 100, 200],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: 1
    },
    actions: [
      {
        action: 'explore',
        title: 'View Details',
        icon: '/assets/images/logo-192.png'
      },
      {
        action: 'close',
        title: 'Close',
        icon: '/assets/images/logo-192.png'
      }
    ]
  };
  
  event.waitUntil(
    self.registration.showNotification('Timiza Youth Initiative', options)
  );
});

// Handle notification clicks
self.addEventListener('notificationclick', event => {
  console.log('Service Worker: Notification clicked');
  event.notification.close();
  
  if (event.action === 'explore') {
    // Open the app
    event.waitUntil(
      clients.openWindow('/')
    );
  }
});

// Message handling for communication with main thread
self.addEventListener('message', event => {
  console.log('Service Worker: Message received', event.data);
  
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});

// Update notification
self.addEventListener('message', event => {
  if (event.data && event.data.action === 'skipWaiting') {
    self.skipWaiting();
  }
});