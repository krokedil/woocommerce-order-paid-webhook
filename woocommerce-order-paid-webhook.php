<?php // phpcs:ignore
/**
 * Plugin Name:     WooCommerce Order paid webhook
 * Plugin URI:      http://krokedil.com/
 * Description:     Provides a custom Order paid webhook that is triggered when woocommerce_payment_complete is executed.
 * Version:         1.0
 * Author:          Krokedil
 * Author URI:      http://krokedil.com/
 * Text Domain:     woocommerce-order-paid-webhook
 * Domain Path:     /languages
 *
 * WC requires at least: 5.0.0
 * WC tested up to: 5.7.1
 *
 * Copyright:       © 2021 Krokedil.
 * License:         GNU General Public License v3.0
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.html
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'WOPW_VERSION', '1.4.2' );
define( 'WOPW_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) );
define( 'WOPW_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );


/**
 * Plugin main class.
 */
class WOPW {

	/**
	 * The reference the *Singleton* instance of this class.
	 *
	 * @var $instance
	 */
	protected static $instance;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	/**
	 * Returns the *Singleton* instance of this class.
	 *
	 * @return self::$instance The *Singleton* instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private clone method to prevent cloning of the instance of the
	 * *Singleton* instance.
	 *
	 * @return void
	 */
	public function __clone() {
		wc_doing_it_wrong( __FUNCTION__, __( 'Nope' ), '1.0' );
	}
		/**
		 * Private unserialize method to prevent unserializing of the *Singleton*
		 * instance.
		 *
		 * @return void
		 */
	public function __wakeup() {
		wc_doing_it_wrong( __FUNCTION__, __( 'Nope' ), '1.0' );
	}

	/**
	 * Initiates the plugin.
	 *
	 * @return void
	 */
	public function init() {
		load_plugin_textdomain( 'woocommerce-order-paid-webhook', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		add_filter( 'woocommerce_webhook_topics', array( $this, 'add_new_webhook_topics' ) );
		add_filter( 'woocommerce_webhook_topic_hooks', array( $this, 'add_new_topic_hooks' ) );
		add_filter( 'woocommerce_valid_webhook_events', array( $this, 'add_new_topic_events' ) );
		add_action( 'woocommerce_payment_complete', array( $this, 'trigger_custom_order_paid' ) );

	}

	/**
	 * Adds the new webhook to the dropdown list on the Webhook page.
	 *
	 * @param array $topic_hooks Array of topics with the i18n proper name.
	 */
	public function add_new_webhook_topics( $topic_hooks ) {

		$topic_hooks['order.paid'] = __( 'Order paid', 'woocommerce-order-paid-webhook' );

		return $topic_hooks;
	}


	/**
	 * Add a new webhook topic hook.
	 *
	 * @param array $topic_hooks Esxisting topic hooks.
	 */
	public function add_new_topic_hooks( $topic_hooks ) {

		// Array that has the topic as resource.event with arrays of actions that call that topic.
		$new_hooks = array(
			'order.paid' => array(
				'custom_order_paid',
			),
		);

		return array_merge( $topic_hooks, $new_hooks );
	}


	/**
	 * Add new events for topic resources.
	 *
	 * @param array $topic_events Existing valid events for resources.
	 */
	public function add_new_topic_events( $topic_events ) {

		// New events to be used for resources.
		$new_events = array(
			'paid',
		);

		return array_merge( $topic_events, $new_events );
	}


	/**
	 * Trigger for the custom the webbhok.
	 *
	 * @param string $order_id Woocommerce order id.
	 */
	public function trigger_custom_order_paid( $order_id ) {
		$order = wc_get_order( $order_id );
		do_action( 'custom_order_paid', $order_id, $order );
	}





}

WOPW::get_instance();

/**
 * Main instance WOPW.
 *
 * Returns the main instance of WOPW.
 *
 * @return Krokedil_Shipping_Connector
 */
function WOPW() { // phpcs:ignore
	return WOPW::get_instance();
}

//Declare compatibility HPOS
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );