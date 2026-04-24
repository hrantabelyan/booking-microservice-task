# Booking Microservice

Meeting room booking JSON API. Built with Laravel 12, PostgreSQL, Redis, Docker.

## What it does

- Create a booking for a meeting room (with overlap detection).
- List bookings by user, or by room.
- Sends a confirmation email on successful booking.

Authentication is via a static API key passed in the `X-API-Key` header.
Users are identified by an opaque `user_uid` string supplied by the caller — the service does not manage user accounts.

## Stack

- PHP 8.4 (FPM) / Laravel 12
- PostgreSQL 18.1
- Redis 7 (cache)
- Mailcatcher (dev SMTP)
- Nginx

## Quick start

```bash
git clone <repo>
cd booking-microservice
docker compose up -d --build
```

On first boot, the PHP container's entrypoint will:
1. Copy `.env.example` → `.env`
2. Run `composer install`
3. Generate `APP_KEY`
4. Run migrations and seeders (creates 4 rooms: Alpha, Beta, Gamma, Delta)

Service available at `http://localhost`. Mailcatcher UI at `http://localhost:1080`.

## Configuration

Key env vars (see `src/.env.example`):

| Variable | Purpose |
|---|---|
| `API_KEY` | Static key required in the `X-API-Key` header. Default: `local-dev-api-key-change-me` |
| `DB_*` | Postgres connection (injected by docker-compose) |
| `REDIS_HOST` / `REDIS_PORT` | Redis connection (injected by docker-compose) |
| `MAIL_HOST` / `MAIL_PORT` | SMTP — points to the mailcatcher container by default |

## API

All endpoints live under `/api/v1/` and require the `X-API-Key` header.

### GET /api/v1/rooms

List available meeting rooms. Cached for 5 minutes via Redis.

**Response**

- `200 OK` — `{ "data": [ { "id", "name", "capacity" } ] }`

```bash
curl -H "X-API-Key: local-dev-api-key-change-me" \
  http://localhost/api/v1/rooms
```

### POST /api/v1/bookings

Create a booking.

**Request body**

```json
{
  "room_id": "rom_01abc...",
  "user_uid": "user-123",
  "title": "Sprint planning",
  "starts_at": "2026-05-01T10:00:00Z",
  "ends_at": "2026-05-01T11:00:00Z"
}
```

**Responses**

- `201 Created` — booking payload
- `409 Conflict` — room already booked for that time
- `422 Unprocessable Entity` — validation failure (missing/invalid fields, unknown room, `ends_at` not after `starts_at`, etc.)
- `401 Unauthorized` — API key missing/invalid

**Example**

```bash
curl -X POST http://localhost/api/v1/bookings \
  -H "X-API-Key: local-dev-api-key-change-me" \
  -H "Content-Type: application/json" \
  -d '{
    "room_id": "rom_01abc...",
    "user_uid": "user-123",
    "title": "Sprint planning",
    "starts_at": "2026-05-01T10:00:00Z",
    "ends_at": "2026-05-01T11:00:00Z"
  }'
```

### GET /api/v1/bookings

List bookings. **One** of `user_uid` or `room_id` is required.

**Query params**

- `user_uid` — list bookings for this user
- `room_id` — list bookings for this room

**Responses**

- `200 OK` — `{ "data": [ ...bookings ] }` ordered by `starts_at`
- `422 Unprocessable Entity` — neither filter provided
- `401 Unauthorized` — API key missing/invalid

**Examples**

```bash
# My bookings
curl -H "X-API-Key: local-dev-api-key-change-me" \
  "http://localhost/api/v1/bookings?user_uid=user-123"

# Bookings for a room
curl -H "X-API-Key: local-dev-api-key-change-me" \
  "http://localhost/api/v1/bookings?room_id=rom_01abc..."
```

## Architecture

Decoupled, single-responsibility layers (see [CLAUDE.md](CLAUDE.md) for full rules):

```
Routes → Controller → DTO → Action → Repository → Model
                                         ↓
                                  Resource (response)
                                  FormRequest (validation)
```

- **Models** use prefixed ULIDs: `usr_`, `rom_`, `bkg_`.
- **Overlap check** lives in `BookingRepository::hasConflict()` — condition: `existing.starts_at < new.ends_at AND existing.ends_at > new.starts_at`.
- **Email** (`BookingCreatedMail`) is dispatched from `CreateBookingAction` after the booking is persisted, inside the same DB transaction.

## Testing

```bash
docker exec booking_microservice_php php artisan test
```

Tests run against an in-memory SQLite DB (`phpunit.xml`). Suites:

- `tests/Feature/Api/BookingApiKeyTest.php` — API key middleware
- `tests/Feature/Api/CreateBookingTest.php` — create + overlap rules
- `tests/Feature/Api/ListBookingsTest.php` — list by user/room
- `tests/Unit/Actions/CreateBookingActionTest.php` — action-level conflict logic

## Quality tooling

Pre-push hook (`.githooks/pre-push`) runs **PHP Lint → Pint → PHPStan**. Installed automatically in the PHP container on local env. CI (`.github/workflows/ci-cd.yml`) runs the same checks plus `php artisan test` and `composer audit` against a real Postgres 18.1 service.
