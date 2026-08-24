# Hospital Information System (SIMRS)

Sistem Informasi Manajemen Rumah Sakit (SIMRS) berbasis web *full-stack enterprise* dengan tata kelola rekam medis digital (RME), manajemen antrean poliklinik *real-time*, modul farmasi & billing terintegrasi, serta dashboard Super Admin dengan kontrol otorisasi granular.

---

### Tech Stack & Libraries

* **Backend Framework:** Laravel (PHP 8.3+)
* **Database Engine:** PostgreSQL (v15+)
* **Frontend Architecture:** Inertia.js, Vue 3 (TypeScript, Composition API), Tailwind CSS
* **Access Control & Security:** Spatie `laravel-permission` (RBAC), Laravel Sanctum / Fortify (Passkeys & 2FA)
* **Real-time WebSockets:** Laravel Reverb & Laravel Echo
* **Payment Gateway:** Xendit API (QRIS & Virtual Account)
* **Icons & Tooling:** Lucide Vue Next, Vite (manual vendor chunking & code splitting)

---

### Fitur Utama

* **Super Admin & Tata Kelola Pengguna:**
  * Registrasi akun dokter (SIP, spesialisasi), perawat (STR), dan koas via `DB::transaction`.
  * Manajemen status nonaktif aman (*safe deactivation*) tanpa merusak integritas data riwayat medis.
  * Master fasilitas: Poliklinik, Ruangan, Jadwal Praktik Dokter & Kuota Antrean Pasien.
  * Dashboard Analitik: Rekapitulasi pendapatan (Tunai vs QRIS Xendit), statistik 10 besar penyakit (ICD-10), dan audit trail log akses rekam medis.
* **Pelayanan Rawat Jalan & Antrean:**
  * Sistem pemanggilan antrean poli otomatis berbasis WebSockets secara *real-time*.
  * Dokumentasi Rekam Medis Elektronik (RME) format SOAP terstandarisasi.
* **Farmasi & Billing (Kasir):**
  * Manajemen resep dokter & stok obat metode FEFO (*First Expired, First Out*).
  * Kalkulasi invoice otomatis dengan metode pembayaran ganda (Tunai & QRIS Xendit).
* **UI/UX & Performa:**
  * Tampilan responsif *mobile-first* (Smartphone, Tablet/iPad, Desktop).
  * Arsitektur frontend teroptimasi audit Lighthouse (skor 90+).

---

### Prasyarat Sistem (Prerequisites)

Sebelum memulai instalasi, pastikan lingkungan server/lokal Anda memenuhi prasyarat berikut:

* **PHP:** Versi 8.3 atau lebih baru (Ekstensi aktif: `pdo_pgsql`, `pgsql`, `mbstring`, `exif`, `pcntl`, `bcmath`, `gd`)
* **Database:** PostgreSQL Server versi 15.x atau 16.x
* **Node.js:** Versi 20.x LTS atau lebih baru & NPM
* **Package Manager:** Composer versi 2.x

---

### Panduan Instalasi Lengkap

**1. Clone Repositori**
```bash
git clone https://github.com/username/simrs-app.git
cd simrs-app