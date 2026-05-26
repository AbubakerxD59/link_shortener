# Link Shortener API Documentation

Base URL for all examples: `{APP_URL}` (from your `.env` `APP_URL`, e.g. `https://short.example.com`).

All JSON endpoints accept **`Content-Type: application/json`** or **`application/x-www-form-urlencoded`**.

---

## Live documentation

| Format | URL |
|--------|-----|
| Web (styled) | `GET /api-docs` |
| OpenAPI 3.0 | `GET /api-docs/openapi.yaml` |

Source files: `docs/API.md`, `docs/openapi.yaml`, `resources/views/docs/api.blade.php`.

---

## Table of contents

1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Create short link (API)](#create-short-link-api)
4. [Create short link (web)](#create-short-link-web)
5. [Visit short link (redirect)](#visit-short-link-redirect)
6. [Response fields](#response-fields)
7. [Errors](#errors)
8. [Behavior notes](#behavior-notes)

---

## Overview

| Method | Path | Purpose |
|--------|------|---------|
| `POST` | `/api/links` | **Recommended** — store a short link (full params, no CSRF) |
| `POST` | `/shorten` | Public shorten endpoint used by the web UI (CSRF required for browser) |
| `GET` | `/s/{code}` | Open a short link (bridge cloaking page, then destination) |

Every stored link uses **bridge-page cloaking**: visitors see a preview (title + thumbnail when available) and must click **Continue** to reach the destination. There is no auto-redirect timer.

---

## Authentication

No API keys or tokens are required by default. Protect `/api/*` at the infrastructure level (firewall, API gateway) if exposed publicly.

---

## Create short link (API)

Create or return an existing short link for the same destination (deduplication rules apply).

### Request

```
POST /api/links
```

#### Body parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `original_url` | string (URL) | **Yes** | Destination URL (`http` or `https`, max 2048 chars) |
| `user_id` | integer | No | Associate link with a user (min `1`) |
| `user_agent` | string | No | Stored on the record (max 65535). Defaults to request `User-Agent` |
| `ip_address` | string | No | Valid IP (v4/v6). Defaults to client IP |
| `page_title` | string | No | Override bridge preview title (max 500) |
| `thumbnail_url` | string (URL) | No | Override bridge preview image (max 2048) |
| `source` | string | No | Origin label (`api`, `web`, `engagyo`, etc.). Defaults to `api`. Max 64 chars, lowercase alphanumeric with `_` `-` |

If `page_title` / `thumbnail_url` are omitted, the server fetches Open Graph metadata from `original_url` when possible.

#### Example (JSON)

```bash
curl -X POST "{APP_URL}/api/links" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "original_url": "https://example.com/blog/post",
    "user_id": 42,
    "user_agent": "MyIntegration/1.0",
    "ip_address": "203.0.113.10"
  }'
```

#### Example (form)

```bash
curl -X POST "{APP_URL}/api/links" \
  -H "Accept: application/json" \
  -d "original_url=https://example.com/blog/post" \
  -d "user_id=42"
```

#### Example (with preview overrides)

```bash
curl -X POST "{APP_URL}/api/links" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "original_url": "https://example.com/product",
    "page_title": "Summer Sale",
    "thumbnail_url": "https://cdn.example.com/sale.jpg"
  }'
```

### Success response

**`201 Created`** — new link stored  

**`200 OK`** — existing link returned (same URL + same deduplication scope)

```json
{
  "success": true,
  "id": 15,
  "short_url": "https://short.example.com/s/a1B2c3",
  "short_code": "a1B2c3",
  "original_url": "https://example.com/blog/post",
  "redirect_mode": "bridge",
  "page_title": "Example Blog Post",
  "thumbnail_url": "https://example.com/og-image.jpg",
  "source": "api",
  "clicks": 0,
  "existing": false,
  "created_at": "2026-05-26T14:30:00+00:00"
}
```

| Field | Description |
|-------|-------------|
| `success` | Always `true` on success |
| `id` | Database primary key |
| `short_url` | Full shareable URL |
| `short_code` | Path segment used in `/s/{code}` |
| `original_url` | Normalized destination URL |
| `redirect_mode` | Always `bridge` |
| `page_title` | Title shown on bridge page |
| `thumbnail_url` | Image URL for bridge preview (may be `null`) |
| `source` | Origin label (`web`, `api`, or custom) |
| `clicks` | Visit count |
| `existing` | `true` if an existing row was reused |
| `created_at` | ISO 8601 timestamp |

### Error response

**`422 Unprocessable Entity`** — validation failed or invalid URL

```json
{
  "message": "The original url field must be a valid URL.",
  "errors": {
    "original_url": [
      "The original url field must be a valid URL."
    ]
  }
}
```

Invalid destination (normalization failed):

```json
{
  "success": false,
  "message": "Invalid URL"
}
```

---

## Create short link (web)

Used by the homepage form. Same core logic as `/api/links` but fewer parameters and **CSRF protection** when called from the browser.

### Request

```
POST /shorten
```

#### Body parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `original_url` | string (URL) | **Yes** | Destination URL |
| `user_agent` | string | No | Defaults to browser `User-Agent` |

#### Headers (browser / AJAX)

| Header | Value |
|--------|--------|
| `Accept` | `application/json` |
| `Content-Type` | `application/json` |
| `X-CSRF-TOKEN` | Laravel CSRF token (from `<meta name="csrf-token">` or cookie) |
| `X-Requested-With` | `XMLHttpRequest` (optional) |

#### Example

```bash
curl -X POST "{APP_URL}/shorten" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your-csrf-token" \
  -d '{
    "original_url": "https://example.com/page",
    "user_agent": "Mozilla/5.0 ..."
  }'
```

### Success response

**`200 OK`** (always, including new and existing links)

Same JSON shape as `/api/links`, except `id`, `clicks`, and `created_at` are included when available from the service layer.

### Error response

**`422`** — same validation format as above.

---

## Visit short link (redirect)

Not a JSON API; documented for integrators testing end-to-end flow.

### Request

```
GET /s/{code}
```

- `{code}` — alphanumeric short code (e.g. `a1B2c3`)
- Increments `clicks` by 1
- Returns HTML bridge page with preview and **Continue** button
- Does not auto-redirect

### Responses

| Status | Meaning |
|--------|---------|
| `200` | Bridge page HTML |
| `404` | Unknown short code |

---

## Response fields

### `redirect_mode`

| Value | Behavior |
|-------|----------|
| `bridge` | Default for all new links; cloaked bridge page |

Legacy values (`direct`, `meta`) in the database are still served as a bridge page.

### Deduplication

A new row is **not** created when the same normalized `original_url` and `source` already exist for:

- The same `user_id`, if `user_id` is sent; otherwise
- The same `user_agent` (non-empty), if no `user_id`; otherwise
- Anonymous links (`user_id` and `user_agent` both empty)

When a match is found, the API returns `existing: true` and the existing `short_code`.

---

## Errors

### Validation (`422`)

Laravel standard validation JSON:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": ["Error message."]
  }
}
```

### Common validation rules

| Field | Rules |
|-------|--------|
| `original_url` | required, valid URL, max 2048 |
| `user_id` | optional integer, min 1 |
| `user_agent` | optional string, max 65535 |
| `ip_address` | optional valid IP |
| `page_title` | optional string, max 500 |
| `thumbnail_url` | optional valid URL, max 2048 |
| `source` | optional string, max 64 |

---

## Behavior notes

### URL normalization

- Trims whitespace
- Adds `https://` if scheme missing
- Lowercases host
- Removes trailing slash on path (except root)

### Link preview

On create, the server may HTTP-fetch the destination and parse `og:title`, `twitter:title`, `<title>`, and image meta tags. If fetching fails, `page_title` falls back to the hostname and `thumbnail_url` may be `null`.

### Shared Engagyo database

When `ENGAGYO_USE_SHARED_DB=true`, links read/write the Engagyo `short_links` table. Ensure that database has columns: `redirect_mode`, `bridge_delay_seconds`, `page_title`, `thumbnail_url`, `source`.

### Health check

```
GET /up
```

Laravel health endpoint (not part of the shortener API).

---

## Quick reference

```text
GET    /api-docs             API documentation (web UI)
GET    /api-docs/openapi.yaml  OpenAPI specification
POST   /api/links           Create / resolve short link (integrations)
POST   /shorten             Create short link (web UI, CSRF)
GET    /s/{code}            Bridge page → user clicks Continue
GET    /up                  Application health
```
