/**
 * GET /api/push/sw — the service worker.
 *
 * Byte-identical for every site: the VAPID key and endpoint come from the
 * subscription the page creates, never from the worker. That means one cached
 * artefact rather than one per customer, and no secret in a file that is served
 * publicly by definition.
 *
 * It must be served from the site's own origin to control that scope, which is
 * why the install instructions ask for a one-line proxy rather than a script
 * tag — a service worker cannot be loaded cross-origin.
 */
const SW = `/* Mamal push worker */
self.addEventListener('push', function (event) {
  if (!event.data) return;
  var d = {};
  try { d = event.data.json(); } catch (e) { d = { title: 'Notification', body: event.data.text() }; }

  event.waitUntil(
    self.registration.showNotification(d.title || 'Notification', {
      body: d.body || '',
      icon: d.icon,
      image: d.image,
      badge: d.badge,
      tag: d.tag,
      // Collapse quietly: replacing an unread notification should not re-alert.
      renotify: false,
      actions: (d.actions || []).slice(0, 2),
      data: { url: d.url, tag: d.tag },
    })
  );
});

self.addEventListener('notificationclick', function (event) {
  event.notification.close();
  var url = (event.notification.data && event.notification.data.url) || '/';

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (list) {
      // Focus an existing tab on the same origin rather than opening a fifth
      // copy of the site — the single most common complaint about web push.
      for (var i = 0; i < list.length; i++) {
        var c = list[i];
        if (c.url.indexOf(self.location.origin) === 0 && 'focus' in c) {
          c.navigate && c.navigate(url);
          return c.focus();
        }
      }
      return clients.openWindow(url);
    })
  );
});

self.addEventListener('pushsubscriptionchange', function (event) {
  // The browser rotated the subscription. Re-subscribe with the same key and
  // tell the server, or this device silently stops receiving anything.
  event.waitUntil(
    self.registration.pushManager
      .subscribe({ userVisibleOnly: true, applicationServerKey: event.oldSubscription && event.oldSubscription.options.applicationServerKey })
      .then(function (sub) {
        return fetch('/api/push/subscribe', {
          method: 'POST',
          headers: { 'content-type': 'application/json' },
          body: JSON.stringify({ replaces: event.oldSubscription && event.oldSubscription.endpoint, subscription: sub }),
        });
      })
      .catch(function () {})
  );
});

self.addEventListener('install', function () { self.skipWaiting(); });
self.addEventListener('activate', function (e) { e.waitUntil(self.clients.claim()); });
`;

export async function GET() {
  return new Response(SW, {
    headers: {
      'content-type': 'text/javascript; charset=utf-8',
      // A service worker must not be cached hard: an old one keeps running
      // until it is replaced, and a day-long cache means a day-long bug.
      'cache-control': 'public, max-age=0, must-revalidate',
      'service-worker-allowed': '/',
    },
  });
}
