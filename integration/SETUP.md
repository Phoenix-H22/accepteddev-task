# WooCommerce → Zoho CRM integration

## Already done for you

- LocalWP site at `http://accepteddevtask.local`
- WooCommerce store live (coming soon disabled)
- 3 products with categories and images
- 2 test customers and 2 processing orders
- Cash on delivery + free shipping enabled
- WooCommerce REST API key saved to `integration/woocommerce-api.local.json`
- Local HTTP REST auth mu-plugin (`wp-content/mu-plugins/accepteddev-local-rest-auth.php`)
- Deluge script ready at `integration/deluge/sync-woocommerce-orders.deluge`

### Test data

| Type | Details |
|------|---------|
| Customer 1 | John Test — `john.test@example.com` / `TestPass123!` |
| Customer 2 | Jane Demo — `jane.demo@example.com` / `DemoPass123!` |
| Order #26 | John — Headphones + 2× Mug — **109.97 EGP** |
| Order #27 | Jane — T-Shirt — **24.99 EGP** |

### Verify WooCommerce API locally

```powershell
powershell -ExecutionPolicy Bypass -File "D:\GitHub\accepteddev-task-wp\scripts\test-wc-api.ps1"
```

---

## What only you can do

### 1) Expose local WooCommerce to Zoho (required)

Zoho runs in the cloud and cannot reach `accepteddevtask.local` directly.

1. Install [ngrok](https://ngrok.com/download)
2. In LocalWP, note your site HTTP port (often **10005** for nginx)
3. Run:

```bash
ngrok http 10005 --host-header=accepteddevtask.local
```

4. Copy the **https** forwarding URL (example: `https://abc123.ngrok-free.app`)
5. Your WooCommerce API base becomes:

```text
https://abc123.ngrok-free.app/wp-json/wc/v3
```

Paste that URL into the Deluge script (`WOO_BASE_URL`).

Also copy **Consumer Key** and **Consumer Secret** from `integration/woocommerce-api.local.json`.

### 2) Zoho CRM OAuth2 (required)

The Deluge script uses **OAuth2 refresh token → access token → CRM API v8**.

Full steps: **`integration/OAUTH-SETUP.md`**

Quick summary:

1. [Zoho API Console](https://api-console.zoho.com/) — create Server-based client → **Client ID** + **Client Secret**
2. **Self Client** → Generate Code with scopes:
   - `ZohoCRM.modules.contacts.ALL`
   - `ZohoCRM.modules.deals.ALL`
3. Exchange code for **Refresh Token** (curl command in OAUTH-SETUP.md)
4. Paste into Deluge script:
   - `ZOHO_CLIENT_ID`, `ZOHO_CLIENT_SECRET`, `ZOHO_REFRESH_TOKEN`
5. Optional local test: `php scripts/test-zoho-oauth.php` (uses `integration/zoho.local.json`)

### 3) Create the Deluge function in Zoho CRM (required — main deliverable)

1. Zoho CRM → **Setup** → **Developer Space** → **Functions** → **Create Function**
2. Choose **Deluge**
3. Paste contents of `integration/deluge/sync-woocommerce-orders.deluge`
4. Replace `WOO_BASE_URL`, `CONSUMER_KEY`, `CONSUMER_SECRET`
5. **Save** and **Execute** manually first
6. Schedule it (e.g. every 15 minutes) or keep running manually for the demo

### 4) Verify in Zoho CRM (required)

After running the function, confirm:

- **Contacts**: John Test, Jane Demo
- **Deals**: "WooCommerce Order #26", "WooCommerce Order #27" with correct amounts

---

## Optional: re-seed everything

```powershell
powershell -ExecutionPolicy Bypass -File "D:\GitHub\accepteddev-task-wp\scripts\setup-all.ps1"
```

---

## Questions for you

Reply with these if you want help finishing the Zoho side:

1. **ngrok HTTPS URL** after you run it (so the Deluge script can be updated for you)
2. Whether you prefer **Deluge in Zoho CRM** (as per PDF) or a **PHP sync script** using OAuth tokens locally
