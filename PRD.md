# Dokumen Kebutuhan Produk (PRD)
# Sistem Informasi Manajemen Rumah Sakit (SIMRS Web App)

**Status Dokumen:** Spesifikasi Produksi  
**Version:** 1.0.0  
**Date:** 2 September 2026  
**Jenis Produk:** Sistem Informasi Manajemen Rumah Sakit berbasis Web (SIMRS)  
**Architecture:** Laravel + Inertia.js + Vue 3 single-stack application  
**Pengguna Utama:** Super Admin, Dokter, Perawat, Koas/Magang Medis, Pasien

---

## 1. Metadata

| Field | Specification |
|---|---|
| Product Name | Integrated Hospital Management System (SIMRS) |
| Application Type | Web Application |
| Backend | Laravel 13 |
| Database | PostgreSQL |
| Authorization | Spatie Laravel Permission |
| Frontend | Vue 3 |
| Frontend Language | TypeScript |
| Frontend Architecture | Composition API + `<script setup lang="ts">` |
| SPA Bridge | Inertia.js |
| Styling | Tailwind CSS |
| Authentication | Laravel Authentication |
| ORM | Laravel Eloquent |
| Soft Deletion | Laravel SoftDeletes |
| Static Analysis | PHPStan Level 7 / Larastan |
| PHP Formatting | Laravel Pint |
| JS/TS Linting | ESLint |
| Code Formatting | Prettier |
| CI/CD | GitHub Actions |
| Public Queue Display | `/display` |
| Staff Workspace | `/staff` |
| Patient Portal | `/my-appointments` |
| Patient Profile | `/profile` |
| Super Admin Dashboard | `/admin/dashboard` |

---

# 2. Latar Belakang & Masalah

Banyak rumah sakit dan klinik kelas menengah hingga bawah masih bergantung pada rekam medis kertas, spreadsheet yang terfragmentasi, registrasi manual, pengelolaan antrean secara verbal, dan pencatatan staf secara manual.

Kondisi tersebut menimbulkan:

- Duplicate data entry and inconsistent patient information.
- Slow patient registration and queue handling.
- Limited visibility into staff status.
- Manual administration of doctors, nurses, and rotating medical interns.
- Difficult retrieval of historical clinical information.
- Risk of unauthorized access from inactive staff accounts.
- Manual management of waiting-room TV content.
- Risk of destroying historical medical relationships when user accounts are deleted.

SIMRS akan memusatkan alur kerja tersebut dalam satu aplikasi berbasis Laravel.

---

# 3. Tujuan Produk

Membangun SIMRS yang andal, mudah dipelihara, dan berfokus pada operasional untuk mendigitalisasi:

1. Patient appointments and queue monitoring.
2. Clinical consultation and EMR workflows.
3. Staff lifecycle and account governance.
4. Public queue display.
5. Waiting-room video management.
6. Historical medical-data preservation.

Sistem harus tetap menggunakan arsitektur **modular monolith** dan tidak boleh menambahkan Filament, engine frontend kedua, atau backend SPA terpisah.

---

# 4. Sasaran & Metrik Keberhasilan

## 4.1 Sasaran Produk

- Centralize hospital operational workflows.
- Reduce administrative overhead.
- Provide focused interfaces for each user role.
- Protect EMR data integrity.
- Make staff lifecycle management safe and immediate.
- Provide a reliable public queue display.
- Establish production-grade engineering quality gates.

## 4.2 Metrik Keberhasilan

| Metric | Target |
|---|---:|
| Appointment workflow completion | ≥ 95% |
| Queue status availability | ≥ 99% |
| Staff deactivation propagation | < 1 minute |
| Public display availability | ≥ 99% |
| Video transition without reload | 100% |
| Historical EMR relationships preserved | 100% |
| Unauthorized access incidents | 0 |

---

# 5. Matriks Peran Pengguna

Sumber kebenaran utama untuk otorisasi adalah **Spatie Laravel Permission**.

Recommended roles:

- `super_admin`
- `doctor`
- `nurse`
- `koas`
- `patient`

| Capability | Super Admin | Doctor | Nurse | Koas | Patient |
|---|:---:|:---:|:---:|:---:|:---:|
| Admin Dashboard | ✓ | — | — | — | — |
| Staff Directory | ✓ | — | — | — | — |
| Activate/Deactivate Staff | ✓ | — | — | — | — |
| Inactive Account Audit | ✓ | — | — | — | — |
| TV Video Manager | ✓ | — | — | — | — |
| Clinical Workspace | — | ✓ | ✓ | ✓ | — |
| EMR Access | — | ✓ | ✓* | ✓* | — |
| Queue Management | ✓ | ✓ | ✓ | ✓ | View own |
| Appointments | ✓ | ✓ | ✓ | ✓ | ✓ |
| Patient Profile | — | — | — | — | ✓ |
| Account Settings | — | — | — | — | ✓ |
| Public Display | Public | Public | Public | Public | Public |

`*` Hak akses klinis yang tepat harus ditetapkan berdasarkan kebijakan rumah sakit.

---

# 6. Autentikasi & Navigasi Cerdas

## 6.1 Login

```text
GET /login
```

Setelah autentikasi, aplikasi memeriksa:

1. Authentication status.
2. `is_active`.
3. Roles.
4. Permissions.

## 6.2 Smart Redirect

```text
/login
   |
   v
Authenticate
   |
   v
Check is_active
   |
   +---- false ----> Reject Login
   |
   +---- true -----> Check Role
                       |
        +--------------+---------------+
        |              |               |
        v              v               v
   super_admin    doctor/nurse/koas  patient
        |              |               |
        v              v               v
/admin/dashboard     /staff       /my-appointments
```

Akun yang tidak aktif tidak boleh mengakses fungsi yang dilindungi meskipun masih memiliki sesi lama.

---

# 7. Diagram Alur Pengguna

```text
                         +----------------+
                         |    /login      |
                         +-------+--------+
                                 |
                         Authentication
                                 |
                         +-------v--------+
                         | Active Account?|
                         +---+--------+---+
                             |        |
                            NO       YES
                             |        |
                          Reject      Role
                                      |
              +-----------------------+------------------+
              |                       |                  |
              v                       v                  v
        SUPER ADMIN             CLINICAL STAFF        PATIENT
              |                       |                  |
              v                       v                  v
    /admin/dashboard                /staff       /my-appointments
              |                       |                  |
       +------+-------+         +-----+------+      +----+----+
       |      |       |         |     |      |      |         |
       v      v       v         v     v      v      v         v
     Staff   Audit  Display    EMR   Queue  Patient Queue   Profile
```

---

# 8. Portal Pasien

## 8.1 Rute

```text
GET   /my-appointments
GET   /profile
PATCH /profile
PATCH /profile/password
```

## 8.2 Fitur

### Live Queue Status Card

Dashboard pasien harus menampilkan:

- Patient queue number.
- Currently called number.
- Queue position.
- Estimated waiting time where available.
- Current queue status.

Example:

```text
Current Queue
----------------------
Your Number       A-023
Currently Called  A-019
Position          4
Estimated Wait    ~20 min
```

### Responsive Sidebar

Navigation:

```text
My Appointments
Profile
Account Settings
Logout
```

**Account Settings hanya boleh dirender untuk pengguna dengan peran pasien.**

Visibilitas frontend bukan batas keamanan; backend juga wajib menegakkan pembatasan tersebut.

---

# 9. Ruang Kerja Klinis

## 9.1 Route

```text
GET /staff
```

Dapat diakses oleh:

- Doctor
- Nurse
- Koas/Medical Intern

Tindakan yang tersedia dikendalikan oleh permission.

## 9.2 Workspace Components

```text
Staff Workspace
|
+-- Today's Queue
+-- Current Patient
+-- Patient Demographics
+-- Appointment Information
+-- Medical History
+-- Clinical Notes
+-- Diagnosis
+-- Prescription
+-- Treatment / Action
+-- Consultation Status
```

## 9.3 Aturan Navigasi

`/display` harus secara eksplisit **tidak** muncul pada navigasi staf.

Display publik merupakan antarmuka operasional/publik, bukan fitur ruang kerja klinis.

---

# 10. Dashboard Super Admin

## 10.1 Route

```text
GET /admin/dashboard
```

Hanya pengguna dengan permission administratif yang sesuai yang boleh mengakses area ini.

---

# 11. Manajemen Siklus Hidup Staf

## 11.1 Direktori Staf Terpadu

The dashboard must provide one directory containing:

- Doctors.
- Nurses.
- Koas/Medical Interns.

Example:

| Name | Role | Status | Last Login | Action |
|---|---|---|---|---|
| Dr. Ahmad | Doctor | Active | Today | Deactivate |
| Siti | Nurse | Active | Yesterday | Deactivate |
| Budi | Koas | Inactive | 45 days ago | Activate |

## 11.2 Penonaktifan

Setting:

```text
is_active = false
```

harus:

1. Update the account status.
2. Invalidate active authentication sessions.
3. Prevent future login.
4. Preserve historical records.
5. Preserve clinical relationships.
6. Create an audit record.

Penting: pencabutan sesi saja tidak cukup. Setiap request yang dilindungi juga wajib memverifikasi status aktif akun.

---

# 12. Audit & Pembersihan Akun Tidak Aktif

## 12.1 Tujuan

Administrator dapat mengidentifikasi akun tidak aktif yang tidak memiliki aktivitas terbaru.

Default review thresholds:

```text
30 days
60 days
```

## 12.2 Aktivitas

`last_login_at` dapat digunakan sebagai indikator utama pada MVP.

Arsitektur harus dapat dikembangkan untuk mendukung indikator tambahan:

- Last login.
- Last appointment.
- Last clinical activity.
- Last administrative activity.
- Other meaningful application activity.

## 12.3 Alur Pembersihan

```text
Admin
  |
  v
Inactive Account Audit
  |
  v
Filter users > 30/60 days inactive
  |
  v
Review
  |
  +---- Keep
  |
  +---- Soft Delete
           |
           v
      deleted_at populated
```

Data klinis historis tidak boleh dihapus permanen sebagai efek samping dari pembersihan akun.

---

# 13. Manajer Video TV Display

## 13.1 Format yang Didukung

```text
.mp4
.webm
```

Maximum file size:

```text
100 MB
```

## 13.2 Operasi

Administrator dapat:

- Upload videos.
- Set titles.
- Reorder videos.
- Activate/deactivate videos.
- Delete videos.
- Delete physical video files.

## 13.3 Validasi Upload

Validate:

- File extension.
- MIME type.
- File size.

Never trust file extensions alone.

## 13.4 Penghapusan

Video deletion must account for both:

```text
Database metadata
+
Physical storage file
```

Aplikasi harus mencegah terbentuknya file fisik yatim.

---

# 14. Public TV Queue Display

## 14.1 Route

```text
GET /display
```

Tidak memerlukan autentikasi.

## 14.2 Layout

```text
+-------------------------------------------------------+
|                  HOSPITAL / CLINIC                    |
+-------------------------+-----------------------------+
|                         |                             |
|     QUEUE CALLING       |        VIDEO PLAYER         |
|                         |                             |
|     A-021               |                             |
|     A-022               |      Promotional Video     |
|    >A-023               |                             |
|     A-024               |                             |
|                         |                             |
|     Counter 2           |                             |
+-------------------------+-----------------------------+
```

## 14.3 Pemutar Video

The HTML5 player must use:

```html
<video
    autoplay
    muted
    playsinline
/>
```

## 14.4 Pemutaran Berurutan

Videos are sorted by `order`.

```text
Video A -> Video B -> Video C -> Video A
```

When a video ends:

```text
@ended
   |
   v
increment current index
   |
   v
load next active video
   |
   v
play()
```

Tidak boleh ada reload halaman di antara video.

Jika tidak ada video aktif, tampilan antrean tetap harus berfungsi normal.

---

# 15. Fitur Gabungan & Arsitektur Sistem

```text
                     INTERNET / LOCAL NETWORK
                              |
                 +------------+------------+
                 |                         |
                 v                         v
          Patient Browser           Public TV Browser
                 |                         |
                 v                         v
        /my-appointments               /display
                 |                         |
                 +------------+------------+
                              |
                              v
                    +-------------------+
                    |    Laravel 13     |
                    |    Application    |
                    +---------+---------+
                              |
          +-------------------+-------------------+
          |                   |                   |
          v                   v                   v
   Authentication           RBAC            Domain Services
          |                Spatie                 |
          |                   |            +------+------+
          |                   |            |             |
          v                   v            v             v
      Sessions             Roles       Queue Service  EMR Service
                              |
                              v
                     +----------------+
                     |   PostgreSQL   |
                     +-------+--------+
                             |
                 +-----------+-----------+
                 |                       |
                 v                       v
          Transactional Data       Video Metadata
                                         |
                                         v
                                  Public Storage
```

---

# 16. Arsitektur Aplikasi

Struktur backend yang direkomendasikan:

```text
app/
├── Actions/
│   ├── Auth/
│   ├── Staff/
│   ├── Display/
│   └── Queue/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   ├── Staff/
│   │   ├── Patient/
│   │   └── Public/
│   ├── Middleware/
│   └── Requests/
├── Models/
├── Policies/
├── Services/
│   ├── Staff/
│   ├── Display/
│   ├── Queue/
│   └── Clinical/
└── Support/
```

Frontend:

```text
resources/js/
├── Components/
├── Layouts/
│   ├── AdminLayout.vue
│   ├── StaffLayout.vue
│   └── PatientLayout.vue
├── Pages/
│   ├── Admin/
│   │   ├── Dashboard.vue
│   │   ├── Staff/
│   │   └── Display/
│   ├── Staff/
│   │   └── Index.vue
│   ├── Patient/
│   │   ├── MyAppointments.vue
│   │   └── Profile.vue
│   └── Public/
│       └── Display.vue
├── Composables/
├── Types/
└── app.ts
```

---

# 17. Entitas Database

## 17.1 `users`

| Column | Type | Constraints | Description |
|---|---|---|---|
| id | BIGINT | PK | User ID |
| name | VARCHAR(255) | NOT NULL | Full name |
| email | VARCHAR(255) | UNIQUE, NOT NULL | Login identifier |
| password | VARCHAR | NOT NULL | Hashed password |
| is_active | BOOLEAN | DEFAULT TRUE | Account status |
| last_login_at | TIMESTAMP | NULL | Last successful login |
| email_verified_at | TIMESTAMP | NULL | Verification timestamp |
| remember_token | VARCHAR(100) | NULL | Laravel remember token |
| created_at | TIMESTAMP | NULL | Creation time |
| updated_at | TIMESTAMP | NULL | Update time |
| deleted_at | TIMESTAMP | NULL | Soft deletion |

### Arsitektur Role

Jangan gunakan `users.role` sebagai mekanisme otorisasi utama.

Use:

```text
users
  |
  v
model_has_roles
  |
  v
roles
  |
  v
role_has_permissions
  |
  v
permissions
```

Spatie Permission menjadi sumber kebenaran utama untuk otorisasi.

---

## 17.2 `display_videos`

| Column | Type | Constraints | Description |
|---|---|---|---|
| id | BIGINT | PK | Video ID |
| title | VARCHAR(255) | NOT NULL | Video title |
| file_path | VARCHAR(500) | NOT NULL | Storage path |
| order | INTEGER | DEFAULT 0 | Playlist order |
| is_active | BOOLEAN | DEFAULT TRUE | Visibility |
| created_at | TIMESTAMP | NULL | Creation time |
| updated_at | TIMESTAMP | NULL | Update time |

Recommended indexes:

```text
INDEX(is_active)
INDEX(order)
```

Playlist query:

```sql
SELECT *
FROM display_videos
WHERE is_active = true
ORDER BY "order" ASC, id ASC;
```

Pengurutan sekunder berdasarkan `id` membuat record dengan nilai `order` yang sama tetap deterministik.

---

# 18. Skema Spatie Permission

## `roles`

```text
id
name
guard_name
created_at
updated_at
```

Recommended roles:

```text
super_admin
doctor
nurse
koas
patient
```

## `permissions`

```text
id
name
guard_name
created_at
updated_at
```

Examples:

```text
dashboard.admin.view

staff.view
staff.create
staff.update
staff.activate
staff.deactivate
staff.delete

patient.view
patient.update

appointment.view
appointment.create
appointment.update
appointment.cancel

queue.view
queue.manage
queue.call

emr.view
emr.create
emr.update

display.view
display.manage
display.upload
display.delete
```

---

# 19. Model Data Klinis

Identitas autentikasi dan identitas klinis harus tetap dipisahkan secara konseptual.

Recommended model:

```text
patients
    |
    +-- appointments
    |       |
    |       +-- queues
    |
    +-- medical_records
            |
            +-- diagnoses
            +-- prescriptions
            +-- treatments
```

Atribusi tenaga medis harus tetap dapat ditelusuri secara historis setelah akun menjadi tidak aktif.

Jangan menghapus atribusi klinis hanya karena akun login dinonaktifkan.

---

# 20. Integritas Data

## 20.1 Soft Deletion

Use Laravel:

```php
use SoftDeletes;
```

for entities where historical retention is required.

## 20.2 Foreign Key

Choose intentionally between:

- `RESTRICT`
- `SET NULL`
- `CASCADE`

Do not blindly use `ON DELETE CASCADE` for EMR relationships.

## 20.3 Atribusi Historis

Clinical records should retain relevant clinician references, such as:

```text
created_by
doctor_id
nurse_id
```

according to the clinical domain model.

---

# 21. Arsitektur Antrean

The queue domain must distinguish:

```text
Appointment
Queue Entry
Queue Status
Queue Call
```

Recommended lifecycle:

```text
Booked
  |
  v
Registered
  |
  v
Waiting
  |
  v
Called
  |
  v
In Consultation
  |
  v
Completed
```

Exceptional states:

```text
Cancelled
No Show
Skipped
```

Server menjadi sumber kebenaran utama untuk status antrean.

---

# 22. Persyaratan Keamanan

## Authentication

Semua rute yang dilindungi membutuhkan autentikasi.

## Authorization

Use:

```text
Policies
+
Gates
+
Spatie Permissions
```

as appropriate.

Navigasi frontend bukan batas keamanan.

## Account Status

Pengguna tidak aktif:

```text
is_active = false
```

harus gagal pada otorisasi yang dilindungi dan tidak boleh dapat login.

## Password Security

Gunakan mekanisme hashing password yang didukung Laravel.

Jangan pernah menyimpan password dalam bentuk plaintext.

## File Upload Security

Validate:

```text
Extension
MIME Type
Maximum Size = 100 MB
```

Simpan file melalui abstraksi filesystem Laravel.

---

# 23. Auditabilitas

Operasi administratif harus dapat diaudit.

Record:

```text
Actor
Action
Affected Entity
Timestamp
```

Important events:

```text
Staff Activated
Staff Deactivated
User Soft Deleted
Video Uploaded
Video Activated
Video Deactivated
Video Reordered
Video Deleted
Permission Changed
```

---

# 24. Desain HTTP / Backend

Controller harus tetap tipis.

Preferred flow:

```text
Controller
   |
   v
Form Request
   |
   v
Authorization
   |
   v
Action / Service
   |
   v
Model / Database
```

Logika bisnis kompleks tidak boleh ditempatkan langsung di dalam controller.

---

# 25. Engineering Frontend

Semua komponen Vue harus menggunakan:

```vue
<script setup lang="ts">
```

Example:

```vue
<script setup lang="ts">
import { ref } from 'vue';

interface QueueItem {
    number: string;
    status: string;
}

const queue = ref<QueueItem[]>([]);
</script>
```

Hindari penggunaan `any` tanpa kontrol.

Gunakan interface/type yang eksplisit dan import khusus type:

```typescript
import type { User } from '@/types';
```

---

# 26. Inertia.js

Inertia adalah bridge utama antara Laravel dan Vue.

Preferred flow:

```text
Laravel Controller
      |
      v
Inertia::render()
      |
      v
Vue Page
```

Jangan membuat backend SPA terpisah hanya untuk mereplikasi fungsi yang sama.

Real-time queue updates may use polling or a real-time transport if required by later performance analysis.

---

# 27. UI Responsif

Antarmuka terautentikasi harus mendukung:

- Desktop
- Tablet
- Mobile

Design priorities:

| Interface | Priority |
|---|---|
| Patient Portal | Mobile-first |
| Clinical Workspace | Desktop-first |
| Admin Dashboard | Desktop-first |
| Public TV Display | Large-screen/TV |

---

# 28. Penanganan Error

Support clear states for:

```text
Validation Error
Unauthorized
Forbidden
Not Found
Server Error
Network Error
Loading
Empty State
```

Response produksi tidak boleh mengekspos:

- SQL errors.
- Stack traces.
- Filesystem paths.
- Secrets.
- Internal infrastructure details.

---

# 29. Standar Migration

Setiap perubahan skema harus menggunakan Laravel migration.

Migration harus:

- Version controlled.
- Reproducible.
- Reviewable.
- Safe for deployment.
- Rollback-aware where practical.

---

# 30. Seed Data

Environment development dan staging harus menyediakan seed data deterministik untuk:

```text
Super Admin
Doctor
Nurse
Koas
Patient
```

Example permissions:

```text
staff.view
staff.activate
staff.deactivate
emr.view
emr.create
display.manage
```

Credential produksi tidak boleh pernah di-commit ke source control.

---

# 31. Standar Engineering

## 31.1 Stack Wajib

```text
Laravel 13
PostgreSQL
Spatie Permission
Laravel SoftDeletes
Vue 3
TypeScript
Composition API
<script setup lang="ts">
Inertia.js
Tailwind CSS
```

Tidak boleh menggunakan:

```text
Filament
Second frontend framework
Separate SPA backend
Unnecessary microservices
```

---

# 32. Quality Gates

GitHub Actions wajib menjalankan:

```text
1. Dependency installation
2. PHP syntax validation
3. PHPStan / Larastan Level 7
4. Laravel Pint
5. TypeScript checking
6. ESLint
7. Prettier
8. Automated tests
```

Pipeline:

```text
Pull Request
     |
     v
GitHub Actions
     |
 +---+----------------+
 |                    |
 v                    v
Backend             Frontend
 |                    |
 v                    v
PHPStan              ESLint
Pint                 TypeScript
Tests                Prettier
 |                    |
 +---------+----------+
           |
         PASS?
        /           NO       YES
      |         |
    BLOCK      MERGE
```

---

# 33. PHPStan / Larastan

Required level:

```text
PHPStan Level 7
```

Target:

```text
0 errors
```

CI harus gagal jika PHPStan melaporkan error.

---

# 34. Laravel Pint

Laravel Pint wajib digunakan untuk formatting PHP.

CI harus memverifikasi kepatuhan formatting.

---

# 35. ESLint

ESLint minimal harus menegakkan:

- Curly braces for control flow.
- Type-only imports.
- No unused variables.
- Strict TypeScript/Vue rules.
- Configured line-padding conventions.

Example:

```typescript
if (isActive) {
    activateUser();
}
```

---

# 36. Prettier

Semua file frontend yang didukung harus lolos Prettier.

CI must fail on formatting violations.

Recommended scripts:

```bash
npm run format
npm run format:check
```

---

# 37. Strategi Pengujian

## Unit Test

Uji:

- Queue calculations.
- Inactivity calculations.
- Video ordering.
- Staff lifecycle actions.
- Authorization policies.

## Feature Test

Uji:

```text
Login
Smart Redirect
Staff Deactivation
Inactive Account Login
Patient Profile
Appointment Access
Video Upload
Video Delete
Queue Retrieval
```

## Pengujian Otorisasi

Explicitly test negative cases:

```text
Patient -> /admin/dashboard -> 403
Doctor -> staff management -> 403
Inactive doctor -> /staff -> 403
Nurse -> admin dashboard -> 403
Koas -> unauthorized EMR operation -> 403
```

Pengujian keamanan harus memverifikasi tindakan yang tidak boleh dilakukan pengguna.

---

# 38. Acceptance Criteria

## Authentication

- [ ] `/login` works.
- [ ] Inactive users cannot log in.
- [ ] Super Admin redirects to `/admin/dashboard`.
- [ ] Clinical users redirect to `/staff`.
- [ ] Patients redirect to `/my-appointments`.
- [ ] Unauthorized routes are blocked.

## Patient Portal

- [ ] `/my-appointments` works.
- [ ] Queue status is visible.
- [ ] Sidebar is responsive.
- [ ] Profile management works.
- [ ] Password management works.
- [ ] Account Settings appears only for patients.

## Clinical Workspace

- [ ] Doctor can access `/staff`.
- [ ] Nurse can access `/staff`.
- [ ] Koas can access `/staff`.
- [ ] Clinical permissions are enforced server-side.
- [ ] `/display` is absent from staff navigation.

## Super Admin

- [ ] Unified staff directory works.
- [ ] Staff can be activated/deactivated.
- [ ] Active sessions are invalidated.
- [ ] Deactivated accounts cannot log in.
- [ ] Dormant accounts can be identified.
- [ ] User cleanup uses soft deletion.
- [ ] Historical relationships remain intact.

## Public Display

- [ ] `/display` is public.
- [ ] Queue board renders.
- [ ] Active videos render.
- [ ] Autoplay works.
- [ ] Muted playback is enabled.
- [ ] `playsinline` is enabled.
- [ ] `@ended` advances the playlist.
- [ ] Playlist loops.
- [ ] No page reload occurs.
- [ ] Empty playlist does not break queue display.

## Video Manager

- [ ] MP4 upload works.
- [ ] WebM upload works.
- [ ] Files over 100 MB are rejected.
- [ ] Invalid MIME types are rejected.
- [ ] Videos can be reordered.
- [ ] Videos can be activated/deactivated.
- [ ] Physical files are removed when deleted.
- [ ] Orphaned files are prevented or periodically cleaned.

---

# 39. Arsitektur Deployment

```text
                    Internet / Local Network
                              |
                              v
                   Reverse Proxy / Web Server
                              |
                              v
                       Laravel 13 App
                         /                                  /                                   v              v
                 PostgreSQL       File Storage
```

Deployment awal tidak membutuhkan microservices.

Modular monolith adalah arsitektur yang direkomendasikan.

---

# 40. Keputusan Arsitektur: Modular Monolith

Aplikasi harus tetap menjadi modular monolith karena:

```text
Moderate operational scope
+
Single application team
+
Lower deployment complexity
+
Strong transactional requirements
=
Modular Monolith
```

Microservices akan menambahkan kompleksitas yang belum diperlukan berupa:

- Network boundaries.
- Distributed transactions.
- Deployment complexity.
- Monitoring requirements.
- Authentication complexity.

Microservices hanya perlu dipertimbangkan jika skala terukur atau kebutuhan organisasi benar-benar membenarkannya.

---

# 41. Filosofi Retensi Data

Account lifecycle:

```text
Active
  |
  v
Inactive
  |
  v
Soft Deleted
```

Clinical lifecycle:

```text
Created
  |
  v
Updated
  |
  v
Completed
  |
  v
Historically Retained
```

Akun login yang tidak aktif/dihapus tidak boleh berarti bahwa riwayat klinis ikut dihapus.

---

# 42. Ekstensibilitas Masa Depan

Potential future modules:

```text
Billing
Pharmacy
Laboratory
Radiology
Inventory
Insurance
Reporting
Notifications
Advanced Queue Management
Audit Analytics
External Integrations
```

Modul masa depan harus diperkenalkan sebagai bounded domain tanpa mengganggu core yang sudah ada.

---

# 43. Prioritas MVP

## P0 — Wajib

```text
Authentication
RBAC
Smart Redirect
Patient Portal
Staff Workspace
Super Admin Dashboard
Staff Lifecycle
Queue Management
Public TV Display
Video Management
Soft Delete
Database Integrity
CI/CD Quality Gates
```

## P1 — Penting

```text
Audit Logs
Advanced Queue States
Activity Tracking
Automated Dormant Account Reporting
Automated Storage Cleanup
Comprehensive Feature Tests
```

## P2 — Masa Depan

```text
Billing
Pharmacy
Laboratory
Reporting
Notifications
Advanced Analytics
External Integrations
```

---

# 44. Definition of Done

Sebuah fitur dianggap selesai hanya jika:

- [ ] Backend implementation is complete.
- [ ] Frontend implementation is complete.
- [ ] Server-side authorization exists.
- [ ] Validation exists.
- [ ] Error states exist.
- [ ] Loading states exist where appropriate.
- [ ] Database migrations exist.
- [ ] Critical tests exist.
- [ ] TypeScript contains no unjustified `any`.
- [ ] PHPStan Level 7 passes.
- [ ] Pint passes.
- [ ] ESLint passes.
- [ ] Prettier passes.
- [ ] CI passes.
- [ ] No known security regression exists.
- [ ] Historical data integrity is preserved.

---

# 45. Kontrak Sistem Akhir

```text
+------------------------------------------------------------+
|                     SIMRS WEB APP                          |
+------------------+------------------+----------------------+
| PATIENT PORTAL   | CLINICAL         | ADMINISTRATION       |
|                  | WORKSPACE        |                      |
| /my-appointments | /staff           | /admin/dashboard     |
| /profile         |                  |                      |
+------------------+------------------+----------------------+
|                    PUBLIC DISPLAY                          |
|                        /display                            |
+------------------------------------------------------------+
|                 LARAVEL 13 APPLICATION                     |
|          Authentication + RBAC + Domain Logic              |
+------------------------------------------------------------+
|                       POSTGRESQL                           |
|                Transactional System of Record               |
+------------------------------------------------------------+
```

Prinsip arsitektur inti:

1. Laravel adalah backbone aplikasi.
2. Inertia.js menghubungkan Laravel dan Vue tanpa backend SPA terpisah.
3. Vue 3 + TypeScript menyediakan UI interaktif.
4. Spatie Permission menjadi sumber kebenaran utama untuk otorisasi.
5. `is_active` mengendalikan siklus hidup akun secara independen dari role.
6. Penonaktifan segera mencegah akses terautentikasi berikutnya.
7. Soft deletion melindungi relasi EMR historis.
8. `/display` terpisah dari navigasi staf terautentikasi.
9. Server menjadi sumber kebenaran untuk data antrean dan playlist.
10. Browser menangani pemutaran video HTML5 secara berurutan.
11. Sistem tetap modular monolith sampai skala benar-benar membutuhkan pemisahan.
12. Filament dan engine administrasi kedua tidak digunakan.
13. Perubahan produksi harus melewati quality gate otomatis.
14. Riwayat klinis tidak boleh dikorbankan demi manajemen siklus hidup akun.

> **Prinsip inti: Akun pengguna dapat dinonaktifkan/dihapus secara aman; riwayat klinis tidak.**
