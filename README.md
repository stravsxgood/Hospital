# 🏥 Hospital Management Information System (SIMRS)

> Enterprise-grade Hospital Information System built with Laravel, Inertia.js (Vue 3), and PostgreSQL. Designed for high-reliability medical workflows, real-time outpatient queue orchestration, and strict financial transaction reconciliation.

[![PHPStan](https://img.shields.io/badge/PHPStan-Level%207-brightgreen.svg)](#code-quality--standards)
[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![Vue](https://img.shields.io/badge/Vue.js-3.x-green.svg)](https://vuejs.org)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-blue.svg)](https://www.postgresql.org)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-Adapter-purple.svg)](https://inertiajs.com)

---

## 📌 Executive Summary

SIMRS ini dibangun untuk mendemonstrasikan implementasi arsitektur monolit modern (*modern monolith*) yang menangani domain kompleks: alur rekam medis rawat jalan, dokumentasi klinis terstandarisasi (SOAP & ICD-10), sinkronisasi antrean klinik berbasis *event-driven* WebSocket, serta orkestrasi inventaris farmasi dan pembayaran *multi-channel*.

---

## 🏛 System Architecture & Tech Stack

* **Backend Core**: Laravel 11 (PHP 8.2+)
* **Database**: PostgreSQL 16 (Relational integrity, JSONB support, strict ACID)
* **Frontend Layer**: Vue 3 (Composition API, TypeScript) via Inertia.js
* **Real-time Engine**: Laravel Reverb (WebSocket server for live queue broadcasting)
* **Styling**: Tailwind CSS
* **Payment Integration**: Xendit Payment Gateway (QRIS & Virtual Account)
* **Static Analysis & Linting**: PHPStan (Level 7), Laravel Pint, ESLint

---

## ⚙️ Key Engineering Challenges & Solutions

### 1. Real-Time Outpatient Queue Synchronization (Event-Driven)
* **Problem**: Penumpukan antrean pasien poli tanpa feedback langsung sering memicu *race condition* status periksa dan *polling overhead* di database.
* **Implementation**: Memanfaatkan **Laravel Reverb** untuk *broadcasting* status loket dan antrean poli secara real-time via WebSockets. Layar display antrean dan dashboard dokter tersinkronisasi otomatis tanpa refresh browser saat dokter memanggil nomor berikutnya.

### 2. Standardized Clinical Documentation (EMR)
* **Problem**: Integritas data rekam medis sering rapuh jika format catatan medis tidak terstruktur.
* **Implementation**:
  * Implementasi *workflow* catatan medis terstruktur **SOAP** (*Subjective, Objective, Assessment, Plan*).
  * Pengindeksan diagnosis terstandarisasi menggunakan katalog **ICD-10** untuk audit klinis dan pelaporan.

### 3. Idempotent Payment & Automated Dispensing
* **Problem**: Inkonsistensi stok obat saat pembayaran tertunda atau terjadi *duplicate webhook triggers*.
* **Implementation**:
  * Integrasi **Xendit Payment Gateway** (QRIS & VA) menggunakan *idempotency keys* dan verifikasi *signature webhook*.
  * *Database transactions* otomatis memotong stok farmasi hanya saat status tagihan dinyatakan `PAID`, mencegah kondisi *stock drift*.

### 4. Granular Role-Based Access Control (RBAC)
* Memisahkan otorisasi untuk 5 peran pengguna:
  * **Super Admin / Rekam Medis**: Manajemen master data & audit log.
  * **Dokter**: Input SOAP, diagnosis ICD-10, dan resep digital.
  * **Perawat**: Triase awal, tanda-tanda vital (TTV), dan pemanggilan antrean.
  * **Farmasi**: Validasi resep dan penyerahan obat.
  * **Kasir**: Cetak invoice dan rekonsiliasi pembayaran.

---

## 🧪 Code Quality & Standards

Repositori ini menerapkan kontrol kualitas kode ketat sebelum perubahan di-*merge*:

* **Static Analysis**: `PHPStan Level 7` diterapkan untuk mendeteksi *type mismatches*, *unhandled nulls*, dan *dead code*.
  ```bash
  ./vendor/bin/phpstan analyse
  ```
* **Code Style**: Format seragam dengan standar Laravel via `Laravel Pint`.
  ```bash
  ./vendor/bin/pint --test
  ```
* **Type Safety**: TypeScript pada sisi klien Vue 3 untuk menjaga validasi payload Inertia.

---

## 🚀 Local Setup & Installation

### Prerequisites
* PHP >= 8.2
* Composer
* Node.js >= 18.x & NPM
* PostgreSQL >= 15

### Step-by-Step

1. **Clone repository**
   ```bash
   git clone [https://github.com/stravsxgood/SIMRS.git](https://github.com/stravsxgood/SIMRS.git)
   cd SIMRS
   ```

2. **Setup Dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Sesuaikan konfigurasi kredensial PostgreSQL dan Xendit Sandbox di file `.env`.*

4. **Database Migration & Seeding**
   ```bash
   php artisan migrate --seed
   ```

5. **Start Development Services**
   ```bash
   # Terminal 1: Application Server
   php artisan serve

   # Terminal 2: WebSocket Server (Laravel Reverb)
   php artisan reverb:start

   # Terminal 3: Vite Dev Server
   npm run dev
   ```

---

## 🔑 Demo & Test Credentials

Aplikasi sudah dilengkapi seeder data realistis untuk pengujian setiap role:

| Role | Email | Password | Akses Utama |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@simrs.test` | `password` | Master Data, User Roles |
| **Dokter** | `dokter@simrs.test` | `password` | EMR (SOAP), Resep, Antrean Poli |
| **Perawat** | `perawat@simrs.test` | `password` | Triase, TTV Pasien |
| **Farmasi** | `apotek@simrs.test` | `password` | Dispensing Obat, Stok |
| **Kasir** | `kasir@simrs.test` | `password` | Invoice & Payment Gateway |

---

## 👤 Author

* **Qusay Adya Galaghazy**
* GitHub: [@stravsxgood](https://github.com/stravsxgood)
* LinkedIn: [linkedin.com/in/qusay-adya](https://linkedin.com/in/qusay-adya)
* Email: qusayadya0@gmail.com