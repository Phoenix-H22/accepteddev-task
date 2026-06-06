# Zoho OAuth2 setup (required for Deluge script)

The Deluge integration uses **OAuth2** explicitly:

1. Exchange **Refresh Token** → **Access Token** (`POST /oauth/v2/token`)
2. Call **Zoho CRM API v8** with `Authorization: Zoho-oauthtoken <access_token>`

---

## Step 1 — Create a Self Client (recommended)

Use **Self Client**, not Server-based Application, for Deluge/backend integrations.

1. Go to [Zoho API Console](https://api-console.zoho.com/)
2. **Add Client** → **Self Client** → **Create Now** → OK
3. Open **Client Secret** tab → copy **Client ID** and **Client Secret**

> If you created **Server-based Application** earlier, its Client ID/Secret are **different**.
> The grant code must be exchanged with the **same client** that generated it.

---

## Step 1b — Server-based app only (alternative)

If you keep Server-based Application, add during token exchange:

```powershell
redirect_uri = "https://www.zoho.com"
```

(must match the URI registered on the client). Self Client is simpler — no redirect URI needed.

---

## Step 2 — Generate a grant code (NOT a refresh token yet)

1. In API Console, open your client → **Generate Code** (Self Client tab)
2. Click **Generate Code**
3. Enter scopes as **one line**, **comma-separated** (no spaces, no line breaks):

```
ZohoCRM.modules.contacts.ALL,ZohoCRM.modules.deals.ALL
```

**Common mistake:** pasting without a comma merges into invalid scope:
`ZohoCRM.modules.contacts.ALLZohoCRM.modules.deals.ALL` ❌

**Easier option** (all CRM modules):

```
ZohoCRM.modules.ALL
```

4. Choose expiry (e.g. 10 minutes) → **Create**
5. Copy the **grant code** immediately — it expires in minutes and works **only once**

> ⚠️ **Do NOT paste this grant code into `ZOHO_REFRESH_TOKEN` in Deluge.**  
> That causes `"invalid_code"` when the script runs. You must exchange it first (Step 3).

---

## Step 3 — Exchange grant code → Refresh Token (required)

Use the **same Client ID and Client Secret** from your Server-based app.

**PowerShell — Self Client (no redirect_uri):**

```powershell
# 1. Generate NEW code in API Console FIRST (Self Client → Generate Code)
# 2. Run this IMMEDIATELY and paste the code when prompted:

$code = Read-Host "Paste grant code (use within 2 minutes)"
$body = @{
  code          = $code
  client_id     = "YOUR_SELF_CLIENT_ID"
  client_secret = "YOUR_SELF_CLIENT_SECRET"
  grant_type    = "authorization_code"
}
Invoke-RestMethod -Method Post -Uri "https://accounts.zoho.com/oauth/v2/token" -Body $body
```

**PowerShell — Server-based app (add redirect_uri):**

```powershell
$code = Read-Host "Paste grant code"
$body = @{
  code          = $code
  client_id     = "YOUR_CLIENT_ID"
  client_secret = "YOUR_CLIENT_SECRET"
  grant_type    = "authorization_code"
  redirect_uri  = "https://www.zoho.com"
}
Invoke-RestMethod -Method Post -Uri "https://accounts.zoho.com/oauth/v2/token" -Body $body
```

**curl:**

```bash
curl -X POST "https://accounts.zoho.com/oauth/v2/token" \
  -d "code=YOUR_GRANT_CODE" \
  -d "client_id=YOUR_CLIENT_ID" \
  -d "client_secret=YOUR_CLIENT_SECRET" \
  -d "grant_type=authorization_code"
```

Response includes:

```json
{
  "access_token": "...",
  "refresh_token": "...",
  "expires_in": 3600
}
```

**Copy only `refresh_token`** from the response → paste into Deluge as `ZOHO_REFRESH_TOKEN`.

If exchange fails with `invalid_code`:
- Grant code expired → generate a **new** code and exchange within minutes
- Grant code already used → generate a **new** code (each code works once)
- Wrong client_id/client_secret → use the pair from the same app that generated the code

> **EU / IN accounts:** use `https://accounts.zoho.eu` or `https://accounts.zoho.in` and matching API domain (`www.zohoapis.eu`, etc.)

---

## Step 4 — Paste into Deluge script

In Zoho CRM function editor, set these variables at the top of the script:

```deluge
ZOHO_CLIENT_ID = "1000.xxxx";
ZOHO_CLIENT_SECRET = "xxxx";
ZOHO_REFRESH_TOKEN = "1000.xxxx";
ZOHO_ACCOUNTS_URL = "https://accounts.zoho.com";
ZOHO_API_DOMAIN = "https://www.zohoapis.com";
```

Also set WooCommerce keys:

```deluge
CONSUMER_KEY = "ck_...";
CONSUMER_SECRET = "cs_...";
```

---

## Step 5 — Test locally (optional)

```powershell
copy integration\zoho.local.json.example integration\zoho.local.json
# Fill client_id, client_secret, refresh_token

php scripts/test-zoho-oauth.php
```

Expected: `OAuth OK` + list of CRM modules or a test contact search.

---

## Step 6 — Run Deluge function

1. Paste updated script from `integration/deluge/sync-woocommerce-orders.deluge`
2. **Save & Execute**
3. Log should show OAuth token request + CRM v8 API calls

---

## What to say in your submission

> WooCommerce auth: REST API consumer key/secret.  
> Zoho CRM auth: OAuth2 refresh token flow → access token → CRM API v8 (`/crm/v8/Contacts`, `/crm/v8/Deals`).  
> Integration logic written in Deluge with `invokeurl`.
