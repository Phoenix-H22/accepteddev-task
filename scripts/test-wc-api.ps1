$ErrorActionPreference = "Stop"
$php = "C:\Users\abdal\AppData\Roaming\Local\lightning-services\php-8.2.29+0\bin\win64\php.exe"
$ini = "D:\GitHub\accepteddev-task-wp\conf\php\cli-php.ini"
$wp = "D:\GitHub\accepteddev-task-wp\app\public\wp-cli.phar"
$path = "D:\GitHub\accepteddev-task-wp\app\public"
$config = "D:\GitHub\accepteddev-task-wp\integration\woocommerce-api.local.json"

if (-not (Test-Path $config)) {
    Write-Error "Missing $config - run create-wc-api-key.php first."
}

& $php -c $ini -d memory_limit=512M $wp --path=$path eval-file "D:\GitHub\accepteddev-task-wp\scripts\test-wc-api.php"
