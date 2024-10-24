<?php
/**
 * Main functions
 *
 * @package WooCommercePaypalBGN
 * @author Atanas Antonov
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Check if requirements are ok.
 *
 * @return bool
 */
function woo_paypal_bgn_requirements() {
	// Check if all requirements are ok.
	$woocommerce_version = defined( 'WC_VERSION' ) && version_compare( WC_VERSION, WOO_PAYPAL_BGN_MIN_WC_VER, '>=' );
	$php_version         = version_compare( PHP_VERSION, WOO_PAYPAL_BGN_MIN_PHP_VER, '>=' );

	return $woocommerce_version && $php_version;
}


/**
 * Requirements error message.
 *
 * @return void
 */
function woo_paypal_bgn_requirements_error() {
	global $pagenow;

	if ( 'plugins.php' !== $pagenow ) {
		return;
	}

	?>
	<div class="notice notice-error">
		<p>
			<?php
			printf(
				// translators: %1$s is plugin name, %2$s is PHP version, %3$s is WooCommerce version.
				esc_html__( '%1$s needs PHP version %2$s and WooCommerce version %3$s or newer.', 'woocommerce-paypal-bgn' ),
				'<strong>' . esc_html( WOO_PAYPAL_BGN_NAME ) . '</strong>',
				esc_html( WOO_PAYPAL_BGN_MIN_PHP_VER ),
				esc_html( WOO_PAYPAL_BGN_MIN_WC_VER )
			);
			?>
			</strong>
		</p>
	</div>
	<?php
}


/**
 * Plugin initialization.
 *
 * @return void
 */
function woo_paypal_bgn_init() {
	// Load the textdomain.
	load_plugin_textdomain( 'woocommerce-paypal-bgn', false, plugin_basename( dirname( __FILE__, 2 ) ) . '/languages' );

	// Hook filters and actions.
	add_filter( 'woocommerce_paypal_supported_currencies', 'woo_paypal_bgn_supported_currencies' );
	add_filter( 'woocommerce_paypal_args', 'woo_paypal_bgn_paypal_args' );
	add_filter( 'woocommerce_new_order_note_data', 'woo_paypal_bgn_fix_order_status', 10, 2 );
}


/**
 * Declare High-Performance Order Storage (HPOS) compatibility.
 *
 * @return void
 */
function woo_paypal_bgn_declare_wc_compatibility() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', WOO_PAYPAL_BGN_PATH . 'zota-for-woocommerce.php', true );
	}
}


/**
 * Add BGN currency to PayPal valid currencies.
 *
 * @param array $currencies PayPal valid currencies.
 *
 * @return array
 */
function woo_paypal_bgn_supported_currencies( $currencies ) {
	array_push ( $currencies , 'BGN' );

	return $currencies;
}

/**
 * Convert BGN to EUR
 *
 * @param array $paypal_args PayPal arguments.
 *
 * @return array
 */
function woo_paypal_bgn_paypal_args($paypal_args){
	if ( $paypal_args['currency_code'] !== 'BGN') {
		return $paypal_args;
	}

	$paypal_args['currency_code'] = 'EUR'; // Change BGN to EUR.
	$i = 1;

	while ( isset( $paypal_args['amount_' . $i] ) ) {
		$paypal_args['amount_' . $i] = round( $paypal_args['amount_' . $i] / WOO_PAYPAL_BGN_CONVERSION_RATE, 2 );
		$i++;
	}

	if ( $paypal_args['shipping_1'] > 0 ) {
		$paypal_args['shipping_1'] = round( $paypal_args['shipping_1'] / WOO_PAYPAL_BGN_CONVERSION_RATE, 2 );
	}

	if ( $paypal_args['discount_amount_cart'] > 0 ) {
		$paypal_args['discount_amount_cart'] = round( $paypal_args['discount_amount_cart'] / WOO_PAYPAL_BGN_CONVERSION_RATE, 2 );
	}

	return $paypal_args;
}



function woo_paypal_bgn_fix_order_status($comment, $order_note)
{
    // Check order note content for the specific message.
    if ( strpos($comment['comment_content'],'PayPal валутите не съвпадат') !== false
    || strpos($comment['comment_content'],'PayPal currencies do not match') !== false )
    {
        // Get WC_Order object.
        $order = wc_get_order($order_note['order_id']);

        // Change status to processing and add an optional note.
        if( $order->status == 'on-hold' ) {
			$order->update_status( 
				'processing', 
				sprintf(
					// translators: %1$s is plugin name, %2$s is PHP version, %3$s is WooCommerce version.
					esc_html__( 'Changed to processing by %s.', 'woocommerce-paypal-bgn' ),
					esc_html( WOO_PAYPAL_BGN_NAME )
				)
			);
		}
    }

    return $comment;
}



/**
 * Deactivate plugin.
 *
 * @return void
 */
function woo_paypal_bgn_deactivate() {}
