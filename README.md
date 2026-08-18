# SF-Tracker

SF-Tracker adalah aplikasi pencatatan dan analisis operasional untuk driver ShopeeFood. Fokus utamanya adalah membantu driver menghitung performa shift, pendapatan bersih, biaya operasional, transaksi dompet, dan laporan historis berbasis data.

> Status: aktif dikembangkan menuju sistem production-ready dengan pipeline Docker, CI, dan auto-deploy ke homelab production.

## Ringkasan Sistem

- **Domain utama:** tracking shift, trip/order, expense, dual-wallet ledger, dan laporan historis.
- **Target pengguna:** driver gig-economy/food delivery yang membutuhkan kontrol finansial harian.
- **Runtime lokal:** Docker Compose, tidak lagi bergantung pada XAMPP.
- **Runtime production:** self-hosted VM di homelab dengan image dari GitHub Container Registry.
- **CI/CD:** GitHub Actions menjalankan lint, test, build image, push image, dan deploy production.

## Tech Stack

- **Backend:** PHP 8.2, Laravel 11
- **Frontend:** Livewire 3, Volt, Blade, Tailwind CSS, Vite
- **Database:** MySQL 8
- **Testing & Quality:** PHPUnit, Laravel Pint, Larastan/PHPStan
- **Container:** Docker, Docker Compose, Nginx, PHP-FPM
- **Registry:** GitHub Container Registry (`ghcr.io`)
- **Deployment:** GitHub Actions self-hosted runner di VM production

## Modul Aplikasi

- **Shift Tracker:** mencatat sesi kerja, waktu mulai/selesai, dan metrik operasional.
- **Trip Log:** mencatat order/trip sebagai basis perhitungan performa.
- **Expense Logger:** mencatat pengeluaran operasional seperti BBM dan biaya kendaraan.
- **Wallet Ledger:** mencatat transaksi saldo dan kas untuk rekonsiliasi keuangan.
- **Financial Dashboard:** menyajikan ringkasan performa finansial harian.
- **Historical Report:** agregasi laporan historis dan export data.
- **SEO Guides:** halaman panduan publik untuk akuisisi organik.

## Arsitektur Runtime

```text
User Browser
    |
    v
Nginx Container :8080
    |
    v
PHP-FPM / Laravel App
    |
    +--> MySQL 8 Container
    |
    +--> Storage / Cache / Logs

GitHub Actions
    |
    +--> Test + Lint
    +--> Build Docker Image
    +--> Push GHCR Image
    +--> Self-hosted Runner Pulls Image
    +--> Docker Compose Production Up
```

## Struktur Project

```text
app/                    Core aplikasi: Controller, Livewire, Model, Service
bootstrap/              Laravel bootstrap/cache runtime
config/                 Konfigurasi framework dan package
database/               Migration, factory, seeder
resources/              Blade views, Livewire views, CSS, JS
routes/                 Web/auth route definitions
tests/                  PHPUnit feature/unit tests
docker/                 Konfigurasi container runtime
.github/workflows/      CI/CD pipeline
Dockerfile              Image aplikasi production-ready
docker-compose.yml      Runtime lokal development
docker-compose.deploy.yml Runtime staging/production
```

## Menjalankan Lokal dengan Docker

Prasyarat:

- Docker Desktop / Docker Engine
- Docker Compose

Langkah:

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

Akses aplikasi:

```text
http://localhost:8080
```

Health check:

```text
http://localhost:8080/up
```

## Quality Gate

Jalankan sebelum merge/push perubahan penting:

```bash
docker compose exec app php artisan test
docker compose exec app ./vendor/bin/pint --test
docker compose exec app ./vendor/bin/phpstan analyse
npm run build
```

## Deployment Production

Production menggunakan workflow GitHub Actions:

1. Push ke branch `main`.
2. CI menjalankan lint, static analysis, test, dan build frontend.
3. Image aplikasi dibuild dan dipush ke GHCR.
4. Self-hosted runner di VM production menarik image terbaru.
5. Docker Compose production menjalankan migration, seeder kategori expense, dan health check.

Production endpoint saat ini:

```text
http://10.10.10.249:8080
```

## Prinsip Engineering

- Gunakan Docker sebagai runtime standar; hindari dependency lokal seperti XAMPP.
- Utamakan agregasi database untuk laporan dan metrik historis.
- Jaga invariant finansial di level database/service, bukan hanya UI.
- Semua perubahan production harus lewat Git, CI, image registry, dan deploy pipeline.
- Jangan simpan secret production di repository.

## Risiko dan Roadmap Kritis

- **Self-hosted runner public repo:** batasi workflow trigger, permission token, dan akses host.
- **Database durability:** perlu strategi backup MySQL terjadwal dan restore drill.
- **Security hardening:** tambah TLS/reverse proxy, secret management, dan least-privilege runner user.
- **Observability:** tambah structured logging, metrics, dan alerting untuk failure deploy/runtime.
- **Data integrity:** audit transaksi wallet/shift agar atomic dan konsisten saat concurrency.

## Lisensi

Belum ditentukan secara eksplisit untuk distribusi publik. Tentukan lisensi sebelum repository dipakai sebagai artefak akademik atau production open-source.
