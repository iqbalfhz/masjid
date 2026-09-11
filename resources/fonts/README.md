# Font

**Instrument Sans** (bobot 400, 500, 600; subset latin) — dirilis di bawah
[SIL Open Font License 1.1](https://openfontlicense.org), yang mengizinkan
penggunaan dan distribusi ulang, termasuk menyimpannya bersama aplikasi.

Disimpan di repo, bukan diunduh saat build. Sebelumnya `vite.config.js`
memakai plugin `bunny()` yang mengambil font dari `fonts.bunny.net` pada setiap
build — dan build di server pernah gagal karena DNS container tidak bisa
menjangkau domain itu. Dengan font di sini, build tidak butuh internet sama
sekali untuk aset.
