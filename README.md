# AcceptedDev Advance Task — WooCommerce → Zoho CRM

Local WooCommerce store integrated with **Zoho CRM** using **Deluge**, **WooCommerce REST API**, and **OAuth2** (CRM API v8).

## What this project does

When the sync function runs, it:

1. Fetches **processing** orders from WooCommerce
2. Refreshes a Zoho **OAuth2 access token**
3. Creates or updates a **Contact** (by email)
4. Creates a **Deal** per order (skips duplicates)

```
WooCommerce REST API          Zoho OAuth2 + CRM API v8
        │                              │
        └──────── Deluge function ─────┘
                      │
              Contacts + Deals
```

## Live demo URLs

| System | URL |
|--------|-----|
| WooCommerce (production) | https://accepted.phoenixtechs.tech |
| LocalWP (development) | http://accepteddevtask.local |
| Zoho CRM | Your org (Functions under Setup → Developer Space) |

> Zoho runs in the cloud and cannot reach `.local` URLs. The Deluge script uses the public HTTPS store URL for API calls. The store was built locally with LocalWP.

## Repository structure

```
app/public/              WordPress + WooCommerce site root
conf/                    LocalWP PHP/MySQL templates
integration/
  deluge/                Deluge scripts (standalone + schedule)
  OAUTH-SETUP.md         OAuth2 refresh token guide
  SETUP.md               Full integration setup
scripts/                 WP-CLI seeders, API tests, OAuth helpers
deployment/              FastPanel deployment notes
submission/              Screenshots for task submission
```

## Test data

| Type | Details |
|------|---------|
| Customer 1 | John Test — `john.test@example.com` |
| Customer 2 | Jane Demo — `jane.demo@example.com` |
| Order #26 | John — **109.97 EGP** |
| Order #27 | Jane — **24.99 EGP** |

## Quick start (local)

### 1. WooCommerce

Store is pre-seeded. To re-seed:

```powershell
powershell -ExecutionPolicy Bypass -File scripts\setup-all.ps1
```

Test REST API:

```powershell
powershell -ExecutionPolicy Bypass -File scripts\test-wc-api.ps1
```

### 2. Zoho OAuth2

Follow **`integration/OAUTH-SETUP.md`**.

```powershell
powershell -ExecutionPolicy Bypass -File scripts\exchange-zoho-token.ps1
php scripts/test-zoho-oauth.php
```

### 3. Deluge function in Zoho CRM

1. **Setup → Developer Space → Functions → Create Function**
2. **Standalone** — paste `integration/deluge/sync-woocommerce-orders.deluge`
3. Replace placeholders: WooCommerce keys + Zoho OAuth credentials
4. **Save & Execute**
5. Optional: **Schedule** function + **Setup → Automation → Schedules** (Daily)

## Authentication

| Service | Method |
|---------|--------|
| WooCommerce | REST API consumer key + secret (query string) |
| Zoho CRM | OAuth2 refresh token → access token → `Authorization: Zoho-oauthtoken` |

## Key files

| File | Purpose |
|------|---------|
| `integration/deluge/sync-woocommerce-orders.deluge` | Manual test (Standalone, `string` return) |
| `integration/deluge/sync-woocommerce-orders-schedule.deluge` | Scheduled runs (`void` return) |
| `integration/OAUTH-SETUP.md` | OAuth2 setup |
| `scripts/exchange-zoho-token.ps1` | Exchange grant code → refresh token |
| `app/public/wp-content/mu-plugins/accepteddev-local-rest-auth.php` | Local HTTP REST auth for dev |

## Secrets (do not commit)

Gitignored local files:

- `integration/woocommerce-api.local.json`
- `integration/zoho.local.json`

Paste real credentials only in Zoho CRM function editor or local JSON files.

## Task requirements checklist

- [x] LocalWP + WooCommerce + 3 products + 2 orders
- [x] Zoho CRM — Contacts & Deals modules
- [x] Deluge integration script
- [x] WooCommerce REST API
- [x] OAuth2 + Zoho CRM API v8
- [x] Create/update Contacts, create Deals
- [x] Duplicate deal prevention
- [x] Verified in CRM (see `submission/`)

## Submission

See **`submission/SUBMISSION.md`** for email template and screenshot notes.

## References

- [WooCommerce REST API](https://woocommerce.github.io/woocommerce-rest-api-docs/)
- [Zoho CRM API v8](https://www.zoho.com/crm/developer/docs/api/v8/)
- [Advance Task Objective.pdf](./Advance%20Task%20Objective.pdf)
