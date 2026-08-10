# SF-Tracker — Development Plan

Fase pembangunan ShopeeFood Driver Finance Tracker. Diturunkan dari roadmap di `Product Requirement Document (PRD).md` §7, plus fase tambahan yang muncul di luar PRD awal (SEO/growth). Update status di sini setiap fase berpindah state — detail progres harian ada di `devlog.md`.

Status: `done` | `in-progress` | `pending` | `blocked`

---

## Phase 1: Core Setup & Migrations
**Status:** done
- Scaffold Laravel 11 + Livewire v3 + Tailwind CSS
- Domain schema: `shift_sessions`, `trip_logs`, `expenses`, `wallet_transactions`
- Breeze auth

## Phase 2: Shift Engine & Fast-Input UI
**Status:** done
- Start/End Shift Livewire component (odometer, target income)
- Trip Quick-Logger (argo, tip tunai/digital, poin/berlian)
- Expense quick-entry dengan payment source tagging (cash/digital)

## Phase 3: Financial Calculations & Ledger Logic
**Status:** done
- Dual-Wallet Reconciliation Engine (FR-3.1, FR-3.2)
- Vehicle performance analytics engine (Cost/KM, KM/L, Target Achievement)

## Phase 4: Dashboard & Analytics
**Status:** done
- Live Metrics Dashboard (Gross Revenue, Net Profit, Cost/KM, Hourly Rate) — FR-4.1
- UI retrofit ke design-system rigor (WCAG AAA, touch target, state feedback)
- Bottom-sheet UI untuk primary actions (thumb-zone ergonomics)

**Belum masuk scope:** FR-4.2 Historical Reporting (filter rentang tanggal + export CSV/Excel) — belum dikerjakan, masih di PRD tapi belum ada commit terkait.

## Phase 5: Mobile Hardening & PWA
**Status:** pending
- PWA Manifest — belum ada (`public/build/manifest.json` yang ada sekarang cuma Vite asset manifest, bukan web app manifest)
- Service Worker — belum ada
- Testing performa di perangkat mobile entry-level — belum dilakukan

## Phase 6: Infra Stability (di luar PRD, muncul dari insiden lapangan)
**Status:** done, dengan catatan
- Migrasi MySQL dari XAMPP native ke Docker container (`mysqld.exe` sering crash pas idle/sleep)
- Docker Desktop AutoStart di Windows login diaktifkan
- ⚠️ Belum tervalidasi 100%: `restart: unless-stopped` sempat gak auto-hidup sendiri pas Docker Desktop restart (butuh start manual sekali). Perlu diamati di kejadian nyata berikutnya — kalau berulang, pasang scheduled task `docker start` beberapa menit setelah boot.

## Phase 7: Public Landing Page & Technical SEO (ekstensi non-PRD)
**Status:** done
- Landing page publik `/` (zero-hydration Blade), meta tags, OG/Twitter Card, JSON-LD `WebApplication`+`SoftwareApplication`
- `robots.txt` + `sitemap.xml` dinamis (route, bukan file statis)
- Audit lanjutan: Knowledge Graph `@graph` dengan `@id` persisten, `lastmod` dari file mtime, `Cache-Control`/`ETag` di sitemap & robots

## Phase 8: Topical Authority Content Cluster (ekstensi non-PRD)
**Status:** done (kode), draft (konten)
- Pillar `/panduan-driver-shopeefood` + 5 spoke (net-profit, cost-per-km, poin-insentif, dual-wallet, target-harian)
- Internal linking silo (pillar↔spoke + lateral antar-spoke), semua Article/FAQPage/HowTo terhubung ke `Organization @id` yang sama
- Terdaftar penuh di sitemap dinamis
- ⚠️ **Gap terbuka:** isi artikel masih draft baseline pakai angka referensi yang dikasih Bos, belum ada fact-check final sebelum publish ke publik.

## Phase 9: Tooling & Dev Ops (pendukung)
**Status:** done
- Screenshot tool (Playwright + Chromium) di `.tools/screenshot` — dipakai untuk cross-check visual UI tanpa Bos harus screenshot manual

## Phase 10: Multi-Environment & Release Governance
**Status:** in-progress
- Standar governance (environment taxonomy, branch-to-environment mapping, pipeline gating) diadopsi dari spec Bos.
- ✅ Branch strategy: `develop` + `staging` dibuat dari `main`, dipush ke `origin` (mapping: `develop`→Dev, `staging`/`release/*`→Staging, `main`/`tags/v*`→Production).
- ✅ CI pipeline dasar: `.github/workflows/ci.yml` — job `lint` (Pint + Larastan/PHPStan level 5) dan `test` (Vite build + PHPUnit), trigger on push/PR ke `main`/`develop`/`staging`.
- ✅ Larastan dipasang, baseline (`phpstan-baseline.neon`) dibuat untuk 16 error pre-existing (lihat backlog di bawah).
- ⏳ **Belum ada:** Preview/PR ephemeral env, auto-deploy Dev/Staging beneran (karena belum ada target hosting), Production dengan Blue/Green/Canary, DR. Semua itu butuh keputusan hosting dulu dari Bos — belum bisa dikerjakan tanpa itu.
- ⏳ Belum ada healthcheck route (`/up` atau `/healthz`) — kandidat kerjaan kecil berikutnya, gampang ditambahin kapan saja.

---

## Belum dikerjakan / kandidat fase berikutnya
- FR-4.2 Historical Reporting (filter tanggal + export CSV/Excel)
- Phase 5 PWA (manifest + service worker)
- Fact-check & finalisasi 6 artikel content cluster sebelum go-live publik
- `og:image` asset 1200×630 belum ada filenya (dibutuhkan Bos upload manual, agent gak bisa generate gambar)
- Healthcheck route `/up` untuk smoke test CI/CD
- Target hosting untuk Dev/Staging/Production — keputusan Bos, blocker buat lanjutin Phase 10
- **16 error PHPStan ter-baseline** (bukan di-fix, cuma disembunyikan dari gate CI) — daftar detail ada di `phpstan-baseline.neon`, ringkasannya: properti Livewire tanpa deklarasi tipe (`ExpenseLogger`, `FinancialDashboard`, `ShiftTracker`), beberapa method service (`WalletReconciliationService`, `FinancialMetricsService`) nerima `Model` generik padahal butuh `User`/`TripLog` spesifik. Worth dedicated cleanup session.
- ⚠️ Soal "2 test pre-existing gagal" yang dicatat sebelumnya (`FinancialMetricsServiceTest`, `PerformanceMetricsServiceTest`) — pas dijalankan ulang barusan (2026-08-10, setup CI), full suite **155/155 pass**. Kemungkinan sudah kebetulan fix di salah satu commit SEO sebelumnya, tapi belum ada verifikasi eksplisit kapan/kenapa berubah — dicatat di sini biar gak dianggap kontradiksi diam-diam.
