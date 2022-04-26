<?php
if( !class_exists( 'CCFWC_Webhook' ) ):
class CCFWC_Webhook {
	
	/**
    * Register Rest route call-back
    * @since 1.4
    * @version 1.0
    */
	public function __construct() {
    	add_action( 'rest_api_init', array( $this, 'register_rest_route' ) );
    }
    
    /**
    * Register Rest route call-back
    * @since 1.4
    * @version 1.0
    */
    public function register_rest_route()
    {
        register_rest_route( 'ccfwc/v1', '/complete-payment', array(
            'methods'   => 'POST',
            'callback'  => array( $this, 'complete_payment' ),
            'permission_callback' => function () {
            return true; // security can be done in the handler
            }
        ));
    }
    
    /**
    * Completes payment
    * @since 1.4
    * @version 1.0
    */
    public function complete_payment()
    {
        $payload = @file_get_contents('php://input');
		
		$payload = json_decode( $payload );
		
		if( $payload->event->type != 'charge:confirmed' ) return;
		
		$unique_id = $payload->event->data->checkout->id;
		$order_id = get_option( $unique_id );
		
		if( $order_id )
		{
			$order = new WC_Order( $order_id );
		
			$order->update_status( 'completed', 'Coinbase Commerce Webhook (Charged)' );
			
			delete_option( $order_id );
			
			wp_send_json_success( array(), 200 );
		}
		
			wp_send_json( array( 'message'	=>	'No order associated with this ID.' ), 404 );
    }
}

new CCFWC_Webhook();
endif;