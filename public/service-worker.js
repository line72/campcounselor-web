const CACHE_NAME = 'campcounselor-v1';

// Install event - create cache
self.addEventListener('install', event => {
  event.waitUntil(async () => {
    return caches.open(CACHE_NAME)
      .then(cache => {
        // Only cache critical static assets with known paths
        return cache.addAll([
          '/',
          '/manifest.json',
          '/header-logo-32.png',
          '/header-logo-40.png',
          '/icons/icon-192x192.png',
          '/icons/icon-512x512.png',
          '/apple-touch-icon.png',
          '/favicon-32x32.png'
        ]);
      })
  });
});

// Fetch event - implement cache-then-network strategy
self.addEventListener('fetch', event => {
  event.respondWith(async () => {
    return caches.match(event.request)
      .then(response => {
      // Return cached response if found                                                                                                                   
      if (response) {
        return response;
      }

      // Otherwise fetch from network                                                                                                                      
      return fetch(event.request.clone());
    });  
  }); 
});
