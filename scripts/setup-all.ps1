$ErrorActionPreference = "Stop"
$root = "D:\GitHub\accepteddev-task-wp"
$php = "C:\Users\abdal\AppData\Roaming\Local\lightning-services\php-8.2.29+0\bin\win64\php.exe"
$ini = "$root\conf\php\cli-php.ini"
$wp = "$root\app\public\wp-cli.phar"
$path = "$root\app\public"

Write-Host "==> Ensuring store is live"
& $php -c $ini -d memory_limit=512M $wp --path=$path option update woocommerce_coming_soon no | Out-Null
& $php -c $ini -d memory_limit=512M $wp --path=$path option update woocommerce_store_pages_only no | Out-Null

Write-Host "==> Enabling COD + free shipping"
& $php -c $ini -d memory_limit=512M $wp --path=$path wc payment_gateway update cod --user=1 --enabled=true | Out-Null

Write-Host "==> Seeding products (skip if already exist)"
if (Test-Path "$root\scripts\seed-products.ps1") {
    powershell -ExecutionPolicy Bypass -File "$root\scripts\seed-products.ps1"
}

Write-Host "==> Seeding orders + customers"
& $php -c $ini -d memory_limit=512M $wp --path=$path eval-file "$root\scripts\seed-orders.php"

Write-Host "==> Creating WooCommerce REST API key"
& $php -c $ini -d memory_limit=512M $wp --path=$path eval-file "$root\scripts\create-wc-api-key.php"

Write-Host "==> Testing WooCommerce REST API"
powershell -ExecutionPolicy Bypass -File "$root\scripts\test-wc-api.ps1"

Write-Host ""
Write-Host "Done. Next steps: read integration/SETUP.md"
