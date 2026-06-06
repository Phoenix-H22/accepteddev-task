<?php
$cfg = json_decode( file_get_contents( dirname( __DIR__ ) . '/integration/woocommerce-api.local.json' ), true );

$tests = [
    'query_auth' => add_query_arg(
        [
            'consumer_key'    => $cfg['consumer_key'],
            'consumer_secret' => $cfg['consumer_secret'],
            'status'          => 'processing',
            'per_page'        => 10,
        ],
        $cfg['api_base'] . '/orders'
    ),
    'basic_auth' => $cfg['api_base'] . '/orders?status=processing&per_page=10',
];

foreach ( $tests as $label => $url ) {
    $args = [ 'timeout' => 20 ];
    if ( 'basic_auth' === $label ) {
        $args['headers'] = [
            'Authorization' => 'Basic ' . base64_encode( $cfg['consumer_key'] . ':' . $cfg['consumer_secret'] ),
        ];
    }

    $response = wp_remote_get( $url, $args );
    $code     = wp_remote_retrieve_response_code( $response );
    $body     = wp_remote_retrieve_body( $response );

    WP_CLI::log( strtoupper( $label ) . " => HTTP {$code}" );
    if ( 200 === (int) $code ) {
        $data = json_decode( $body, true );
        foreach ( $data as $order ) {
            WP_CLI::log(
                sprintf(
                    '- Order #%d | %s %s | %s %s',
                    $order['id'],
                    $order['billing']['first_name'],
                    $order['billing']['last_name'],
                    $order['total'],
                    $order['currency']
                )
            );
        }
    } else {
        WP_CLI::log( substr( $body, 0, 300 ) );
    }
}
