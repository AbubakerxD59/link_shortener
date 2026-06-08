# Branded domain HTTPS (SSL)

DNS verification only confirms that a customer's CNAME points to your server. **Links still fail in the browser** until `https://their-branded-domain` works end-to-end.

## Cloudflare Error 525

If you see **Error 525: SSL handshake failed**, the browser and Cloudflare are fine — Cloudflare cannot complete TLS with your **origin** (Hostinger).

Typical cause on shared hosting:

1. Cloudflare SSL mode is **Full** or **Full (strict)**, but Hostinger has **no certificate** for the branded hostname (only for your main app domain).
2. The branded hostname is **not added** in Hostinger hPanel, so the origin rejects the request.

### Fix (Cloudflare + Hostinger)

1. **Cloudflare DNS:** CNAME `shrtlnk` → `CUSTOM_DOMAIN_CNAME_TARGET` with **Proxied** (orange cloud) ON.
2. **Cloudflare SSL/TLS → Overview:** set encryption mode to **Flexible** (not Full).
3. **Hostinger hPanel:** on the site for `CUSTOM_DOMAIN_CNAME_TARGET`, go to **Parked Domains** and add `shrtlnk.customer.com`.
4. Click **Refresh** on the branded domain page in this app.

Use **Full (strict)** only if you install a valid origin certificate for each branded hostname.

## Why visitors need HTTPS

Your app generates branded short URLs with `https://` (see `CUSTOM_DOMAIN_SCHEME`). When a visitor opens `https://shrtlnk.customer.com/abc123`:

1. DNS resolves the branded hostname → Cloudflare (if proxied) → your server
2. The browser expects a working HTTPS response
3. If Cloudflare cannot reach the origin correctly, you get Error 525 or 403

## Checklist

- [ ] `CUSTOM_DOMAIN_CNAME_TARGET` is your live app hostname (e.g. `shrtlnk.com`)
- [ ] CNAME is **proxied** in Cloudflare (orange cloud)
- [ ] Cloudflare encryption mode is **Flexible** (shared hosting) or origin has a matching cert (Full strict)
- [ ] Branded hostname is a **parked domain** on Hostinger pointing to the same site
- [ ] `CUSTOM_DOMAIN_SCHEME=https` in production `.env`

## Testing

```bash
curl -I https://shrtlnk.customer.com/testcode
```

A working setup returns `HTTP/2 302` or `404` — not `525`, `526`, or a TLS error.
