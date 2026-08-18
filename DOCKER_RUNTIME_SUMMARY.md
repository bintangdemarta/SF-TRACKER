# Ringkasan Migrasi Runtime Docker SF-Tracker

Tanggal: 2026-08-18

## Status Akhir

SF-Tracker sekarang berjalan menggunakan Docker, bukan XAMPP.

- App container: `sf-tracker-app`
- URL aplikasi: `http://localhost:8080`
- Health endpoint: `http://localhost:8080/up`
- MySQL container: `sf-tracker-mysql`
- Database: `sf_tracker`
- Port MySQL internal Docker: `3306`
- Port MySQL dari host: `33061`
- Status validasi: app dan MySQL healthy, halaman utama dan health endpoint mengembalikan HTTP 200
- Migrasi database: berhasil, status terakhir `Nothing to migrate`

## Perubahan Konfigurasi

- `docker-compose.yml` sekarang memiliki dua service utama: `app` dan `mysql`.
- Service `app` membangun image dari `Dockerfile` target `app`.
- Service `app` menjalankan nginx + php-fpm + Laravel melalui supervisord.
- Service `app` terhubung ke database melalui Docker network dengan `DB_HOST=mysql` dan `DB_PORT=3306`.
- Service `mysql` menggunakan image `mysql:8.0` dan volume persisten `sf-tracker-mysql-data`.
- `.env` diarahkan ke runtime Docker:
  - `APP_URL=http://localhost:8080`
  - `APP_PORT=8080`
  - `DB_HOST=mysql`
  - `DB_PORT=3306`
  - `DB_USERNAME=sf_tracker`
  - `FORWARD_DB_PORT=33061`

## Architecture Flow

```text
Browser
  -> http://localhost:8080
  -> Docker app container: nginx + php-fpm + Laravel
  -> Docker network
  -> MySQL container: sf_tracker database
```

## Justifikasi Teknologi

Docker dipilih sebagai runtime utama karena memberikan environment yang lebih konsisten dibanding XAMPP. Ini lebih dekat ke praktik staging/production, mengurangi masalah konfigurasi lokal, dan memudahkan deployment berbasis image.

## Trade-off

### Kelebihan

- Runtime lebih konsisten antar mesin.
- Tidak bergantung pada XAMPP untuk menjalankan Laravel dan MySQL.
- Lebih siap untuk staging/production karena memakai container image.
- MySQL tetap bisa diakses dari host melalui port `33061`.
- Aplikasi berjalan di port `8080`, sehingga tidak bentrok dengan Apache XAMPP di port `80`.

### Kekurangan

- Karena image app bersifat production-style, perubahan kode PHP/frontend perlu rebuild agar masuk ke container.
- Development hot reload belum optimal karena source code tidak di-mount sebagai volume.
- Stack lokal saat ini belum memisahkan worker queue sebagai service tersendiri.
- Konfigurasi secret masih berbasis `.env` lokal, belum memakai secret manager.

## Command Operasional

Menjalankan stack:

```powershell
docker compose up -d
```

Build ulang setelah perubahan kode/config:

```powershell
docker compose up -d --build
```

Menghentikan stack:

```powershell
docker compose down
```

Menjalankan migrasi:

```powershell
docker compose exec -T app php artisan migrate
```

Melihat status container:

```powershell
docker compose ps
```

Melihat log aplikasi:

```powershell
docker compose logs --tail=100 app
```

## Catatan Engineering

Migrasi ke Docker menyelesaikan masalah ketergantungan runtime pada XAMPP, tetapi belum otomatis membuat aplikasi production-ready. Prioritas berikutnya tetap pada penguatan financial ledger: transaksi database atomic, row locking/ledger invariant, dan constraint satu active shift per user.

## Deploy Homelab VM

Tanggal validasi: 2026-08-18

Target deploy homelab:

- Proxmox host: `10.10.10.230`
- VM: `400 sf-tracker-dev`
- VM IP: `10.10.10.249`
- VM user: `sftracker`
- Deploy path: `/home/sftracker/sf-tracker`
- URL aplikasi LAN: `http://10.10.10.249:8080`
- Health endpoint LAN: `http://10.10.10.249:8080/up`

Status validasi homelab:

- `sf-tracker-app`: running dan healthy
- `sf-tracker-mysql`: running dan healthy
- Halaman utama dari laptop: HTTP 200
- Health endpoint dari laptop: HTTP 200
- Migrasi database: berhasil
- Seeder `ExpenseCategorySeeder`: berhasil dijalankan

Catatan build:

- `Dockerfile` diperbaiki agar membuat direktori runtime Laravel sebelum `composer dump-autoload`.
- Tanpa direktori `storage/framework/views` dan cache terkait, build image gagal pada `artisan package:discover` dengan error `Please provide a valid cache path`.

## Production Auto-Deploy Design

Target production sekarang menggunakan image dari GHCR, bukan build source lokal di VM.

Runtime production VM:

- Deploy path: `/home/sftracker/sf-tracker-production`
- Compose file: `/home/sftracker/sf-tracker-production/docker-compose.deploy.yml`
- Env file: `/home/sftracker/sf-tracker-production/.env.production`
- Image: `ghcr.io/bintangdemarta/sf-tracker:main` atau tag SHA dari CI
- URL LAN: `http://10.10.10.249:8080`
- Laravel env: `production`
- Debug mode: `false`

Auto-deploy production harus memakai GitHub self-hosted runner di VM homelab, bukan GitHub-hosted runner. Alasannya, GitHub-hosted runner tidak bisa menjangkau IP privat `10.10.10.249` di LAN.

Runner requirement:

- Lokasi runner: VM `sf-tracker-dev` (`10.10.10.249`)
- User runner: `sftracker`
- Required labels: `self-hosted`, `linux`, `x64`, `sf-tracker-production`
- Optional GitHub variable: `PRODUCTION_DEPLOY_PATH=/home/sftracker/sf-tracker-production`

Production deploy flow:

```text
Push to main
  -> GitHub Actions lint
  -> PHPUnit tests
  -> Build and push image to GHCR
  -> Self-hosted runner inside VM pulls image
  -> Docker Compose updates production stack
  -> Laravel migrate + ExpenseCategorySeeder
  -> Health check /up
```

Catatan kritis:

- Jangan menggunakan GitHub-hosted runner untuk SSH langsung ke `10.10.10.249`; IP tersebut private LAN dan tidak routable dari GitHub cloud.
- Jangan commit `.env.production`; file ini harus tetap berada di VM atau secret manager.
- Auto-deploy production aktif untuk setiap push ke branch `main` setelah self-hosted runner online.
