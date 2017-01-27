<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class FMAMC_Multi_Currency extends WP_Widget {

	var $widget_description;
	var $widget_id;
	var $widget_name;

	function __construct() {

		$this->widget_name 			= __('FMA Multi Currency', 'fmamc' );
		$this->widget_description 	= __( 'Show a drop down for currency switch.', 'fmamc' );
		$this->widget_id		= 'fmamc_multi_currency';

		$widget_data = array( 'description' => $this->widget_description );

		parent::__construct($this->widget_id, $this->widget_name, $widget_data );
		

	}


	/*Update widget entries*/
	function update( $new_data, $old_data ) {
		$data['title'] = strip_tags( stripslashes( $new_data['title'] ) );
		if(isset($new_data['show_name']) && $new_data['show_name']!='') {
			$data['show_name'] = strip_tags( stripslashes( $new_data['show_name'] ) );
		} else {
			$data['show_name'] = '';
		}

		if(isset($new_data['orderby']) && $new_data['orderby']!='') {
			$data['orderby'] = strip_tags( stripslashes( $new_data['orderby'] ) );
		} else {
			$data['orderby'] = '';
		}
	
		if ( ! $data['orderby'] )
			$data['orderby'] = 'name';

		if ( ! $data['show_name'] )
			$data['show_name'] = 0;
		
		return $data;
	}


	/* Widget Entry Form*/
	function form( $data ) {
		
		if ( ! isset( $data['show_name'] ) ) 
			$data['show_name'] = 0;
		
		?>
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'fmamc') ?></label>
				<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php if ( isset ( $data['title'] ) ) echo esc_attr( $data['title'] ); ?>" />
			</p>
			
			
		<?php
	}



	/* Show Widget on Front End*/
	function widget( $args, $data ) { 
		extract( $args );
		global $wpdb;

		if(isset($data['exclude']) && $data['exclude']!='') {
			$dataexclude = $data['exclude'];
		} else {
			$dataexclude = '';
		}
		$exclude = array_map( 'intval', explode( ',', $dataexclude  ) );
		$order = $data['orderby'] == 'currency_name' ? 'asc' : 'desc';

		$currencies = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."fma_currency WHERE currency_status = %s ORDER BY currency_name", 'published'));
	
		//echo $_SESSION["currency"];

		if(isset($_SESSION['currency']) && $_SESSION['currency']!='') {
			$currs = $_SESSION['currency'];
		} else {
			$currs = get_woocommerce_currency();
		}

		if ( ! $currencies ) 
		return;

	        $title = apply_filters( 'widget_title', $data['title'], $data, $this->widget_id );
	
	        echo $args['before_widget'];
	        if ( $title !== '' ) {
	        	echo $args['before_title'] . $title . $args['after_title'];
	        }
	      
	    if ( ! empty( $currencies ) ) { ?>
	    <a id="acart" class="button product_type_simple add_to_cart_button ajax_add_to_cart" data-product_sku="" data-product_id="879546545524" data-quantity="" href="/woocommerce_test/shop/?add-to-cart=0" rel="nofollow">Add to cart</a>
	    <div class="select_style">
		<select name="currency" onchange="setCurrency(this.value)">
		<option value="<?php echo get_woocommerce_currency(); ?>" <?php echo selected(get_woocommerce_currency(), $currs) ?>><?php echo get_woocommerce_currency(); ?> (<?php echo get_woocommerce_currency_symbol(get_woocommerce_currency()); ?>)</option>
	    <?php foreach($currencies as $currency) { ?>
			
				<option value="<?php echo $currency->currency_code ?>" <?php echo selected($currency->currency_code, $currs) ?>><?php echo $currency->currency_code; ?> (<?php echo $currency->currency_symbol; ?>)</option>
			
	    <?php } ?>
	    </select>
	    <span></span>
	    </div>


		


		<?php } 

		echo $after_widget;
	}

}