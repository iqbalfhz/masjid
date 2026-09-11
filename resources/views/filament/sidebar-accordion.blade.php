{{--
    Sidebar akordion: hanya satu grup terbuka pada satu waktu.

    Dengan 25 menu di enam grup, sidebar yang membuka semuanya sekaligus jadi
    jauh lebih panjang daripada layar dan memaksa pengurus menggulir untuk
    menemukan menu yang sebenarnya dekat.

    Filament sudah menyimpan status buka/tutup di Alpine store `sidebar`
    (`collapsedGroups`, dipersist ke localStorage). Yang belum ada hanyalah
    aturan "buka satu, tutup sisanya". Jadi di sini metode bawaannya dibungkus,
    bukan sidebar-nya ditulis ulang — supaya pembaruan Filament tidak
    mematahkannya.

    Perhatikan bahwa isinya adalah daftar grup yang TERTUTUP, bukan yang
    terbuka. Membuka satu grup berarti mengisi daftar itu dengan semua grup
    lainnya.
--}}
<script>
    (() => {
        const LABEL_GRUP = '.fi-sidebar-group[data-group-label]'

        /**
         * Label semua grup yang sedang dirender.
         *
         * Sub-navigasi ikut memakai penanda yang sama tapi bukan grup sidebar,
         * jadi disaring keluar agar tidak ikut ditutup.
         */
        const semuaGrup = () =>
            [...document.querySelectorAll(LABEL_GRUP)]
                .map((el) => el.dataset.groupLabel)
                .filter((label) => label && !label.startsWith('sub_navigation_'))

        const grupAktif = () =>
            document.querySelector(`${LABEL_GRUP}.fi-active`)?.dataset
                .groupLabel ?? null

        /**
         * Sisakan hanya grup halaman yang sedang dibuka.
         *
         * Tanpa ini, berpindah ke menu di grup lain akan meninggalkan grup itu
         * tertutup — pengurus kehilangan jejak posisinya sendiri.
         */
        const ikutiHalaman = (store) => {
            const aktif = grupAktif()

            store.collapsedGroups = semuaGrup().filter((label) => label !== aktif)
        }

        document.addEventListener('alpine:initialized', () => {
            const store = window.Alpine?.store('sidebar')

            if (!store) {
                return
            }

            store.toggleCollapsedGroup = function (label) {
                const sedangTertutup = this.collapsedGroups.includes(label)

                this.collapsedGroups = sedangTertutup
                    ? semuaGrup().filter((lain) => lain !== label)
                    : this.collapsedGroups.concat(label)
            }

            ikutiHalaman(store)

            // Navigasi SPA Livewire tidak memuat ulang halaman, jadi grup aktif
            // harus disesuaikan ulang tiap perpindahan.
            document.addEventListener('livewire:navigated', () =>
                ikutiHalaman(store),
            )
        })
    })()
</script>
