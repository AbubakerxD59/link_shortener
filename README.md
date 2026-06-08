# Link Shortener

Laravel URL shortener with **bridge-page link cloaking**, preview metadata (title + thumbnail), and click tracking.

## Documentation

- **[API Documentation](/api-docs)** — styled web reference (also [docs/API.md](docs/API.md))
- **[OpenAPI 3.0 spec](docs/openapi.yaml)** — import into Postman, Swagger UI, or Insomnia

## Quick start

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

## API (integrations)

```bash
# Create a link
POST /api/links
Content-Type: application/json

{ "original_url": "https://example.com/page", "user_id": 1 }

# Get link details + clicks
GET /api/links/abc123
```

See [docs/API.md](docs/API.md) for full details.

## Web

| Route | Description |
|-------|-------------|
| `GET /` | Shortener homepage |
| `POST /shorten` | JSON shorten (CSRF required from browser) |
| `GET /{code}` | Bridge page → Continue to destination |

## Environment

| Variable | Description |
|----------|-------------|
| `APP_URL` | Base URL used in `short_url` responses |
| `ENGAGYO_USE_SHARED_DB` | Use Engagyo shared `short_links` table |
| `ENGAGYO_DB_CONNECTION` | Database connection name when shared DB is enabled |
