# Booking Microservice — Development Standards

Meeting room booking API microservice.
Laravel 12, PHP 8.2+, PostgreSQL, Redis, Docker-based development.
Authentication via Laravel Sanctum (token-based).

All routes under `/api/v1/` prefix. Routes defined in `routes/api.php`.

---

## Architecture Pattern

Follow a decoupled, domain-driven approach. Every layer has a single responsibility.

```
Routes → Controllers → DTOs → Actions → Repositories → Models
                                           ↓
                                    Resources (Response)
                                    FormRequests (Validation)
```

---

## Layer Rules

### 1. Controllers (Thin Layer)

- Controllers handle HTTP-specific logic only.
- **Responsibility:** Validate via `FormRequest`, map data to a **DTO**, call an **Action**, return a response via **Resource**.
- **Constraint:** No business logic or Eloquent queries in Controllers.

### 2. DTOs (Data Transfer Objects)

- Use DTOs to move data between the Controller and Action/Service layers.
- Never pass raw `Request` objects or associative arrays into Actions.
- All DTOs are `final readonly class` with strict typing.
- Required methods: `fromArray(array)`, `fromRequest(FormRequest)`, `toArray()`.
- Location: `app/DTOs/{Domain}/` (e.g., `app/DTOs/Booking/StoreBookingDTO`).

### 3. Actions & Services

- **Actions:** Single-responsibility classes for discrete business tasks (e.g., `CreateBookingAction`).
- **Services:** For broader orchestration when multiple Actions must be coordinated.
- **Dependency Injection:** Constructor injection for all dependencies. No `static` methods or `app()` helper.
- Location: `app/Actions/`.

### 4. Repositories

- Abstract all database interactions into Repository classes.
- Actions and Services interact with the Repository, never the Eloquent Model directly.
- **Interfaces:** `app/Repositories/Contracts/`
- **Implementations:** `app/Repositories/Eloquent/`
- **Binding:** `app/Providers/RepositoryServiceProvider.php`

### 5. Form Requests

- All incoming data validated via FormRequest classes.
- Location: `app/Http/Requests/`.
- Use `ApiResponseTrait` for custom validation error responses where needed.

### 6. API Resources & Response Handling

- All responses transformed via API Resource classes.
- Location: `app/Http/Resources/`.
- **Single resource responses:** Use `ApiResponseTrait` — `$this->respondWithSuccess($resource)` for success, `$this->respondCreated($resource)` for 201, `$this->respondError($message, $code)` for failures.
- **Single-object responses must NOT use wrapping keys.** Pass the resource directly: `$this->respondCreated(new BookingResource($booking))`.
- **Collection responses:** Use dedicated `XCollection` classes extending `ResourceCollection` when needed.
- `ApiResponseTrait` is in `app/Traits/ApiResponseTrait.php`. See the trait for all available methods: `respondWithSuccess`, `respondCreated`, `respondError`, `respondNotFound`, `respondUnAuthenticated`, `respondForbidden`, `respondNoContent`, `respondWithToken`, etc.

### 7. Models

- Keep models skinny: relationships, casts, and local scopes only.
- **ID Generation:** Prefixed ULIDs via `HasPrefixedUlid` trait (`app/Traits/HasPrefixedUlid.php`).
  - Format: `{prefix}_{24chars}` (e.g., `usr_01abc123def456`)
  - Prefixes: `usr_` (User), `rom_` (Room), `bkg_` (Booking)
- **Authentication:** Laravel Sanctum — `HasApiTokens` on the User model.

### 8. API Key Authentication

- All API routes are protected by the `api.key` middleware.
- Clients must send the key in the `X-API-Key` request header.
- The key is stored in the `API_KEY` env var and read via `config('app.api_key')`.
- Middleware: `app/Http/Middleware/ValidateApiKey.php`.
- Invalid or missing key returns `{"error": "Unauthorized"}` with HTTP 401.

### 9. Authorization (Policies)

- Use Laravel Policies for model-level authorization.
- Laravel auto-discovers by `{Model}Policy` naming convention.
- **Controller pattern:** Fetch model → null-check (404) → `$this->authorize('ability', $model)` (403) → proceed.
- 403 returns `{"error": "Forbidden"}`.

---

## Coding Standards

- **Strict Typing:** `declare(strict_types=1)` in all files. All methods must have parameter types and return types.
- **Validation:** FormRequests for all incoming data.
- **Models:** Skinny — relationships, casts, scopes only. No business logic.
- **No Eloquent in Controllers or Actions.** Always go through Repositories.
- **Naming Conventions:**

| Layer                | Pattern                      | Example                       |
| -------------------- | ---------------------------- | ----------------------------- |
| Controller           | `{Model}Controller`          | `BookingController`           |
| Action               | `{Verb}{Model}Action`        | `CreateBookingAction`         |
| DTO                  | `{Verb}{Model}DTO`           | `StoreBookingDTO`             |
| Repository Interface | `{Model}RepositoryInterface` | `BookingRepositoryInterface`  |
| Repository Impl      | `{Model}Repository`          | `BookingRepository`           |
| FormRequest          | `{Verb}{Model}Request`       | `StoreBookingRequest`         |
| Resource             | `{Model}Resource`            | `BookingResource`             |
| Policy               | `{Model}Policy`              | `BookingPolicy`               |

---

## Testing

- Every feature must include:
  - **Feature Tests:** Verify endpoint behavior, status codes, and JSON structure.
  - **Unit Tests:** Verify isolated logic within Actions and Services.
- Tests use SQLite in-memory (`phpunit.xml` configured).
- Location: `tests/Feature/` and `tests/Unit/`.

---

## File Locations

| What                  | Where                              |
| --------------------- | ---------------------------------- |
| Routes                | `routes/api.php`                   |
| Controllers           | `app/Http/Controllers/Api/`        |
| Actions               | `app/Actions/`                     |
| DTOs                  | `app/DTOs/{Domain}/`               |
| Repository Interfaces | `app/Repositories/Contracts/`      |
| Repository Impls      | `app/Repositories/Eloquent/`       |
| Form Requests         | `app/Http/Requests/`               |
| Resources             | `app/Http/Resources/`              |
| Models                | `app/Models/`                      |
| Traits                | `app/Traits/`                      |
| Policies              | `app/Policies/`                    |
| Seeders               | `database/seeders/`                |
| Migrations            | `database/migrations/`             |
| Tests                 | `tests/Feature/`, `tests/Unit/`    |
| Providers             | `app/Providers/`                   |

---

## Infrastructure

- **Docker:** `docker-compose.yml` at project root. Services: PHP-FPM, Nginx, PostgreSQL, Redis, Mailcatcher.
- **Container prefix:** `booking_microservice_` (e.g., `booking_microservice_php`, `booking_microservice_postgres`).
- **CI/CD:** GitHub Actions at `.github/workflows/ci-cd.yml`. Runs: PHP lint, Pint, Larastan, migrations, seeds, tests, composer audit.
- **Pre-push hook:** `.githooks/pre-push` — PHP lint, PHPStan, Pint check. Auto-installed in container on local env.
