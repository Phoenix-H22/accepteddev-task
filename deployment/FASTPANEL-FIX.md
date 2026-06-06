# Fix infinite loading on FastPanel

## Most common cause

Database still has `http://accepteddevtask.local` while the server forces HTTPS.
The browser keeps redirecting to a domain that does not exist → endless loading.

---

## Fix 1 — wp-config.php (do this first, 2 minutes)

SSH or FastPanel File Manager → edit `app/public/wp-config.php`:

### A) Set FastPanel database credentials

```php
define( 'DB_NAME', 'YOUR_FASTPANEL_DB_NAME' );
define( 'DB_USER', 'YOUR_FASTPANEL_DB_USER' );
define( 'DB_PASSWORD', 'YOUR_FASTPANEL_DB_PASSWORD' );
define( 'DB_HOST', 'localhost' );
```

Remove the whole block:

```php
if ( defined( 'WP_CLI' ) && WP_CLI ) {
    define( 'DB_HOST', '127.0.0.1:10004' );
} else {
    define( 'DB_HOST', 'localhost' );
}
```

Replace with only: `define( 'DB_HOST', 'localhost' );`

### B) Paste before "That's all, stop editing"

Copy from `deployment/wp-config-additions.php` in this repo.

---

## Fix 2 — Update URLs in database

phpMyAdmin → your database → SQL tab → run `deployment/fix-infinite-loading.sql`

---

## Fix 3 — If still hanging: disable heavy plugins

Rename via SSH:

```bash
cd /var/www/accepted_pho_usr/data/www/accepted.phoenixtechs.tech/app/public/wp-content
mv plugins/jetpack plugins/jetpack.off
mv plugins/google-listings-and-ads plugins/google-listings-and-ads.off
```

Reload the site. Re-enable one by one later.

---

## Fix 4 — Check PHP error log

```bash
tail -50 /var/www/accepted_pho_usr/data/logs/accepted.phoenixtechs.tech.error.log
```

Or WordPress log:

```bash
tail -50 /var/www/.../app/public/wp-content/debug.log
```

---

## Fix 5 — Document root

Must point to WordPress files:

```text
/var/www/accepted_pho_usr/data/www/accepted.phoenixtechs.tech/app/public
```

`index.php` must exist in that folder.

---

## Quick test

Open in browser:

```text
https://accepted.phoenixtechs.tech/wp-login.php
```

If login loads but homepage does not, it is a theme/plugin issue.
If nothing loads, it is wp-config / DB / document root.
