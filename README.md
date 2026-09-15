# 🏥 Hospital Management Information System (SIMRS)

> Sistem Informasi Manajemen Rumah Sakit (SIMRS) berbasis arsitektur *Modern Monolith* yang reaktif, tangguh, dan terstandarisasi. Dibangun menggunakan **Laravel 13**, **Inertia.js v3 (Vue 3 TypeScript)**, **Tailwind CSS v4**, **PostgreSQL 16**, serta **Laravel Reverb** untuk sinkronisasi antrean poliklinik dan transaksi kasir secara *real-time*.

[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?style=flat&logo=laravel&logoColor=white)](https://laravel.com)
[![Vue 3](https://img.shields.io/badge/Vue.js-3.5-4FC08D?style=flat&logo=vue.js&logoColor=white)](https://vuejs.org)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-v3-9553E9?style=flat&logo=inertia&logoColor=white)](https://inertiajs.com)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-4169E1?style=flat&logo=postgresql&logoColor=white)](https://www.postgresql.org)
[![PHPStan](https://img.shields.io/badge/PHPStan-Level%207-brightgreen.svg)](#-standar-kualitas-kode)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.2-3178C6?style=flat&logo=typescript&logoColor=white)](https://www.typescriptlang.org)

---

## 📋 Daftar Isi
1. [Deskripsi Proyek](#-deskripsi-proyek)
2. [Arsitektur Sistem & Tech Stack](#-arsitektur-sistem--tech-stack)
3. [Alur Kerja Sistem (Patient Journey)](#-alur-kerja-sistem-patient-journey)
4. [Fitur Utama per Modul](#-fitur-utama-per-modul)
5. [Prasyarat Sistem (Requirements)](#-prasyarat-sistem-requirements)
6. [Panduan Instalasi Langkah demi Langkah](#-panduan-instalasi-langkah-demi-langkah)
7. [Cara Menjalankan Aplikasi di Perangkat Lain](#-cara-menjalankan-aplikasi-di-perangkat-lain)
8. [Daftar Akun Demo & Kredensial Pengujian](#-daftar-akun-demo--kredensial-pengujian)
9. [Peta Navigasi URL Utama](#-peta-navigasi-url-utama)
10. [Standar Kualitas Kode](#-standar-kualitas-kode)
11. [Troubleshooting & Solusi Kendala Umum](#-troubleshooting--solusi-kendala-umum)

---

## 📌 Deskripsi Proyek

Aplikasi **SIMRS** ini dirancang untuk mendigitalisasi dan mengorkestrasi seluruh siklus operasional rumah sakit dan klinik rawat jalan dalam satu platform terpadu. Sistem ini menyelesaikan berbagai tantangan kritis pelayanan kesehatan:

* **Eliminasi Penumpukan & Antrean Buta**: Menggantikan papan antrean konvensional dengan layar TV antrean cerdas yang bersuara otomatis (Text-to-Speech bahasa Indonesia) tersinkronisasi instan dengan ruang periksa dokter melalui WebSockets.
* **Standardisasi Rekam Medis Elektronik (EMR)**: Dokumentasi klinis terstruktur menggunakan metodologi **SOAP** (*Subjective, Objective, Assessment, Plan*) dan pengindeksan diagnosis resmi **ICD-10**.
* **Keamanan Klinis Pasien (Clinical Safety)**: Evaluasi otomatis riwayat alergi pasien saat dokter meresepkan obat (*drug allergy alerts*).
* **Manajemen Farmasi Akurat (FEFO)**: Pengelolaan stok obat dengan metode *First Expired, First Out* (FEFO) dan peringatan otomatis untuk obat yang mendekati masa kedaluwarsa.
* **Kasir & Billing POS Multi-Channel**: Rekonsiliasi tagihan otomatis dari resep dan tindakan dengan dukungan multi-metode pembayaran (Tunai, EDC, Dynamic QRIS, dan Payment Gateway Xendit), cetak struk kasir thermal, serta manajemen sesi shift kasir.
* **Pendidikan Kedokteran Terpadu**: Modul buku log (*logbook*) klinis digital untuk mahasiswa Koas / Dokter Muda dengan alur peninjauan dan persetujuan oleh Dokter Spesialis Pembimbing (DPJP).
* **Kesiapan Interoperabilitas**: Modul konversi data klinis ke format standar **FHIR R4 Bundle** untuk integrasi ekosistem SatuSehat Kemenkes RI.
* **Kepatuhan Privasi (UU PDP)**: Pencatatan jejak audit (*audit trail*) setiap kali data rekam medis pasien diakses oleh tenaga kesehatan.

---

## 🏛 Arsitektur Sistem & Tech Stack

Sistem dibangun menggunakan pendekatan **Modern Monolith** yang menyatukan keandalan *backend* Laravel dengan pengalaman antarmuka SPA reaktif Vue 3 tanpa kompleksitas API terpisah.

* **Backend Framework**: Laravel 13 (PHP 8.3 / 8.4)
* **Frontend Architecture**: Inertia.js v3 + Vue 3 (Composition API, `<script setup>`, TypeScript)
* **Styling & UI**: Tailwind CSS v4, Lucide Vue Icons, Reka UI
* **Real-time WebSockets**: Laravel Reverb + Laravel Echo (`pusher-js`)
* **Basis Data**: PostgreSQL 16 (Integritas relasional ketat, tipe data JSONB, performa tinggi)
* **Dokumen & Cetak**: Barryvdh Laravel DomPDF (Resume Medis, Surat Sakit, Surat Rujukan, Struk Thermal Kasir)
* **Payment Gateway**: Xendit API (QRIS Dinamis, Virtual Account, Idempotent Webhooks)
* **Autentikasi & Otorisasi**: Laravel Fortify + Spatie Laravel Permission (RBAC)
* **Testing & Analisis**: Pest PHP, PHPStan (Level 7), Laravel Pint, ESLint, Prettier

---

## 🔄 Alur Kerja Sistem (Patient Journey)

```
[ Pasien ] ──> Reservasi Janji Temu Daring / Mandiri
      │
      ▼
[ Meja Depan / Front Office ] ──> Verifikasi Kedatangan (Check-in) Pasien
      │
      ▼
[ Triase Perawat ] ──> Pengukuran Tanda-Tanda Vital (TTV: Tensi, Nadi, Suhu, RR)
      │
      ▼
[ Layar TV Ruang Tunggu ] <── WebSockets Reverb ──> [ Ruang Periksa Dokter ]
   (Pemanggilan Suara TTS + Chime)                     (Panggil, Periksa SOAP, ICD-10, Resep)
      │
      ▼
[ Instalasi Farmasi ] ──> Validasi Resep, Alokasi Obat FEFO & Peracikan
      │
      ▼
[ Kasir & Billing POS ] ──> Pembayaran (Tunai/EDC/QRIS/Xendit) & Cetak Struk
      │
      ▼
[ Pasien Pulang / Rujukan ] ──> Cetak Resume Medis / Surat Sakit / Surat Rujukan
```

---

## 🌟 Fitur Utama per Modul

### 1. 📺 Layar Monitor TV Display Antrean Publik (`/display`)
* **Siaran Real-time Zero-Latency**: Berjalan di atas WebSocket server Laravel Reverb tanpa *polling* database.
* **Text-to-Speech (TTS) Otomatis**: Memanggil nomor antrean dan nama poliklinik tujuan dalam pelafalan Bahasa Indonesia baku (`id-ID`) menggunakan Web Speech API.
* **Audio Chime Rumah Sakit**: Nada panggil 4-nada khas sebelum pengumuman suara dimulai.
* **Edukasi Pasien**: Dilengkapi pemutar video edukasi kesehatan yang playlist-nya dapat diatur oleh administrator.

### 2. 🩺 Portal Dokter & Konsultasi Medis (`/doctor/queue`)
* **Konsol Pemanggilan Antrean**: Tombol panggil (*call*), periksa (*consult*), dan lewati (*skip*).
* **EMR Terstandarisasi SOAP**: Dokumentasi lengkap *Subjective, Objective, Assessment, Plan*.
* **Pencarian Cepat ICD-10**: Autocomplete katalog diagnosis ICD-10 resmi.
* **Asisten Klinis Cerdas**: Template catatan SOAP instan dan sistem deteksi riwayat alergi obat pasien secara *real-time*.
* **Resep Digital (E-Prescription)**: Pemilihan obat langsung terhubung dengan katalog farmasi aktif.
* **Riwayat Klinis Pasien**: Akses rekam medis kunjungan terdahulu secara aman.
* **Supervisi Koas**: Panel verifikasi dan peninjauan logbook tindakan klinis mahasiswa dokter muda.

### 3. 🧑‍⚕️ Workspace Staf & Perawat (`/staff/dashboard`)
* **Front Office Check-In**: Konfirmasi kedatangan pasien saat tiba di lokasi rumah sakit.
* **Pemeriksaan Tanda-Tanda Vital (TTV)**: Input tekanan darah (sistolik/diastolik), denyut nadi, frekuensi napas, dan suhu tubuh sebelum pasien masuk ke dokter.
* **Penerbitan Dokumen Resmi**: Cetak Resume Medis Klinis (PDF), Surat Keterangan Sakit, dan Surat Rujukan Pasien.

### 4. 💊 Farmasi & FEFO Inventory Engine (`/staff/medicines`)
* **FEFO Batching Engine**: Pengeluaran obat otomatis memprioritaskan stok dengan tanggal kedaluwarsa terdekat (*First Expired, First Out*).
* **Indikator Masa Kedaluwarsa**: Tanda visual peringatan obat mendekati kadaluwarsa (Warning < 30 hari, Danger < 7 hari).
* **Dispensing Real-time**: Notifikasi resep masuk dari ruang dokter secara langsung melalui WebSocket channel privat.
* **Penyesuaian Stok**: Fitur stok opname dan penyesuaian kuantitas obat.

### 5. 💳 Kasir POS & Rekonsiliasi Tagihan (`/staff/billing`)
* **Perhitungan Tagihan Otomatis**: Akumulasi biaya konsultasi dokter, tindakan medis, dan rincian obat resep.
* **Multi-Channel Payment**: Mendukung Tunai (kembalian otomatis), Kartu Debit/Kredit EDC, Dynamic QRIS Canvas, dan Xendit Payment Gateway.
* **Manajemen Sesi Shift Kasir**: Buka shift dengan modal awal (*opening cash*), rekap transaksi berjalan, tutup shift (*closing cash*), dan cetak ringkasan shift.
* **Cetak Struk Transaksi**: Mendukung format invoice formal (PDF) dan format thermal printer kasir (58mm / 80mm).

### 6. 🎓 Digital Logbook Mahasiswa Koas (`/koas/logbook`)
* **Pencatatan Tindakan Klinis**: Input nama tindakan medis, kategori keterampilan, diagnosis terkait, dan refleksi kasus.
* **Alur Persetujuan (Approval)**: Pengajuan entri logbook untuk diperiksa dan disetujui oleh dokter spesialis pembimbing (DPJP).

### 7. 📱 Portal Pasien & Reservasi Daring (`/patient/dashboard`)
* **Pendaftaran Janji Temu**: Pemilihan poli, dokter, dan jadwal dengan batas kuota otomatis.
* **Riwayat Kunjungan**: Pemantauan status antrean dan riwayat pemeriksaan pribadi.

### 8. 🛡️ Tata Kelola Super Admin & Audit Regulasi (`/admin/dashboard`)
* **Manajemen Pengguna & Tenaga Medis**: Pembuatan akun dokter, perawat, penugasan poli, pengaturan jadwal, dan aktivasi akun.
* **Master Fasilitas**: Pengelolaan data poliklinik, ruangan pemeriksaan, dan kuota antrean harian.
* **Audit Trail Akses Data Medis**: Pencatatan riwayat akses data pasien (siapa, kapan, dan data apa yang diakses) sesuai amanat regulasi UU Perlindungan Data Pribadi (UU PDP).
* **Ekspor FHIR R4 SatuSehat**: Endpoint konversi rekam medis ke standar JSON FHIR R4 Kemenkes (`/api/satusehat/records/{id}/fhir-bundle`).

---

## 💻 Prasyarat Sistem (Requirements)

Sebelum menginstal proyek di perangkat lain (Windows, macOS, atau Linux), pastikan perangkat telah terpasang:

| Komponen | Versi Minimum | Keterangan |
| :--- | :--- | :--- |
| **PHP** | `>= 8.3` (Disarankan PHP 8.3 atau 8.4) | CLI & Web Server |
| **Composer** | `>= 2.2` | Manajemen dependensi PHP |
| **Node.js** | `>= 18.x` atau `20.x LTS` | Runtime JavaScript |
| **NPM** | `>= 9.x` | Manajemen paket frontend |
| **PostgreSQL** | `>= 15` (Disarankan versi 16) | Database Server Utama |
| **Git** | Versi terbaru | Version control |

### Ekstensi PHP Wajib
Pastikan ekstensi berikut aktif di file konfigurasi `php.ini` Anda:
* `pdo_pgsql` & `pgsql` *(Wajib untuk koneksi basis data PostgreSQL)*
* `openssl`
* `mbstring`
* `curl`
* `fileinfo`
* `gd`
* `intl`
* `xml`
* `zip`

> 💡 **Pengguna Windows (Laragon / XAMPP)**: Buka menu PHP Extensions dan centang `pdo_pgsql` serta `pgsql`.

---

## 🚀 Panduan Instalasi Langkah demi Langkah

Ikuti langkah-langkah berikut secara berurutan untuk memasang proyek ini di perangkat baru:

### 1. Clone Repository
Buka terminal dan unduh repositori proyek:
```bash
git clone https://github.com/stravsxgood/Hospital.git
cd Hospital
```

### 2. Instal Dependensi Backend (Composer)
Unduh seluruh pustaka PHP yang dibutuhkan:
```bash
composer install
```

### 3. Instal Dependensi Frontend (NPM)
Pasang seluruh paket JavaScript dan Vue 3:
```bash
npm install
```

### 4. Konfigurasi Environment (`.env`)
Salin file template `.env.example` menjadi `.env`:
```bash
# Windows (Command Prompt)
copy .env.example .env

# Windows (PowerShell) / Linux / macOS
cp .env.example .env
```

Generate application key Laravel:
```bash
php artisan key:generate
```

Buka file `.env` dengan teks editor favorit Anda, lalu sesuaikan konfigurasi berikut:

#### a. Konfigurasi Database PostgreSQL
Sesuaikan dengan kredensial PostgreSQL yang ada di perangkat Anda:
```ini
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=hospital
DB_USERNAME=postgres
DB_PASSWORD=password_postgres_anda
```

#### b. Konfigurasi Real-time WebSocket (Laravel Reverb)
Pastikan parameter siaran WebSocket Reverb di `.env` terkonfigurasi sebagai berikut:
```ini
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=100001
REVERB_APP_KEY=ehgs1mjbjxtjxdgnwpqe
REVERB_APP_SECRET=reverbsecret
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME="http"

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

#### c. Konfigurasi Xendit Payment Gateway (Opsional untuk Uji Coba Sandbox)
```ini
XENDIT_SECRET_KEY=xnd_development_...
XENDIT_PUBLIC_KEY=xnd_public_development_...
XENDIT_WEBHOOK_TOKEN=0ZMZ...
```

### 5. Buat Database di PostgreSQL
Buat basis data baru bernama `hospital` pada server PostgreSQL Anda:
* **Via Terminal CLI (psql)**:
  ```bash
  createdb -U postgres hospital
  ```
* **Via pgAdmin / DBeaver**:
  Jalankan query SQL:
  ```sql
  CREATE DATABASE hospital;
  ```

### 6. Jalankan Migrasi & Seeding Data
Isi basis data dengan seluruh struktur tabel serta data master awal (spesialisasi, poliklinik, 100 dokter spesialis beserta jadwal praktiknya, katalog ICD-10, template SOAP, obat & batch FEFO, serta akun pengguna pengujian):
```bash
php artisan migrate --seed
```

### 7. Buat Symbolic Link Storage
Pastikan folder storage terhubung untuk aset publik dan berkas dokumen:
```bash
php artisan storage:link
```

---

## ⚡ Cara Menjalankan Aplikasi di Perangkat Lain

Untuk menjalankan SIMRS dengan seluruh fungsi real-time dan tampilan antarmukanya, terdapat beberapa metode yang dapat dipilih:

### Metode 1: Menjalankan Servis Terpisah (Sangat Disarankan untuk Dev)
Buka **3 terminal terpisah** di direktori proyek:

* **Terminal 1 — Web Application Server**:
  ```bash
  php artisan serve
  ```
  *(Aplikasi berjalan pada `http://127.0.0.1:8000`)*

* **Terminal 2 — WebSocket Server (Laravel Reverb)**:
  ```bash
  php artisan reverb:start
  ```
  *(Menangani siaran panggil antrean TV, notifikasi resep farmasi, dan event kasir pada port 8080)*

* **Terminal 3 — Frontend Asset Compiler (Vite)**:
  ```bash
  npm run dev
  ```
  *(Menjalankan Vite HMR development server)*

> 💡 **Terminal 4 (Opsional - Queue Worker)**: Jika Anda ingin memproses background jobs/queue:
> ```bash
> php artisan queue:work
> ```

---

### Metode 2: Menjalankan Sekaligus (All-in-One Command)
Anda juga dapat menjalankan aplikasi menggunakan skrip bawaan Laravel:
```bash
composer run dev
```
Perintah ini akan mengeksekusi `php artisan dev` yang mengelola web server dan Vite secara bersamaan. Namun, **pastikan tetap menjalankan `php artisan reverb:start` di terminal lain** agar fitur pemanggilan antrean dan WebSocket berfungsi penuh.

---

### Metode 3: Menjalankan & Mengakses dari Perangkat Lain dalam Jaringan LAN
Fitur ini digunakan saat Anda ingin membuka **Layar TV Antrean di Smart TV ruang tunggu**, **tablet perawat**, atau **laptop dokter** yang terhubung ke jaringan Wi-Fi/LAN yang sama:

1. **Cari Tahu Alamat IP Lokal Komputer Server**:
   * Windows: Jalankan `ipconfig` (misal: `192.168.1.50`)
   * Linux/macOS: Jalankan `ip a` atau `ifconfig`
2. **Sesuaikan `.env` Komputer Server**:
   ```ini
   APP_URL=http://192.168.1.50:8000
   REVERB_HOST="192.168.1.50"
   VITE_REVERB_HOST="192.168.1.50"
   ```
3. **Jalankan Servis dengan Bind Host `0.0.0.0`**:
   * Terminal 1:
     ```bash
     php artisan serve --host=0.0.0.0 --port=8000
     ```
   * Terminal 2:
     ```bash
     php artisan reverb:start --host=0.0.0.0 --port=8080
     ```
   * Terminal 3:
     ```bash
     npm run dev -- --host
     ```
4. **Buka dari Perangkat Lain**:
   * Layar TV Ruang Tunggu: Buka `http://192.168.1.50:8000/display`
   * Laptop Dokter / Tablet Staf: Buka `http://192.168.1.50:8000`

---

## 🔑 Daftar Akun Demo & Kredensial Pengujian

Basis data seeder telah menyediakan akun pengguna realistis untuk setiap peran:

| Peran (Role) | Email | Password | Halaman Utama & Hak Akses |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `admin@hospital.com` | `password123` | `/admin/dashboard` (Master Poli, User, Jadwal, Video Display, Audit Log) |
| **Dokter Spesialis (DPJP)** | `dokter@hospital.com` | `password123` | `/doctor/queue` (Konsol Panggilan Antrean, EMR SOAP, ICD-10, Resep) |
| **Perawat & Staf Meja Depan** | `perawat@hospital.com` | `password123` | `/staff/dashboard` (Check-in, Triase TTV, Resume Medis, Surat Sakit) |
| **Kasir & Billing POS** | `perawat@hospital.com` | `password123` | `/staff/billing` (Kasir POS, Shift Kasir, Cetak Struk Thermal / PDF) |
| **Farmasi & Apoteker** | `perawat@hospital.com` | `password123` | `/staff/medicines` (FEFO Inventory, Validasi & Dispensing Resep) |
| **Pasien** | `pasien@hospital.com` | `password123` | `/patient/dashboard` (Booking Antrean Online, Riwayat Janji Temu) |

> 📌 **Catatan Tambahan**:
> * Seeder juga membuat 100 akun dokter tambahan: `dokter1@hospital.com` hingga `dokter100@hospital.com` dengan password: `password`.
> * Akun alternatif Super Admin: `admin@hospital.test` dengan password: `password123`.

---

## 🗺 Peta Navigasi URL Utama

Setelah aplikasi berjalan, Anda dapat langsung menguji endpoint berikut:

* **Publik**:
  * `/` : Halaman Beranda & Profil Rumah Sakit
  * `/display` : Layar TV Antrean Ruang Tunggu (Buka di tab browser terpisah untuk uji pemanggilan suara)
  * `/schedule-guest` : Direktori Jadwal Dokter Terbuka
  * `/specializations` : Layanan & Spesialisasi Medis
  * `/teams` : Tim Medis & Fasilitas Poliklinik
  * `/clinic-location` : Lokasi & Jaringan Klinik
* **Autentikasi**:
  * `/login` : Masuk Pengguna Terdaftar
  * `/register` : Registrasi Pasien Baru
* **Internal Klinik**:
  * `/patient/dashboard` : Dasbor Pasien & Booking Antrean
  * `/doctor/queue` : Konsol Antrean Poli & Form Pemeriksaan SOAP Dokter
  * `/doctor/supervision` : Supervisi Kasus Mahasiswa Koas oleh DPJP
  * `/staff/dashboard` : Meja Depan (Check-in) & Triase Perawat (TTV)
  * `/staff/billing` : Modul Kasir & Billing POS
  * `/staff/medicines` : Manajemen Stok Farmasi & Batch FEFO
  * `/koas/logbook` : Buku Log Tindakan Klinis Mahasiswa Koas
  * `/admin/dashboard` : Tata Kelola Super Administrator

---

## 🧪 Standar Kualitas Kode

Repositori ini menerapkan standar rekayasa perangkat lunak ketat untuk memastikan keandalan sistem:

```bash
# 1. Jalankan Unit & Feature Tests (Pest PHP)
php artisan test

# 2. Analisis Statis PHPStan (Level 7)
composer run types:check
# atau
./vendor/bin/phpstan analyse --memory-limit=2G

# 3. Pengecekan Gaya Kode PHP (Laravel Pint)
vendor/bin/pint --format agent

# 4. Validasi Tipe Data TypeScript Vue 3
npm run types:check

# 5. Pengecekan Linting ESLint
npm run lint:check
```

---

## 🛠 Troubleshooting & Solusi Kendala Umum

### 1. `SQLSTATE[08006] connection to server failed: Connection refused`
* **Penyebab**: Service PostgreSQL belum berjalan atau port 5432 tidak aktif.
* **Solusi**:
  * Pastikan PostgreSQL service sudah dijalankan di komputer Anda.
  * Periksa kembali `DB_HOST`, `DB_PORT`, `DB_USERNAME`, dan `DB_PASSWORD` di file `.env`.
  * Pastikan database `hospital` sudah dibuat di PostgreSQL.

### 2. `could not find driver` atau Driver PostgreSQL tidak terbaca
* **Penyebab**: Ekstensi PHP `pdo_pgsql` belum diaktifkan di `php.ini`.
* **Solusi**: Buka file `php.ini` Anda, cari baris `;extension=pdo_pgsql` dan `;extension=pgsql`, lalu hilangkan tanda titik koma (`;`) di awal baris dan restart web server Anda.

### 3. Layar Display Antrean (`/display`) Tidak Mengeluarkan Suara Panggilan
* **Penyebab**: Kebijakan keamanan browser modern memblokir *autoplay audio* sebelum pengguna melakukan interaksi (klik) pada halaman web.
* **Solusi**: Klik di sembarang area pada layar `/display` satu kali saat halaman pertama kali dimuat untuk memberikan izin pemutaran audio dan Text-to-Speech kepada browser.

### 4. Pemanggilan Antrean Tidak Muncul Real-Time di Layar Display
* **Penyebab**: Servis WebSocket Laravel Reverb belum aktif atau terjadi perbedaan konfigurasi port/host.
* **Solusi**:
  * Pastikan perintah `php artisan reverb:start` sedang berjalan di salah satu terminal.
  * Pastikan `BROADCAST_CONNECTION=reverb` pada file `.env`.
  * Jika mengubah variabel `VITE_REVERB_*` di `.env`, jalankan ulang perintah `npm run dev` agar Vite memuat variabel lingkungan yang baru.

### 5. `Vite manifest not found at: .../public/build/manifest.json`
* **Penyebab**: Aset frontend belum dikompilasi atau Vite dev server belum berjalan.
* **Solusi**: Jalankan `npm run dev` untuk mode pengembangan, atau jalankan `npm run build` untuk membuat berkas build produksi.

### 6. Reset Total Database Jika Data Mengalami Inkonsistensi
* Jika Anda ingin mereset ulang seluruh data seeder ke kondisi awal pabrik:
  ```bash
  php artisan migrate:fresh --seed
  ```

---

## 📄 Lisensi
Proyek SIMRS ini dilisensikan di bawah [MIT License](LICENSE).
