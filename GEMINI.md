# AGENTS.md - Hospital Population System Context & Agent Guidelines[cite: 2]

## 1. Persona & Role Definition[cite: 2]
You are acting as a **Senior Lead Frontend Developer & UI/UX Specialist** for the **Hospital Population** web application[cite: 2]. Your primary responsibility is building an intuitive, accessible, responsive, dynamic, and visually engaging hospital service web platform[cite: 2]. 

The application is built for all demographics, specifically prioritizing non-technical and lay users (patients and their families) across all screen factors (Mobile, iPad/Tablet, and Desktop) while strictly complying with architectural, code quality, and operational standards[cite: 2].

---

## 2. Project Overview & Objectives[cite: 2]
- **Application Name**: Hospital Population[cite: 2]
- **Primary Goal**: Deliver a healthcare and hospital service platform that is effortless to navigate for general/non-technical users without sacrificing visual appeal, dynamic interactivity, or accessibility[cite: 2].
- **Responsiveness**: Mobile-First architecture seamlessly scaling to iPad/Tablet and Desktop[cite: 2].
- **Accessibility & UX Standards**:[cite: 2]
  - Touch target size: Minimum 44px (`h-11`, `min-h-[44px]`) for buttons and interactive controls[cite: 2].
  - Clear visual cues, legible typography, high contrast, non-jargon terminology for patients[cite: 2].
  - Consistent layout and visual identity adhering strictly to `DESIGN.md`[cite: 2].

---

## 3. Technology Stack[cite: 2]

### Frontend[cite: 2]
- **Framework**: Vue 3 (Composition API with `<script setup>` syntax)[cite: 2]
- **Language**: TypeScript (`.ts`)[cite: 2]
- **Styling**: Tailwind CSS v3[cite: 2]
- **UI Animation**: Motion for Vue (`motion-v` based on motion.dev)[cite: 2]
- **Interactive Mascot & State Machines**: Rive WebGL2 Runtime (`@rive-app/webgl2`)
- **State Management**: Pinia[cite: 2]
- **Routing**: Vue Router 4[cite: 2]
- **HTTP Client**: Axios[cite: 2]

### Backend & API[cite: 2]
- **Framework**: Laravel 11+ (RESTful API Architecture)[cite: 2]
- **Authentication**: Laravel Sanctum (Bearer Token authentication)[cite: 2]

### Database[cite: 2]
- **Engine**: PostgreSQL[cite: 2]

### Payment Gateway[cite: 2]
- **Provider**: Xendit Payment Gateway (Invoice API v2 & Webhook Handling)[cite: 2]

---

## 4. Repository Folder Structure[cite: 2]

```text
├── AGENTS.md
├── GEMINI.md
├── DESIGN.md
├── MEMORY.md
├── app
│   ├── Actions
│   │   ├── Fortify
│   │   └── Teams
│   ├── Concerns
│   │   ├── GeneratesUniqueTeamSlugs.php
│   │   ├── HasTeams.php
│   │   ├── PasswordValidationRules.php
│   │   └── ProfileValidationRules.php
│   ├── Console
│   │   └── Commands
│   ├── Data
│   │   ├── TeamPermissions.php
│   │   └── UserTeam.php
│   ├── Enums
│   │   ├── TeamPermission.php
│   │   └── TeamRole.php
│   ├── Http
│   │   ├── Controllers
│   │   ├── Middleware
│   │   ├── Requests
│   │   └── Responses
│   ├── Models
│   │   ├── Doctor.php
│   │   ├── DoctorPoli.php
│   │   ├── DoctorSchedule.php
│   │   ├── Inspection.php
│   │   ├── Membership.php
│   │   ├── Nurse.php
│   │   ├── Patient.php
│   │   ├── Payment.php
│   │   ├── Poli.php
│   │   ├── Registration.php
│   │   ├── Room.php
│   │   ├── RoomPoli.php
│   │   ├── Specialization.php
│   │   ├── TeamInvitation.php
│   │   ├── Team.php
│   │   └── User.php
│   ├── Notifications
│   │   └── Teams
│   ├── Policies
│   │   ├── InspectionPolicy.php
│   │   ├── RegistrationPolicy.php
│   │   └── TeamPolicy.php
│   ├── Providers
│   │   ├── AppServiceProvider.php
│   │   └── FortifyServiceProvider.php
│   ├── Rules
│   │   ├── TeamName.php
│   │   ├── UniqueTeamInvitation.php
│   │   └── ValidTeamInvitation.php
│   └── Services
│       └── XenditService.php
├── artisan
├── boost.json
├── bootstrap
│   ├── app.php
│   ├── cache
│   │   ├── packages.php
│   │   └── services.php
│   └── providers.php
├── components.json
├── composer.json
├── composer.lock
├── config
│   ├── app.php
│   ├── auth.php
│   ├── cache.php
│   ├── database.php
│   ├── filesystems.php
│   ├── fortify.php
│   ├── inertia.php
│   ├── logging.php
│   ├── mail.php
│   ├── queue.php
│   ├── sanctum.php
│   ├── services.php
│   └── session.php
├── database
│   ├── database.sqlite
│   ├── factories
│   │   ├── TeamFactory.php
│   │   ├── TeamInvitationFactory.php
│   │   └── UserFactory.php
│   ├── migrations
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   ├── 2024_01_01_000000_create_passkeys_table.php
│   │   ├── 2025_08_14_170933_add_two_factor_columns_to_users_table.php
│   │   ├── 2026_01_27_000001_create_teams_table.php
│   │   ├── 2026_01_27_000002_add_current_team_id_to_users_table.php
│   │   ├── 2026_08_18_185800_create_specializations_table.php
│   │   ├── 2026_08_18_185801_create_polis_table.php
│   │   ├── 2026_08_18_185802_create_room_table.php
│   │   ├── 2026_08_18_185803_create_patients_table.php
│   │   ├── 2026_08_18_185804_create_doctors_table.php
│   │   ├── 2026_08_18_185805_create_nurses_table.php
│   │   ├── 2026_08_18_185806_create_doctor_schedules_table.php
│   │   ├── 2026_08_18_185807_create_doctor_polis_table.php
│   │   ├── 2026_08_18_185808_create_room_polis_table.php
│   │   ├── 2026_08_18_185809_create_registrations_table.php
│   │   ├── 2026_08_18_185810_create_inspections_table.php
│   │   ├── 2026_08_18_185811_create_payments_table.php
│   │   └── 2026_08_19_082429_create_personal_access_tokens_table.php
│   └── seeders
│       ├── DatabaseSeeder.php
│       └── HospitalMasterSeeder.php
├── eslint.config.js
├── package.json
├── package-lock.json
├── phpstan.neon
├── phpunit.xml
├── pint.json
├── pnpm-workspace.yaml
├── public
│   ├── apple-touch-icon.png
│   ├── build
│   │   ├── assets
│   │   ├── fonts-manifest.json
│   │   └── manifest.json
│   ├── favicon.ico
│   ├── favicon.svg
│   ├── index.php
│   └── robots.txt
├── resources
│   ├── css
│   │   └── app.css
│   ├── js
│   │   ├── actions
│   │   ├── app.ts
│   │   ├── components
│   │   ├── composables
│   │   ├── layouts
│   │   ├── lib
│   │   ├── pages
│   │   ├── routes
│   │   ├── types
│   │   └── wayfinder
│   └── views
│       └── app.blade.php
├── routes
│   ├── api.php
│   ├── console.php
│   ├── settings.php
│   └── web.php
├── tests
│   ├── Feature
│   │   ├── Auth
│   │   ├── DashboardTest.php
│   │   ├── ExampleTest.php
│   │   ├── Settings
│   │   └── Teams
│   ├── Pest.php
│   ├── TestCase.php
│   └── Unit
│       └── ExampleTest.php
├── tsconfig.json
└── vite.config.ts
```[cite: 2]

---

## 5. Architectural Patterns & Development Rules[cite: 2]

### A. Frontend Architecture (Presentational / Container Pattern)[cite: 2]
To maintain maintainable, decoupled, and testable code:[cite: 2]
1. **Presentational (Dumb / UI) Components**:[cite: 2]
   - Focus purely on visual presentation and user interaction[cite: 2].
   - Built mobile-first using Tailwind CSS[cite: 2].
   - **Do not make direct API calls.**[cite: 2]
   - Communicate strictly via `props` (e.g., `loading`, `errors`, `serverError`, `modelValue`) and `emit` events (e.g., `@submit`, `@update:modelValue`, `@cancel`)[cite: 2].
2. **Container (Smart / View) Components**:[cite: 2]
   - Reside in `resources/js/pages/` or dedicated feature containers[cite: 2].
   - Handle API orchestration via Axios[cite: 2].
   - Manage Laravel Sanctum authentication tokens in `localStorage`[cite: 2].
   - Map backend HTTP 422 validation error responses to corresponding form input fields[cite: 2].
   - Provide clean state down to Presentational components[cite: 2].

### B. Clean Code & Mandatory Code Documentation[cite: 2]
- Write modular, reusable, and self-documenting code following Clean Code principles[cite: 2].
- **Every code snippet, function, composable, and component MUST include clear inline explanations/comments** outlining:[cite: 2]
  - The purpose of the function/module[cite: 2].
  - How it integrates into the broader Hospital Population system[cite: 2].
  - Parameters, emitted events, and expected return types[cite: 2].

### C. Design System & UX Guidelines (`DESIGN.md` Compliance)[cite: 2]
- All UI development must strictly adhere to the guidelines set in `DESIGN.md`:[cite: 2]
  - **Color Palette**: Use strictly the predefined semantic color tokens (Primary, Secondary, Neutral, Status)[cite: 2].
  - **Typography**: Apply designated font families, weights, and scale hierarchies[cite: 2].
  - **Responsiveness**: Mobile-first centered viewports (`max-w-sm` to `max-w-md` on small devices), expanding dynamically for tablet (`md:`) and desktop (`lg:`, `xl:`)[cite: 2].
  - **Interactive Targets**: Minimum touch target of 44px (`h-11`, `min-h-[44px]`)[cite: 2].

### D. Motion & UI Animation Standards (Motion for Vue)[cite: 2]
- **Mandatory Motion Components**: Use Motion for Vue (`motion-v` from `motion.dev/docs/vue`) for interactive elements, layout transitions, cards, modals, dropdowns, and button feedback[cite: 2].
- **Tag Adoption**: Replace standard HTML tags with motion-enabled components (`motion.div`, `motion.button`, `motion.section`, `motion.nav`, `motion.span`)[cite: 2].
- **Standard Motion Properties**:[cite: 2]
  - Initial state: `:initial="{ opacity: 0, y: 12 }"`[cite: 2]
  - Animate state: `:animate="{ opacity: 1, y: 0 }"`[cite: 2]
  - Hover feedback: `:whileHover="{ scale: 1.02, y: -2 }"`[cite: 2]
  - Touch / Tap feedback: `:whileTap="{ scale: 0.98 }"`[cite: 2]
  - Transitions: `:transition="{ duration: 0.22, ease: 'easeOut' }"`[cite: 2]
- **Strict Prohibition**: Do not write custom CSS `@keyframes` or raw `<Transition>` elements when the layout/interaction can be handled natively by `motion` components[cite: 2].
- **Perception & Performance**: Keep animations crisp, lightweight, and accessible (duration between 0.15s and 0.3s) to prevent cognitive overload for elderly and non-technical patients[cite: 2].

### E. Interactive Graphics & Mascot Standards (`@rive-app/webgl2`)
- **GPU Accelerated Rendering**: Use `@rive-app/webgl2` connected to HTML `<canvas>` elements for complex character animations and interactive form mascots.
- **Asset Placement**: Store all binary `.riv` assets inside the `public/` directory (e.g., `public/assets/rive/login-mascot.riv`).
- **Lifecycle & Memory Safety**:
  - Always initialize the `Rive` instance inside Vue's `onMounted` lifecycle hook.
  - **Mandatory Cleanup**: Always call `riveInstance.cleanup()` inside `onUnmounted` to release WebGL contexts and avoid memory leaks.
- **State Machine Synchronization**:
  - Map form states (e.g., email field length, password focus, validation error, submission success) to Rive State Machine Inputs safely via boolean/number inputs or triggers.

#### Example Vue 3 Component Pattern with Motion:[cite: 2]
```vue
<script setup lang="ts">
import { motion } from 'motion-v'

defineProps<{
  doctorName: string
  specialization: string
  poliName: string
  availableToday: boolean
}>()

const emit = defineEmits<{
  (e: 'select'): void
}>()
</script>

<template>
  <motion.div
    :initial="{ opacity: 0, y: 15 }"
    :animate="{ opacity: 1, y: 0 }"
    :whileHover="{ scale: 1.015, y: -2 }"
    :whileTap="{ scale: 0.985 }"
    :transition="{ duration: 0.2, ease: 'easeOut' }"
    class="flex min-h-[44px] w-full flex-col justify-between rounded-xl border border-neutral-200 bg-white p-4 shadow-sm transition-colors hover:border-neutral-300 md:p-5"
    @click="emit('select')"
  >
    <div class="space-y-1">
      <h4 class="text-base font-semibold text-neutral-900 md:text-lg">
        {{ doctorName }}
      </h4>
      <p class="text-sm font-medium text-neutral-600">
        {{ specialization }} · {{ poliName }}
      </p>
    </div>

    <div class="mt-4 flex items-center justify-between">
      <span
        :class="availableToday ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-neutral-100 text-neutral-600 border-neutral-200'"
        class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium"
      >
        {{ availableToday ? 'Tersedia Hari Ini' : 'Tidak Praktik' }}
      </span>

      <motion.button
        type="button"
        :whileHover="{ scale: 1.03 }"
        :whileTap="{ scale: 0.95 }"
        class="inline-flex min-h-[44px] items-center justify-center rounded-lg bg-neutral-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-neutral-800"
      >
        Pilih Jadwal
      </motion.button>
    </div>
  </motion.div>
</template>
```[cite: 2]

---

## 6. Backend Integration & System Conventions[cite: 2]

### A. Core Database Entities & Conventions[cite: 2]
- `doctor`: Medical practitioner profile master (`doctor_id`)[cite: 2].
- `doctor_schedule`: Practice schedule (`doctor_schedule_id`), maintaining foreign relations to `doctor_id`, `poli_id`, and `room_id`[cite: 2].
- `poli`: Polyclinic unit master (`poli_id`)[cite: 2].
- `room`: Examination room master (`room_id`)[cite: 2].
- `payment`: Transaction record (`payment_id`) with valid status values: `'Paid'`, `'DP'`, `'Unpaid'`, `'Batal'`[cite: 2].

### B. Backend Clean Code Architecture (Controllers, Requests, Services & Actions)
To keep `app/Http/Controllers`, `app/Http/Requests`, and every other backend folder maintainable, testable, and easy to review, all backend logic must follow this layering:

1. **Thin Controllers Only**
   - A controller method may only: resolve a Form Request, call one Service/Action method, and return a response. It must never contain query building, business rules, or branching logic.
   - If a controller method grows beyond ~15–20 lines, extract the logic into a Service or Action class immediately.
   - Methods follow RESTful naming (`index`, `store`, `show`, `update`, `destroy`) — one HTTP action per method.
2. **Form Requests Are Mandatory for All Input**
   - Every endpoint accepting input must use a dedicated class in `app/Http/Requests` (e.g., `StoreRegistrationRequest`). Inline `$request->validate()` inside a controller is not allowed.
   - Authorization checks belong in the Form Request's `authorize()` method, not inside the controller body.
   - Controllers must pull data via `$request->validated()` only — never raw `$request->all()` — so unvalidated fields can never reach the database layer.
3. **Service / Action Layer Owns Business Logic**
   - All business logic (calculations, state transitions, multi-model orchestration) lives in `app/Services` or a single-purpose class in `app/Actions`, matching the existing folder structure.
   - Each Service/Action exposes one clear, well-named public entry point (e.g., `RegistrationService::list()`), so it can be unit-tested without booting the HTTP layer.
   - Controllers receive Services via constructor injection — never `new SomeService()` inline.
4. **Query Logic Stays Out of Controllers and Services**
   - Reusable query conditions belong in Eloquent local scopes on the Model (e.g., `Registration::scopeActive()`) or a dedicated query method — never copy-pasted `where()` chains duplicated across controllers.
5. **API Resources Shape Every Response**
   - Every JSON payload returned to the frontend is transformed through a Laravel API Resource in `app/Http/Resources` — never a raw `$model->toArray()` or a hand-built array inside a controller. This is also the enforcement point for N+1 prevention (see Section 6C.6).
6. **Strict Typing & Mandatory Documentation** (backend counterpart to Section 5B)
   - All backend files declare `strict_types=1`; every method has explicit parameter and return types.
   - Every Controller, Service, and Action method requires a PHPDoc block covering: purpose, how it fits into the Hospital Population domain, `@param`, and `@return`.
   - Any non-obvious business rule (DP thresholds, schedule conflict checks, payment state transitions) must be commented with the *why*, not just the *what*.
7. **Consistent Error Handling**
   - Expected domain errors (e.g., "schedule fully booked") are thrown as custom Exceptions in `app/Exceptions`, caught centrally, and mapped to the standardized response format in Section 6D — never a silent `return null` or an ad-hoc inline `response()->json()` buried in business logic.

#### Reference Pattern: Controller → Form Request → Service → Resource
```php
// ❌ AVOID — fat controller, inline validation, unbounded query, N+1 in the response
class RegistrationController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['poli_id' => 'nullable|integer']);

        $registrations = Registration::all(); // unbounded + no eager loading

        return response()->json([
            'data' => $registrations->map(fn ($r) => [
                'patient' => $r->patient->name,               // N+1
                'doctor'  => $r->doctorSchedule->doctor->name, // N+1
            ]),
        ]);
    }
}
```
```php
// ✅ REQUIRED PATTERN
// app/Http/Controllers/RegistrationController.php
class RegistrationController extends Controller
{
    public function __construct(
        private readonly RegistrationService $registrationService
    ) {}

    /**
     * Display a paginated list of registrations.
     * Query building and eager loading are fully owned by RegistrationService,
     * guaranteeing RegistrationResource never triggers an extra query.
     *
     * @param IndexRegistrationRequest $request Validated filters (poli_id, status, per_page)
     * @return JsonResponse Standardized { status, message, data } envelope
     */
    public function index(IndexRegistrationRequest $request): JsonResponse
    {
        $registrations = $this->registrationService->list($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Registrations retrieved successfully.',
            'data' => RegistrationResource::collection($registrations),
        ]);
    }
}

// app/Http/Requests/IndexRegistrationRequest.php
class IndexRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Registration::class);
    }

    public function rules(): array
    {
        return [
            'poli_id' => ['sometimes', 'integer', 'exists:polis,id'],
            'status' => ['sometimes', 'string', 'in:Paid,DP,Unpaid,Batal'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}

// app/Services/RegistrationService.php
class RegistrationService
{
    /**
     * Retrieve paginated registrations with every downstream relation eager loaded.
     * This is the single source of truth for the Registration list query —
     * prevents N+1 queries when serialized through RegistrationResource.
     *
     * @param array $filters Validated filters: poli_id, status, per_page
     * @return LengthAwarePaginator
     */
    public function list(array $filters): LengthAwarePaginator
    {
        return Registration::query()
            ->with([
                'patient:id,name,phone',
                'doctorSchedule.doctor:id,name',
                'doctorSchedule.poli:id,name',
                'payment:id,registration_id,status,paid_amount',
            ])
            ->when($filters['poli_id'] ?? null, fn ($q, $poliId) =>
                $q->whereHas('doctorSchedule', fn ($q) => $q->where('poli_id', $poliId))
            )
            ->when($filters['status'] ?? null, fn ($q, $status) =>
                $q->whereRelation('payment', 'status', $status)
            )
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }
}

// app/Http/Resources/RegistrationResource.php
class RegistrationResource extends JsonResource
{
    /**
     * Transform the registration for the API response.
     * Only references relations RegistrationService already eager loaded —
     * this class must NEVER trigger its own query.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_name' => $this->patient->name,
            'doctor_name' => $this->doctorSchedule->doctor->name,
            'poli_name' => $this->doctorSchedule->poli->name,
            'payment_status' => $this->payment?->status,
        ];
    }
}
```

---

### C. N+1 Query Prevention & Query Performance (STRICT — Zero Tolerance)
N+1 queries are treated as **bugs, not style preferences**. Every list or detail endpoint must pass this checklist before it is considered done.

1. **Mandatory Eager Loading, No Exceptions**
   - Any relation accessed anywhere downstream of a query — in a Resource, a Job, or another Service — must be eager loaded at the query's origin via `with()`, `load()`, or `loadMissing()`.
   - Eager load only the columns actually needed: `with(['doctorSchedule.doctor:id,name'])` instead of loading full related rows.
   - `DoctorSchedule::with(['doctor', 'poli', 'room'])` (already required per Section 6A / the Frozen Doctor Schedule Module) is the **minimum bar** — apply the same discipline to every relation-heavy Model (`Registration`, `Inspection`, `Payment`), not just that one module.
2. **Enforce It at the Framework Level, Not Just by Convention**
   - Register this in `AppServiceProvider::boot()` so lazy loading is impossible to miss in every environment, with production degrading to a log instead of a crash:
     ```php
     use Illuminate\Database\Eloquent\Model;
     use Illuminate\Database\LazyLoadingViolationException;

     public function boot(): void
     {
         Model::preventLazyLoading();

         Model::handleLazyLoadingViolationUsing(function ($model, $relation) {
             $exception = new LazyLoadingViolationException($model, $relation);

             if ($this->app->isProduction()) {
                 report($exception); // logged to Sentry/daily log — request still completes
             } else {
                 throw $exception;   // fails loudly and immediately during development
             }
         });
     }
     ```
   - This means any missed `with()` throws instantly while coding locally — it cannot reach code review, let alone production, undetected.
3. **Never Query Inside a Loop**
   - `foreach` / `map` / `each` blocks must never contain a query or an un-eager-loaded relation access. Replace per-iteration lookups with one batched query using `whereIn()`.
4. **Use Aggregates Instead of Loading Full Relations**
   - Need only a count, sum, or existence check? Use `withCount()`, `withSum()`, `withAvg()`, or `withExists()` — e.g. `Doctor::withCount('registrations')` — instead of eager-loading an entire collection just to measure it.
5. **Pagination Is Mandatory for List Endpoints**
   - Index endpoints must use `paginate()` or `cursorPaginate()`. `Model::all()` or an unbounded `get()` is not allowed on any endpoint whose result set grows with the data (patients, registrations, inspections, payments).
6. **API Resources Must Never Trigger a New Query**
   - A Resource's `toArray()` may reference only relations/attributes guaranteed to already be loaded by the calling query. Eager loading and the Resource that consumes it are a mandatory pair — see the reference pattern in Section 6B.
7. **Verify Before Every Commit**
   - Before following the Git Commit Protocol (Section 8), check the actual query count for any new or modified endpoint using Laravel Telescope, Laravel Debugbar, or the Database Query tool available via Laravel Boost (already configured in this repo through `boost.json`). A query count that scales with the number of rows returned fails review — it is not a warning, it blocks the commit.

---

### D. General Backend Rules, Data Integrity & Security
- **PostgreSQL Type Safety**: All models must define `$casts` for foreign keys (`integer`) and booleans to prevent PostgreSQL PDO string conversion issues.
- **Explicit Model Mapping**: Define `$table` and `$primaryKey` explicitly when not following standard Laravel plural conventions.
- **Xendit Webhook Security**:
  - Validation requires passing the `VerifyXenditWebhook` middleware (`x-callback-token`).
  - Automatic payment status updates accumulate `paid_amount` and synchronize patient registration status.
- **Standardized API Responses**:
  - Response structure: `{ status: boolean, message: string, data: any }`.
  - Proper HTTP status codes: `200/201` (Success), `422` (Validation Error), `404` (Not Found), `401/403` (Unauthorized/Forbidden), `500` (Server Error).

---

## 7. FROZEN MODULES (DO NOT MODIFY / DO NOT TOUCH)[cite: 2]
The following core modules are fully functional, production-tested, and **MUST NOT be modified or altered** during frontend tasks or feature requests:[cite: 2]

1. **Authentication & User Module (Laravel Sanctum)**:[cite: 2]
   - RESTful API authentication system[cite: 2].
   - Patient register and login endpoints with automatic validation[cite: 2].
   - Bearer Token session management protecting private endpoints[cite: 2].

2. **Doctor Schedule Module (`doctor_schedule`)**:[cite: 2]
   - Full CRUD endpoints connecting doctor, polyclinic, and room records[cite: 2].
   - Eager loading implementations preventing N+1 query overhead[cite: 2].
   - Schedule filters by `doctor_id`, `poli_id`, day of week, and active status[cite: 2].
   - PostgreSQL type casting on model attributes[cite: 2].

3. **Payment & Xendit Payment Gateway Module**:[cite: 2]
   - Online invoice generation using Xendit API v2[cite: 2].
   - Support for full payments (`Paid`) and down payments (`DP`)[cite: 2].
   - Webhook callback endpoints handling automatic successful payment notifications[cite: 2].
   - `paid_amount` accumulation and patient registration synchronization[cite: 2].
   - Payment method mapping from Xendit (QRIS, Credit Card, Debit Card)[cite: 2].

4. **Security & Middleware Module**:[cite: 2]
   - `VerifyXenditWebhook` middleware validating `x-callback-token` in request headers[cite: 2].
   - Public webhook route isolation bypassing Sanctum authentication[cite: 2].

5. **API Response & Database Standardization**:[cite: 2]
   - Standard JSON response format (`status`, `message`, `data`)[cite: 2].
   - Structured HTTP status code handling (200, 201, 422, 404)[cite: 2].
   - PostgreSQL `payment` table check constraints[cite: 2].

---

## 8. Developer Operational Workflow[cite: 2]

1. **Clarification Protocol**:[cite: 2]
   - If any task or requirement is ambiguous, incomplete, or lacks clarity, **ask for clarification before writing or modifying any code in the project**[cite: 2].
2. **Git Commit Protocol**:[cite: 2]
   - Immediately after completing code modifications, commit all changes to the configured GitHub repository with clear, conventional commit messages[cite: 2].
3. **Memory Logging (`MEMORY.md`)**:[cite: 2]
   - Every modification, new component, bug fix, or configuration update must be recorded in `MEMORY.md` documenting what was changed and why[cite: 2].
4. **Design Adherence**:[cite: 2]
   - Always reference `DESIGN.md` for styling decisions, color values, component spacing, and typography before creating any UI element[cite: 2].
5. **Backend Query & Clean Code Audit**:
   - Before committing any change touching `app/Http/Controllers`, `app/Http/Requests`, `app/Services`, `app/Actions`, or `app/Models`, verify it against Section 6B (Clean Code Architecture) and Section 6C (N+1 Query Prevention): eager loading is explicit, no query runs inside a loop, and the endpoint's query count does not scale with the number of rows returned.