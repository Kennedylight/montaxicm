const CACHE_NAME = 'montaxi-v1';
const ASSETS_TO_CACHE = [
  './offline.html',
  'drivers/assets/css/all.css',
  'drivers/assets/css/popupsBox.css',
  'drivers/assets/css/polices.css',
  'drivers/assets/css/style.css',
  'drivers/assets/css/errorOrSuccessBox.css',
  'drivers/assets/js/router.js',
  'drivers/assets/js/functions.js',
  'drivers/assets/js/all.js',
  'drivers/assets/js/biblio.js',
  'drivers/assets/images/fav.png',
  'drivers/assets/images/logo2.png',
  'drivers/assets/images/google.png',
  'drivers/assets/images/load4.gif',
  'drivers/assets/images/load3.gif',
  'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
  'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'
];

// Installation : Mise en cache des ressources statiques
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('[SW] Mise en cache des fichiers');
        return cache.addAll(ASSETS_TO_CACHE);
      })
      .then(() => self.skipWaiting())
  );
});

// Activation : Nettoyage des anciens caches
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cache => {
          if (cache !== CACHE_NAME) {
            console.log('[SW] Suppression ancien cache', cache);
            return caches.delete(cache);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

// Interception des requêtes (Fetch)
self.addEventListener('fetch', event => {
  // Vérification du mode "Lite" (Economie de données)
  const saveData = event.request.headers.get('save-data');
  
  // Stratégie : Cache First pour les assets, Network First pour le HTML
  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request).catch(() => caches.match('./offline.html'))
    );
  } else {
    event.respondWith(
      caches.match(event.request).then(response => response || fetch(event.request))
    );
  }
});