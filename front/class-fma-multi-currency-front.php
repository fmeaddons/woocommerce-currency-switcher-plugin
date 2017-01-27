<?php 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'FMA_Multi_Currency_Front' ) ) {

	class FMA_Multi_Currency_Front extends FMA_Multi_Currency {

		function __construct() {


			add_action( 'wp_loaded', array( $this, 'front_init' ) );

			add_filter( 'woocommerce_get_price_html', array($this, 'changePrice' ), 100, 2);
			add_filter( 'woocommerce_cart_item_price', array($this, 'changeCartPrice' ), 100, 3);
			add_filter('woocommerce_cart_item_subtotal', array($this, 'changeCartItemSubtotal'), 100, 2);
			add_filter('woocommerce_cart_subtotal', array($this, 'changeCartSubtotal'), 100, 3);
			add_filter('woocommerce_cart_shipping_method_full_label', array($this, 'chnageShippingPrice'), 100, 2);
			add_filter('woocommerce_cart_tax_totals', array($this, 'changeCartTexPrice'), 100, 2);
			add_filter('woocommerce_cart_total', array($this, 'changeCartTotal'), 100, 1);
			add_action('parse_request', array($this, 'show_query'), 10, 1);
			
			
		}

		public function front_init() {	
           
	        wp_enqueue_style( 'fmamc-front-css', plugins_url( '/css/fmamc_front_style.css', __FILE__ ), false );
	        wp_enqueue_script('jquery');
	        wp_enqueue_script( 'fmamc-front-script', plugins_url( '/js/fmamc_front_script.js', __FILE__ ), false );

	        wp_localize_script( 'fmamc-front-script', 'fma_ajax_url',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

           

		}

		function show_query($wp){
		     if ( ! is_admin() ){ // heck we don't need the admin pages
		     	if(isset($wp->query_vars['pagename']) && $wp->query_vars['pagename']!='') {
		     		$pname = $wp->query_vars['pagename'];
		     	} else {
		     		$pname = '';
		     	}
		         
		         if($pname == 'cart') { ?>
		         <style type="text/css">
					.mini_car { margin-left: 0 !important;
				    margin-top: 3px  !important;
				    position: relative;
				    width: auto;}
		         </style>
				
		         <?php }
		         //$wp->query_vars['name'];
		     }
		}

		public function changePrice( $price, $product) {

			global $wpdb;
			if(isset($_SESSION['currency']) && $_SESSION['currency']!='') {
				$currency = $_SESSION['currency'];
			} else {
				$currency = get_woocommerce_currency();
			}
		        
		    $query_chk = "SELECT * FROM ".$wpdb->prefix . "fma_currency where currency_code = '".$currency."'";
			$query_chk_list = $wpdb->get_row( $query_chk, ARRAY_A );

			$decimails = wc_get_price_decimals();
			
			if($query_chk_list!='') { ?>
				<span class="price">
				<?php if($product->sale_price!='') { ?>

					<del>
						<span class="woocommerce-Price-currencySymbol"><?php echo $query_chk_list['currency_symbol']; ?></span>
						<?php echo number_format($product->price * $query_chk_list['currency_value'], $decimails); ?>
					</del>

					<ins>
						<span class="woocommerce-Price-amount amount">
						<span class="woocommerce-Price-currencySymbol"><?php echo $query_chk_list['currency_symbol']; ?></span>
						<?php echo number_format($product->regular_price * $query_chk_list['currency_value'], $decimails); ?>
						</span>
					</ins>

				<?php } else { ?>

				<ins>
				<span class="woocommerce-Price-amount amount">
					<span class="woocommerce-Price-currencySymbol"><?php echo $query_chk_list['currency_symbol']; ?></span>
					<?php echo number_format($product->price * $query_chk_list['currency_value'], $decimails); ?>
					</span>
				</ins>

				<?php  } ?>
				
				</span>

			<?php } else {
				return $price;
			}

		}


		public function changeCartPrice($price, $cart_item) {
			 
			global $wpdb;
			if(isset($_SESSION['currency']) && $_SESSION['currency']!='') {
				$currency = $_SESSION['currency'];
			} else {
				$currency = get_woocommerce_currency();
			}
		        
		    $query_chk = "SELECT * FROM ".$wpdb->prefix . "fma_currency where currency_code = '".$currency."'";
			$query_chk_list = $wpdb->get_row( $query_chk, ARRAY_A );
			echo $this->show_query($wp);
			$decimails = wc_get_price_decimals();
			if($query_chk_list!='') {



				$cart_item_price = ($cart_item['line_total'] / $cart_item['quantity']);
				
				?>
				
				<p class='mini_car'><?php echo $query_chk_list['currency_symbol'].''.number_format($cart_item_price * $query_chk_list['currency_value'], $decimails); ?></p>
				

			<?php  } else {
				return $price;
			}

		}


		public function changeCartItemSubtotal($subtotal, $cart_item) {

			global $wpdb;
			if(isset($_SESSION['currency']) && $_SESSION['currency']!='') {
				$currency = $_SESSION['currency'];
			} else {
				$currency = get_woocommerce_currency();
			}
		        
		    $query_chk = "SELECT * FROM ".$wpdb->prefix . "fma_currency where currency_code = '".$currency."'";
			$query_chk_list = $wpdb->get_row( $query_chk, ARRAY_A );
			
			$decimails = wc_get_price_decimals();
			if($query_chk_list!='') {

				
				echo $query_chk_list['currency_symbol'].''.number_format($cart_item['line_subtotal'] * $query_chk_list['currency_value'], $decimails);

			} else {
				return $subtotal;
			}
		}

		public function changeCartSubtotal($subtotal, $data, $item) {

			global $wpdb;
			if(isset($_SESSION['currency']) && $_SESSION['currency']!='') {
				$currency = $_SESSION['currency'];
			} else {
				$currency = get_woocommerce_currency();
			}
		        
		    $query_chk = "SELECT * FROM ".$wpdb->prefix . "fma_currency where currency_code = '".$currency."'";
			$query_chk_list = $wpdb->get_row( $query_chk, ARRAY_A );
			
			$decimails = wc_get_price_decimals();
			if($query_chk_list!='') {

				$price = 0;
	            if ($data)
	            {
	                $amount = $item->cart_contents_total + $item->shipping_total + $item->get_taxes_total(false, false);
	            } else
	            {

	                if ($item->tax_display_cart == 'excl')
	                {
	                    $price = $item->subtotal_ex_tax;
	                } else
	                {
	                    $price = $item->subtotal;
	                }
	            }

	            echo $query_chk_list['currency_symbol'].''.number_format($price * $query_chk_list['currency_value'], $decimails);
				

			} else {
				return $subtotal;
			}
		}



		public function chnageShippingPrice($text, $type) {

			if ($type->cost > 0) {


				global $wpdb;
				if(isset($_SESSION['currency']) && $_SESSION['currency']!='') {
					$currency = $_SESSION['currency'];
				} else {
					$currency = get_woocommerce_currency();
				}
			        
			    $query_chk = "SELECT * FROM ".$wpdb->prefix . "fma_currency where currency_code = '".$currency."'";
				$query_chk_list = $wpdb->get_row( $query_chk, ARRAY_A );
				
				$decimails = wc_get_price_decimals();
				if($query_chk_list!='') {

					if (WC()->cart->tax_display_cart == 'excl') {
	                    $price = $type->cost;
	                } else {
	                    $price = $type->cost + $type->get_shipping_tax();
	                }
	                echo $type->label.': ';
					echo $query_chk_list['currency_symbol'].''.number_format($price * $query_chk_list['currency_value'], $decimails);

				} else {
					return $text;
				}

			}
		}



		public function changeCartTexPrice($total, $data) {

			ini_set('display_errors',0);
			global $wpdb;
			if(isset($_SESSION['currency']) && $_SESSION['currency']!='') {
				$currency = $_SESSION['currency'];
			} else {
				$currency = get_woocommerce_currency();
			}
		        
		    $query_chk = "SELECT * FROM ".$wpdb->prefix . "fma_currency where currency_code = '".$currency."'";
			$query_chk_list = $wpdb->get_row( $query_chk, ARRAY_A );
			
			$decimails = wc_get_price_decimals();
			if($query_chk_list!='') {

				if (!empty($total)) {
	                foreach ($total as $key => $val) {

	                    $price = $val->amount;
	             ?>

	             <tr class="tax-rate">
					<th><?php echo $val->label ?></th>
					<td><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol"><?php echo $query_chk_list['currency_symbol']; ?></span><?php echo number_format($price * $query_chk_list['currency_value'], $decimails); ?></span></td>
				 </tr>

	             <?php

	                }
	            }

			} else {
				return $total;
			}

		}


		public function changeCartTotal($value) {

			global $wpdb;
			if(isset($_SESSION['currency']) && $_SESSION['currency']!='') {
				$currency = $_SESSION['currency'];
			} else {
				$currency = get_woocommerce_currency();
			}
		        
		    $query_chk = "SELECT * FROM ".$wpdb->prefix . "fma_currency where currency_code = '".$currency."'";
			$query_chk_list = $wpdb->get_row( $query_chk, ARRAY_A );
			
			$decimails = wc_get_price_decimals();
			if($query_chk_list!='') {

				echo "<b>".$query_chk_list['currency_symbol'].''.number_format(WC()->cart->total * $query_chk_list['currency_value'], $decimails)."</b>";

			} else {
				return $value;
			}
		}


		

			

	}

	new FMA_Multi_Currency_Front();
}


?>