# SF-Tracker — Dev Log

Catatan perkembangan proyek, urutan **terbaru di atas**. Diisi tiap ada progres baru (fitur, fix, infra, konten). Lihat `devplan.md` untuk status fase secara keseluruhan.

---

### 2026-08-10 — Branch strategy + CI/CD pipeline (Phase 10 kickoff)
- Adopsi standar governance multi-environment dari Bos: audit realitas project (solo-dev, 1 environment, 0 CI) dicatat sebagai gap di `devplan.md` Phase 10.
- Git: `main` di-push (1 commit lokal yang ketinggalan), branch `develop` dan `staging` dibuat dari `main` dan dipush ke `origin` — fondasi buat branch-to-environment mapping (`develop`→Dev, `staging`/`release/*`→Staging, `main`/`tags/v*`→Production) sesuai spec Bos.
- CI: `.github/workflows/ci.yml` ditambahin — 2 job (`lint`: Pint + Larastan/PHPStan level 5, `test`: build asset Vite + PHPUnit), jalan on push/PR ke `main`/`develop`/`staging`. Test suite Laravel udah pakai SQLite in-memory (`phpunit.xml`), jadi CI gak butuh service DB terpisah.
- Larastan (PHPStan buat Laravel) dipasang sebagai dev dependency baru (`composer.json`/`composer.lock` berubah). Analisis pertama nemu **16 error pre-existing** (properti Livewire gak dideklarasikan, type mismatch `Model` vs `User`/`TripLog`, nullsafe yang gak perlu) — di-baseline (`phpstan-baseline.neon`) biar CI gak langsung merah karena debt lama, bukan di-fix sekarang (di luar scope task ini). **Backlog nyata, bukan ditutup-tutupi** — lihat `devplan.md`.
- Diverifikasi lokal sebelum push: Pint pass, PHPStan pass (dengan baseline), PHPUnit 155/155 pass (naik dari yang tercatat sebelumnya 2 test gagal — kemungkinan sudah kebetulan fix di commit sebelumnya, perlu dikonfirmasi ulang kapan itu berubah).

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
