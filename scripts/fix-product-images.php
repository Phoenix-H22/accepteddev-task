<?php
$map = [
    21 => 20,
    23 => 22,
    25 => 24,
];

foreach ( $map as $product_id => $attachment_id ) {
    $product = wc_get_product( $product_id );
    if ( ! $product ) {
        WP_CLI::warning( "Product {$product_id} not found." );
        continue;
    }

    $product->set_image_id( $attachment_id );
    $product->save();

    WP_CLI::success(
        sprintf(
            'Product %d (%s) image set to attachment %d.',
            $product_id,
            $product->get_name(),
            $attachment_id
        )
    );
}
