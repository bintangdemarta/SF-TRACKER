# Product Requirement Document (PRD)

**Nama Produk:** ShopeeFood Driver Finance Tracker (SF-Tracker)

**Versi:** 1.0.0 (MVP)

**Status:** Ready for Development

**Target Platform:** Mobile-First Web App (PWA)

**Tech Stack:** Laravel 11, Livewire v3 / Alpine.js, Tailwind CSS, PostgreSQL/MySQL

---

## 1. Executive Summary & Problem Statement

### 1.1 Latar Belakang

Mitra pengemudi ShopeeFood beroperasi dalam lingkungan finansial dengan perputaran kas harian yang cepat dan berlapis (*multi-source income* & *fragmented operational costs*). Banyak pengemudi mengalami fenomena *false revenue*—merasa berpenghasilan cukup karena perputaran saldo aplikasi dan uang tunai tinggi, namun mengalami defisit kas akibat tidak terhitungnya biaya operasional harian, biaya tersembunyi kendaraan, serta tercampurnya modal kerja dengan uang pribadi.

### 1.2 Problem Statement

1. **Pemisahan Kas yang Lemah:** Uang talangan resto, pembayaran tunai dari konsumen, dan uang pribadi berada dalam satu kantong fisik yang sama.
2. **Ketiadaan Visibilitas Profit Riil:** Tidak adanya kalkulasi instan mengenai *Net Take-Home Pay* setelah dipotong bensin, amortisasi perawatan kendaraan (servis/oli), dan biaya mikro (parkir).
3. **Pencatatan Lapangan Tidak Praktis:** Solusi pencatatan umum membutuhkan waktu pengisian yang panjang dan tidak dirancang untuk interaksi cepat dengan satu tangan di jalan (*one-hand quick input*).

---

## 2. User Persona

| Atribut | Deskripsi |
| --- | --- |
| **Profil** | Pengemudi aktif (full-time / part-time) ShopeeFood. |
| **Karakteristik Operasional** | Durasi narik 8–12 jam/hari, mobilitas tinggi, berhenti singkat di lampu merah atau resto. |
| **Pain Points** | Lelah merekap di malam hari, uang modal sering terpakai untuk konsumsi, bingung menentukan efisiensi bahan bakar motor. |
| **Kebutuhan Kritis** | Input data di bawah 5 detik, antarmuka sederhana dengan tombol sentuh besar (*numpad friendly*), dan metrik profit langsung terlihat. |

---

## 3. Product Goals & Success Metrics

### 3.1 Business & User Goals

* Memberikan kepastian laba bersih (*Net Profit*) harian secara *real-time*.
* Mencegah defisit operasional melalui pembagian dompet (*Dual-Wallet Partitioning*).
* Menjaga integritas data finansial pengemudi dengan pencatatan terstruktur.

### 3.2 Key Performance Indicators (KPIs)

* **Time-to-Log:** Waktu rata-rata untuk mencatat satu transaksi $\le 5$ detik.
* **Daily Active Logging:** Rata-rata pengguna aktif menyelesaikan pencatatan hingga sesi *End Shift*.
* **Reconciliation Accuracy:** Selisih antara saldo fisik/digital aktual dengan catatan sistem $= 0$ di akhir shift.

---

## 4. Scope of Work (Features & Requirements)

### 4.1 Modul 1: Shift & Delivery Management (Core)

```
[Start Shift] ──> [Log Trip / Log Expense] ──> [Track Target/Points] ──> [End Shift]

```

* **FR-1.1: Shift Session Handling**
* Input Odometer Awal (KM) & Target Pendapatan saat *Start Shift*.
* Input Odometer Akhir (KM) saat *End Shift*.
* Kalkulasi otomatis total jarak tempuh harian ($KM_{akhir} - KM_{awal}$) dan durasi kerja.


* **FR-1.2: Trip Quick-Logger**
* Nomor Pesanan / Order ID (opsional untuk kecepatan input).
* Pendapatan trip: Argo bersih (ongkir mitra).
* Tips: Pisahkan antara Tip Tunai (langsung ke tangan) dan Tip Digital (masuk saldo aplikasi).
* Poin/Berlian: Akumulasi poin per trip untuk kalkulasi target insentif harian.



### 4.2 Modul 2: Operational Expense & Sinking Fund

* **FR-2.1: Expense Categories**
* *BBM:* Nominal Rupiah, volume liter (opsional), odometer saat isi.
* *Biaya Mikro:* Parkir, retribusi, makan/minum harian.
* *Pemeliharaan:* Servis rutin, ganti oli mesin/gardan, perbaikan ban.
* *Sinking Fund (Amortisasi Kendaraan):* Alokasi otomatis atau manual per hari (misal: Rp10.000/hari disisihkan untuk servis besar & pajak kendaraan).


* **FR-2.2: Payment Source Tagging**
* Setiap pengeluaran wajib ditandai: dibayar menggunakan **Dompet Tunai Fisik** atau **Saldo Dompet Digital/Mitra**.



### 4.3 Modul 3: Dual-Wallet Reconciliation Engine

Sistem mengelola dua kantong saldo terpisah secara simultan:

```
┌─────────────────────────────────────────────────────────────┐
│                 DUAL-WALLET ARCHITECTURE                    │
├──────────────────────────────┬──────────────────────────────┤
│    1. DOMPET TUNAI FISIK     │   2. SALDO MITRA / DIGITAL   │
├──────────────────────────────┼──────────────────────────────┤
│ (+) Pendapatan Tunai/Tip     │ (+) Ongkir Non-Tunai / Argo  │
│ (+) Penarikan Kas (Withdraw) │ (+) Insentif / Berlian       │
│ (-) Uang Talangan Resto      │ (+) Tip dari Aplikasi        │
│ (-) Pengeluaran Kas (BBM/dll)│ (-) Potongan Sistem          │
│                              │ (-) Transfer Out / Withdraw  │
└──────────────────────────────┴──────────────────────────────┘

```

* **FR-3.1: Automatic Balance Ledger**
* Menampilkan posisi saldo uang tunai fisik yang wajib ada di kantong pengemudi.
* Menampilkan posisi saldo digital di akun ShopeePay pengemudi.


* **FR-3.2: Cash-in-Hand Safety Check**
* Peringatan jika pengeluaran tunai melebihi kas yang tersedia (mencegah uang modal/talangan resto terpakai).



### 4.4 Modul 4: Analytics & Real-Time KPI

* **FR-4.1: Live Metrics Dashboard**
* **Gross Revenue:** Total argo + total insentif + total tips.
* **Operational Cost:** Total pengeluaran harian + alokasi *sinking fund*.
* **Net Profit:** $\text{Gross Revenue} - \text{Operational Cost}$.
* **Cost per KM:** $\frac{\text{Total Pengeluaran BBM \& Servis}}{\text{Total Jarak Tempuh (KM)}}$.
* **Hourly Rate:** $\frac{\text{Net Profit}}{\text{Total Jam Kerja (Shift)}}$.


* **FR-4.2: Historical Reporting**
* Filter rentang tanggal (Harian, Mingguan, Bulanan).
* Ekspor rekapan ke format CSV/Excel.



---

## 5. Non-Functional & Technical Requirements

| Parameter | Spesifikasi |
| --- | --- |
| **Design Paradigm** | Mobile-First Responsive, High Contrast UI (terbaca di bawah sinar matahari), Dark Mode ready. |
| **Performance** | Waktu render halaman $< 1.5$ detik pada jaringan seluler 4G/3G. |
| **Data Integrity** | Tipe data moneter menggunakan `DECIMAL(12, 2)` atau `BIGINT` (satuan Rupiah bulat). Operasi finansial wajib menggunakan DB Transactions (`DB::transaction`). |
| **Offline Resilience** | Form quick-entry menggunakan caching lokal (Alpine.js / LocalStorage) untuk mencegah data hilang saat sinyal *drop*. |

---

## 6. Entity Relationship Model (High-Level Data Schema)

```
[ users ]
   │ 1
   ├──────── n ─── [ expense_categories ]
   │
   ├──────── n ─── [ wallet_transactions ]
   │
   └──────── n ─── [ shift_sessions ]
                         │ 1
                         ├──────── n ─── [ trip_logs ]
                         │
                         └──────── n ─── [ expenses ]

```

### Definisi Tabel Kunci:

1. `shift_sessions`: `id`, `user_id`, `start_odometer`, `end_odometer`, `started_at`, `ended_at`, `target_income`, `status` (active/closed).
2. `trip_logs`: `id`, `shift_session_id`, `order_id` (nullable), `fare_amount`, `tip_cash`, `tip_app`, `points_earned`, `distance_km`, `created_at`.
3. `expenses`: `id`, `user_id`, `shift_session_id` (nullable), `category_id`, `amount`, `payment_source` (enum: `cash`, `digital_balance`), `odometer` (nullable), `notes`.
4. `wallet_transactions`: `id`, `user_id`, `type` (enum: `withdraw`, `deposit`, `adjustment`), `source_wallet` (enum: `cash`, `digital`), `amount`, `balance_after`, `created_at`.

---

## 7. Roadmap Implementasi (Milestones)

```
Phase 1: Core Setup & Migrations (Days 1-2)
└── Setup Laravel 11, Breeze, Database Migrations, Seeders Kategori Biaya.

Phase 2: Shift Engine & Fast-Input UI (Days 3-5)
└── Implementasi Livewire components untuk Start/End Shift dan One-Tap Trip Logging.

Phase 3: Financial Calculations & Ledger Logic (Days 6-8)
└── Pembuatan Service Classes: FinancialMetricsService, WalletReconciliationService.

Phase 4: Dashboard & Analytics (Days 9-10)
└── Pembuatan UI Dashboard, visualisasi Rp/KM, Net Profit, dan modul export Excel.

Phase 5: Mobile Hardening & PWA (Days 11-12)
└── PWA Manifest, Service Worker setup, testing performa pada perangkat mobile kelas entry-level.

```

---

## 8. Out of Scope (Non-MVP)

* Integrasi otomatis *scraping* / integrasi API resmi ke server ShopeeFood (input tetap manual demi kepatuhan ToS dan reliabilitas sistem).
* Multi-platform courier support (Grab/Gojek) — difokuskan secara spesifik pada skema poin dan terminologi ShopeeFood terlebih dahulu.
* Sistem OCR (scan struk/bon belanja) otomatis berbasis AI.

---

Apakah Anda ingin melanjutkan ke pembuatan **file migrasi database Laravel** atau fokus pada rancangan antarmuka komponen **Quick-Input Form Livewire v3** terlebih dahulu?