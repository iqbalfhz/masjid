/**
 * Service worker reminder sholat Masjid An-Nur.
 *
 * Server mengirim push tanpa payload, lalu service worker mengambil isi
 * notifikasi terbaru dari endpoint /push/konten. Dengan begitu tidak ada data
 * jamaah yang melewati layanan push milik browser.
 */

const KONTEN_ENDPOINT = '/push/konten';

self.addEventListener('install', () => self.skipWaiting());

self.addEventListener('activate', (event) => event.waitUntil(self.clients.claim()));

self.addEventListener('push', (event) => {
    event.waitUntil(
        fetch(KONTEN_ENDPOINT, { credentials: 'omit' })
            .then((response) => (response.ok ? response.json() : null))
            .catch(() => null)
            .then((data) => {
                const isi = data || {
                    title: 'Pengingat sholat',
                    body: 'Waktu sholat sudah dekat.',
                    url: '/jadwal-sholat',
                };

                return self.registration.showNotification(isi.title, {
                    body: isi.body,
                    icon: '/images/icon-notifikasi.png',
                    badge: '/images/icon-notifikasi.png',
                    tag: 'pengingat-sholat',
                    renotify: true,
                    data: { url: isi.url || '/jadwal-sholat' },
                });
            })
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const tujuan = (event.notification.data && event.notification.data.url) || '/jadwal-sholat';

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (const client of clientList) {
                if ('focus' in client) {
                    client.navigate(tujuan);
                    return client.focus();
                }
            }

            return self.clients.openWindow(tujuan);
        })
    );
});
