const OFFLINE_URL = 'tmpl/offline.html';
var dataCacheName = 'e2sCache-v1';
var cacheName = 'e2s';
var filesToCache = [
];

self.addEventListener('install', function(e) {
  console.log('[sw.js] Installed');
  e.waitUntil(
    caches.open(cacheName).then(function(cache) {
      console.log('[sw.js] Caching app shell');
      return cache.addAll(filesToCache);
    })
  );
});

self.addEventListener('activate', function(e) {
  console.log('[sw.js] Activated');
  e.waitUntil(
	fetch(OFFLINE_URL).then(function(response) {
		return caches.open(cacheName).then(function(cache) {
			return cache.put(OFFLINE_URL, response);
		});
	})
  );
  e.waitUntil(
    caches.keys().then(function(keyList) {
      return Promise.all(keyList.map(function(key) {
        if (key !== cacheName && key !== dataCacheName) {
         console.log('[sw.js] Removed old cache', key);
          return caches.delete(key);
        }
      }));
    })
  );
  return self.clients.claim();
});

self.addEventListener('fetch', function(e) {
	if (e.request.cache === 'only-if-cached' && e.request.mode !== 'same-origin') {
	  return;
	}
    e.respondWith(
		caches.match(e.request).then(function(response) {
			return response || fetch(e.request).catch(error => {
				console.error (error);
				return caches.match(OFFLINE_URL);
			})
		})
    );
});
