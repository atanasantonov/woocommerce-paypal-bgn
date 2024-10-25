<?php
/**
 * Plugin Name: WooCommerce Paypal BGN
 * Description: Add support of BGN currency for PayPal in WooCommerce
 * Requires Plugins: woocommerce, woocommerce-paypal-payments
 * Version: 1.0.0
 * Author: Atanas Antonov
 * Tags: translation-ready
 * Text Domain: woocommerce-paypal-bgn
 * Domain Path: /languages
 * License: LICENSE
 *
 * Requires at least: 4.9.8
 * Requires PHP: 8.2
 *
 * @package WooCommercePaypalBGN
 * @author Atanas Antonov
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'WOO_PAYPAL_BGN_NAME', 'WooCommerce Paypal BGN' );
define( 'WOO_PAYPAL_BGN_MIN_PHP_VER', '8.2.0' );
define( 'WOO_PAYPAL_BGN_MIN_WC_VER', '3.0' );
define( 'WOO_PAYPAL_BGN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WOO_PAYPAL_BGN_URL', plugin_dir_url( __FILE__ ) );

define( 'WOO_PAYPAL_BGN_CONVERSION_RATE', 1.9585 );

// Includes.
require_once WOO_PAYPAL_BGN_PATH . 'functions.php';

// Check requirements.
add_action( 'init', 'woo_paypal_bgn_init' );
add_action( 'before_woocommerce_init', 'woo_paypal_bgn_declare_wc_compatibility' );

/**
 * Register deactivation hook.
 */
register_deactivation_hook( __FILE__, 'woo_paypal_bgn_deactivate' );
