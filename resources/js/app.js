/**
 * Perilaku ringan untuk website publik: menu mobile, dan tombol berlangganan
 * reminder sholat lewat Web Push (PRD 5.1.2).
 */

// --- Menu navigasi mobile -----------------------------------------------

document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-menu-toggle]');

    if (!toggle) {
        return;
    }

    const menu = document.getElementById(toggle.getAttribute('aria-controls'));

    if (!menu) {
        return;
    }

    const terbuka = toggle.getAttribute('aria-expanded') === 'true';
    toggle.setAttribute('aria-expanded', String(!terbuka));
    menu.hidden = terbuka;
});

// --- Reminder sholat -----------------------------------------------------

const tombolReminder = document.querySelector('[data-push-toggle]');

if (tombolReminder && 'serviceWorker' in navigator && 'PushManager' in window) {
    const status = document.querySelector('[data-push-status]');

    const setStatus = (pesan, jenis = 'info') => {
        if (!status) {
            return;
        }

        status.textContent = pesan;
        status.dataset.jenis = jenis;
    };

    const urlBase64ToUint8Array = (base64String) => {
        const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        return Uint8Array.from([...rawData].map((char) => char.charCodeAt(0)));
    };

    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    const prayerPreferences = () =>
        [...document.querySelectorAll('[data-push-prayer]:checked')].map((input) => input.value);

    const perbaruiTampilan = (aktif) => {
        tombolReminder.dataset.active = String(aktif);
        tombolReminder.textContent = aktif ? 'Matikan pengingat' : 'Aktifkan pengingat sholat';
    };

    const daftar = async () => {
        const konfigurasi = await fetch('/push/kunci-publik').then((r) => r.json());

        if (!konfigurasi.enabled) {
            setStatus('Pengingat belum diaktifkan pengurus masjid.', 'warning');
            return;
        }

        const izin = await Notification.requestPermission();

        if (izin !== 'granted') {
            setStatus('Izin notifikasi ditolak. Aktifkan lewat pengaturan browser bila berubah pikiran.', 'warning');
            return;
        }

        const registration = await navigator.serviceWorker.register('/sw.js');
        const subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(konfigurasi.publicKey),
        });

        const payload = subscription.toJSON();

        await fetch('/push/langganan', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
            body: JSON.stringify({
                endpoint: payload.endpoint,
                keys: payload.keys,
                prayers: prayerPreferences(),
                minutes_before: Number(document.querySelector('[data-push-minutes]')?.value ?? 10),
            }),
        });

        perbaruiTampilan(true);
        setStatus('Pengingat aktif. Anda akan diberi tahu menjelang waktu sholat.', 'success');
    };

    const berhenti = async () => {
        const registration = await navigator.serviceWorker.getRegistration();
        const subscription = await registration?.pushManager.getSubscription();

        if (subscription) {
            await fetch('/push/langganan', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
                body: JSON.stringify({ endpoint: subscription.endpoint }),
            });

            await subscription.unsubscribe();
        }

        perbaruiTampilan(false);
        setStatus('Pengingat dimatikan.', 'info');
    };

    tombolReminder.addEventListener('click', async () => {
        tombolReminder.disabled = true;

        try {
            await (tombolReminder.dataset.active === 'true' ? berhenti() : daftar());
        } catch (error) {
            setStatus('Gagal memproses pengingat. Coba lagi beberapa saat lagi.', 'warning');
            console.error(error);
        } finally {
            tombolReminder.disabled = false;
        }
    });

    navigator.serviceWorker
        .getRegistration()
        .then((registration) => registration?.pushManager.getSubscription())
        .then((subscription) => perbaruiTampilan(Boolean(subscription)))
        .catch(() => perbaruiTampilan(false));
}
