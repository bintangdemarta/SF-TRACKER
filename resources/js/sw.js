import { precacheAndRoute, createHandlerBoundToURL } from 'workbox-precaching';
import { registerRoute, setCatchHandler } from 'workbox-routing';
import { NetworkFirst } from 'workbox-strategies';

precacheAndRoute(self.__WB_MANIFEST);

self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

// Livewire's own AJAX endpoint and the health-check route must always hit
// the network live — never served from a page cache.
const isExcludedFromNavigationCache = (url) =>
    url.pathname.startsWith('/livewire/') || url.pathname === '/up';

registerRoute(
    ({ request, url }) => request.mode === 'navigate' && !isExcludedFromNavigationCache(url),
    new NetworkFirst({
        cacheName: 'sf-tracker-pages',
        networkTimeoutSeconds: 5,
    })
);

// Last resort ONLY: fires when no route above produced a response at all
// (genuinely offline + nothing cached for that URL yet) — not on every navigation.
setCatchHandler(async ({ request }) => {
    if (request.mode === 'navigate') {
        return createHandlerBoundToURL('/offline.html')();
    }

    return Response.error();
});
