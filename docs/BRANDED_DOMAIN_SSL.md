# Branded domain HTTPS (SSL)

DNS verification only confirms that a customer's CNAME points to your server. **Links still fail in the browser** until `https://their-branded-domain` has a valid TLS certificate.

Short.io handles this on their edge automatically. Self-hosted deployments must configure SSL explicitly.

## Why you see `ERR_SSL_PROTOCOL_ERROR`

Your app generates branded short URLs with `https://` (see `CUSTOM_DOMAIN_SCHEME`). When a visitor opens `https://shrtlnk.customer.com/s/abc123`:

1. DNS resolves `shrtlnk.customer.com` → your `CUSTOM_DOMAIN_CNAME_TARGET`
2. The browser connects on port **443** and expects TLS for **`shrtlnk.customer.com`**
3. If your server only has a certificate for your main app hostname, the handshake fails

## Option A — Cloudflare proxy (recommended)

If the customer manages DNS in Cloudflare:

1. Create the CNAME: `shrtlnk` → your `CUSTOM_DOMAIN_CNAME_TARGET` (e.g. `shrtlnk.com`)
2. Turn **Proxied** on (orange cloud)
3. Cloudflare issues SSL for `shrtlnk.customer.com` automatically
4. Set Cloudflare SSL mode to **Full** or **Full (strict)** toward your origin
5. Your origin must accept HTTP/HTTPS for customer `Host` headers (Laravel already does)

This works well on shared hosting (e.g. Hostinger) where you cannot install a custom reverse proxy.

## Option B — Manual certificate

Install a TLS certificate for each customer hostname on your web server. This does not scale well for many domains.

## Checklist

- [ ] `CUSTOM_DOMAIN_CNAME_TARGET` resolves to your app server
- [ ] Your main app hostname has HTTPS on the origin
- [ ] Each verified customer domain has HTTPS (Cloudflare proxy or manual cert)
- [ ] `CUSTOM_DOMAIN_SCHEME=https` in production `.env`

## Testing

```bash
curl -I https://shrtlnk.customer.com/s/testcode
```

A working setup returns `HTTP/2 302` or `404` (not a TLS error).
