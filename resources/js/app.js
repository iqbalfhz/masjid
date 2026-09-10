/**
 * Perilaku website publik Masjid An-Nur.
 *
 * Semua interaksi di sini bersifat penyempurna: halaman tetap terbaca penuh
 * bila JavaScript gagal dimuat, dan seluruh animasi dimatikan otomatis ketika
 * pengguna mengaktifkan "reduce motion" di sistem operasinya.
 */

const kurangiGerak = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

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

// --- Header menempel: beri bayangan setelah halaman di-scroll -----------

const header = document.querySelector('[data-site-header]');

if (header) {
    const perbaruiHeader = () => header.toggleAttribute('data-scrolled', window.scrollY > 8);

    perbaruiHeader();
    window.addEventListener('scroll', perbaruiHeader, { passive: true });
}

// --- Animasi elemen saat masuk viewport ---------------------------------

const elemenReveal = document.querySelectorAll('[data-reveal]');

if (elemenReveal.length > 0) {
    if (kurangiGerak || !('IntersectionObserver' in window)) {
        elemenReveal.forEach((el) => el.classList.add('is-revealed'));
    } else {
        const pengamat = new IntersectionObserver(
            (entries, observer) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    entry.target.classList.add('is-revealed');
                    observer.unobserve(entry.target);
                });
            },
            { rootMargin: '0px 0px -8% 0px', threshold: 0.12 }
        );

        elemenReveal.forEach((el) => pengamat.observe(el));
    }
}

// --- Counter statistik yang menghitung naik -----------------------------

const angkaCounter = document.querySelectorAll('[data-count-to]');

if (angkaCounter.length > 0) {
    const formatAngka = (nilai, prefix, suffix) =>
        `${prefix}${new Intl.NumberFormat('id-ID').format(Math.round(nilai))}${suffix}`;

    const jalankanHitung = (el) => {
        const target = Number(el.dataset.countTo);
        const prefix = el.dataset.countPrefix ?? '';
        const suffix = el.dataset.countSuffix ?? '';

        if (!Number.isFinite(target)) {
            return;
        }

        if (kurangiGerak) {
            el.textContent = formatAngka(target, prefix, suffix);
            return;
        }

        const durasi = 1400;
        const mulai = performance.now();

        const langkah = (waktu) => {
            const progres = Math.min((waktu - mulai) / durasi, 1);
            // easeOutExpo — cepat di awal, melambat di akhir.
            const eased = progres === 1 ? 1 : 1 - Math.pow(2, -10 * progres);

            el.textContent = formatAngka(target * eased, prefix, suffix);

            if (progres < 1) {
                requestAnimationFrame(langkah);
            }
        };

        requestAnimationFrame(langkah);
    };

    if ('IntersectionObserver' in window) {
        const pengamatAngka = new IntersectionObserver(
            (entries, observer) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    jalankanHitung(entry.target);
                    observer.unobserve(entry.target);
                });
            },
            { threshold: 0.4 }
        );

        angkaCounter.forEach((el) => pengamatAngka.observe(el));
    } else {
        angkaCounter.forEach(jalankanHitung);
    }
}

// --- Hitung mundur menuju waktu sholat berikutnya -----------------------

const wadahCountdown = document.querySelector('[data-countdown]');

if (wadahCountdown) {
    const target = new Date(wadahCountdown.dataset.countdown);
    const kolomJam = wadahCountdown.querySelector('[data-countdown-hours]');
    const kolomMenit = wadahCountdown.querySelector('[data-countdown-minutes]');
    const kolomDetik = wadahCountdown.querySelector('[data-countdown-seconds]');

    const duaDigit = (angka) => String(angka).padStart(2, '0');

    const perbarui = () => {
        const sisaMs = target.getTime() - Date.now();

        if (sisaMs <= 0) {
            wadahCountdown.dataset.state = 'masuk';
            kolomJam.textContent = '00';
            kolomMenit.textContent = '00';
            kolomDetik.textContent = '00';

            // Muat ulang agar jadwal berikutnya diambil dari server.
            setTimeout(() => window.location.reload(), 60_000);

            return false;
        }

        const totalDetik = Math.floor(sisaMs / 1000);
        kolomJam.textContent = duaDigit(Math.floor(totalDetik / 3600));
        kolomMenit.textContent = duaDigit(Math.floor((totalDetik % 3600) / 60));
        kolomDetik.textContent = duaDigit(totalDetik % 60);

        return true;
    };

    if (!Number.isNaN(target.getTime()) && kolomJam && kolomMenit && kolomDetik) {
        perbarui();
        const interval = setInterval(() => {
            if (!perbarui()) {
                clearInterval(interval);
            }
        }, 1000);
    }
}

// --- Salin teks (nomor rekening, nomor pendaftaran) ---------------------

document.addEventListener('click', async (event) => {
    const tombol = event.target.closest('[data-copy-target]');

    if (!tombol) {
        return;
    }

    const sumber = document.getElementById(tombol.dataset.copyTarget);

    if (!sumber) {
        return;
    }

    try {
        await navigator.clipboard.writeText(sumber.textContent.trim());
        const teksAsli = tombol.dataset.copyLabel ?? tombol.textContent;
        tombol.dataset.copyLabel = teksAsli;
        tombol.textContent = 'Tersalin!';
        setTimeout(() => (tombol.textContent = teksAsli), 2000);
    } catch (error) {
        console.error(error);
    }
});

// --- Reminder sholat (Web Push) -----------------------------------------

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
