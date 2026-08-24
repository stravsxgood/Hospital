# Hospital Information System (SIMRS)

Sistem Informasi Manajemen Rumah Sakit (SIMRS) berbasis web _full-stack enterprise_ dengan tata kelola rekam medis digital (RME), manajemen antrean poliklinik _real-time_, modul farmasi & billing terintegrasi, serta dashboard Super Admin dengan kontrol otorisasi granular.

---

### Tech Stack & Libraries

- **Backend Framework:** Laravel (PHP 8.3+)
- **Database Engine:** PostgreSQL (v15+)
- **Frontend Architecture:** Inertia.js, Vue 3 (TypeScript, Composition API), Tailwind CSS
- **Access Control & Security:** Spatie `laravel-permission` (RBAC), Laravel Sanctum / Fortify (Passkeys & 2FA)
- **Real-time WebSockets:** Laravel Reverb & Laravel Echo
- **Payment Gateway:** Xendit API (QRIS & Virtual Account)
- **Icons & Tooling:** Lucide Vue Next, Vite (manual vendor chunking & code splitting)

---

### Fitur Utama

- **Super Admin & Tata Kelola Pengguna:**
    - Registrasi akun dokter (SIP, spesialisasi), perawat (STR), dan koas via `DB::transaction`.
    - Manajemen status nonaktif aman (_safe deactivation_) tanpa merusak integritas data riwayat medis.
    - Master fasilitas: Poliklinik, Ruangan, Jadwal Praktik Dokter & Kuota Antrean Pasien.
    - Dashboard Analitik: Rekapitulasi pendapatan (Tunai vs QRIS Xendit), statistik 10 besar penyakit (ICD-10), dan audit trail log akses rekam medis.
- **Pelayanan Rawat Jalan & Antrean:**
    - Sistem pemanggilan antrean poli otomatis berbasis WebSockets secara _real-time_.
    - Dokumentasi Rekam Medis Elektronik (RME) format SOAP terstandarisasi.
- **Farmasi & Billing (Kasir):**
    - Manajemen resep dokter & stok obat metode FEFO (_First Expired, First Out_).
    - Kalkulasi invoice otomatis dengan metode pembayaran ganda (Tunai & QRIS Xendit).
- **UI/UX & Performa:**
    - Tampilan responsif _mobile-first_ (Smartphone, Tablet/iPad, Desktop).
    - Arsitektur frontend teroptimasi audit Lighthouse (skor 90+).

---

### Prasyarat Sistem (Prerequisites)

Sebelum memulai instalasi, pastikan lingkungan server/lokal Anda memenuhi prasyarat berikut:

- **PHP:** Versi 8.3 atau lebih baru (Ekstensi aktif: `pdo_pgsql`, `pgsql`, `mbstring`, `exif`, `pcntl`, `bcmath`, `gd`)
- **Database:** PostgreSQL Server versi 15.x atau 16.x
- **Node.js:** Versi 20.x LTS atau lebih baru & NPM
- **Package Manager:** Composer versi 2.x

---

### Panduan Instalasi Lengkap

**1. Clone Repositori**

```bash
git clone https://github.com/username/simrs-app.git
cd simrs-app
```
