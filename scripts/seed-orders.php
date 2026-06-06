<?php
/**
 * Seed WooCommerce categories, customers, and test orders.
 */

// Categories.
$categories = [
    'Electronics' => [21],
    'Apparel'     => [23],
    'Home & Kitchen' => [25],
];

foreach ( $categories as $name => $product_ids ) {
    $term = term_exists( $name, 'product_cat' );
    if ( ! $term ) {
        $term = wp_insert_term( $name, 'product_cat' );
    }
    if ( is_wp_error( $term ) ) {
        WP_CLI::warning( $term->get_error_message() );
        continue;
    }
    $term_id = (int) ( is_array( $term ) ? $term['term_id'] : $term );
    foreach ( $product_ids as $product_id ) {
        wp_set_object_terms( $product_id, [ $term_id ], 'product_cat', true );
    }
    WP_CLI::log( "Category '{$name}' assigned to products: " . implode( ', ', $product_ids ) );
}

$customers = [
    [
        'email'      => 'john.test@example.com',
        'first_name' => 'John',
        'last_name'  => 'Test',
        'username'   => 'john.test',
        'password'   => 'TestPass123!',
    ],
    [
        'email'      => 'jane.demo@example.com',
        'first_name' => 'Jane',
        'last_name'  => 'Demo',
        'username'   => 'jane.demo',
        'password'   => 'DemoPass123!',
    ],
];

$customer_ids = [];

foreach ( $customers as $customer ) {
    $user = get_user_by( 'email', $customer['email'] );
    if ( ! $user ) {
        $user_id = wc_create_new_customer(
            $customer['email'],
            $customer['username'],
            $customer['password'],
            [
                'first_name' => $customer['first_name'],
                'last_name'  => $customer['last_name'],
            ]
        );
        if ( is_wp_error( $user_id ) ) {
            WP_CLI::warning( $user_id->get_error_message() );
            continue;
        }
    } else {
        $user_id = $user->ID;
    }

    $customer_ids[] = $user_id;
    WP_CLI::success( "Customer ready: {$customer['first_name']} {$customer['last_name']} (#{$user_id})" );
}

$orders = [
    [
        'customer_index' => 0,
        'products'       => [
            [ 'product_id' => 21, 'quantity' => 1 ],
            [ 'product_id' => 25, 'quantity' => 2 ],
        ],
    ],
    [
        'customer_index' => 1,
        'products'       => [
            [ 'product_id' => 23, 'quantity' => 1 ],
        ],
    ],
];

foreach ( $orders as $index => $order_data ) {
    $customer_id = $customer_ids[ $order_data['customer_index'] ] ?? 0;
    $user        = get_user_by( 'id', $customer_id );

    $order = wc_create_order(
        [
            'customer_id' => $customer_id,
            'status'      => 'processing',
        ]
    );

    if ( is_wp_error( $order ) ) {
        WP_CLI::warning( $order->get_error_message() );
        continue;
    }

    foreach ( $order_data['products'] as $item ) {
        $product = wc_get_product( $item['product_id'] );
        if ( ! $product ) {
            continue;
        }
        $order->add_product( $product, $item['quantity'] );
    }

    $address = [
        'first_name' => $user->first_name,
        'last_name'  => $user->last_name,
        'email'      => $user->user_email,
        'phone'      => '+20100000000' . ( $index + 1 ),
        'address_1'  => ( $index + 1 ) . ' Test Street',
        'city'       => 'Cairo',
        'state'      => 'C',
        'postcode'   => '11511',
        'country'    => 'EG',
    ];

    $order->set_address( $address, 'billing' );
    $order->set_address( $address, 'shipping' );
    $order->set_payment_method( 'cod' );
    $order->set_payment_method_title( 'Cash on delivery' );
    $order->calculate_totals();
    $order->save();

    WP_CLI::success(
        sprintf(
            'Order #%d created for %s %s — total %s',
            $order->get_id(),
            $user->first_name,
            $user->last_name,
            wc_price( $order->get_total() )
        )
    );
}

WP_CLI::log( '' );
WP_CLI::log( 'Summary:' );
WP_CLI::log( '- Products: ' . wp_count_posts( 'product' )->publish );
WP_CLI::log( '- Orders: ' . wc_orders_count( 'processing' ) . ' processing' );
