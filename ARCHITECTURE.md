# Arsitektur SIPANDHALU

## Gambaran Umum

SIPANDHALU adalah aplikasi monolitik native PHP 8.2 + MySQL 8 tanpa framework. Aplikasi menggunakan pola MVC dengan front controller pattern.

```
┌──────────────────────────────────────────────────────────┐
│ Browser                                                  │
└────────────────────┬─────────────────────────────────────┘
                     │ HTTP Request
                     ▼
┌──────────────────────────────────────────────────────────┐
│ public/index.php (Front Controller)                      │
│   ↓                                                      │
│ app/Core/bootstrap.php                                   │
│   • Load Composer autoload                               │
│   • Load .env via Config::loadEnv()                      │
│   • Set timezone (Asia/Jakarta)                          │
│   • Set error handling (dev/prod)                        │
│   • Set security headers (X-Frame, X-Content-Type, etc) │
│   • Session::start()                                     │
│   • Router dispatch                                      │
└────────────────────┬─────────────────────────────────────┘
                     │
                     ▼
┌──────────────────────────────────────────────────────────┐
│ App\Core\Router                                          │
│   • Match URL path + HTTP method                         │
│   • Execute middleware pipeline                          │
│   • Dispatch to controller                               │
└────────────────────┬─────────────────────────────────────┘
                     │
        ┌────────────┼────────────┐
        ▼            ▼            ▼
┌─────────────┐ ┌──────────┐ ┌──────────┐
│Middleware 1 │ │Middleware│ │Middleware│
│ (Auth)      │ │ (CSRF)   │ │ (Role)   │
└─────────────┘ └──────────┘ └──────────┘
        │
        ▼
┌──────────────────────────────────────────────────────────┐
│ Controller                                               │
│   • Validate input                                       │
│   • Call Service for business logic                      │
│   • Call Repository for data access                      │
│   • Return Response (view, redirect, json)               │
└────────────────────┬─────────────────────────────────────┘
                     │
        ┌────────────┼────────────┐
        ▼            ▼            ▼
┌──────────────┐ ┌───────────┐ ┌──────────┐
│  Service     │ │ Repository│ │ Response │
│ (Business)   │ │ (Data)    │ │ (Output) │
└──────────────┘ └───────────┘ └──────────┘
                              │
                              ▼
                    ┌────────────────┐
                    │  Database (PDO) │
                    └────────────────┘
```

## Konvensi Naming

| Komponen | Convention | Contoh |
|---|---|---|
| Controller | `{Nama}Controller` | `SerutiController` |
| Service | `{Nama}Service` | `SerutiService` |
| Repository | `{Nama}Repository` | `PeriodeRepository` |
| Middleware | `{Nama}Middleware` | `AuthMiddleware` |
| View template | `{module}/{nama}.phtml` | `seruti/index.phtml` |
| Route | `GET/POST /path` | `GET /seruti` |
| Migration | `NNN_{nama}.sql` | `006_lk_pengolahan.sql` |
| Seed | `00N_{nama}_seed.php` | `004_dummy_seed.php` |
| Test | `{Nama}Test.php` | `SerutiModulTest.php` |

## Request Lifecycle

1. **Capture**: `Request::capture()` membaca method, path, GET, POST, server vars
2. **Bootstrap**: Load env, set error handling, start session
3. **Routing**: Router mencocokkan path + method ke rute yang terdaftar
4. **Middleware**: Setiap middleware `handle()` dipanggil berurutan
5. **Controller**: Method controller dipanggil dengan `(Request, params)`
6. **Response**: Controller mengembalikan Response (view, redirect, json)
7. **Exit**: Response::view/redirect/json keluar → tidak ada routing lebih lanjut

## Dependency Injection

Aplikasi menggunakan konstruksi manual (bukan container DI):
- Controller membuat Service instances di constructor
- Service membuat Repository instances di constructor
- Repository membuat PDO via `Database::connection()` (singleton)

## Konvensi Method di Controller

```php
public function index(Request $req, array $params = []): void
public function show(Request $req, array $params = []): void    // GET /{id}
public function create(Request $req, array $params = []): void  // GET form
public function store(Request $req, array $params = []): void   // POST create
public function edit(Request $req, array $params = []): void    // GET edit form
public function update(Request $req, array $params = []): void  // POST update
public function toggle(Request $req, array $params = []): void // POST toggle
```

## Konvensi Service

```php
// Business logic methods
public function canTransferSeruti(array $user): bool
public function getDaftarRuta(int $periodeId, array $filters, array $user): array
public function getKpi(int $periodeId, int $pengolahId = -1): array

// Validation methods (throw RuntimeException dengan kode HTTP)
public function assertCanTransferSeruti(int $rutaId, array $user): void  // throws 403
public function assertCanEditRuta(int $rutaId, array $user): void        // throws 403

// CRUD methods
public function updateRuta(int $rutaId, array $data, array $user): array
public function batchTransfer(array $params, array $user): int
```
