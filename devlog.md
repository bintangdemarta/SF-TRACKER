# SF-Tracker — Dev Log

Catatan perkembangan proyek, urutan **terbaru di atas**. Diisi tiap ada progres baru (fitur, fix, infra, konten). Lihat `devplan.md` untuk status fase secara keseluruhan.

---

### 2026-08-10 — PWA: verifikasi offline beneran jalan + bug fix (Phase 5 closeout attempt)
- **Blocker ketemu:** dev server `sf-tracker.test` jalan di HTTP biasa (custom hostname via hosts file, bukan `localhost` literal) — browser gak nganggap ini *secure context*, jadi `navigator.serviceWorker` gak ke-expose sama sekali. Ini akar masalah kenapa PWA "belum diverifikasi" dari kickoff sebelumnya — bukan cuma belum sempat dicek, tapi memang gak bisa dicek sampai ini di-fix.
- **Fix:** install `mkcert` (portable binary, gak lewat Chocolatey karena gak ada akses admin shell) → `mkcert -install` (root CA lokal, gak perlu admin prompt) → generate cert utk `sf-tracker.test`/`localhost`/`127.0.0.1` → tambah `<VirtualHost *:443>` baru di `httpd-vhosts.conf` dengan `SSLEngine on` + cert tsb → `httpd -t` (syntax check) → restart Apache → update `APP_URL` ke `https://` di `.env` lokal (gak kena commit) + `php artisan config:clear`.
- **Verifikasi otomatis** via Playwright (`​.tools/screenshot/verify-pwa.js` di workspace root, bukan di repo project) — login, cek registrasi SW, simulasi offline (`context.setOffline(true)`), cek konten yang di-render:
  - `isSecureContext: true`, SW `activeState: "activated"`, `controllerNow: true` — registrasi sukses.
  - Reload dashboard (route yang udah pernah dibuka) pas offline → full render dari cache, termasuk banner offline bawaan app. ✅
  - **Bug nyata ketemu:** navigasi offline ke route yang belum pernah di-cache (`/profile`) gagal total (`ERR_FAILED`, blank page) — seharusnya nampilin `offline.html`. Root cause (di-trace lewat temporary debug logging di SW, di-cabut lagi setelah ketemu): `setCatchHandler` di `resources/js/sw.js` manggil `createHandlerBoundToURL('/offline.html')()` tanpa argumen `options`, padahal Workbox butuh itu di-forward (event/request/url) — internalnya crash `TypeError: Cannot set properties of undefined`.
  - **Fix diterapkan:** `createHandlerBoundToURL('/offline.html')(options)` — options diteruskan dari parameter `setCatchHandler`. Rebuild (`npm run build`), retest: fallback page sekarang tampil benar buat route manapun yang belum ke-cache.
- **Efek samping ketemu di jalan:** container Docker `sf-tracker-mysql` (DB asli app, port `33061`) ternyata mati padahal Docker Desktop jalan — persis skenario "belum tervalidasi 100%" yang dicatat di Phase 6. Start manual (`docker compose up -d`), sehat lagi. Kejadian ke-2, dicatat di `devplan.md` Phase 6.
- **Implikasi buat Phase 10:** TLS/reverse-proxy production sekarang jadi hard requirement, bukan cuma governance checklist — kalau production jalan tanpa HTTPS, fitur PWA yang baru diverifikasi ini gak akan berfungsi di sana.
- Belum: Lighthouse PWA audit formal, test di device fisik entry-level.

### 2026-08-10 — PWA: manifest + service worker (Phase 5 kickoff)
- `vite-plugin-pwa` (strategi `injectManifest` + Workbox) dipasang: `resources/js/sw.js` precache asset build (`assets/*.{js,css}`) plus offline shell (`offline.html`, icon 192/512px).
- Web app manifest digenerate dari config di `vite.config.js` — nama/short_name/theme_color SF-Tracker, `display: standalone`, `start_url: /dashboard`.
- Service worker fisiknya ada di `/build/sw.js` (ikut `outDir` Vite) tapi butuh scope `/` biar bisa kontrol seluruh app, bukan cuma folder `/build/` — di-fix dengan header `Service-Worker-Allowed: /` di `public/.htaccess` (Apache/XAMPP lokal) dan `docker/nginx/app.conf` (nginx di image Docker deploy).
- Registrasi SW dipasang di `resources/js/app.js` via `virtual:pwa-register` (`registerSW({ immediate: true })`).
- Build lokal (`npm run build`) sukses — `public/build/sw.js`, `manifest.webmanifest`, `manifest.json` ter-generate bersih.
- **Gap terbuka:** belum diverifikasi jalan beneran di browser (registrasi SW, offline mode, Lighthouse PWA audit) — lihat `devplan.md` Phase 5.

### 2026-08-10 — Docker deploy architecture (staging + production)
- Target hosting dari Bos: Staging = self-hosted Docker (tunnel/domain internal), Production = VPS Cloud terpisah (Hostinger, tier belum final).
- `Dockerfile` multi-stage: composer deps (cached by lock file) → Vite asset build → runtime `php:8.2-fpm-alpine` dengan nginx+php-fpm dijalanin bareng via supervisord dalam 1 image. `docker-compose.deploy.yml` satu file dipakai staging & production (env-file beda per host) — prinsip parity.
- **Bug nyata ketemu & fix pas testing build lokal:** `apk del icu-dev oniguruma-dev` di runtime stage ikut narik turun `icu-libs` (dependency runtime, bukan cuma header dev), bikin ekstensi `intl` gagal load. Fix: pakai `apk add --virtual .build-deps` supaya cuma paket build-time yang kehapus, runtime lib (`icu-libs`, `oniguruma`) tetap ada. Diverifikasi ulang: warning hilang.
- **Diuji end-to-end lokal** (bukan cuma nulis YAML terus dipush): `docker build` sukses, `docker compose -f docker-compose.deploy.yml up -d` (app+mysql), `php artisan migrate --force` jalan bersih (10 migration), `/up` dan `/` sama-sama 200, container healthcheck `healthy`. Environment test langsung dibongkar (`down -v`) biar gak numpuk di Docker Desktop lokal Bos.
- Koreksi audit sebelumnya: healthcheck `/up` ternyata **sudah ada** dari default Laravel 11 (`bootstrap/app.php`), bukan "belum ada" seperti dicatat pas audit governance awal — kesalahan grep (cuma cek `routes/web.php`).
- `ci.yml` ditambah 3 job baru: `build-and-push` (build image, push ke GHCR dengan tag immutable `<branch>-<sha>` + moving tag `<branch>`), `deploy-staging`, `deploy-production` (SSH ke host target via `appleboy/ssh-action`, `docker compose pull && up -d`, migrate, cek `/up`).
- Deploy job **sengaja di-gate** di belakang repo variable `STAGING_DEPLOY_ENABLED`/`PRODUCTION_DEPLOY_ENABLED` (default gak ada = off) — supaya CI gak gagal berisik tiap push sebelum server & secrets-nya beneran siap. Lihat `devplan.md` Phase 10 buat checklist lengkap yang masih butuh input Bos (provisioning server, GitHub Secrets, GHCR visibility, environment protection rule buat manual-approval production).
- **Push pertama ke GitHub sempat gagal** di job `Build & Push Image` — `cache-from/cache-to: type=gha` butuh Buildx driver, bukan driver "docker" default runner. Fix: tambahin step `docker/setup-buildx-action@v3` sebelum `docker/build-push-action`. Push kedua (`06b7ffc`) **hijau penuh**, dikonfirmasi lewat GitHub Actions API — `lint`/`test`/`build-and-push` sukses, `deploy-staging`/`deploy-production` di-skip sesuai desain (gate variable belum aktif).

### 2026-08-10 — Branch strategy + CI/CD pipeline (Phase 10 kickoff)
- Adopsi standar governance multi-environment dari Bos: audit realitas project (solo-dev, 1 environment, 0 CI) dicatat sebagai gap di `devplan.md` Phase 10.
- Git: `main` di-push (1 commit lokal yang ketinggalan), branch `develop` dan `staging` dibuat dari `main` dan dipush ke `origin` — fondasi buat branch-to-environment mapping (`develop`→Dev, `staging`/`release/*`→Staging, `main`/`tags/v*`→Production) sesuai spec Bos.
- CI: `.github/workflows/ci.yml` ditambahin — 2 job (`lint`: Pint + Larastan/PHPStan level 5, `test`: build asset Vite + PHPUnit), jalan on push/PR ke `main`/`develop`/`staging`. Test suite Laravel udah pakai SQLite in-memory (`phpunit.xml`), jadi CI gak butuh service DB terpisah.
- Larastan (PHPStan buat Laravel) dipasang sebagai dev dependency baru (`composer.json`/`composer.lock` berubah). Analisis pertama nemu **16 error pre-existing** (properti Livewire gak dideklarasikan, type mismatch `Model` vs `User`/`TripLog`, nullsafe yang gak perlu) — di-baseline (`phpstan-baseline.neon`) biar CI gak langsung merah karena debt lama, bukan di-fix sekarang (di luar scope task ini). **Backlog nyata, bukan ditutup-tutupi** — lihat `devplan.md`.
- Diverifikasi lokal sebelum push: Pint pass, PHPStan pass (dengan baseline), PHPUnit 155/155 pass (naik dari yang tercatat sebelumnya 2 test gagal — kemungkinan sudah kebetulan fix di commit sebelumnya, perlu dikonfirmasi ulang kapan itu berubah).
- **Run pertama CI di GitHub (`fb3aa6f`) gagal** di job PHPUnit — root cause: `.env.example` (template yang dipakai `cp .env.example .env` di CI) masih default Laravel (`APP_NAME=Laravel`, `APP_URL=http://localhost`), padahal 2 assertion di `SeoTest.php` hardcode `"SF-Tracker"` / `"http://sf-tracker.test"`. Lolos di local dev karena `.env` asli sudah pernah di-update manual, tapi `.env.example`-nya kelupaan. Diperbaiki di commit `7f01c8a`, direproduksi & diverifikasi lokal dulu (fresh `.env.example` → 155/155 pass) sebelum push.
- **Run kedua (`7f01c8a`) hijau penuh** — dikonfirmasi via GitHub Actions API (`conclusion: success`), bukan cuma asumsi dari test lokal.

### 2026-08-10 — Screenshot tooling + devplan/devlog setup
- Playwright + Chromium ter-install penuh di `.tools/screenshot` (workspace root, bukan di repo project) — sempat kepotong gateway restart pas `npx playwright install` belum selesai download browser binary, dilanjutin dan diverifikasi jalan dengan screenshot beneran ke `sf-tracker.test`.
- Dikonfirmasi visual: landing page `/` sudah full styled (hero dark navy + CTA hijau + 3 feature card), bukan HTML polos — komplain "UI polos" sebelumnya kemungkinan cache CSS lama di browser.
- Setup `devplan.md` + `devlog.md` ini di root project.

### 2026-08-10 — Topical authority content cluster
- Commit `c21336f`: pillar `/panduan-driver-shopeefood` + 5 spoke guide (net-profit, cost-per-km, poin-insentif, dual-wallet, target-harian).
- Internal linking silo pillar↔spoke, JSON-LD Article+FAQPage/HowTo semua nyambung ke `Organization @id` yang sama, terdaftar di sitemap dinamis.
- Bug xpath di `GuideClusterTest.php` diperbaiki (`//url` gak match default XML namespace → diganti `//*[local-name()='url']`). 55 test terkait pass.
- **Gap terbuka:** isi artikel masih draft baseline (angka referensi dari Bos), belum fact-check final sebelum publish publik.

### 2026-08-10 — Technical SEO audit pass
- Commit `4d9b680`: 4 finding diperbaiki — meta description diperpanjang ke window 150-155 char, JSON-LD dibangun ulang jadi `@graph` dengan `@id` persisten (bukan node isolated), `lastmod` sitemap diganti dari `now()` ke file mtime, `Cache-Control`+`ETag` ditambahin ke sitemap.xml/robots.txt.

### 2026-08-10 — Public landing page & technical SEO module
- Commit `7301ff9`: landing page publik `/` (zero-hydration Blade), `<x-seo-meta>` + `<x-structured-data>`, `robots.txt` + `sitemap.xml` sebagai route dinamis (bukan file statis — biar testable & domain gak hardcoded).
- 120 test pass.
- Gap: `og:image` nunjuk ke file yang belum ada, perlu Bos upload asset 1200×630.

### 2026-08-09/10 — Infra: MySQL pindah ke Docker
- Commit `a9451a5`: MySQL native XAMPP sering crash (`mysqld.exe` mati gak bersih pas idle/sleep, 2x kejadian dengan exception signature sama). Dipindah ke Docker container, `restart: unless-stopped`, port 33061. Apache/PHP tetap XAMPP (gak full migrasi ke Sail — kebesaran buat masalah yang cuma di layer DB).
- 2026-08-10: Docker Desktop sendiri sempat mati total (bukan cuma container), root cause kemungkinan sleep/restart PC. AutoStart Docker Desktop di Windows login diaktifkan (edit `settings-store.json`, diverifikasi tetap `true` setelah restart).
- ⚠️ Catatan belum tuntas: container sempat gak auto-restart sendiri (~30 detik) pas Docker Desktop-nya restart, walau policy `unless-stopped` — kemungkinan quirk Docker Desktop for Windows. Belum 100% yakin bakal auto-recover pas PC boot/wake dari sleep, perlu diamati kejadian nyata berikutnya.

### 2026-08-09 — Core product build (Phase 1-4)
- `4034bd6` first commit → `7c16ba9` scaffold Laravel 11 + Livewire v3 + Tailwind.
- `4720a9e` domain schema (shift_sessions, trip_logs, expenses, wallet_transactions).
- `053c504` Breeze auth + ShiftTracker Livewire (Start/End Shift + Trip Logger).
- `2a81547` dual-wallet reconciliation engine.
- `e94426a` expense quick-entry.
- `e940679` financial analytics dashboard (FR-4.1).
- `b6309be` UI retrofit ke design-system rigor (WCAG AAA, touch target, state feedback).
- `cc2b5a8` bottom-sheet UI untuk primary actions (thumb-zone ergonomics).
- `0432456` vehicle performance analytics engine (Cost/KM, KM/L, Target Achievement).

---

## Cara pakai file ini
Tiap ada progres baru — fitur selesai, bug fix, keputusan arsitektur, insiden infra — tambahin entry baru di **atas** dengan format:

```
### YYYY-MM-DD — Judul singkat
- Apa yang berubah, kenapa (kalau non-obvious), status/gap yang masih terbuka.
```
