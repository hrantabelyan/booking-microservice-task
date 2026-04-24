# Booking Microservice

Meeting room booking JSON API. Built with Laravel 12, PostgreSQL, Redis, Docker.

## What it does

- Create a booking for a meeting room (with overlap detection and a configurable max duration).
- List bookings by user or by room, with date-range filtering and pagination.
- List available rooms.
- Sends a confirmation email on successful booking.

Authentication is a static API key sent in the `X-API-Key` header. Users are
identified by an opaque `user_uid` string supplied by the caller — the service
does not manage user accounts.

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
./bin/setup.sh           # one-time: configures git pre-push hook
docker compose up -d --build
```

On first boot the PHP container's entrypoint will:

1. Copy `.env.example` → `.env`
2. Run `composer install`
3. Generate `APP_KEY`
4. Run migrations and seeders (creates 4 rooms: Alpha, Beta, Gamma, Delta)

| Service | URL |
|---|---|
| API | `http://localhost` |
| Mailcatcher | `http://localhost:1080` |
| Dev homepage (local/dev/staging only) | `http://localhost/` |

The dev homepage links to `/docs/api` (Scramble), `/docs/flow`, `/docs/devops`,
and exposes the Postman collection at `/postman/collection`.

## Configuration

See [`src/.env.example`](src/.env.example) for the full list. The most relevant vars:

| Variable | Purpose | Default |
|---|---|---|
| `API_KEY` | Static key required in the `X-API-Key` header | `local-dev-api-key-change-me` |
| `BOOKING_MAX_DURATION_MINUTES` | Max booking length, in minutes | `480` (8 h) |
| `DB_*` | Postgres connection (injected by docker-compose) | — |
| `REDIS_HOST` / `REDIS_PORT` | Redis connection (injected by docker-compose) | — |
| `MAIL_HOST` / `MAIL_PORT` | SMTP — points to mailcatcher by default | — |

## API

All endpoints live under `/api/v1/` and require the `X-API-Key` header.

### Conventions

- **Timestamps** — ISO 8601. Requests may include a timezone offset
  (`2026-05-01T10:00:00Z` or `2026-05-01T15:00:00+05:00`); the service normalises
  everything to **UTC** for storage. Responses always return UTC ISO 8601 strings
  (`2026-05-01T10:00:00+00:00`).
- **List responses** — Laravel's standard paginated shape:
  `{ "data": [...], "links": {...}, "meta": {...} }`. Control with
  `?per_page=` (1–100, default 15) and `?page=`.
- **Single-item responses** — return the object directly, no `data` wrapper.
- **Error responses** — `{ "errors": { ... } }`, with a `message` field for
  validation failures.

### GET /api/v1/rooms

List available meeting rooms. Cached for 5 minutes via Redis.

**Response**

- `200 OK` — `{ "data": [ { "id", "name", "capacity" } ], "meta": { "total": N } }`

```bash
curl -H "X-API-Key: local-dev-api-key-change-me" \
  http://localhost/api/v1/rooms
```

### POST /api/v1/bookings

Create a booking. The service rejects bookings that overlap an existing one for
the same room, and bookings whose duration exceeds `BOOKING_MAX_DURATION_MINUTES`.

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

- `201 Created` — booking payload (with nested room)
- `409 Conflict` — room already booked for that time window
- `422 Unprocessable Entity` — validation failure (missing fields, unknown room,
  `ends_at` not after `starts_at`, `starts_at` in the past, duration exceeded, ...)
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

List bookings, sorted by `starts_at`. **One** of `user_uid` or `room_id` is required.

**Query params**

| Param | Required | Notes |
|---|---|---|
| `user_uid` | one of | Filter to bookings for this user |
| `room_id`  | one of | Filter to bookings for this room |
| `from` | optional | ISO 8601 — include bookings with `ends_at >= from` |
| `to`   | optional | ISO 8601 — include bookings with `starts_at <= to` (must be ≥ `from`) |
| `per_page` | optional | 1–100, default 15 |
| `page` | optional | page number |

A booking is included in a date range when it overlaps the window
(`starts_at <= to AND ends_at >= from`).

**Responses**

- `200 OK` — paginated `BookingCollection`
- `422 Unprocessable Entity` — neither filter provided, bad date format,
  `to < from`, or `per_page` out of range
- `401 Unauthorized` — API key missing/invalid

**Examples**

```bash
# My bookings
curl -H "X-API-Key: local-dev-api-key-change-me" \
  "http://localhost/api/v1/bookings?user_uid=user-123"

# Bookings for a room, restricted to one week, 50 per page
curl -H "X-API-Key: local-dev-api-key-change-me" \
  "http://localhost/api/v1/bookings?room_id=rom_01abc...&from=2026-05-01&to=2026-05-07&per_page=50"
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
- **Overlap check** lives in `BookingRepository::hasConflict()` — condition:
  `existing.starts_at < new.ends_at AND existing.ends_at > new.starts_at`.
- **Email** (`BookingCreatedMail`) is dispatched from `CreateBookingAction`
  after the booking is persisted, inside the same DB transaction.
- **Rooms** are read through `RoomRepository`, which caches the sorted list in
  Redis for 5 minutes.

## Testing

```bash
docker exec booking_microservice_php php artisan test
```

Tests run against an in-memory SQLite database (`phpunit.xml`). Suites:

- `tests/Feature/Api/BookingApiKeyTest.php` — API key middleware
- `tests/Feature/Api/CreateBookingTest.php` — create, overlap, max duration, time-order, unknown room
- `tests/Feature/Api/ListBookingsTest.php` — list by user/room, pagination, date range
- `tests/Feature/Api/ListRoomsTest.php` — rooms listing
- `tests/Unit/Actions/CreateBookingActionTest.php` — action-level conflict logic

## Quality tooling

Pre-push hook ([`.githooks/pre-push`](.githooks/pre-push)) runs
**PHP Lint → Pint → PHPStan** inside the `php` container. Wired up by
`./bin/setup.sh` (per clone).

CI ([`.github/workflows/ci-cd.yml`](.github/workflows/ci-cd.yml)) runs the same
checks plus `php artisan test` and `composer audit` against a real Postgres 18.1
service on every push/PR to `main`.
