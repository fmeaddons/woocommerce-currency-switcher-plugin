<?php 
session_start();
/*
 * Plugin Name:       FMA Woo Multi Currency
 * Plugin URI:        https://www.fmeaddons.com/woocommerce-plugins-extensions/multi-currency-switcher-plugin.html
 * Description:       Woocommerce currency switcher adds multiple currencies to your store and displays product prices according to geographical preferences.
 * Version:           1.0.1
 * Author:            FME Addons
 * Developed By:  	  Raja Usman Mehmood
 * Author URI:        http://fmeaddons.com/
 * Support:		  	  http://support.fmeaddons.com/
 * Text Domain:       fmamc
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Check if WooCommerce is active
 * if WooCommerce is not active FMA Multi Currency module will not work.
 **/
if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	
	function my_admin_notice() {

		// Deactivate the plugin
		   deactivate_plugins(__FILE__);
	$error_message = __('This plugin requires <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> plugin to be installed and active!', 'woocommerce');
	die($error_message);
}
add_action( 'admin_notices', 'my_admin_notice' );
}

if ( !class_exists( 'FMA_Multi_Currency' ) ) {

	class FMA_Multi_Currency {

		function __construct() {

			$this->module_constants();
			$this->module_tables();

			add_action( 'wp_loaded', array( $this, 'init' ) );
			if ( is_admin() ) {
				add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array($this, 'plugin_action_links' ));
				require_once( FMAMC_PLUGIN_DIR . 'admin/class-fma-multi-currency-admin.php' );
				add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 1 );
				add_action('wp_ajax_fma_multi_currency_rated', array($this, 'fma_multi_currency_rated')); 
			} else {

				require_once( FMAMC_PLUGIN_DIR . 'front/class-fma-multi-currency-front.php' );

				
			}

			add_action('wp_ajax_setChoosenCurrency', array($this, 'setChoosenCurrency'));
			add_action('wp_ajax_nopriv_setChoosenCurrency', array($this, 'setChoosenCurrency'));



			

			//This will create Widgets for the multi currency
			add_action( 'widgets_init', array( $this, 'create_widgets' ) );
		}

		public function module_constants() {


            if ( !defined( 'FMAMC_URL' ) )
                define( 'FMAMC_URL', plugin_dir_url( __FILE__ ) );

            if ( !defined( 'FMAMC_BASENAME' ) )
                define( 'FMAMC_BASENAME', plugin_basename( __FILE__ ) );

            if ( ! defined( 'FMAMC_PLUGIN_DIR' ) )
                define( 'FMAMC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
        }



        public function module_tables() {
            
			global $wpdb;
			
			$charset_collate = '';
			$wpdb->fma_currency = $wpdb->prefix . 'fma_currency';
			if ( !empty( $wpdb->charset ) )
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			if ( !empty( $wpdb->collate ) )
				$charset_collate .= " COLLATE $wpdb->collate";	
				
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$wpdb->fma_currency'" ) != $wpdb->fma_currency ) {
				$sql = "CREATE TABLE " . $wpdb->fma_currency . " (
									 currency_id int(25) NOT NULL auto_increment,
									 currency_name varchar(255) NULL,
									 currency_code varchar(255) NULL,
									 currency_symbol varchar(255) NULL,
									 currency_value varchar(255) NULL,
									 currency_status varchar(255) NULL,
									 date_modified date NULL,
									 
									 PRIMARY KEY (currency_id)
									 ) $charset_collate;";
		
			
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				dbDelta( $sql );
			}

		}


        function plugin_action_links( $actions ) {
		
			$custom_actions = array();
		
			// support url
			$custom_actions['support'] = sprintf( '<a href="%s" target="_blank">%s</a>', 'http://support.fmeaddons.com/', __( 'Support', 'fmamc' ) );
			
			// add the links to the front of the actions list
			return array_merge( $custom_actions, $actions );
			
		}

		function init() {
	        if ( function_exists( 'load_plugin_textdomain' ) )
	            load_plugin_textdomain( 'fmamc', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	   	}

	   	/**
		 * Change the admin footer text on admin pages.
		 * This function is get from woocommerce admin
		 * @since  2.3
		 * @param  string $footer_text
		 * @return string
		 */
		public function admin_footer_text( $footer_text ) { 
			

			// Check to make sure we're on a WooCommerce admin page
			if ( apply_filters( 'woocommerce_display_admin_footer_text', $footer_text ) ) {
				// Change the footer text
				if ( ! get_option( 'fma_multi_currency_rated_text' ) ) {
					$footer_text = sprintf( __( 'If you like <strong>FMA Multi Currency</strong> please leave us a %s&#9733;&#9733;&#9733;&#9733;&#9733;%s rating. A huge thank you from FME Addons in advance!', 'woocommerce' ), '<a href="https://www.fmeaddons.com" target="_blank" class="wc-rating-link" data-rated="' . esc_attr__( 'Thanks :)', 'woocommerce' ) . '">', '</a>' );
					wc_enqueue_js( "
						jQuery( 'a.wc-rating-link' ).click( function() { 
							jQuery.post( '" . WC()->ajax_url() . "', { action: 'fma_multi_currency_rated' } );
							jQuery( this ).parent().text( jQuery( this ).data( 'rated' ) );
						});
					" );
				} else {
					$footer_text = __( 'Thank you for buying with FME Addons', 'woocommerce' );
				}
			}

			return $footer_text;
		}


		function fma_multi_currency_rated() {

			update_option( 'fma_multi_currency_rated_text', 1 );
		}


		/* Create widgets for multi currency*/
		function create_widgets() {

			require_once( FMAMC_PLUGIN_DIR.'widgets/fmamc_multi_currency_widget.php' );
			register_widget( 'FMAMC_Multi_Currency' );

		}


		function setChoosenCurrency() { 
			
			
			$_SESSION['currency'] = $_POST['currency_name']; 

		   
			die();
		}

		

		


	}

	$fmamc = new FMA_Multi_Currency();

}
