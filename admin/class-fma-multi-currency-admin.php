<?php 
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	if ( !class_exists( 'FMA_Multi_Currency_Admin' ) ) {

		class FMA_Multi_Currency_Admin extends FMA_Multi_Currency {

			public function __construct() {
				add_action( 'wp_loaded', array( $this, 'admin_init' ) );
				add_action('wp_ajax_getCurrencyValue', array($this, 'getCurrencyValue'));
			}


			public function admin_init() {
				add_action( 'admin_menu', array( $this, 'create_admin_menu' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );	
			}

			public function admin_scripts() {	
            	
            	wp_enqueue_style( 'fmamc-admin-css', plugins_url( '/css/fmamc_admin_style.css', __FILE__ ), false );
	        	wp_enqueue_style( 'fmamc-select2-css', plugins_url( '/css/select2.min.css', __FILE__ ), false );
	        	wp_enqueue_script('fmamc-select2-js', plugins_url( '/js/select2.min.js', __FILE__ ), false );
	        	
	        }

			public function create_admin_menu() {	
				
				add_menu_page('FMA Multi Currency', __( 'Multi Currency', 'fmamc' ), 'manage_options', 'fma-multi-currency', array( $this, 'fma_multi_currency_module' ) ,plugins_url( 'images/fma.png', dirname( __FILE__ ) ), apply_filters( 'fmamc_menu_position', 30 ) );


		    }

		    function fma_multi_currency_module() {

		    	$allcurrencies = get_woocommerce_currencies();
		    	require_once( FMAMC_PLUGIN_DIR . 'admin/currency_form.php' );

		    }

		    function getCurrencyValue() {
		    	

		    	 $currency_input = 1;
				 //currency codes : http://en.wikipedia.org/wiki/ISO_4217
				 $currency_from = get_woocommerce_currency();
				 $currency_to = sanitize_text_field($_POST['currency_name']);
				 $currency = $this->currencyConverter($currency_from,$currency_to,$currency_input);

				 echo number_format($currency,2);

		    	die();
		    }


		    function currencyConverter($currency_from,$currency_to,$currency_input){
			    $yql_base_url = "http://query.yahooapis.com/v1/public/yql";
			    $yql_query = 'select * from yahoo.finance.xchange where pair in ("'.$currency_from.$currency_to.'")';
			    $yql_query_url = $yql_base_url . "?q=" . urlencode($yql_query);
			    $yql_query_url .= "&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";
			    $yql_session = curl_init($yql_query_url);
			    curl_setopt($yql_session, CURLOPT_RETURNTRANSFER,true);
			    $yqlexec = curl_exec($yql_session);
			    $yql_json =  json_decode($yqlexec,true);
			    $currency_output = (float) $currency_input*$yql_json['query']['results']['rate']['Rate'];

			    return $currency_output;
			}


		    

		}

		new FMA_Multi_Currency_Admin();

	}
?>