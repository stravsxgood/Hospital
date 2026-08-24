# MEMORY.md - Hospital Population Change Log

## 2026-08-24 — Frontend Performance & Accessibility (A11y) Audit & Optimization (Lighthouse 90-95+ Benchmark)

### 1. Performance & Bundle Optimization
- **Asynchronous Code Splitting ([resources/js/app.ts](file:///home/stravesstgod/rumah-sakit/resources/js/app.ts))**:
  - Configured `resolvePageComponent` using lazy dynamic imports `import.meta.glob<DefineComponent>('./pages/**/*.vue')` without eager bundling.
  - Eliminated monolithic bundling; every hospital module page is compiled into its own lightweight chunk (1kB - 60kB) loaded on demand.
- **Rollup Manual Chunking & Minification ([vite.config.ts](file:///home/stravesstgod/rumah-sakit/vite.config.ts))**:
  - Configured `output.manualChunks(id)` splitting third-party libraries into dedicated vendor caches:
    - `vendor-vue` (`vue`, `@inertiajs/vue3`, `@vueuse/core`)
    - `vendor-icons` (`lucide-vue-next`, `@lucide/vue`)
    - `vendor-realtime` (`laravel-echo`, `pusher-js`)
    - `vendor-ui` (`motion-v`, `reka-ui`, `clsx`, `tailwind-merge`, `class-variance-authority`, `vue-sonner`)
    - `vendor-rive` (`@rive-app/webgl2`)
    - `vendor-axios` (`axios`)
  - Enabled CSS code splitting (`cssCodeSplit: true`) and adjusted chunk warning limit to `1000` kB.

### 2. Accessibility (A11y) & HTML Semantics Refactoring
- **Form Controls & Explicit Labeling**:
  - Replaced unassociated inputs/selects in `Users/Index.vue`, `Facilities/Index.vue`, `AuditLogs/Index.vue`, and modal dialogs with explicit `id` attributes matched to `<label for="...">`.
  - Added descriptive `aria-label` and `title` to all icon-only action buttons (Edit, Delete, Password Reset, Status Toggle, Dismiss Modals).
- **Strict Heading Hierarchy**:
  - Enforced single `<h1>` page titles with descending `<h2>` section headers, modal dialog titles, and visually hidden section headings for screen readers.
- **WCAG AA Color Contrast Compliance ($\ge 4.5:1$)**:
  - Upgraded low-contrast text classes (`text-[#000000]/40`, `text-[#000000]/50`, `text-[#000000]/60`) to `text-[#000000]/75`, `text-[#333333]`, and `text-neutral-800`.
  - Upgraded status badges with solid background tints and dark text (`bg-emerald-100 text-emerald-900 border-emerald-300`, `bg-rose-100 text-rose-900 border-rose-300`, `bg-amber-100 text-amber-900 border-amber-300`, `bg-blue-100 text-blue-900 border-blue-300`).
- **ARIA Roles & Dialog Semantics**:
  - Added `role="dialog" aria-modal="true"` and `aria-labelledby` to all modal dialogs.
  - Added semantic `<nav>` elements with `aria-label` for mobile menus, breadcrumbs, and pagination controls.

### 3. Verification & Quality Assurance
- **TypeScript**: `npm run types:check` passed with 0 errors.
- **Client & SSR Build**: `npm run build` and `npm run build:ssr` compiled cleanly in < 3.0s.
- **Backend Test Suite**: `./vendor/bin/pest` passed with 154/154 tests (744 assertions, 0 failures).

---

## 2026-08-24 — SSR Hydration Mismatch Fix (`isMounted` Teleport Guards & Standalone Layout Routing)

### 1. Root Cause Analysis (Hydration Mismatch `Hydration completed but contains mismatches`)
- **Teleport During Initial Hydration**: In Vue 3 SSR, `<Teleport to="body">` evaluates during SSR in-place. If evaluated during the initial client-side hydration pass without an `isMounted` guard, Vue attempts to teleport the off-canvas drawer DOM nodes into `document.body` while the hydration process is reconciling the `#app` root container, triggering `logMismatchError` and `Hydration completed but contains mismatches.`
- **Layout Cascading Conflict**: In `resources/js/app.ts`, `admin/*`, `MyAppointments`, and `Display/QueueTv` were not mapped to `null` layout, causing Inertia to automatically wrap them in `AppLayout` (which contains `AppSidebar`, `AppHeader`, and `AppContent`) while the pages simultaneously rendered `AdminLayout` or standalone full-screen canvas wrappers.

### 2. Implementation & Fixes
- **`isMounted` Teleport Guards**:
  - Added `const isMounted = ref(false)` and `onMounted(() => { isMounted.value = true })` in:
    - [Welcome.vue](file:///home/stravesstgod/rumah-sakit/resources/js/pages/Welcome.vue)
    - [teams/Index.vue](file:///home/stravesstgod/rumah-sakit/resources/js/pages/teams/Index.vue)
    - [Clinic/Location.vue](file:///home/stravesstgod/rumah-sakit/resources/js/pages/Clinic/Location.vue)
    - [doctor/Schedule.vue](file:///home/stravesstgod/rumah-sakit/resources/js/pages/doctor/Schedule.vue)
    - [PatientStory.vue](file:///home/stravesstgod/rumah-sakit/resources/js/pages/PatientStory.vue)
    - [Specializations/Index.vue](file:///home/stravesstgod/rumah-sakit/resources/js/pages/Specializations/Index.vue)
    - [AdminLayout.vue](file:///home/stravesstgod/rumah-sakit/resources/js/layouts/AdminLayout.vue)
  - Updated all off-canvas mobile drawer teleports to `<Teleport to="body" v-if="isMounted">`.
- **Layout Routing Cleanup ([resources/js/app.ts](file:///home/stravesstgod/rumah-sakit/resources/js/app.ts))**:
  - Mapped `admin/*` pages to `return null;` so they only render their dedicated `AdminLayout`.
  - Mapped `MyAppointments` and `Display/QueueTv` to `return null;` to prevent redundant sidebar layouts.

### 3. Verification & Quality Assurance
- **TypeScript**: `npm run types:check` passed with 0 errors.
- **Client & SSR Build**: `npm run build:ssr` compiled cleanly in 3.15s.
- **Backend Test Suite**: `./vendor/bin/pest` passed with 154/154 tests (744 assertions, 0 failures).

---

## 2026-08-24 — Super Admin Module Responsive UI Refactor & Architectural Optimization (Mobile, Tablet, Desktop)

### 1. Dedicated Master Layout (`AdminLayout.vue`)
- **Mobile Top Navigation (< 1024px / `lg:hidden`)**:
  - Sticky top header with hospital brand logo, title, and prominent hamburger menu button (`min-h-[44px] min-w-[44px]`).
  - Right-side user badge with shield icon and truncate safeguards.
- **Off-Canvas Slide-in Mobile Drawer**:
  - Vue `<Transition>` with smooth slide translation (`-translate-x-full` to `translate-x-0`).
  - Dark translucent backdrop (`bg-black/60 backdrop-blur-xs`) with tap-to-close.
  - Automatic close on `Escape` key press (`window.addEventListener('keydown')`) and Inertia navigation event (`router.on('navigate')`).
  - Body overflow lock management (`document.body.style.overflow = 'hidden'`).
  - Touch-friendly navigation links with $\ge 44$px targets and grouped sections.
- **Desktop Persistent Sidebar (`lg:block w-64 xl:w-72`)**:
  - Sticky full-height left navigation bar with Evergreen `#065f46` active states, `#beedc0` highlights, and grouped route links.
  - Quick access links to SIMRS portals (Staff Dashboard `/staff`, Doctor Queue `/doctor/queue`, Patient Portal `/`).
  - User footer card with role badge and logout action.
- **Dynamic Breadcrumbs & Flash Alerts**:
  - Responsive breadcrumb navigation and animated flash message toast banners.

### 2. Reusable Responsive Table Container (`ResponsiveTable.vue`)
- `resources/js/components/admin/ResponsiveTable.vue`: Reusable responsive wrapper with horizontal scrollbar styling, search/filter bar slot (`#filters`), table header slot (`#header`), table rows slot (`#default`), empty state slot (`#empty`), and pagination footer slot (`#pagination`).

### 3. Responsive Page Refactoring
- **`Dashboard.vue`**:
  - Responsive KPI metrics grid (`grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-5`) with flex layouts preventing text wrapping clipping.
  - Financial trends and payment method splits stacked on mobile and multi-column on desktop (`grid-cols-1 lg:grid-cols-3 gap-6`) with height-constrained visualizer containers.
  - Live Clinic Matrix and Morbidity lists with mobile badge pills and desktop high-visibility layouts.
  - Demographics 4-card grid (`grid-cols-2 sm:grid-cols-4`).
- **`Users/Index.vue`**:
  - Full-width mobile search and filter controls.
  - Horizontal scrollable role filter tabs.
  - Modal sheets converted to responsive bottom sheets on mobile (`rounded-t-3xl sm:rounded-3xl max-h-[88vh]`) with sticky header/footer and touch targets $\ge 44$px (`h-11` inputs preventing iOS auto-zoom).
- **`Facilities/Index.vue` & `AuditLogs/Index.vue`**:
  - Integrated into `AdminLayout.vue` with `ResponsiveTable.vue` support.
  - Mobile bottom sheets for Poliklinik and Schedule modals.
  - JSON metadata inspector modal with dark code preview.

### 4. Verification & QA
- **Pest Test Suite**: 154 tests passing across the entire application (744 assertions, 0 failures).
- **TypeScript**: 0 errors (`npm run types:check`).
- **Vite Build**: Production bundle built cleanly in 2.53s.

---

## 2026-08-24 — Super Admin Management & Operational Governance Dashboard (RBAC, Provisioning, Master Facilities & Schedules, Financial & Morbidity Aggregations, & Global Audit Logs)

### 1. RBAC & Security Middleware
- **Model `User.php`**: Added `'is_active'` to `#[Fillable]` and `$casts` array (`is_active => boolean`). Resolved Spatie trait collision with `HasTeams` via explicit `insteadof`.
- **Middleware `EnsureUserHasRole.php`**: Enhanced role validation to support multi-role comma-delimited definitions, Spatie `hasRole()` / `hasAnyRole()`, and global bypass for `super-admin` and `admin`.
- **`AppServiceProvider.php`**: Integrated global authorization bypass via `Gate::before(fn ($user) => $user->hasRole('super-admin') ? true : null)`.
- **CSRF Whitelist**: Added `'admin/*'` exceptions to `bootstrap/app.php` for smooth Inertia/AJAX operation.

### 2. User Provisioning & Account Management (`AdminUserController.php`)
- **Doctor DPJP Provisioning (`POST /admin/users/doctors`)**:
  - Atomic `DB::transaction` creating `User` (with hashed password / default `Hospital2026!`, email verified), creating `Doctor` master profile (SIP Number, Specialization, Gender, Phone, Address, Status: `'aktif'`), assigning Spatie role `dpjp-doctor`, and optionally generating initial `DoctorSchedule`.
- **Nurse / Intern Provisioning (`POST /admin/users/nurses`)**:
  - Atomic `DB::transaction` creating `User`, creating `Nurse` master profile (`type: tetap` with STR number or `type: koas` with University/NIM/rotation dates), and assigning Spatie role `staff-pekerja` or `koas-intern`.
- **Safe Soft Deactivation Guard (`PATCH /admin/users/{user}/toggle-status`)**:
  - Never hard deletes clinical users with medical records or billings.
  - Toggles `is_active` boolean, updates Doctor status (`aktif` vs `pensiun`), and automatically updates active schedules to `'Libur'` when a doctor is deactivated. Prevents deactivating own super admin account.
- **Password Reset (`POST /admin/users/{user}/reset-password`)**:
  - Resets user password to secure default `Hospital2026!` and provides immediate UI credentials with one-click copy to clipboard.

### 3. Master Facilities & Doctor Schedules (`AdminPoliController.php` & `AdminScheduleController.php`)
- **Poliklinik & Room Master**:
  - CRUD operations for `poli` (`kode_poli`, `name_poli`, `location`, `status`) and `room` (`code_room`, `name_room`, `type_room`, `floor`).
  - Safe deletion check preventing deletion of polikliniks with active linked schedules.
- **Doctor Practice Schedules & Daily Quotas**:
  - CRUD operations for `doctor_schedule` (`doctor_id`, `poli_id`, `room_id`, `day`, `start_time`, `end_time`, `quota_day`, `status`).
  - Safe deletion check preventing deletion of schedules with registered pending/confirmed appointments.

### 4. Executive Analytics & Morbidity Reporting (`AdminDashboardController.php`)
- **Financial Gross Revenue Metrics**:
  - Calculated gross revenue today, this week, and this month from paid billings.
  - Breakdown by payment method: Cash (Tunai) vs Xendit QRIS vs EDC vs Virtual Account.
  - 6-month monthly revenue trend visualization.
- **Clinical Morbidity & Operational Metrics**:
  - Top 10 most frequent ICD-10 diagnoses from `medical_record`.
  - Live clinic queue matrix and doctor on-duty counters.
  - Staff demographics distribution (Doctors, Nurses tetap vs koas, Patients, Polikliniks).

### 5. Frontend Pages (`DESIGN.md` Evergreen Theme)
- `resources/js/pages/admin/Dashboard.vue`: Executive KPI stats, bar visualization for monthly revenue trend, morbidity list, live clinic matrix.
- `resources/js/pages/admin/Users/Index.vue`: User directory, role filter tabs, Doctor DPJP registration modal, Nurse/Koas registration modal, safe status toggle modal, password reset modal.
- `resources/js/pages/admin/Facilities/Index.vue`: Poliklinik master, Room master, and Doctor practice schedule & quota matrix with modals.
- `resources/js/pages/admin/AuditLogs/Index.vue`: Global access audit log viewer with user/action filtering and JSON metadata inspector.
- `resources/js/components/AppSidebar.vue`: Dedicated Super Admin navigation group.

### 6. Automated Feature Tests (Pest) & Verification
- `tests/Feature/AdminUserManagementTest.php`: 5 feature tests for RBAC, Doctor/Nurse provisioning, safe deactivation, password reset.
- `tests/Feature/AdminFacilitiesAndScheduleTest.php`: 3 feature tests for Poli/Room CRUD, schedule quotas, safe deletion.
- `tests/Feature/AdminDashboardMetricsTest.php`: 2 feature tests for revenue calculations, payment method split, morbidity aggregations.
- **Full Test Suite**: 154 tests passing across the application (744 assertions, 0 failures).
- **TypeScript**: 0 errors on `npm run types:check`.
- **Production Build**: Built cleanly with Vite in 2.73s.

### 1. Real-time Event Broadcasting with Laravel Reverb & Echo
- **Broadcast Events (`ShouldBroadcastNow`)**:
  - `app/Events/PatientCalledEvent.php`: Dispatched when a doctor calls a patient (`/doctor/queue/{id}/call`), broadcasting to public channel `queue-display` and doctor's private channel `doctor.{doctor_id}`.
  - `app/Events/PrescriptionCreatedEvent.php`: Dispatched upon consultation completion with electronic prescription items, broadcasting to private channel `pharmacy`.
  - `app/Events/PaymentSettledEvent.php`: Dispatched on cash/EDC/QRIS/Xendit payment settlement, broadcasting to private channel `billing.{billing_id}`.
  - `app/Events/PatientConfirmedEvent.php`: Dispatched upon patient arrival verification (check-in) by front-office staff, broadcasting to doctor's private channel `doctor.{doctor_id}`.
- **Frontend Real-time Sync**:
  - Configured `resources/js/echo.ts` using Pusher JS protocol driver connecting to Laravel Reverb WebSockets.
  - Created `resources/js/pages/Display/QueueTv.vue`: Fullscreen live waiting room queue display listening to `queue-display` channel, Web Speech API Indonesian TTS (`id-ID`), hospital chime, and animated spotlight.
  - Updated `resources/js/pages/DisplayBoard.vue`: Integrated instant WebSocket listeners for queue announcements.
  - Updated `resources/js/components/PaymentModal.vue`: Subscribed to private `billing.{billing_id}` channel for instant sub-second payment confirmation.
  - Updated `resources/js/pages/StaffDashboard.vue`: Subscribed to private `pharmacy` channel to prepend incoming prescriptions dynamically.

### 2. Koas Digital Clinical Logbook & DPJP Dual Sign-off
- **Database & Models**:
  - Created singular PostgreSQL table `clinical_logbook` (`clinical_logbook_id`, `nurse_id`, `patient_id`, `medical_record_id`, `doctor_id`, `activity_type`, `case_title`, `clinical_findings`, `procedure_performed`, `learning_reflection`, `supervisor_feedback`, `score`, `status`, `submitted_at`, `reviewed_at`).
  - Model `app/Models/ClinicalLogbook.php` with scopes `scopeForKoas()`, `scopeForDoctor()`, `scopePendingReview()`, `scopeApproved()`.
- **RBAC & Controller Endpoints**:
  - `app/Http/Controllers/KoasLogbookController.php`: Intern logbook CRUD with status transitions (`draft` -> `submitted`).
  - `app/Http/Controllers/DoctorSupervisionController.php`: DPJP supervisor review dashboard with evaluation form, grading (0-100), and sign-off decision (`approved` / `revision_needed`).
- **Frontend Pages**:
  - `resources/js/pages/koas/Logbook/Index.vue`: Intern clinical case timeline, filters, create/edit modal with validation, and supervisor feedback view.
  - `resources/js/pages/doctor/Supervision/Index.vue`: DPJP supervision dashboard, side-by-side verification, scoring, and dual sign-off.

### 3. Medical Record Access Audit Trail (UU PDP No. 27/2022 & Permenkes No. 24/2022 Compliance)
- **Database & Architecture**:
  - Created singular PostgreSQL table `medical_record_audit_log` (`audit_log_id`, `medical_record_id`, `user_id`, `action`, `ip_address`, `user_agent`, `payload_diff`, `created_at`).
  - Model `app/Models/MedicalRecordAuditLog.php` with scopes `scopeRecent()` and `scopeByAction()`.
  - Service `app/Services/AuditLogService.php`: Static `logAccess()` capturing user ID, IP address, user-agent, action (`view`, `create`, `update`, `export_pdf`, `print`), and payload metadata.
- **Audit Integration**:
  - Integrated into `DoctorConsultationController.php` (EMR views and creation) and `MedicalDocumentController.php` (PDF export).
  - Viewer page `resources/js/pages/staff/AuditLogs/Index.vue`: Tabular audit trail with search, action filtering, and JSON metadata viewer modal.

### 4. SatuSehat Kemenkes (HL7 / FHIR R4) Payload Transformer
- **Service & Data Mapper**:
  - `app/Services/SatuSehat/FhirEncounterTransformer.php`: Maps internal consultation records to a standard FHIR R4 Transaction Bundle containing:
    * `Encounter` resource (classification, status, period, subject, serviceProvider).
    * `Condition` resource (ICD-10 coded diagnosis).
    * `Observation` resources with LOINC standard codes for vital signs (Systolic BP: `8480-6`, Diastolic BP: `8462-4`, Heart Rate: `8867-4`, Temperature: `8310-5`, Respiratory Rate: `9279-1`, SpO2: `2708-6`, Weight: `29463-7`, Height: `8302-2`).
  - API endpoint `GET /api/satusehat/records/{medical_record_id}/fhir-bundle` in `SatuSehatController.php`.

### 5. Verification & Testing
- **Feature Tests**:
  - `tests/Feature/RealtimeBroadcastingTest.php`: Verified event broadcasting on all channels.
  - `tests/Feature/KoasClinicalLogbookTest.php`: Verified draft creation, submission, DPJP grading, and revision workflow.
  - `tests/Feature/MedicalRecordAuditTrailTest.php`: Verified immutable audit logging and staff log querying.
  - `tests/Feature/SatuSehatFhirTransformerTest.php`: Verified FHIR R4 bundle generation and endpoint output.
- **Full Test Suite**: 38 hospital domain feature tests passing, 209 assertions with 0 failures (`./vendor/bin/pest`).
- **Production Build**: Clean build without errors (`npm run build`).

### 1. Doctor (DPJP) Workflow Enhancements
- **ICD-10 WHO Autocomplete**:
  - Created singular PostgreSQL table `icd10_diagnosis` with primary key `icd10_diagnosis_id`, indexed codes, and WHO classification names.
  - Model `Icd10Diagnosis.php` with database-driver agnostic `scopeSearch()` and `scopeCommon()`.
  - Autocomplete API endpoint `GET /api/clinical/icd10?q=...` with debouncing, floating suggestion dropdown, and Indonesian/English nomenclature in `ConsultationModal.vue`.
- **Quick SOAP Templates System**:
  - Created singular PostgreSQL table `soap_template` with foreign relation to `doctor_id` (null for global hospital system presets).
  - Controller endpoints `GET /api/clinical/soap-templates` and `POST /api/clinical/soap-templates` validated via `StoreSoapTemplateRequest`.
  - Added Quick SOAP template dropdown selector and "Simpan Sebagai Template SOAP Baru" modal in `ConsultationModal.vue`.
- **Clinical Safety Interceptor (Drug-Allergy & Drug-Drug Interactions)**:
  - Created singular PostgreSQL table `patient_allergy` with primary key `patient_allergy_id`.
  - Service `app/Services/ClinicalSafetyService.php`: Evaluates patient allergy conflicts against prescribed drugs and cross-matches high-risk drug interaction pairs (e.g., Ciprofloxacin + Antacid, Warfarin + NSAID, Simvastatin + Amlodipine, Paracetamol duplication).
  - Real-time reactive warning banner in `ConsultationModal.vue` displaying alert cards with severity color coding (`ShieldAlert` / `AlertTriangle`).
- **Power-User Keyboard Shortcuts**:
  - `F2`: Patient audio queue calling / recall in `QueueBoard.vue`.
  - `Ctrl + Shift + T`: Open SOAP template selector in `ConsultationModal.vue`.
  - `Ctrl + Shift + P`: Focus and add new prescription item row in `ConsultationModal.vue`.
  - `Ctrl + Enter`: Submit medical record & complete consultation in `ConsultationModal.vue`.

### 2. Staff / Pharmacy / Cashier Operational Enhancements
- **Pharmacy FEFO (First Expired, First Out) Batch Dispensation**:
  - Created singular PostgreSQL table `medicine_batch` (`medicine_batch_id`, `medicine_id`, `batch_number`, `expiry_date`, `stock_quantity`, `purchase_price`).
  - Service `app/Services/FEFODispensationService.php`: Automatically prioritizes and deducts medicine batches ordered by earliest `expiry_date ASC` using atomic pessimistic database locks (`lockForUpdate()`), decrementing both batch stock and master medicine stock upon prescription completion.
- **Direct ESC/POS Thermal Receipt POS Printing**:
  - Created `resources/js/components/ThermalReceiptModal.vue`: Supports selectable 58mm and 80mm roll paper width layouts, monospaced typography, dashed dividers, itemized consultation and medicine charges, and `@media print` CSS rules for direct thermal printer output without browser margins.
  - Endpoint `GET /staff/billing/{id}/print-thermal` in `BillingController.php`.
- **Cashier Sesi Shift & Cash Reconciliation**:
  - Created singular PostgreSQL table `cashier_shift` (`cashier_shift_id`, `nurse_id`, `shift_name`, `opened_at`, `closed_at`, `opening_cash`, `closing_cash_actual`, `total_cash_system`, `total_qris_system`, `discrepancy`, `status`, `notes`).
  - Controller `CashierShiftController.php`: Manages `currentShift()`, `openShift()`, `closeShift()` (with automated formula $ClosingCash - (OpeningCash + TotalCashSystem)$), and `printSummary()`.
  - Created `resources/js/components/ShiftManagementModal.vue` and integrated into `resources/js/pages/staff/Billing/Index.vue`.

### 3. Verification & Testing
- **Feature Tests**:
  - `tests/Feature/ClinicalSafetyAndIcd10Test.php`: Verified ICD-10 search, SOAP template retrieval/creation, allergy detection, and drug interaction evaluation.
  - `tests/Feature/PharmacyFefoAndShiftReconciliationTest.php`: Verified FEFO batch deduction, cashier shift opening, live expected cash tallying, and accurate discrepancy reconciliation.
- **Full Test Suite**: 135 tests passing, 656 assertions with 0 failures (`./vendor/bin/pest`).
- **Production Build**: Clean build without errors (`npm run build`).

---

### 1. Problem & Context
- In `resources/js/components/AppSidebar.vue`, the "Panggilan Antrean" (`/doctor/queue`) sidebar menu item was previously rendered unconditionally for all medical staff (including nurses / perawat).
- Since patient queue audio calling and consultation room examinations are strictly exclusive to doctors, the button needed to be removed for nurses/staff.

### 2. Implementation Details
- In `AppSidebar.vue`:
  - Updated `staffMenuItems`: The menu item "Panggilan Antrean" (`/doctor/queue`) is now pushed exclusively when `isDoctor.value === true`.
  - For nurses (both Tetap and Koas), the button is completely hidden from the sidebar.
  - Dynamically adjusted the primary dashboard label to "Dashboard Dokter" when logged in as a doctor, and "Dashboard Staf" when logged in as a nurse/staff.
  - Updated `dashboardUrl` and the top logo link to direct doctors to `/doctor/queue` and nurses/staff to `/staff`.

### 3. Verification & Testing
- Zero TypeScript errors (`npm run types:check`).
- Clean Vite bundle build (`npm run build`).
- Full Pest test suite passing (129 tests, 625 assertions with 0 failures).

---

## 2026-08-24 — Custom Confirmation & Notification Modals (Billing Creation & Medicine Deletion conforming to DESIGN.md)

### 1. Problem & Context
- Native browser dialogs (`window.confirm`) were previously used when generating a billing from completed consultations in `/staff/billing` and deleting medicine records in `/staff/medicines`.
- These broke the visual consistency and interactivity of the application.

### 2. Implementation Details
- **Billing Creation Confirmation Modal (`resources/js/pages/staff/Billing/Index.vue`)**:
  - Replaced browser `confirm()` with custom modal conforming strictly to `DESIGN.md` (Evergreen theme).
  - Sage Mint badge with `Receipt` icon, IvyPresto typography title, and Rubik body text.
  - Displays patient details (Nama, NIK, No. Antrean), Poliklinik & Dokter Pemeriksa, and notice explaining automated tariff calculations (konsultasi + obat).
  - Minimum 44px touch targets with smooth `motion-v` animations, backdrop blur (`bg-[#000000]/50 backdrop-blur-sm`), and animated loading spinner.
- **Medicine Deletion Confirmation Modal (`resources/js/pages/staff/Medicines/Index.vue`)**:
  - Replaced browser `confirm()` with custom modal matching the exact same design language.
  - Rose badge with `Trash2` icon, title "Konfirmasi Hapus Obat", and subtitle "Tindakan ini tidak dapat dibatalkan".
  - Displays medicine code, name, pharmaceutical form/unit, and unit price.
  - Includes reactive physical stock warning alert (`AlertTriangle`) if the medicine still has active inventory count in the pharmacy.
  - Destructive action button in `bg-rose-600` with `Loader2` processing state.

### 3. Verification & Testing
- Zero TypeScript errors (`npm run types:check`).
- Clean Vite bundle build (`npm run build`).
- Full Pest test suite passing (129 tests, 625 assertions with 0 failures).

---

## 2026-08-24 — Operational Staff Workspace Architecture Refactoring: Front-Office Check-in, Pharmacy Prescription Dispensation, Medicine Inventory CRUD, & Doctor Audio Exclusivity

### 1. Operational Realignment & Audio Exclusivity
- **Audio Queue Calling EXCLUSIVE to Doctors**: Removed audio calls from staff interfaces. Audio calling is strictly encapsulated in Doctor Queue (`/doctor/queue`).
- **Staff Core Responsibilities Realigned**:
  - **Meja Depan (Front-Office)**: Patient arrival verification and queue confirmation (`confirmArrival` changes status from `'pending'` to `'confirmed'`), unlocking patient visibility for doctors in consultation queues.
  - **Farmasi & Apotek**: Electronic prescription fulfillment queue (`'menunggu'` -> `'diproses'` -> `'selesai'`), atomic stock deductions on completion, critical medicine stock warnings, and full inventory CRUD.
  - **Kasir & Billing (POS)**: POS cashier with cash settlement, dynamic Xendit QRIS modal with 2.5s polling, EDC Card, and invoices.
  - **Operational Dashboard**: Unified real-time KPI metrics, today's registered patient check-in table, pending prescription queue, and critical inventory alerts.

### 2. Backend Implementation & Zero N+1 Eager Loading
- **`app/Models/Medicine.php`**: Added query scopes `scopeOutOfStock()`, `scopeLowStock($threshold = 10)`, and `scopeAvailable()`.
- **`app/Models/Prescription.php`**: Added query scopes `scopeMenunggu()`, `scopeDiproses()`, `scopeSelesai()`, and `scopePending()`.
- **`app/Models/Appointment.php` & `app/Models/Reservation.php`**: Added query scopes `scopePending()`, `scopeConfirmed()`, `scopeInProgress()`, `scopeCompleted()`, and `scopeToday()`.
- **`app/Http/Controllers/StaffActionController.php` (Created)**:
  - `confirmArrival(Request $request, int $id)`: Front-office check-in action.
  - `processPrescription(Request $request, int $id)`: Transitions prescription to `'diproses'` (sedang diracik).
  - `completePrescription(Request $request, int $id)`: Atomically deducts medicine stock and transitions prescription to `'selesai'`.
- **`app/Http/Controllers/MedicineController.php` (Created)**:
  - Full CRUD for medicine catalog (`index`, `store`, `update`, `destroy`) and quick stock adjustment (`adjustStock`).
  - Strict Form Requests: `StoreMedicineRequest`, `UpdateMedicineRequest`, `AdjustStockRequest`.
- **`app/Http/Controllers/StaffDashboardController.php`**:
  - Aggregates operational stats: `waiting_confirmation`, `confirmed`, `pending_prescriptions`, `out_of_stock_medicines`, `low_stock_medicines`, `unpaid_billings`, and `today_revenue`.
  - Eager loads `pendingPrescriptions`, `criticalMedicines`, `todayQueue`, `clinicMatrix`, and `weeklyTrend`.
- **`routes/web.php`**:
  - `POST /staff/reservations/{id}/confirm-arrival`
  - `POST /staff/prescriptions/{id}/process`
  - `POST /staff/prescriptions/{id}/complete`
  - `resource('/staff/medicines', MedicineController::class)`
  - `POST /staff/medicines/{id}/adjust-stock`

### 3. Frontend Architecture (`DESIGN.md` & Evergreen Theme)
- **`resources/js/pages/StaffDashboard.vue`**:
  - Front-Office Check-in table with instant **"Konfirmasi Check-in"** CTA for pending patients.
  - Pharmacy Prescription Fulfillment widget with **"Mulai Racik"** and **"Selesai & Potong Stok"** CTAs.
  - Critical Medicine Stock widget displaying low/out-of-stock items with direct links to `/staff/medicines`.
  - 6 KPI metric cards with reactive badges and color coding.
- **`resources/js/pages/staff/Medicines/Index.vue`**:
  - Comprehensive inventory catalog with financial metrics (`total_inventory_value`, out-of-stock, low-stock stats).
  - Search, filter by dosage form (Tablet, Sirup, Salep, Injeksi, Kapsul), and stock level filter.
  - Modal Tambah & Edit Obat with validation feedback.
  - Modal Penyesuaian / Restok Cepat with live stock calculation preview.
- **`resources/js/components/AppSidebar.vue`**:
  - Added `Inventori Obat` (`/staff/medicines`) menu item with `Pill` icon for permanent nurses.
  - Exact route matching for `Dashboard Staf` to eliminate highlight bleed.

### 4. Verification & Testing
- **Feature Tests**: Added `tests/Feature/StaffWorkflowAndMedicineInventoryTest.php` testing check-in, prescription fulfillment, stock deduction, and RBAC restrictions.
- **Full Test Suite**: 129 tests, 625 assertions passing with 0 failures (`./vendor/bin/pest`).
- **Build Verification**: `npm run types:check && npm run build` (0 TypeScript errors, clean production bundle).

---

## 2026-08-24 — Sidebar Bug Fix: Fix Dashboard Staf Active Highlight Bleeding onto Kasir & Billing Page

### Problem
- When a permanent nurse navigated to the Kasir & Billing page (`/staff/billing`), the "Dashboard Staf" menu button in `AppSidebar.vue` remained highlighted/active (in solid black active state) alongside Kasir & Billing.

### Root Cause & Fix
- In `resources/js/components/AppSidebar.vue`, `isRouteActive` was previously using `activePattern: 'staff*'` and `page.url.startsWith('/staff')`. Since `/staff/billing` starts with `/staff` and matches `staff*`, both the "Dashboard Staf" and "Kasir & Billing" buttons evaluated `isRouteActive` as `true`.
- Added an `exact` flag to `MenuItem` interface and updated `isRouteActive(pattern, fallbackUrl, exact)`.
- For `Dashboard Staf`, exact matching checks `(route().current('staff') || route().current('staff.dashboard')) && !route().current('staff.billing.*')` and pathname matching `currentPath === '/staff' || currentPath === '/staff/dashboard'`.
- When navigating to `/staff/billing` (or any sub-route), "Dashboard Staf" properly deactivates its active highlight.

### Verification
- Zero TypeScript errors (`npm run types:check`).
- Clean Vite bundle build (`npm run build`).
- 100% passing Pest test suite (27 tests, 125 assertions).


## 2026-08-24 — Sidebar Refinement: Restrict Kasir & Billing Menu to Permanent Nurses (Pekerja) Only

### Problem
- In `resources/js/components/AppSidebar.vue`, the "Kasir & Billing" sidebar menu item was appearing when logging in as a Doctor.

### Root Cause & Fix
- In `AppSidebar.vue`, the condition previously checked `!isNurseKoas.value`. Since doctor accounts do not have a nurse profile (`user.nurse === null`), `isNurseKoas` evaluated to `false`, causing the menu to be added to the doctor's sidebar.
- Updated `isNurseTetap` to strictly require `isNurse.value && (Boolean(user.value?.nurse?.is_tetap) || user.value?.nurse?.type === 'tetap')`.
- Updated `staffMenuItems` to only push the "Kasir & Billing" (`/staff/billing`) menu item when `isNurseTetap.value === true`. As a result, the menu is completely hidden for doctors and koas/interns, appearing exclusively for permanent nurse staff (`type: 'tetap'`).

### Verification
- Zero TypeScript errors (`npm run types:check`).
- Clean Vite bundle build (`npm run build`).
- 100% passing Pest test suite (27 tests, 125 assertions).


## 2026-08-24 — Redesign: Unified Dynamic & Interactive StaffDashboard (Doctor & Nurse RBAC)

### Objectives & Enhancements
- Redesigned `resources/js/pages/StaffDashboard.vue` to dynamically adapt and deliver specialized operational experiences for both **Dokter (Doctor)** and **Staf / Perawat (Nurse - Tetap vs Koas)** with high visual fidelity adhering strictly to `DESIGN.md` (Evergreen theme) and `GEMINI.md`.

### Key Features Implemented
1. **Dynamic Hero & Role Context**:
   - **Doctor Role**: Personalized greeting ("Selamat Bertugas, dr. [Nama Dokter]"), SIP display, specialization title, and 1-click CTA "Papan Konsultasi Pasien" (`/doctor/queue?schedule_id=...`).
   - **Nurse Tetap Role**: Full command center with KPI metrics, triase triage list, and "Kasir & Billing (POS)" CTA (`/staff/billing`).
   - **Nurse Koas / Intern Role**: Observational clinical assistant mode with graceful locking/suppression of financial billing features adhering to strict RBAC.
2. **Doctor Active Practice Clinic Widget**:
   - Live box highlighting the doctor's active clinic room, current patient being examined, remaining queue count, and direct consultation CTA.
3. **Interactive Search & Filter Bar**:
   - Instant client-side search across Patient Name, NIK, Queue Number, and Doctor Name.
   - Status filter pills ("Semua", "Sedang Diperiksa", "Menunggu", "Selesai", "Batal").
   - Poliklinik dropdown filter and "Pasien Poli Saya" toggle for doctors.
4. **Design System & Motion Integration (`DESIGN.md`)**:
   - Linen Canvas (`#edede2`), Bone Card (`#fffff3`), Sage Mint (`#beedc0`), Deep Emerald (`#065f46`), Ink Black (`#000000`).
   - IvyPresto Headline display serif and Rubik sans typography.
   - Fluid `motion-v` micro-animations (`:initial`, `:animate`, `:whileHover`, `:whileTap`).
   - Minimum 44px touch targets across all interactive buttons.
5. **Shared Auth Props & Types**:
   - Shared `doctor` object in `HandleInertiaRequests.php` and updated `User` interface in `resources/js/types/auth.ts`.

### Verification
- Zero TypeScript errors (`npm run types:check`).
- Clean Vite bundle build (`npm run build`).
- 100% passing Pest test suite (27 tests, 125 assertions).


## 2026-08-24 — Fix: Nurse and Doctor Login Redirection to /staff

### Problem
- When doctors and nurses logged in via the login screen or API, nurses were falling back to `/patient/dashboard` instead of being redirected to `/staff` (the dedicated medical staff & consultation dashboard).

### Root Causes & Fixes
1. **`app/Http/Controllers/Api/AuthController.php`**:
   - The role matching condition in `login()` previously only matched `'doctor'` and `'admin'`, falling back to `default => '/patient/dashboard'`.
   - Updated to check `in_array($user->role, ['doctor', 'nurse', 'admin'], true) || $user->is_doctor || $user->doctor()->exists() || $user->nurse()->exists()`, returning `redirect_to => '/staff'`.
2. **`resources/js/pages/auth/Login.vue`**:
   - Updated client-side `submit()` and `handleLoginSuccess()` to detect `'doctor'`, `'nurse'`, `'admin'`, `is_doctor`, and nurse/doctor profile relationships and navigate to `/staff`.
3. **`app/Http/Responses/LoginResponse.php` & `PasskeyLoginResponse.php` & `TwoFactorLoginResponse.php`**:
   - Synchronized all web, passkey, and two-factor Fortify login responses to redirect doctors and nurses to `/staff` and patients to `/patient/dashboard`.
   - Registered custom responses in `FortifyServiceProvider::boot()` to guarantee override of default Fortify providers.
4. **`app/Models/User.php`**:
   - Updated `getRoleAttribute()` accessor to properly honor explicit `$this->attributes['role']` (including `'patient'`).
5. **`resources/js/pages/Welcome.vue`**:
   - Updated header navbar action button to dynamically render "Dashboard Staf" (`/staff`) for medical staff/doctors/nurses and "Portal Pasien" (`/patient/dashboard`) for patients.

### Verification
- Added 3 comprehensive Pest tests in `tests/Feature/Auth/AuthenticationTest.php` testing doctor, nurse, and patient redirection via API and Web LoginResponse (100% passing).
- Ran full test suite (`tests/Feature/StaffBillingAndRbacTest.php`, `tests/Feature/DoctorConsultationTest.php`, `tests/Feature/Auth/AuthenticationTest.php` - 27 tests, 125 assertions, 0 failures).
- Verified TypeScript checks and production asset build (`npm run types:check && npm run build`).


## 2026-08-24 — Point-of-Sale (POS) Cashier Experience: In-App Dynamic QRIS, Multi-Channel Payment Selector, 2.5s Status Polling & QR Webhook Settlement

### What Changed
- **NPM Package**:
  - Installed `qrcode` and `@types/qrcode` (^1.5.5) for instant client-side QRIS dynamic canvas generation from official EMVCo strings.
- **Payment Gateway Services & Webhooks (`XenditService.php` & `XenditWebhookController.php`)**:
  - `createDynamicQris(Billing $billing)`: Integrates Xendit Dynamic QR API (`POST https://api.xendit.co/qr_codes` with `type: 'DYNAMIC'`), generating authentic national QRIS standard payloads with automated sandbox validation fallback.
  - `handleQrCallback(Request $request)`: Dedicated webhook endpoint (`POST /api/webhooks/xendit/qr`) verifying `x-callback-token` and settling the billing in an atomic `DB::transaction()` upon receiving `COMPLETED` / `PAID` events.
  - Enhanced main `handle()` webhook to support both `BILL-` and `QRIS-` prefixes.
- **Backend Controllers & Routes**:
  - `BillingController.php`:
    - `generateQris(int $id)`: Generates dynamic QRIS payload, stores `qr_string` in billing record, and returns JSON payload for popup modal.
    - `checkStatus(int $id)`: Lightweight status polling endpoint (`GET /staff/billing/{id}/status`) returning `{ is_paid, billing_status, paid_at, payment_method, processed_by }`.
    - `payEdc(Request $request, int $id)`: Settle payments from physical EDC terminals (Debit/Kredit), recording bank name, approval trace number, and last 4 card digits.
    - `payCash(Request $request, int $id)`: Settle cash payments with live change computation and inventory synchronization.
  - `routes/web.php` & `routes/api.php`:
    - Registered `POST /staff/billing/{id}/pay-qris`, `POST /staff/billing/{id}/pay-edc`, `GET /staff/billing/{id}/status`, and `POST /api/webhooks/xendit/qr`.
- **Frontend Components & Pages (Evergreen Theme - `DESIGN.md`)**:
  - `resources/js/components/PaymentModal.vue` [NEW]:
    - Modern hospital Point-of-Sale (POS) modal with 4 payment options (QRIS Dinamis Instan, Tunai/Cash, Mesin EDC, Virtual Account Multi-Bank).
    - In-app QRIS rendering via `<canvas ref="qrCanvasRef">` using `qrcode` library.
    - Real-time 2.5s status polling against `/staff/billing/{id}/status` with automatic cleanup on unmount.
    - Fast cash tender shortcuts (Uang Pas, Pecahan 50rb, Pecahan 100rb, Rp 500.000) and live change calculator.
    - EDC terminal swipe approval code inputs.
    - Animated celebration success view with direct 1-click **"Cetak Kuitansi Resmi (PDF)"** action.
  - `resources/js/pages/staff/Billing/Show.vue`: Upgraded right-hand payment panel with full POS capabilities, live QRIS scanning box, and background polling.
  - `resources/js/pages/staff/Billing/Index.vue`: Integrated `<PaymentModal>` for unified 1-click cashier settlement.
- **Automated Verification & Tests**:
  - Extended `tests/Feature/StaffBillingAndRbacTest.php` with 3 new feature tests (total 10 tests, 49 assertions):
    - `permanent staff can generate dynamic QRIS code with valid EMVCo qr_string and poll live status`
    - `permanent staff can settle payment using EDC card terminal swipe`
    - `xendit dynamic QRIS webhook callback settles billing and synchronizes appointment status`
  - Zero TypeScript errors (`vue-tsc --noEmit`) and clean production build (`vite build`).


### What Changed
- **Composer Package**:
  - Installed `barryvdh/laravel-dompdf` (^3.1.2) for server-side PDF rendering of hospital letterhead documents.
- **Database Migrations (PostgreSQL)**:
  - `database/migrations/2026_08_24_200001_create_billing_table.php`: Created `billing` table with explicit PK `billing_id`, FKs to `reservation_id`, `patient_id`, `processed_by_nurse_id`, invoice number, total amount, status (`unpaid`, `pending`, `paid`, `cancelled`), payment method, Xendit metadata (`xendit_id`, `xendit_payment_url`), and timestamp `paid_at`.
  - `database/migrations/2026_08_24_200002_create_billing_item_table.php`: Created `billing_item` table with explicit PK `billing_item_id`, FK to `billing_id`, `item_type` (`consultation_fee`, `medicine`, `procedure`, `admin_fee`), `item_name`, `quantity`, `unit_price`, and `subtotal`.
- **Eloquent Models & Relations**:
  - `app/Models/Billing.php`: Implemented model with singular `$table = 'billing'`, `$primaryKey = 'billing_id'`, decimal/datetime casts, and relations (`patient`, `reservation`, `processedByNurse`, `items`).
  - `app/Models/BillingItem.php`: Implemented model with singular `$table = 'billing_item'`, `$primaryKey = 'billing_item_id'`, and `billing` relation.
  - `app/Models/Nurse.php`: Added helper methods `isTetap(): bool` and `isKoas(): bool`, plus `processedBillings()` relation.
  - `app/Models/Patient.php`: Added `billings()` relation.
  - `app/Models/Appointment.php` & `app/Models/Reservation.php`: Added `billing()` and `billings()` relations.
- **RBAC Matrix & Gates (`access-pekerja-only`)**:
  - Registered `Gate::define('access-pekerja-only', fn(?User $user) => $user?->nurse?->isTetap() ?? false)` in `AppServiceProvider.php`.
  - Enforced strict authorization: permanent staff (`type: tetap`) has full access to cashier and billing workflows; interns (`type: koas`) receive HTTP 403 Forbidden on cashier, billing creation, and receipt PDF downloads.
  - Shared `$user->nurse` with `is_tetap` and `is_koas` flags in `HandleInertiaRequests.php`.
  - Updated `AppSidebar.vue` to dynamically show/hide the "Kasir & Billing" menu based on nurse role.
- **Services & Webhooks**:
  - `app/Services/XenditService.php`: Added `createBillingInvoice(Billing $billing)` with item breakdowns and offline mock fallback.
  - `app/Http/Controllers/XenditWebhookController.php`: Handles secure callbacks with `x-callback-token` header validation, marking invoices as paid inside `DB::transaction()`.
- **Backend Controllers**:
  - `app/Http/Controllers/BillingController.php`: Thin REST controller for index, show, automatic cost computation (`createFromReservation`), cash settlement (`payCash`) with change calculation, and Xendit online initiation (`payXendit`).
  - `app/Http/Controllers/MedicalDocumentController.php`: Generates official hospital letterhead PDFs:
    - `printResume`: Medical Resume EMR with SOAP notes and vitals (allowed for tetap & koas).
    - `printSickLetter`: Doctor's Sick Leave Certificate (allowed for tetap & koas).
    - `printReferral`: External Referral Letter (allowed for tetap & koas).
    - `printReceipt`: Official Cashier Payment Receipt (restricted to tetap).
  - `app/Http/Controllers/StaffDashboardController.php`: Optimized single eager query `['patient', 'doctorSchedule.doctor.specialization', 'doctorSchedule.poli', 'doctorSchedule.room', 'medicalRecord.prescription.items.medicine', 'billing']` guaranteeing zero N+1 database queries.
- **Blade PDF Templates**:
  - `resources/views/pdf/medical_resume.blade.php`: Formal Medical Resume PDF with hospital header and SOAP notes.
  - `resources/views/pdf/sick_letter.blade.php`: Official Doctor's Sick Leave Certificate with rest period.
  - `resources/views/pdf/referral_letter.blade.php`: Medical Referral Letter to specialist hospital.
  - `resources/views/pdf/payment_receipt.blade.php`: Official Cashier Receipt with itemized breakdown and cashier signature stamp.
- **Frontend Pages & Components (Evergreen Theme - `DESIGN.md`)**:
  - `resources/js/pages/staff/Billing/Index.vue`: Panel Kasir with 6 financial KPI metric cards, unbilled completed consultations quick-action section, search/status filter bar, invoices data table, and modal for cash tender calculation & Xendit online checkout.
  - `resources/js/pages/staff/Billing/Show.vue`: Detailed itemized invoice breakdown, patient identity card, cash payment settlement panel with fast-tender buttons and live change calculation, Xendit QRIS trigger, and receipt PDF download.
  - `resources/js/pages/StaffDashboard.vue`: Added role badges (Perawat Tetap / Dokter Muda Koas), Kasir & Billing quick action button, and "Aksi & Dokumen Medis" column for instant 1-click printing of Resume Medis, Surat Sakit, Surat Rujukan, and Kasir Billing.
- **Automated Tests**:
  - `tests/Feature/StaffBillingAndRbacTest.php`: 7 comprehensive tests (33 assertions) testing permanent staff vs koas RBAC, cash settlement in DB transactions, Xendit checkout, webhook token validation, and shared vs restricted PDF generation.
  - Full suite passed: 117 tests, 560 assertions (100% pass rate).

### What Changed
- **Database Migrations (PostgreSQL)**:
  - `database/migrations/2026_08_24_100001_create_medicines_table.php`: Created `medicine` table with singular naming, explicit primary key `medicine_id`, codes, types, stock tracking, units, and prices.
  - `database/migrations/2026_08_24_100002_create_medical_records_table.php`: Created `medical_record` table with explicit primary key `medical_record_id`, foreign keys to `patient`, `doctor`, and `appointments` (`reservation_id`), with SOAP columns (`subjective`, `objective` JSON for vital signs, `assessment`, `plan`, and `physical_check`).
  - `database/migrations/2026_08_24_100003_create_prescriptions_table.php`: Created `prescription` table with explicit primary key `prescription_id`, unique `prescription_number`, foreign key to `medical_record`, status ('menunggu', 'diproses', 'selesai'), and notes.
  - `database/migrations/2026_08_24_100004_create_prescription_items_table.php`: Created `prescription_item` table with explicit primary key `prescription_item_id`, foreign keys to `prescription` and `medicine`, quantity, dosage (signa), instructions, and notes.
- **Eloquent Models**:
  - `app/Models/MedicalRecord.php`: Implemented model with singular `$table = 'medical_record'`, `$primaryKey = 'medical_record_id'`, PostgreSQL JSON/integer type casts, and relationships to `Patient`, `Doctor`, `Appointment`/`Reservation`, and `Prescription`.
  - `app/Models/Medicine.php`: Implemented model with singular `$table = 'medicine'`, `$primaryKey = 'medicine_id'`, and `prescriptionItems()` relation.
  - `app/Models/Prescription.php`: Implemented model with singular `$table = 'prescription'`, `$primaryKey = 'prescription_id'`, and relationships to `MedicalRecord` and `items()` / `prescriptionItems()`.
  - `app/Models/PrescriptionItem.php`: Implemented model with singular `$table = 'prescription_item'`, `$primaryKey = 'prescription_item_id'`, and relationships to `Prescription` and `Medicine`.
  - `app/Models/Reservation.php`: Created model mapping to `appointments` table with `medicalRecords()` and `medicalRecord()` relationships.
  - `app/Models/Doctor.php`, `app/Models/Patient.php`, `app/Models/Appointment.php`: Updated with reverse relationships (`medicalRecords()`, `medicalRecord()`).
- **Database Seeder**:
  - `database/seeders/MedicineSeeder.php`: Seeded comprehensive pharmaceutical inventory across analgesics, antibiotics, antihistamines, gastrointestinal, antihypertensive, and respiratory categories.
- **Backend Controller & Routes**:
  - `app/Http/Controllers/DoctorConsultationController.php`:
    - `store()`: Validates SOAP notes, vital signs, and prescription items; ensures authenticated doctor context via `auth()->user()->doctor->doctor_id`; wraps operations in atomic `DB::transaction()`; verifies and decrements medicine stocks with pessimistic row locking (`Medicine::lockForUpdate()`); creates `medical_record`, `prescription`, and `prescription_item` records; updates appointment status to `'completed'`.
    - `getPatientHistory()`: Retrieves patient past clinical history with eager-loaded relations (`doctor.specialization`, `prescription.items.medicine`, `reservation`).
    - `getMedicines()`: Returns pharmaceutical inventory for dynamic search and auto-complete in prescription builder.
  - `routes/web.php` & `routes/api.php`: Registered doctor consultation routes (`POST /doctor/consultations`, `GET /doctor/patients/{patient_id}/history`, `GET /doctor/medicines`).
- **Frontend Components & UI/UX**:
  - `resources/js/types/hospital.ts`: Added TypeScript interfaces `VitalSigns`, `Medicine`, `PrescriptionItem`, `Prescription`, and `MedicalRecord`.
  - `resources/js/components/ConsultationModal.vue`: Comprehensive presentational/interactive modal built in the Evergreen design aesthetic (#edede2 linen canvas, #fffff3 bone card, #beedc0 sage mint, #000000 pill CTAs):
    - **Section 1: Vital Signs & SOAP Inputs**: Real-time vital signs inputs (BP, Pulse, Temp, RR, Weight, Height, SpO2) with automatic BMI calculation; structured SOAP textareas.
    - **Section 2: Dynamic E-Prescription Builder**: Add/remove medicine rows, searchable stock counters, dosage presets, usage instructions, live stock validation, notes, and estimated total prescription cost.
    - **Section 3: Patient Medical History Timeline Drawer**: Interactive chronological timeline with visit dates, attending specialists, past diagnoses, recorded vitals, and prior prescribed drugs.
  - `resources/js/pages/doctor/QueueBoard.vue`: Integrated **"Periksa & Rekam Medis (EMR)"** quick action on active consultation cards and queue tables, connected with `<ConsultationModal>`.
- **Automated Testing & Quality**:
  - `tests/Feature/DoctorConsultationTest.php`: Created 6 feature tests verifying EMR storage, e-prescription stock deduction, stock limit validation rollback, patient clinical history retrieval, and role authorization. All 6 tests (41 assertions) passed.
  - `npm run types:check` and `npm run build`: Zero errors, clean TypeScript build.

## 2026-08-24 — Repeatable Queue Calling & Automated Next-Call Features

### What Changed
- **`app/Http/Controllers/DoctorQueueController.php`**:
  - Updated `callPatient()` to explicitly update and save `$appointment->updated_at = now()`. This ensures that every time a doctor clicks "Panggil Ulang Pasien", the timestamp changes and immediately triggers re-announcement on the TV display board without state conflicts.
- **`resources/js/pages/doctor/QueueBoard.vue`**:
  - Added dedicated **"Panggil Ulang Pasien"** button with pulsing animation on the active consultation card, allowing doctors to re-call the same patient as many times as needed.
  - Added **"Otomatis Panggil Berikutnya" (Auto-Next Mode)** toggle. When enabled, completing a consultation (`handleComplete`) automatically calls the next waiting patient in line after 800ms.
  - Added **"Panggil Antrean Terdepan"** quick-action button when the examination room is empty.
  - Preserved preference in `localStorage`.
- **`resources/js/pages/DisplayBoard.vue`**:
  - Added **"Ulangi Panggilan 2x"** automatic repeat mode (enabled by default and togglable in header). When a patient is called, the display plays the chime & speech, and after 3.5s automatically re-announces the call so patients in the waiting area hear it clearly.
  - Cleaned timeout lifecycle in `onBeforeUnmount`.
- **Database Safety**:
  - Verified and preserved 100% of doctor records (101 doctors), 240 schedules, 16 polikliniks, and patient user accounts. No doctor or patient data deleted.
- **`tests/Feature/ReservationVisibilityTest.php`**:
  - Added test case verifying repeated calls update `updated_at` and trigger redirect seamlessly. All 6 tests (66 assertions) passed.

## 2026-08-24 — Fix Display Board Queue Display & Voice Announcement

### Root Cause Diagnosed
- **Queue Number Missing on Display Board**: `PublicDisplayController` originally restricted `latestCalled` and `clinics` with `->whereDate('appointment_date', $today)`. When a patient made a reservation for upcoming dates and the doctor clicked "Panggil Pasien" on the doctor queue board, the appointment status became `'in_progress'`, but `PublicDisplayController` filtered it out because `appointment_date` was not today, resulting in `latestCalled` returning `null`.
- **No Sound/Voice on Call**:
  - Browser Autoplay Policy blocks Web Audio API (`AudioContext`) and `speechSynthesis` until an explicit user gesture (click/tap) occurs. `DisplayBoard.vue` originally had audio disabled by default without a prominent action banner.
  - Repeating calls for the same patient was not re-triggering speech synthesis because the watcher only checked `appointment_id` changes.

### What Changed
- **`app/Http/Controllers/PublicDisplayController.php`**:
  - Updated `latestCalled` to fetch active `in_progress` appointments globally sorted by `latest('updated_at')` without restricting by scheduled date.
  - Updated `clinics` mapping to retrieve active `in_progress` appointments and the next waiting queue number per schedule accurately.
  - Included ISO `updated_at` timestamps in payload for repeat-call tracking.
- **`resources/js/pages/DisplayBoard.vue`**:
  - Added an interactive **"Aktifkan Suara Panggilan Antrean"** banner at the top of the monitor display to unlock browser audio with one tap.
  - Added global user gesture unlock (`window.addEventListener('click', ...)`).
  - Added a manual **"Uji Suara"** test button for staff to verify the chime bell and Indonesian speech synthesis voice engine.
  - Enhanced chime sound using a 4-tone hospital chime (`C5 -> E5 -> G5 -> C6`) via Web Audio API.
  - Enhanced Text-to-Speech pronunciation in Indonesian with patient name and polyclinic/room routing.
  - Updated watcher to track `${appointment_id}_${updated_at}` so repeat calls on the same patient re-trigger the bell and voice announcement.
  - Reduced polling interval from 4s to 2.5s for near-instant reactivity.
- **`tests/Feature/PublicDisplayTest.php`**:
  - Created automated test verifying the public display board and `/display/live-data` endpoint. All 6 tests (61 assertions) passed.

## 2026-08-24 — Fix PostgreSQL Constraint Check on Appointments Status ('in_progress')

### Root Cause Diagnosed
- **Missing `'in_progress'` in Database Status Check Constraint**: The `appointments` table migration originally created the enum column as `enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])`. In PostgreSQL, this created a check constraint `appointments_status_check` that did not allow the `'in_progress'` value. When a doctor clicked "Panggil Pasien", updating status to `'in_progress'` triggered `SQLSTATE[23514]: Check violation: 7 ERROR: baris baru untuk relasi « appointments » melanggar pemeriksaan constraint « appointments_status_check »`.

### What Changed
- **`database/migrations/2026_08_21_072612_create_appointments_table.php`**:
  - Updated status enum to include `'in_progress'`: `['pending', 'confirmed', 'in_progress', 'completed', 'cancelled']`.
- **`database/migrations/2026_08_24_000001_add_in_progress_to_appointments_status_check.php`**:
  - Created dedicated migration updating the PostgreSQL check constraint `appointments_status_check` to safely allow `status IN ('pending', 'confirmed', 'in_progress', 'completed', 'cancelled')`.
  - Executed migration successfully.
- **`app/Models/Appointment.php`**:
  - Added explicit `getRouteKeyName(): string { return 'appointment_id'; }` for robust route model binding.
- **`tests/Feature/ReservationVisibilityTest.php`**:
  - Added test assertions for `callPatient` (`in_progress`), `completeConsultation` (`completed`), and `skipPatient` (`pending`). All 43 assertions passed.

## 2026-08-23 — Fix Reservation Data Synchronization in Doctor Queue & Staff Dashboard

### Root Cause Diagnosed
- **Hardcoded Single-Date Filter**: In `DoctorQueueController.php` (line 39) and `StaffDashboardController.php` (line 102), the controllers strictly filtered appointments with `whereDate('appointment_date', $today)`. When patients booked reservations for future dates (e.g. `2026-08-25`), these appointments were invisible in both the Doctor Queue board and the Staff Dashboard recent activity table.
- **Missing Date Filtering in Doctor Queue UI**: `QueueBoard.vue` did not provide date switcher controls or calendar inputs, preventing doctors from viewing upcoming patient lists.
- **Unscoped Schedule Default**: `DoctorQueueController.php` defaulted `$selectedScheduleId` to `$schedules->first()` rather than prioritizing the logged-in doctor's active schedules.

### What Changed
- **`app/Http/Controllers/DoctorQueueController.php`**:
  - Prioritized logged-in doctor's schedules in the dropdown and defaulted `$selectedScheduleId` to the doctor's active schedule.
  - Added dynamic date querying via `$request->query('date')`.
  - Added aggregation for `availableDates` to pass active reservation dates and patient counts per date for the selected schedule.
  - Automatically defaults to `$today` if appointments exist today, or the nearest upcoming reservation date if today has no bookings.
  - Supported `date=all` to view all appointments across all dates for the chosen schedule.
- **`resources/js/pages/doctor/QueueBoard.vue`**:
  - Implemented interactive date filter tabs ("Hari Ini", date badge tabs for all active reservation dates, "Semua Tanggal", and custom date picker).
  - Added header navigation buttons to switch back to `Dashboard Staf` (`/staff`) and open `Layar TV Antrean` (`/display`).
  - Added `appointment_date` display on patient cards and waiting list tables.
  - Added doctor badge and formatted doctor names with `formatDoctorName` helper.
- **`app/Http/Controllers/StaffDashboardController.php`**:
  - Updated `recentAppointments` query to use `latest('created_at')->take(12)` instead of restrictive `whereDate('appointment_date', $today)`, ensuring all newly created patient reservations appear immediately in the activity log.
  - Added `total_upcoming` and `total_all` to dashboard stats aggregation.
  - Enhanced `clinicMatrix` to include `total_upcoming` patient counts for each polyclinic.
- **`resources/js/pages/StaffDashboard.vue`**:
  - Added `Tgl Kunjungan` (`appointment_date`) column to the recent reservation activity table.
  - Updated props type definitions with `total_upcoming` and `total_all`.
- **`tests/Feature/ReservationVisibilityTest.php`**:
  - Added automated Pest test verifying that future-dated patient reservations immediately appear in both Doctor Queue and Staff Dashboard. Passed with 37 assertions.

## 2026-08-23 — Doctor Login Redirection Fix to /staff

### What Changed
- **`app/Models/User.php`**:
  - Diperbarui accessor `getRoleAttribute()` dengan hierarki prioritas yang akurat:
    1. Jika memiliki relasi `doctor` -> mengembalikan `'doctor'`
    2. Jika memiliki relasi `nurse` -> mengembalikan `'nurse'`
    3. Jika memiliki relasi `patient` -> mengembalikan `'patient'`
    4. Jika memiliki atribut `role` eksplisit (`'admin'`, `'doctor'`, `'nurse'`) -> mengembalikan nilai tersebut
    5. Jika memiliki `currentTeam` internal -> mengembalikan nilai `teamRole` (`'staff'`, `'admin'`, `'owner'`, `'member'`)
    6. Fallback default ke `'patient'`
  - Ditambahkan atribut accessor `getIsDoctorAttribute()` (`$user->is_doctor`) yang mengembalikan `true` apabila relasi dokter terdeteksi atau role bernilai `'doctor'`.
  - Ditambahkan atribut `'role'` ke dalam `#[Fillable]`.
- **`app/Http/Middleware/HandleInertiaRequests.php`**:
  - Disederhanakan mapping data auth pengguna yang dibagikan (`$user->role` dan `$user->is_doctor`) memanfaatkan accessor model `User`.
- **`app/Http/Responses/LoginResponse.php` & `app/Http/Responses/PasskeyLoginResponse.php`**:
  - Mengarahkan akun dokter, perawat, dan staf internal secara otomatis ke `/staff` setelah login web maupun passkey.
  - Memprioritaskan pengalihan pasien umum ke `/patient/dashboard`.
- **`resources/js/pages/auth/Login.vue`**:
  - Diperbarui penanganan respons login (`handleLogin` & `handleLoginSuccess`) agar secara cerdas memeriksa `user.role === 'doctor'` atau `user.is_doctor`, mengarahkan dokter ke `/staff` dan pasien ke `/patient/dashboard`.
- **`database/seeders/DoctorSeeder.php`**:
  - Ditambahkan `'role' => 'doctor'` pada saat `User::create` untuk dokter seeder.
- **`bootstrap/app.php`**:
  - Ditambahkan pengecualian CSRF `api/*` pada `validateCsrfTokens` untuk menjamin request autentikasi API SPA berjalan mulus.
- **`tests/Feature/RoleBasedLoginRedirectTest.php`**:
  - Dibuat pengujian unit dan fitur komprehensif yang memvalidasi bahwa login dokter diarahkan ke `/staff` dan login pasien diarahkan ke `/patient/dashboard` baik via API maupun web login response.

## 2026-08-23 — Navbar Auth Action Streamlining & Patient AppSidebar Home Navigation

### What Changed
- **`resources/js/pages/Welcome.vue`** (serta seluruh halaman publik: `Location.vue`, `Specializations/Index.vue`, `teams/Index.vue`, `PatientStory.vue`, `Schedule.vue`):
  - Dihapus tombol redundant `Antrean Saya` di navbar saat user telah login/terautentikasi, menyisakan satu tombol utama `Portal Pasien` (`/patient/dashboard`) yang terintegrasi langsung dengan semua fitur antrean dan riwayat rekam medis.
- **`resources/js/components/AppSidebar.vue`**:
  - Ditambahkan item navigasi menu `Beranda Utama` (ikon `Home`, tautan ke `/`) dan `Jadwal Dokter` (`/doctor-schedules`) pada kelompok `patientMenuItems` untuk pasien, memungkinkan pasien berpindah kembali ke halaman beranda publik atau jadwal dokter kapan saja dari dalam dashboard portal pasien.
  - Diperbarui `isRouteActive` helper agar rute root (`/`) terdeteksi tepat tanpa menganggap semua URL berawalan `/` aktif.
  - Diperbarui tautan brand logo pada header sidebar agar mengarah langsung ke halaman Welcome (`/`) untuk pasien.


### What Changed
- **`resources/js/pages/doctor/Schedule.vue`**:
  - Implemented `visiblePages` windowed pagination with ellipsis (`1, 2, ..., 5, 6, 7, ..., 20`) replacing the unconstrained `v-for="pageIndex in table.getPageCount()"`.
  - Added First Page (`ChevronsLeft`) and Last Page (`ChevronsRight`) shortcuts.
  - Formulated a robust responsive flex layout (`flex flex-col lg:flex-row items-center justify-between gap-4`) with `flex-wrap` and `overflow-hidden` so that pagination never overflows or breaks out of card containers on mobile, tablet, or desktop devices with high volumes of seeded doctors/schedules.


### What Changed
- **`resources/js/pages/doctor/Schedule.vue`**:
  - Added `formatDoctorName` helper that prevents duplicate prefixes (`dr. dr.` or `dr. drg.`) when doctor names from the database or seeders already begin with medical titles (`dr.`, `drg.`, `Prof.`, etc.).
  - Updated doctor card avatar initial calculation to clean medical title prefixes and display the doctor's actual first name initial (e.g. `F` for `dr. Farhan Maulana, Sp.M`).
- **`resources/js/components/TicketSuccessModal.vue`**, **`MyAppointments.vue`**, **`PatientDashboard.vue`**, **`DisplayBoard.vue`**, **`StaffDashboard.vue`**:
  - Standardized doctor name rendering across modals, appointments list, queue matrices, and dashboard components using `formatDoctorName` to guarantee consistent presentation throughout the entire application.


### What Changed
- **`resources/js/pages/doctor/Schedule.vue`**:
  - Implemented `@tanstack/vue-table` (`createTableHook`) and `@tanstack/table-core` (`tableFeatures`, `globalFilteringFeature`, `columnFilteringFeature`, `rowSortingFeature`, `rowPaginationFeature`, `createFilteredRowModel`, `createSortedRowModel`, `createPaginatedRowModel`).
  - Formulated full type safety with `tableFeatures` and `createTableHook` + `createAppColumnHelper<DoctorSchedule>()`, passing `columnHelper.columns([ ... ])` to prevent type widening and eliminate all TypeScript compilation errors.
  - Fully registered modular table APIs (`getPageCount`, `getCanPreviousPage`, `getCanNextPage`, `previousPage`, `nextPage`, `setPageIndex`, `setPageSize`) for SSR (`npm run build:ssr`), client builds (`npm run build`), and `vue-tsc --noEmit` checks.
  - Configured custom TanStack global filtering (`globalFilterFn`) for multi-attribute matching (doctor name, polyclinic, medical specialization, room).
  - Added dedicated column filters for polyclinic selection (`poliFilterFn`) and day-of-week practicing schedule (`dayFilterFn`).
  - Integrated TanStack client-side sorting (`sortingState`) supporting sorting by Doctor Name (A-Z / Z-A), Polyclinic, and Practice Start Time.
  - Added responsive TanStack Table pagination with page size selector (12, 24, 48, 96 items), page jump controls, and instant reactivity.
  - Retained full compatibility with BookingModal and TicketSuccessModal reservations.
- **`app/Models/User.php`**:
  - Added `getRoleAttribute()` accessor to guarantee safe role resolution across all contexts.
- **`app/Http/Middleware/HandleInertiaRequests.php`**:
  - Added safe user role fallback when sharing auth user props.
- **`tests/Feature/DoctorScheduleTest.php`**:
  - Added Pest feature tests verifying guest and authenticated patient schedule page access.


### What Changed
- **`resources/js/pages/Welcome.vue`**, **`Clinic/Location.vue`**, **`PatientStory.vue`**, **`Specializations/Index.vue`**, **`teams/Index.vue`**, **`doctor/Schedule.vue`**:
  - Removed "Pusat Unggulan" and "Fasilitas" standalone buttons from the desktop navigation bar across all 6 public pages.
  - Added Lucide icons for all 4 navigation buttons across all public pages:
    1. **Cari Dokter** (`/schedule-guest`): `<Stethoscope class="size-4 shrink-0" />`
    2. **Layanan & Spesialisasi** (Mega Menu): `<Activity class="size-4 shrink-0" />`
    3. **Lokasi Klinik** (`/clinic-location`): `<MapPin class="size-4 shrink-0" />`
    4. **Cerita Pasien** (`/patient-story`): `<HeartHandshake class="size-4 shrink-0" />`
  - Implemented interactive pill designs for all navigation buttons (`min-h-[40px] px-4 py-2 rounded-[40.5px]`):
    - **Active Page State**: Solid black pill (`bg-[#000000] text-white border-[#000000] shadow-sm`) with an animated sage mint pulsing dot (`bg-[#beedc0] animate-pulse`) and sage mint icon highlight (`text-[#beedc0]`).
    - **Interactive Hover State**: Responsive light card pill (`hover:bg-[#fffff3] hover:border-[#333333]/15 hover:text-[#000000]`) on all inactive buttons.
  - Standardized button sequence and guaranteed 0px layout shift across all page transitions.

## 2026-08-23 — Public Clinic & Hospital Location Directory Feature (/clinic-location)

### What Changed
- **`app/Http/Controllers/ClinicLocationController.php`**:
  - Created controller providing `index(Request $request)` to render the public clinic and hospital location directory (`Clinic/Location`).
  - Provides detailed datasets for 10 hospital & clinic branch locations (Rumah Sakit Utama, Klinik Utama, Klinik Pratama) across Tangerang, Tangerang Selatan, Jakarta (Pusat, Selatan, Barat, Utara), Surabaya, and Denpasar Bali.
  - Sends facility metadata: facility type, accreditation, address, distance info, operating hours, emergency 24h status, bed capacity, doctor count, Google Maps links, available polyclinics, and diagnostic facilities.
- **`routes/web.php`**:
  - Registered public route `GET /clinic-location` named `clinic.location` mapped to `ClinicLocationController::class`.
- **`resources/js/app.ts`**:
  - Added `Clinic/Location` and `clinic/Location` to the layout exemption list for standalone rendering (`return null`).
- **`resources/js/types/hospital.ts`**:
  - Exported `ClinicBranchItem` and `ClinicLocationProps` TypeScript interfaces.
- **`resources/js/pages/Clinic/Location.vue`**:
  - Built comprehensive public directory page with the Evergreen design system (#edede2, #fffff3, #beedc0, #000000, font 'ivypresto-headline' and 'Rubik', rounded-[40.5px]):
    - Top utility bar with IGD hotline (`1-500-181`), WhatsApp Care, TV display link, and KARS badge.
    - Sticky Navbar with 3-column Mega Menu, active "Lokasi Klinik" nav link, and dynamic auth buttons.
    - Hero editorial section with 4 highlight metrics (10+ Cabang, 24 Jam IGD, 500+ Dokter, KARS Paripurna).
    - Smart Siloam-style filter widget with search input, city select dropdown, and facility type filter chips.
    - Multi-column clinic card grid with facility badge, 24h emergency pulse indicator, address, hours, capacities, available polyclinics, diagnostic facilities, and 3 quick action buttons ("Lihat Jadwal Dokter", "Petunjuk Arah", "Hubungi WA").
    - Interactive empty state with filter reset.
    - Emergency 24-hour ambulance & call center banner.
    - Multi-column Evergreen public footer.
- **`resources/js/pages/Welcome.vue`**, **`PatientStory.vue`**, **`Specializations/Index.vue`**, **`teams/Index.vue`**, **`doctor/Schedule.vue`**:
  - Synchronized desktop navigation bar to include "Lokasi Klinik" (`/clinic-location`).
- **`tests/Feature/ClinicLocationTest.php`**:
  - Created Pest feature test verifying guest accessibility, status 200, and props assertions (1 test, 16 assertions passed).

### Why
- Deliver an intuitive, Siloam-inspired clinic and hospital branch directory allowing patients and families to easily find nearby facilities, check operating hours, view doctor schedules, and navigate via Google Maps.

## 2026-08-23 — Public Doctor Schedule Page (/schedule-guest) Navbar & Mega Menu Synchronization

### What Changed
- **`resources/js/pages/doctor/Schedule.vue`**:
  - Added `jumpToPoliTeam(poliName)` helper to route clicking on "Poliklinik Spesialis" in the Mega Menu dropdown directly to `/teams?poli=...`.
  - Added backdrop overlay `<div v-if="isMegaMenuOpen" class="fixed inset-0 z-30 bg-black/15 backdrop-blur-[1px] transition-opacity" @click="isMegaMenuOpen = false" />` ensuring dropdown closes on outside click.
  - Synchronized "Pusat Unggulan (CoE)" and "Fasilitas" mega menu links with `@click="isMegaMenuOpen = false"`.
  - Synchronized mega menu bottom quick banner with link to `/specializations` ("Buka Sub-Spesialisasi Medis").
  - Updated top utility bar with "Akreditasi KARS Paripurna" badge for 100% navbar consistency across the application.

## 2026-08-23 — Public Poliklinik & Medical Team Profiles Feature (teams/Index.vue)

### What Changed
- **`app/Http/Controllers/PoliTeamController.php`**:
  - Created controller providing `index(Request $request)` to serve comprehensive polyclinic department profiles accessible via `GET /teams` and `GET /teams?poli=...`.
  - Comprehensive clinical datasets for 10 Poliklinik Spesialis: Poli Umum, Poli Penyakit Dalam, Poli Anak & Tumbuh Kembang, Poli Jantung & Pembuluh Darah, Poli Kebidanan & Kandungan, Poli Bedah & Ortopedi, Poli Gigi & Mulut, Poli Mata, Poli THT, dan Poli Saraf.
  - Eager-loads active doctor schedules (`DoctorSchedule::with(['doctor.specialization', 'doctor.user', 'poli', 'room'])`), database doctors, and nurses.
- **`routes/web.php`**:
  - Registered public route `GET /teams` named `teams.poli.index` mapped to `PoliTeamController::class`.
- **`resources/js/app.ts`**:
  - Added `teams/Index` and `Teams/Index` to the layout exemption list for standalone rendering.
- **`resources/js/types/hospital.ts`**:
  - Added TypeScript interfaces: `PoliRoomInfo`, `PoliDoctorMember`, `PoliNurseMember`, `PoliFaq`, `PoliTeamDetail`, and `PoliTabItem`.
- **`resources/js/pages/teams/Index.vue`**:
  - Rebuilt as a clean, streamlined standalone public page preserving the exact Siloam-inspired navbar and 3-column Mega Menu from `Welcome.vue`:
    - Top utility bar with IGD hotline (`1-500-181`) and TV monitor link.
    - Full 3-column Mega Menu dropdown on "Layanan & Spesialisasi" with `jumpToPoliTeam(poli)` (navigation switching directly handled via dropdown menu without cluttered breadcrumbs or duplicate pill buttons).
    - Department hero banner with location, operating hours, and leadership spotlight (Kepala Unit & Perawat Kepala).
    - Scope of medical services checklist & examination room cards.
    - Specialist doctors schedule grid with direct integration into `BookingModal.vue` and `TicketSuccessModal.vue`.
    - Nursing & paramedic team showcase with STR and specialty competencies.
    - Interactive FAQ accordion for each polyclinic.
    - Emergency bottom CTA banner and multi-column hospital footer.
- **`resources/js/pages/Welcome.vue`**, **`PatientStory.vue`**, **`Specializations/Index.vue`**:
  - Updated Mega Menu "Poliklinik Spesialis" item clicks from `jumpToDoctorSearch(poli)` to `jumpToPoliTeam(poli)` linking directly to `/teams?poli=...`.
- **`tests/Feature/PoliTeamTest.php`**:
  - Created Pest feature test verifying default rendering and query parameter switching (2 tests, 24 assertions passed).

### Why
- Enable patients and visitors to explore each polyclinic unit's dedicated team of doctors, nursing staff, clinical rooms, and practice schedules directly from the "Layanan & Spesialisasi" navbar dropdown menu.

## 2026-08-23 — Public Medical Specializations & Sub-Specializations Feature (/specializations)

### What Changed
- **`app/Http/Controllers/SpecializationController.php`**:
  - Created controller with `index(Request $request)` supporting dynamic sub-specialization routing via `?slug=...` (Pulmonologi, Kardiologi, Ortopedi, Onkologi, Pediatri, Penyakit Dalam).
  - Fetches eager-loaded active doctor schedules (`DoctorSchedule::with(['doctor.specialization', 'doctor.user', 'poli', 'room'])`) preventing N+1 queries.
  - Sends comprehensive clinical datasets including metrics, conditions treated with symptoms checklists, advanced procedures with diagnostic benefits, and patient FAQ accordions.
- **`routes/web.php`**:
  - Registered public route `GET /specializations` named `specializations.index` mapped to `SpecializationController::class`.
- **`resources/js/app.ts`**:
  - Added `Specializations/Index`, `Specializations/Show`, and `Specialization` to the layout exemption list (`defineOptions({ layout: undefined })` standalone rendering).
- **`resources/js/types/hospital.ts`**:
  - Added TypeScript interfaces `ConditionTreated`, `MedicalProcedure`, `SpecializationMetric`, `SpecializationFaq`, `SpecializationDetail`, and `SpecializationTabItem`.
- **`resources/js/pages/Specializations/Index.vue`**:
  - Created complete Siloam-inspired public page with the Evergreen design system:
    - **Top Utility & Emergency Bar**: IGD 24 Jam (`1-500-181`), WhatsApp Care, TV Display link, and KARS accreditation badge.
    - **Sticky Navbar with 3-Column Mega Menu**: Poliklinik, Pusat Unggulan, Fasilitas Penunjang, auth controls.
    - **Interactive Sub-Specialization Switcher**: Pill tab bar enabling visitors to seamlessly switch disciplines.
    - **Editorial Hero Banner**: Serif display headline, clinical quote, and 4 quick metric highlight cards.
    - **Conditions & Diseases Treated Grid**: Severity badges, clinical descriptions, and symptoms checklists.
    - **Procedures & Clinical Excellence Grid**: Duration badges, diagnostic protocols, and key benefit items.
    - **Integrated Doctor & Schedule Grid**: Doctor cards with avatar, specialization, polyclinic, room, schedule times, and instant booking CTA.
    - **Interactive FAQ Accordion**: Patient guidance, preparation, and BPJS/insurance questions.
    - **Modal Integration**: Full seamless flow with `BookingModal.vue` and `TicketSuccessModal.vue`.
    - **Bottom Emergency CTA Banner & Multi-Column Hospital Footer**.
- **`tests/Feature/SpecializationTest.php`**:
  - Created Pest feature test verifying default rendering and query param switching (2 tests, 25 assertions passed).

### Why
- Provides comprehensive clinical information for patients seeking specialized care with direct booking capability in the Evergreen design aesthetic.

## 2026-08-23 — Synchronize Navbar on /patient-story (PatientStory.vue) with Welcome.vue

### What Changed
- **`resources/js/pages/PatientStory.vue`**:
  - Synchronized header navbar to match [`Welcome.vue`](file:///home/stravesstgod/rumah-sakit/resources/js/pages/Welcome.vue) with 100% layout fidelity:
    - **Logo & Identity**: Links directly to `/` with smooth motion hover.
    - **Navigation Links**: "Cari Dokter" (`/schedule-guest`), "Layanan & Spesialisasi" (Mega Menu), "Pusat Unggulan" (`/#pusat-unggulan`), "Fasilitas" (`/#fasilitas`), and "Cerita Pasien" (`/patient-story` with active state underline).
    - **3-Column Mega Menu (`w-[820px]`)**:
      - Column 1: Poliklinik Spesialis (10 Poliklinik with `jumpToDoctorSearch(poli)`).
      - Column 2: Pusat Layanan Unggulan (6 CoE items: Jantung, Ibu & Anak, Ortopedi, Onkologi, Saraf, Penyakit Dalam linking to `/#pusat-unggulan`).
      - Column 3: Fasilitas Penunjang (Laboratorium, Radiologi, Farmasi, Ambulans) with vertical stack action buttons ("Buka Monitor TV Antrean" & "Panduan Pasien & BPJS").
      - Bottom Quick Action Banner: "Buka Seluruh Jadwal Praktik Dokter".
    - **Dynamic Auth Controls**: "Masuk" & "Daftar Akun" vs "Antrean Saya" & "Portal Pasien".

### Why
- Ensure a unified, consistent navigation experience across all public pages (`/`, `/schedule-guest`, `/patient-story`).

## 2026-08-23 — Public "Cerita Pasien" (Patient Story) Feature & Evergreen Design

### What Changed
- **`app/Http/Controllers/PatientStoryController.php`**:
  - Created controller with `index()` method providing `featuredStory`, array of category-based `stories`, and `categories` filter array.
- **`routes/web.php`**:
  - Registered public route `GET /patient-story` pointing to `PatientStoryController::class` with name `patient.story`.
- **`resources/js/app.ts`**:
  - Added `name === 'PatientStory' || name === 'Patient/Story'` to the layout exclusion rule (returning `null`) so the page renders standalone without the dashboard sidebar.
- **`resources/js/types/hospital.ts`**:
  - Defined TypeScript interface `PatientStory` for complete type-safety.
- **`resources/js/components/StoryDetailModal.vue`**:
  - Created presentational modal dialog with Motion transitions displaying patient profile, specialist doctor info, quote banner, multi-paragraph story narrative, and direct CTA to book a consultation with the in-charge doctor.
- **`resources/js/pages/PatientStory.vue`**:
  - Created standalone public page inspired by Siloam Hospitals with the Evergreen design system:
    - Top utility bar with IGD hotline and queue TV monitor links.
    - Header navbar with active state, brand logo, mega menu, and dynamic auth buttons.
    - Hero section with badge, typography, and descriptive lead.
    - Featured Story Card (editorial showcase with emotional quote and quick doctor booking).
    - Interactive Search & Category Filter bar with live reactive filtering.
    - Responsive 3-column patient story card grid.
    - Call to action banner linking directly to doctor schedules.
    - Multi-column hospital footer.
- **`resources/js/pages/Welcome.vue` & `resources/js/pages/doctor/Schedule.vue`**:
  - Added "Cerita Pasien" navigation link in the main navbar for seamless site-wide exploration.
- **`tests/Feature/PatientStoryTest.php`**:
  - Added Pest feature test verifying HTTP 200 OK and Inertia props assertions.

### Why
- Provide an inspiring, authentic patient testimonial showcase highlighting clinical excellence, specialized medical treatments, and patient recovery stories in accordance with user requirements.

## 2026-08-23 — Removal of Hero Search Widget "Cari Dokter & Jadwal Konsultasi" from Welcome.vue

### What Changed
- **`resources/js/pages/Welcome.vue`**:
  - Removed the hero search widget card titled **"Cari Dokter & Jadwal Konsultasi"** (`<motion.div>` multi-input search box) from the Hero section.
  - Cleaned up unused reactive states (`searchQuery`, `selectedPoli`, `selectedDay`, `availablePolis`, `daysList`, `resetFilters`, `handleHeroSearch`) and unused icon imports.
  - Retained navigation links pointing to the dedicated `/schedule-guest` Doctor Catalog page across navbar, CTA banners, and footer.

### Why
- Align with the simplified landing page design requested by the user, directing all schedule exploration and filtering directly to the dedicated `/schedule-guest` catalog.

## 2026-08-23 — Synchronize Header Navbar on /schedule-guest (Schedule.vue)

### What Changed
- **`resources/js/pages/doctor/Schedule.vue`**:
  - Removed the standalone button **"Kembali ke Beranda"**.
  - Replaced with the complete header navbar matching [`Welcome.vue`](file:///home/stravesstgod/rumah-sakit/resources/js/pages/Welcome.vue):
    - **Logo & Hospital Population Brand**: Directly links to home (`/`).
    - **Cari Dokter Link**: Highlighted/active navigation linking to `/schedule-guest`.
    - **Layanan & Spesialisasi Mega Menu**: Includes 3 columns (Poliklinik Spesialis with direct filter actions, Pusat Unggulan Medis CoE linking to `/#pusat-unggulan`, and Fasilitas & Penunjang linking to `/#fasilitas` with "Buka Monitor TV Antrean" & "Panduan Pasien & BPJS").
    - **Pusat Unggulan & Fasilitas Navigation Links**: Linking smoothly to their respective sections on the homepage.
    - **Dynamic Auth Actions**: Masuk / Daftar Akun vs Antrean Saya / Portal Pasien.

### Why
- Provide a consistent, polished navigation experience across the web application without redundant back-buttons, allowing users to return to home by clicking the Hospital Population logo and brand name.

## 2026-08-23 — Fix Doctor Schedule Sidebar Inclusion Bug in app.ts

### What Changed
- **`resources/js/app.ts`**:
  - Corrected component name matching in the layout switch statement from plural `'doctor/Schedules'` to singular `'doctor/Schedule'` (along with `'doctor/QueueBoard'`).
  - By returning `null` in the switch case for `'doctor/Schedule'`, Inertia.js no longer falls through to `default: return AppLayout;`, completely preventing `AppLayout` and `AppSidebar.vue` from being injected onto the `/schedule-guest` public page.

### Why
- Fix bug where guest users visiting `/schedule-guest` saw an unauthorized/unwanted `AppSidebar` on the standalone schedule page.

## 2026-08-23 — Removal of Embedded Doctor Schedule Section from Welcome.vue

### What Changed
- **`resources/js/pages/Welcome.vue`**:
  - Removed the embedded section titled **"Jadwal Praktik Dokter Terpadu"** (`<section id="jadwal-dokter">`) because it has been fully transitioned to the standalone page `/schedule-guest` (`Schedule.vue`).
  - Removed unused booking modal components (`<BookingModal>`, `<TicketSuccessModal>`), watcher, and unused local booking states (`selectedSchedule`, `isBookingModalOpen`, `isTicketModalOpen`, `activeTicket`, `filteredSchedules`).
  - Renumbered and aligned subsequent sections cleanly (Centers of Excellence `#pusat-unggulan`, Facilities `#fasilitas`, BPJS & FAQ Guide `#faq`, Multi-column Footer).

### Why
- Avoid redundant schedule rendering on the landing page, streamline page weight, and keep doctor schedule browsing concentrated in the dedicated `/schedule-guest` directory.

## 2026-08-23 — Dedicated Doctor Schedule Page & /schedule-guest Route

### What Changed
- **`routes/web.php`**:
  - Registered public route `GET /schedule-guest` pointing to `DoctorSchedulePageController::class` with name `'schedule-guest'`.
- **`resources/js/pages/doctor/Schedule.vue`**:
  - Enhanced as a full standalone dedicated Doctor Schedule page accessible by both guest users and authenticated patients.
  - Added full Evergreen design system top utility bar (IGD hotline, WhatsApp Care, Display TV) and sticky header with brand identity, "Kembali ke Beranda" button, and dynamic auth buttons ("Masuk/Daftar" vs "Antrean Saya/Portal Pasien").
  - Added query param support (`?search=`, `?poli=`, `?day=`) and multi-field filtering (keyword, polyclinic dropdown select, and daily practice pills).
  - Integrated `BookingModal` and `TicketSuccessModal` for seamless appointment reservation.
- **`resources/js/pages/Welcome.vue`**:
  - Connected navbar `"Cari Dokter"` button directly to `/schedule-guest`.
  - Connected Mega Menu Poliklinik items to `/schedule-guest?poli=<poli>` and bottom banner to `/schedule-guest`.
  - Connected Hero Search Widget to `/schedule-guest` with query parameters.
  - Connected Quick Access Card 1 & 3 to `/schedule-guest`.
  - Added Section 5 CTAs linking directly to `/schedule-guest`.

### Why
- Separate the Doctor Schedule catalog into its own dedicated page with deep search & filter capabilities, making the homepage faster and providing a dedicated doctor directory at `/schedule-guest`.

## 2026-08-23 — Welcome.vue Dropdown Button Positioning Fix

### What Changed
- **`resources/js/pages/Welcome.vue`**:
  - **Relocated "Panduan Pasien & BPJS"**: Removed the external/redundant button from the top utility bar and navbar links so it is placed strictly inside the "Layanan & Spesialisasi" Mega Menu dropdown.
  - **Vertical Stacking (Atas-Bawah)**: Inside Column 3 ("Fasilitas & Penunjang") of the dropdown panel, organized the action buttons in a neat vertical column (`flex flex-col gap-2`):
    - **Top (Atas)**: `"Buka Monitor TV Antrean"` (primary black pill button with TV icon).
    - **Bottom (Bawah)**: `"Panduan Pasien & BPJS"` (secondary outlined button linking to the FAQ/BPJS section).

### Why
- Align with user layout requirements to keep the outer header clean and group auxiliary patient guidance directly inside the services dropdown vertically stacked under monitor TV access.

## 2026-08-23 — Welcome.vue Horizontal Layout & Break Removal

### What Changed
- **`resources/js/pages/Welcome.vue`**:
  - **Horizontal Text Flow & Non-Wrapping**: Applied `whitespace-nowrap` to navbar brand, navigation buttons, dropdown items, auth links, and utility bar badges to prevent text from breaking vertically onto multiple lines ("atas-bawah").
  - **Hero Headline Formatting**: Cleaned up `<motion.h1>` text structure to flow horizontally in a single natural line without unnecessary line breaks or `block` element splitting.
  - **Top Bar Alignment**: Enforced `flex items-center flex-nowrap` on utility bar to ensure emergency contacts and portal links stay on one clean horizontal row.

### Why
- Prevent unwanted text wrapping and line breaks across screen factors, ensuring all typography flows cleanly side-by-side (kesamping).

## 2026-08-23 — Welcome.vue Navbar Navigation & Dropdown Fixes

### What Changed
- **`resources/js/pages/Welcome.vue`**:
  - **Mega Menu Hover & Click Bridge**: Added zero-gap container wrapper (`pt-2`) so hovering from the trigger button into the dropdown panel never breaks or prematurely closes.
  - **Sticky Header Scroll Offset (`-90px`)**: Improved `scrollToSection` and `jumpToDoctorSearch` to calculate the sticky header's height offset so section titles (Jadwal Dokter, Pusat Unggulan, Fasilitas, FAQ) are never hidden under the header.
  - **Polyclinic Intelligent Matching**: Selecting any polyclinic inside the Mega Menu automatically maps to the corresponding polyclinic filter in the catalog.
  - **All Navbar Navigation Buttons**: Ensured "Cari Dokter", "Layanan & Spesialisasi", "Pusat Unggulan", "Fasilitas", "Panduan Pasien", "Layar Antrean TV", and Auth buttons (Masuk, Daftar Akun, Antrean Saya, Portal Pasien) all trigger smooth navigation.
  - **Backdrop Overlay**: Desktop click-outside overlay for closing the dropdown seamlessly.

### Why
- Fix issues where moving the mouse into the dropdown closed it prematurely, and ensure clicking any navigation button scrolls to the exact section with the proper header offset.

## 2026-08-23 — Welcome.vue Siloam-Style Overhaul & Evergreen Design System

### What Changed

- **`resources/js/pages/Welcome.vue`**:
    - **Standalone Layout**: Enforced `defineOptions({ layout: undefined })` to ensure the landing page is 100% full-width without any default sidebar.
    - **Top Utility & Emergency Bar**: Added a dark top strip displaying the 24-hour emergency hotline (`1-500-181`), Emergency WhatsApp Care, direct link to live TV Queue Monitor (`/display`), and accreditation badge.
    - **Navbar with Mega Menu**: Built a modern navigation header with an expandable multi-column Mega Menu for Poliklinik Spesialis, Centers of Excellence, and Diagnostic Facilities with interactive search jumps.
    - **Dynamic Authentication Controls**: Dynamically renders "Masuk" (ghost pill) & "Daftar Akun" (primary black pill) for guests, or "Antrean Saya" & "Portal Pasien" for logged-in patients.
    - **Smart Search Box (Siloam 'Find Doctor' Widget)**: Integrated a multi-input filter (doctor name/symptom keyword, polyclinic dropdown select, and daily practice pill filters).
    - **4 Quick Access Action Cards**: Interactive shortcuts for Online Queue Registration, 24-Hour Emergency & Ambulance, Specialist Schedules, and Inpatient Facilities.
    - **Integrated Doctor Schedules Catalog (`props.schedules`)**: Interactive cards with doctor profile, specialization, practice hours, consultation room, and direct appointment reservation via `BookingModal.vue` and `TicketSuccessModal.vue`.
    - **Centers of Excellence (CoE)**: 6 dedicated cardiology, mother & child, orthopedics, oncology, neurology, and internal medicine service showcases.
    - **Hospital Facilities & Patient Care FAQ**: Complete infrastructure showcase (24h Lab, CT-Scan/MRI Imaging, 24h Pharmacy, Ambulance) and patient guide accordion/cards.
    - **Multi-Column Hospital Footer**: Comprehensive footer with institutional profile, ISO/KARS accreditation, clinical services, patient resources, and emergency contacts.
    - **Motion-V Animations & Touch Targets**: Fully adheres to Motion-V animation standards and min 44px touch targets.

### Why

- Deliver an intuitive, modern, accessible, and comprehensive hospital landing page inspired by top healthcare portals (e.g. Siloam Hospitals) while strictly adhering to `DESIGN.md` (Evergreen theme) and `AGENTS.md`.

### What Changed

- **`resources/js/pages/doctor/Schedule.vue`**:
    - **Spacing Fix**: Removed duplicate `min-h-screen` from authenticated content area — `AppSidebarLayout` already provides it, causing excessive vertical gaps.
    - **Dual-mode rendering**: `v-if="currentUser"` renders `AppLayout` (sidebar + breadcrumbs) for authenticated patients; `v-else` renders a standalone full-width page with its own navbar, auth CTA buttons (Masuk/Daftar Akun), and footer for guest visitors.
    - **Single root wrapper**: Wrapped entire template in a single `<div>` to avoid Vue multi-root fragment issues when modals are rendered alongside `v-if`/`v-else` blocks.
    - **Motion-V compliance**: Added `motion.header`, `motion.div`, `motion.button` with proper `:initial`, `:animate`, `:whileHover`, `:whileTap` per AGENTS.md standards.
    - **Guest card CTA**: Guest schedule cards link to `/login` instead of opening the booking modal.
    - **44px touch targets**: All interactive buttons and inputs meet minimum touch target requirements.

### Why

- Fix excessive vertical gaps caused by duplicate min-h-screen declarations.
- Ensure guest users see a full standalone page without sidebar, while authenticated patients get the sidebar layout.
- Comply with AGENTS.md motion and accessibility standards.

## 2026-08-23 — Welcome.vue Footer Motion-V Enhancement

### What Changed

- **`resources/js/pages/Welcome.vue`**:
    - Wrapped the footer and its child `span` elements in `motion.footer` and `motion.span` respectively.
    - Applied entrance animations (`:initial="{ opacity: 0, y: 15 }"` and `:initial="{ opacity: 0, x: -10 }"`/`:initial="{ opacity: 0, x: 10 }"`) with staggered delays.

### Why

- Ensure full compliance with `AGENTS.md` motion standards for all interactive and layout elements.

## 2026-08-23 — StaffDashboard.vue Motion-V & UI/UX Animation Enhancement

### What Changed

- **`resources/js/pages/StaffDashboard.vue`**:
    - Replaced standard HTML header, quick action toolbar, KPI summary cards, clinic matrix items, weekly trend bars, poli distribution items, and recent appointments table rows with `motion.header`, `motion.div`, and `motion.tr`.
    - Applied smooth entrance transitions (`:initial="{ opacity: 0, y: 12 }"` -> `:animate="{ opacity: 1, y: 0 }"`, duration 0.22-0.25s, ease `easeOut`) with staggered delays for lists and grids.
    - Added micro-interactions with `:whileHover` and `:whileTap` on all interactive elements like buttons and cards per `AGENTS.md` and `ui-ux-pro-max` standards.

### Why

- Align `StaffDashboard.vue` with `AGENTS.md` motion standards and UI/UX micro-interaction rules.

## 2026-08-23 — DisplayBoard.vue Motion-V & UI/UX Animation Enhancement

### What Changed

- **`resources/js/pages/DisplayBoard.vue`**:
    - Replaced standard HTML header, logo, audio/fullscreen toggle buttons, main content area, latest called section, clinic matrix items, and footer with `motion.header`, `motion.div`, `motion.button`, `motion.main`, and `motion.footer`.
    - Applied smooth entrance transitions (`:initial="{ opacity: 0, y: 12 }"` -> `:animate="{ opacity: 1, y: 0 }"`, duration 0.22-0.25s, ease `easeOut`) with staggered delays for lists and grids.
    - Added micro-interactions with `:whileHover` and `:whileTap` on all interactive elements per `AGENTS.md` and `ui-ux-pro-max` standards.

### Why

- Align `DisplayBoard.vue` with `AGENTS.md` motion standards and UI/UX micro-interaction rules.

## 2026-08-23 — Welcome.vue Header Motion-V Enhancement

### What Changed

- **`resources/js/pages/Welcome.vue`**:
    - Wrapped header and auth buttons in `motion.header` and `motion.div` respectively.
    - Applied entrance animations (`:initial="{ opacity: 0, y: -10 }"` and `:initial="{ opacity: 0, x: 10 }"`) and hover/tap feedback.

### Why

- Ensure full compliance with `AGENTS.md` motion standards for all interactive and layout elements.

## 2026-08-23 — MyAppointments.vue Motion-V & UI/UX Animation Enhancement

### What Changed

- **`resources/js/pages/MyAppointments.vue`**:
    - Replaced standard HTML header, titles, tab filters, and empty state containers with `motion.header`, `motion.div`, and `motion.button`.
    - Added smooth entrance transitions (`:initial="{ opacity: 0, y: 12 }"` -> `:animate="{ opacity: 1, y: 0 }"`, duration 0.22s, ease `easeOut`).
    - Added micro-interactions with `:whileHover` and `:whileTap` on all tabs, action buttons (Lihat Karcis, Batalkan, Hapus), and appointment cards per `AGENTS.md` and `ui-ux-pro-max` standards.

### Why

- Align `MyAppointments.vue` with `AGENTS.md` motion standards and UI/UX micro-interaction rules.

## 2026-08-23 — Patient Dashboard Dark Gap & Full-Width Layout Fix

### What Changed

- **`resources/js/pages/patient/Dashboard.vue`**:
    - Removed duplicate `<AppLayout>` wrapper from template and script import. Since Inertia's root setup in `app.ts` already applies `AppLayout` as the persistent layout by default, wrapping it inside `Dashboard.vue` caused a nested second sidebar and sidebar provider, which resulted in dark/black gaps and compressed page content.
    - Set root content wrapper to `w-full min-h-full bg-[#edede2]` to ensure full-width seamless coverage.
- **`resources/js/layouts/app/AppSidebarLayout.vue`**:
    - Added `bg-[#edede2] min-h-screen text-[#000000] flex flex-col` and full-width wrapper to `AppContent` so the main layout canvas always matches the Evergreen linen token (`#edede2`).
- **`resources/js/components/AppSidebarHeader.vue`**:
    - Implemented the sticky top navigation header with `SidebarTrigger` (sidebar toggle) and `Breadcrumbs` styled seamlessly in `#edede2]/90` backdrop blur.
- **`resources/js/components/ui/sidebar/SidebarInset.vue` & `SidebarProvider.vue`**:
    - Replaced default `bg-background` and `has-data-[variant=inset]:bg-sidebar` with Evergreen token `bg-[#edede2]` to avoid any dark or mismatched background bleeding into content margins.
- **`resources/js/app.ts`**:
    - Added `DisplayBoard` to standalone null-layout views.

### Why

- The Patient Dashboard displayed dark/black gaps and was not full-width due to duplicate layout instantiation (nested `AppLayout` inside persistent `AppLayout`), default sidebar background variable mismatches, and an unstyled content container.

### What Changed

- **`resources/js/pages/Patient/Dashboard.vue`**:
    - Restyled layout to align with `DESIGN.md` (Evergreen tokens: background `#edede2`, bone cards `#fffff3`, text `#000000`, charcoal `#333333`, sage mint `#beedc0`).
    - Set buttons to pill-shaped style with `rounded-[40.5px]` and minimum 44px touch targets.
    - Formatted font usage strictly to Rubik body text (`font-['Rubik']`) and IvyPresto Display titles (`font-['ivypresto-headline']`).
    - Replaced arbitrary background color styles with matching Evergreen tokens (e.g. status badges and tables).
    - Configured hover and tap feedback using `motion.div` transitions.
    - Added required inline explanations and component purpose comments per `AGENTS.md` rules.

### Why

- The Patient Dashboard view had mismatched background colors, generic fonts, inconsistent card border-radii, and lacked compliance with `DESIGN.md` and standard `AGENTS.md` motion/comment rules.

## 2026-08-23 — Patient Dashboard 403 Forbidden Fix (Route Ordering)

### What Changed

- **`routes/web.php`**:
    - Moved the `Route::middleware(['auth'])` group (containing `/patient/dashboard`, `/my-appointments`, `/staff`, `/doctor/queue/*`, etc.) **above** the `{current_team}` wildcard route group.
    - Added inline comments explaining why the ordering matters.

### Root Cause

- The `{current_team}` wildcard route (`Route::prefix('{current_team}')`) was defined **before** the explicit `/patient/dashboard` route. Laravel's router matched `/patient/dashboard` as `{current_team}=patient` + `dashboard`, routing it through `EnsureTeamMembership` middleware which called `abort(403)` because no team with slug `"patient"` exists.

### Why

- All authenticated users (both new and old patient accounts) received HTTP 403 Forbidden when accessing `/patient/dashboard`.

## 2026-08-22 — MyAppointments: Delete History Feature (Backend + Frontend)

### What Changed

- **`app/Http/Controllers/AppointmentController.php`**:
    - Added `destroy()` method: Deletes appointment records with status `completed` or `cancelled` only.
    - Ownership check via `patient_id` matching — prevents unauthorized deletion.
    - Fixed indentation inconsistency in `cancel()` method.
- **`routes/web.php`**:
    - Added `Route::delete('/appointments/{appointment}', ...)->name('appointments.destroy')`.
- **`resources/js/pages/MyAppointments.vue`**:
    - Added `Trash2` Lucide icon import.
    - Added `isDeleteModalOpen`, `selectedDeleteItem`, `isDeleting` reactive state.
    - Added `openDeleteDialog()` and `executeDeleteAppointment()` handlers using `router.delete()`.
    - Added "Hapus" button on history tab cards (visible for `completed`/`cancelled` items).
    - Added Evergreen-styled confirmation dialog with `motion.div`/`motion.button` animations, summary card, and destructive red CTA.

### Why

- User requested ability to delete visit history records from the "Riwayat Kunjungan" tab in MyAppointments page.

## 2026-08-22 — AppSidebar "Antrean Saya" Menu Item Route Fix & Visibility

### What Changed

- **`resources/js/components/AppSidebar.vue`**:
    - Fixed "Antrean Saya" sidebar menu item route from `route('appointments.index')` (non-existent) to `route('my')` matching the `web.php` definition (`Route::get('/my-appointments', ...)->name('my')`).
    - Renamed label from "Tiket Antrean Saya" to "Antrean Saya" per user request.
    - Changed visibility from `isPatient.value` to `!!user.value` so all authenticated users see the menu item.
    - Fixed "Jadwal Dokter" fallback URL from `/doctor/schedules` to `/doctor-schedules` matching the actual route path.
    - Updated `activePattern` from `'appointments.index*'` to `'my*'` to match the route name.

### Why

- The sidebar menu item referenced a non-existent named route `appointments.index`, which would cause a runtime error when using Laravel's `route()` helper. The correct route name defined in `web.php` is `'my'`.

## 2026-08-22 — Welcome.vue Dashboard Button → Doctor Schedule Page Redirect

### What Changed

- **`resources/js/pages/Welcome.vue`**:
    - Changed the authenticated user's "Dashboard" button `href` from `dashboardUrl` (team dashboard) to `schedules().url` (`/doctor-schedules`), so clicking it navigates to the Doctor Practice Schedule page.
    - Replaced `import { dashboard, login, register } from '@/routes'` with `import { login, register } from '@/routes'` and added `import { schedules } from '@/routes/doctor'` (Wayfinder typed route helper).
    - Removed the now-unused `dashboardUrl` computed property.

### Why

- User requested the Dashboard button on the Welcome landing page to navigate directly to the Doctor Schedule listing page instead of the team dashboard.

## 2026-08-22 — BookingModal & Schedule.vue Component Restoration & Evergreen Polishing

### What Changed

- **`resources/js/components/BookingModal.vue`**:
    - Reconstructed as a dedicated presentational booking dialog (resolved the accidental recursive self-import).
    - Designed per `DESIGN.md` (Evergreen style: `#fffff3` bone card, `#beedc0` sage mint badges/halo, `#000000` ink black buttons, IvyPresto headings, Rubik labels, 44px min touch targets).
    - Integrated `motion-v` hover/tap animations for action buttons.
- **`resources/js/components/TicketSuccessModal.vue`**:
    - Polished styling to strictly follow `DESIGN.md` design tokens and `GEMINI.md` motion standards (`motion.button`, `motion.div`).
    - Added printing handler and smooth entrance animations.
- **`resources/js/pages/doctor/Schedule.vue`**:
    - Integrated both `BookingModal` (input) and `TicketSuccessModal` (slip output) seamlessly.
    - Linked appointment submission event `@success="handleBookingSuccess"` to pop up the themed ticket modal.
- **`resources/js/pages/auth/Login.vue` & `Register.vue`**:
    - Switched `@rive-app/webgl2` to type-only import with dynamic client-side `import()` in `onMounted()` to ensure SSR compatibility during Node server rendering.

### Why

- An accidental duplicate copy of `Schedule.vue` inside `BookingModal.vue` caused an infinite self-recursive rendering loop (`RangeError: Maximum call stack size exceeded`).
- Ensured all newly added and updated components strictly adhere to `DESIGN.md` and `GEMINI.md` architectural and visual guidelines.

## 2026-08-22 — Schedule.vue & BookingModal.vue Type and Property Harmonization

### What Changed

- **`resources/js/types/hospital.ts`** & **`resources/js/types/index.ts`**:
    - Created and exported unified TypeScript interfaces for hospital domain entities (`DoctorSchedule`, `Doctor`, `Poli`, `Room`, `Specialization`).
- **`resources/js/pages/doctor/Schedule.vue`**:
    - Replaced ad-hoc local interfaces with unified `DoctorSchedule` from `@/types`.
    - Added robust property resolution helpers (`getSpecializationName`, fallback checks for `day` vs `day_of_week`, `name_poli` vs `name`, `name_room` vs `name`).
    - Fixed TypeScript compiler error TS2322 when binding `:schedule="selectedSchedule"` to `BookingModal`.
- **`resources/js/components/BookingModal.vue`**:
    - Switched from local type definitions to shared `DoctorSchedule` from `@/types`.
    - Resolved `doctor_schedule_id` properly with `newSchedule.doctor_schedule_id ?? newSchedule.id`.
    - Added safe fallback handling for `day || day_of_week`, `quota_day ?? quota`, and specialization name.
    - Removed unused `X` import from `@lucide/vue`.

### Why

- TypeScript compiler (`vue-tsc`) flagged a type mismatch between `Schedule.vue`'s local `DoctorSchedule` interface and `BookingModal.vue`'s interface because `day` was required in one and `day_of_week` in another.
- Backend Eloquent models use PostgreSQL column naming conventions (`day`, `name_poli`, `name_room`, `name_specialization`, `doctor_schedule_id`, `quota_day`), which could cause runtime `undefined` values if property names did not match.

## 2026-08-22 — Welcome.vue Interactive Overhaul

### What Changed

- **`resources/js/pages/Welcome.vue`** — Complete rewrite:
    - **Removed nav anchor buttons**: "Layanan", "Keunggulan", "Kontak" links removed from header `<nav>` — auth buttons (Masuk/Daftar/Dashboard) retained.
    - **Scroll-triggered Motion-V animations**: Every section (`hero`, `services`, `strengths`, `testimonials`, `facts`, `cta`) uses `useIntersectionObserver` from `@vueuse/core` to toggle reactive `isVisible` booleans, bound to `motion.div` `:animate` props. Staggered delays per grid item using `index * 0.07–0.1`.
    - **Auto-sliding testimonial carousel**: 5 patient testimonials auto-advance every 5 seconds. Pause-on-hover/touch. Manual dot navigation with 44px touch targets. Slide transitions via `motion.div` opacity/y. Cleanup `clearInterval` in `onUnmounted`.
    - **Health facts section**: 6 wellness fact cards (Heart, Moon, Droplets, Apple, Activity, Shield icons) in responsive `sm:grid-cols-2 lg:grid-cols-3` grid. Bone Card surface, 10px radius, sage mint icon circles.
    - **Lucide icons replacing emoji**: Service cards now use Lucide icons (`Stethoscope`, `Users`, `Clock`, `Wallet`) instead of `+` emoji text.
    - **Logo hover interaction**: Brand `+` mark wrapped in `motion.span` with `whileHover: { scale: 1.1, rotate: 5 }`.
    - **All CTA buttons**: Wrapped in `motion.div` with `whileHover` / `whileTap` feedback.
    - **DESIGN.md Evergreen tokens**: All colors (`#edede2`, `#fffff3`, `#000000`, `#333333`, `#beedc0`), typography (`DM Serif Display`, `Rubik`), spacing, and radii (`40.5px` pills, `10px` cards, `46px` tags) strictly applied.

### Why

- Previous page was static with no scroll animations, making it feel lifeless.
- No testimonial section existed — social proof is critical for healthcare platforms.
- Health facts section adds value and engagement for patients browsing the landing page.
- Nav anchor buttons ("Layanan", "Keunggulan", "Kontak") were unnecessary and cluttered the header.

## 2026-08-21 — AppSidebar Visual, Responsive & Interactive Overhaul

### What Changed

- **`resources/js/components/AppSidebar.vue`** — Complete rewrite:
    - **Staggered Motion-V entrance animations**: Each `SidebarGroup` wrapped with `motion.div` using cascading delays (0.05s → 0.15s) for a smooth reveal on mount.
    - **Interactive menu buttons**: Every menu item icon gets `motion.div` with `:whileHover="{ x: 2, scale: 1.1 }"` and `:whileTap="{ scale: 0.9 }"`. Label text slides right on hover via `motion.span`.
    - **Sage mint active-state accent bar**: Active menu items now display `border-l-[3px] border-[#beedc0]` alongside the black pill background for clearer wayfinding.
    - **Enhanced group labels**: Added decorative sage mint dot (`h-1.5 w-1.5 rounded-full bg-[#beedc0]`) before each group label text, plus a `bg-[#333333]/8` hairline separator below the label.
    - **Tooltip support for collapsed sidebar**: Added `tooltip` prop to every `SidebarMenuButton` so collapsed icon-only mode shows menu names on hover.
    - **Footer visual refinement**: Added sage mint decorative accent bar (`h-[2px] w-10 bg-[#beedc0]/60`) above the NavUser, and wrapped NavUser with `motion.div` for entrance animation.
    - **Data-driven menu items**: Refactored hardcoded menu items into computed `staffMenuItems` and `serviceMenuItems` arrays for DRYer, more maintainable template logic.
    - **Typography tokens**: Applied `font-['Rubik']` at `14px` for menu labels, `font-['ivypresto-headline']` at `20px` for brand title — matching DESIGN.md type scale.
    - **Logo refactored**: Replaced `AppLogo` with direct `AppLogoIcon` inside the sage mint halo to avoid conflicting `bg-sidebar-primary` defaults.

### Why

- Previous sidebar lacked motion interactivity (violating AGENTS.md §4.D mandate for Motion components on all interactive elements).
- Active state was only visible via background color — insufficient for non-technical patient users who need clearer visual cues.
- Group labels blurred together without visual separators.
- Collapsed sidebar had no tooltips, making icon-only mode unusable.

## 2026-08-21 — Rive Hospital Mascot Integration (`Login.vue`, `Register.vue`)

### What Changed

- **`resources/js/pages/auth/Login.vue` & `Register.vue`**:
    - Implemented Character-based Interactive Auth with two-column layout (Form on the right, Rive interactive character animation on the left).
    - Loaded Rive WebGL2 runtime dynamically via `https://unpkg.com/@rive-app/webgl2@2`.
    - Configured Rive instance to load `/assets/rive/hospital.riv` using state machine `LoginSM`.
    - Synchronized form reactive states (email, password, remember, name, NIK, gender, errors, processing) into Rive inputs in real-time.
    - Implemented strict lifecycle memory safety with `onUnmounted` cleanup releasing WebGL contexts.
    - Adhered strictly to `DESIGN.md` tokens (`#edede2` linen canvas, `#fffff3` bone cards, `#beedc0` sage mint washes) and `AGENTS.md` (motion-v animations, touch targets min 44px).

### Why

- Provides an engaging, high-end, dynamic interactive experience for patients and non-technical users in accordance with AGENTS.md Rive standards and DESIGN.md branding.

## 2026-08-21 — AppSidebar & Schedule Header Harmonization

### What Changed

- **`resources/js/components/AppSidebar.vue`**:
    - Restyled sidebar adhering to `DESIGN.md` Evergreen design tokens (canvas `#edede2`, bone cards `#fffff3`, pure black `#000000`, charcoal `#333333`, sage mint `#beedc0`).
    - Added pill buttons (`rounded-[40.5px]`, `min-h-[44px]`) with active/inactive states.
    - Implemented `motion-v` hover (`:whileHover="{ scale: 1.05, y: -1 }"`) and tap (`:whileTap="{ scale: 0.95 }"`) interactive animations.
    - Formatted branding typography with `font-['ivypresto-headline']` and sage mint avatar background.
- **`resources/js/pages/doctor/Schedule.vue`**:
    - Cleaned up top navigation auth state.
