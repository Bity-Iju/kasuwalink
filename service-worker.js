const CACHE_NAME = 'kasuwalink-shell-v1';
const SHELL = ['./offline.html', './assets/style.css', './assets/app.js'];
self.addEventListener('install', event => {
  event.waitUntil(caches.open(CACHE_NAME).then(cache => cache.addAll(SHELL)).then(() => self.skipWaiting()));
});
self.addEventListener('activate', event => {
  event.waitUntil(caches.keys().then(keys => Promise.all(keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key)))).then(() => self.clients.claim()));
});
self.addEventListener('fetch', event => {
  const request = event.request;
  if (request.method !== 'GET') return;
  const url = new URL(request.url);
  // Never cache authenticated/dynamic pages or API responses: business records must stay fresh.
  if (url.origin !== self.location.origin || url.pathname.endsWith('.php')) return;
  event.respondWith(fetch(request).catch(() => caches.match(request).then(cached => cached || (request.mode === 'navigate' ? caches.match('./offline.html') : undefined))));
});
