// sw.js - Service Worker Minimal pour autoriser l'installation PWA
self.addEventListener('install', (e) => {
    // L'application est en cours d'installation
    self.skipWaiting();
});

self.addEventListener('fetch', (e) => {
    // Nécessaire pour valider les critères d'installation des navigateurs
});
