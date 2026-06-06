$php = "C:\Users\abdal\AppData\Roaming\Local\lightning-services\php-8.2.29+0\bin\win64\php.exe"
$ini = "D:\GitHub\accepteddev-task-wp\conf\php\cli-php.ini"
$wp = "D:\GitHub\accepteddev-task-wp\app\public\wp-cli.phar"
$path = "D:\GitHub\accepteddev-task-wp\app\public"
$imgDir = "$path\wp-content\uploads\product-seed"

$products = @(
    @{
        file = "headphones.jpg"
        name = "Wireless Bluetooth Headphones"
        sku = "WBH-001"
        price = "79.99"
        short = "Over-ear wireless headphones with active noise cancellation."
        description = "Comfortable over-ear design, 30-hour battery life, and crisp audio for work or travel."
    },
    @{
        file = "tshirt.jpg"
        name = "Organic Cotton T-Shirt"
        sku = "OCT-002"
        price = "24.99"
        short = "Soft unisex tee made from 100% organic cotton."
        description = "Breathable everyday tee available in multiple sizes. Pre-shrunk fabric with a relaxed fit."
    },
    @{
        file = "mug.jpg"
        name = "Ceramic Coffee Mug"
        sku = "CCM-003"
        price = "14.99"
        short = "12oz ceramic mug, dishwasher and microwave safe."
        description = "Classic white ceramic mug with a sturdy handle, perfect for home or office."
    }
)

$created = @()

foreach ($p in $products) {
    $imagePath = Join-Path $imgDir $p.file
    if (-not (Test-Path $imagePath)) {
        Write-Error "Missing image file: $imagePath"
        continue
    }

    $imageId = & $php -c $ini -d memory_limit=512M $wp --path=$path media import $imagePath --porcelain --title="$($p.name)"
    if (-not $imageId -or $imageId -match "Error") {
        Write-Error "Failed to import image for $($p.name): $imageId"
        continue
    }

    $productId = & $php -c $ini -d memory_limit=512M $wp --path=$path wc product create `
        --user=1 `
        --name="$($p.name)" `
        --type=simple `
        --status=publish `
        --sku="$($p.sku)" `
        --regular_price="$($p.price)" `
        --short_description="$($p.short)" `
        --description="$($p.description)" `
        --porcelain

    if (-not $productId -or $productId -match "Error") {
        Write-Error "Failed to create product $($p.name): $productId"
        continue
    }

    $created += [pscustomobject]@{
        ProductId = [int]$productId
        ImageId = [int]$imageId
        Name = $p.name
    }

    Write-Host "Created $($p.name) (product: $productId, image: $imageId)"
}

if ($created.Count -gt 0) {
    $fixScript = "D:\GitHub\accepteddev-task-wp\scripts\fix-product-images.php"
    $fixContent = @'
<?php
$map = [
PLACEHOLDERS
];
foreach ( $map as $product_id => $attachment_id ) {
    $product = wc_get_product( $product_id );
    if ( ! $product ) {
        continue;
    }
    $product->set_image_id( $attachment_id );
    $product->save();
}
'@
    $pairs = ($created | ForEach-Object { "    $($_.ProductId) => $($_.ImageId)," }) -join "`n"
    Set-Content -Path $fixScript -Value ($fixContent -replace 'PLACEHOLDERS', $pairs)
    & $php -c $ini -d memory_limit=512M $wp --path=$path eval-file $fixScript
}

& $php -c $ini -d memory_limit=512M $wp --path=$path option update woocommerce_coming_soon no | Out-Null
& $php -c $ini -d memory_limit=512M $wp --path=$path option update woocommerce_store_pages_only no | Out-Null
& $php -c $ini -d memory_limit=512M $wp --path=$path cache flush | Out-Null

& $php -c $ini -d memory_limit=512M $wp --path=$path wc product list --user=1 --fields=id,name,sku,price,status --format=table
