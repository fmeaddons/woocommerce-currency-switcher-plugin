<?php 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<script type="text/javascript">
    function conf(str) {
       if(confirm("Are you sure you want delete") == true){ location.replace(str);}
    }

</script>

<?php 
$cu = get_woocommerce_currencies();

global $wpdb;
$action = "admin.php?page=fma-multi-currency";
$table_name = $wpdb->prefix . "fma_currency";
$query_chk_list = array();

if(isset($_POST['currencyname']) && $_POST['currencyname']!='') {
	$currency_name = sanitize_text_field($_POST['currencyname']);
} else {
	$currency_name = '';
}

if(isset($_POST['currency_name']) && $_POST['currency_name']!='') {
	$currency_code = sanitize_text_field($_POST['currency_name']);
} else {
	$currency_code = '';
}
$currency_symbol = get_woocommerce_currency_symbol($currency_code);

if(isset($_POST['currency_value']) && $_POST['currency_value']!='') {
	$currency_value = sanitize_text_field($_POST['currency_value']);
} else {
	$currency_value = '';
}

if(isset($_POST['status']) && $_POST['status']!='') {
	$status = sanitize_text_field($_POST['status']);
} else {
	$status = '';
}
$date_modified = date('Y-m-d');

if(isset($_GET['add']) && $_GET['add']!='') {
	$add = 1;
} else {
	$add = 0;
}

if(isset($_POST['check_sub']) && $_POST['check_sub'] == 1){
	if(isset($_POST['currency_id']) && $_POST['currency_id'] != '') {

		if ( !current_user_can( apply_filters( 'fmamc_capability', 'manage_options' ) ) )
            die( '-1' );
    
        check_admin_referer( 'fmamc_nonce_action', 'fmamc_nonce_field' );

        $wpdb->query($wpdb->prepare( 
				            "
		    UPDATE " .$table_name." SET currency_name = %s, currency_code = %s, currency_symbol = %s, currency_value = %s, 
		    currency_status = %s, date_modified = %s WHERE currency_id = %s
		    ",
		    $currency_name,
		    $currency_code,
		    $currency_symbol,
		    $currency_value,
		    $status,
		    $date_modified,
		    intval($_POST['currency_id'])
		));

		echo '<div class="updated below-h2"><p> Currency updated succesfully</p></div>';

	} else {


		if ( !current_user_can( apply_filters( 'fmamc_capability', 'manage_options' ) ) )
            die( '-1' );
    
        check_admin_referer( 'fmamc_nonce_action', 'fmamc_nonce_field' );

		$wpdb->query($wpdb->prepare( 
				            "
		    INSERT INTO " .$table_name."
		    (currency_name, currency_code, currency_symbol, currency_value, currency_status, date_modified)
		    VALUES (%s, %s, %s, %s, %s, %s)
		    ",
		    $currency_name,
		    $currency_code,
		    $currency_symbol,
		    $currency_value,
		    $status,
		    $date_modified
		));

		echo '<div class="updated below-h2"><p> Currency added succesfully</p></div>';

	}
}


?>

<div class="wrap">
	
	<?php if($add!=1){ ?>
		<h2><?php _e('Manage Multi Currency','fmamc'); ?>
		<a href="admin.php?page=fma-multi-currency&add=1" class="add-new-h2"><?php _e('Add New', 'fmamc'); ?></a></h2>
		<form action="" method="post">
		<p class="search-box">
			<label class="screen-reader-text" for="user-search-input"><?php _e('Search:', 'fmamc'); ?></label>
			<input type="search" id="user-search-input" name="s" value="<?php if(isset($_REQUEST['s']) && $_REQUEST['s']!='') echo $_REQUEST['s']; ?>">
			<input type="submit" name="search" id="search-submit" class="button button-primary button-large" value="Search"></p>
		</form> 
	<?php }else{ 
			if(isset($_GET['currency_id']) && $_GET['currency_id']!= ''){?>	
			<h2><?php _e('Edit Currency', 'fmamc'); ?></h2>
			<?php }else{ ?>
			<h2><?php _e('Add New Currency', 'fmamc'); ?></h2>
			<?php } ?>
	<?php } ?>

	<?php if(isset($_GET['currency_id']) && $_GET['currency_id'] != '') { 

		$query_chk = "SELECT * FROM ".$table_name." where currency_id = '".$_GET['currency_id']."'";
		$query_chk_list = $wpdb->get_row( $query_chk );

		$editaction = '&currency_id='.$_GET['currency_id'];	
		
		$action = "admin.php?page=fma-multi-currency&add=1".$editaction;

	} ?>


	<?php 

	if(isset($_GET['del_id']) && $_GET['del_id'] != ''){
		if ( !current_user_can( apply_filters( 'fmamc_capability', 'manage_options' ) ) )
			die( '-1' );
			$retrieved_nonce = $_REQUEST['_fmamcdelwpnonce'];
			if (!wp_verify_nonce($retrieved_nonce, 'delete_my_rec' ) ) die( 'Failed security check' );
		
		$res = $wpdb->query("delete from ".$table_name."  where currency_id = '".intval($_GET['del_id'])."'");
		echo '<p style="color:green">Deleted Successfully</p>';
	}

	?>


	
	<?php if($add == 1) { ?>
	<div id="poststuff" style="width: 100%; float: left">
		<div class="stuffbox" style="padding:15px 0 15px 15px;">
			<form action="<?php echo $action;?>" method="post" enctype="multipart/form-data" name="currency_form" id="currency_form">
				<?php wp_nonce_field('fmamc_nonce_action','fmamc_nonce_field'); ?>
				<table class="form-table">
					<tbody>
						
						<tr valign="top">
							<th scope="row"><label for="currency_name"><?php _e('Currency Name:','fmamc'); ?></label></th>
							<td>
								<select id="currency_name" name="currency_name" class="js-example-basic-single">
									<option value=""><?php _e('Select Currency', 'fmamc') ?></option>
									<?php foreach($allcurrencies as $curr_code => $curr_name) { 

										if(isset($query_chk_list->currency_code) && $query_chk_list->currency_code!='') { ?>

										<option value="<?php echo $curr_code; ?>" data-value="<?php echo $curr_name; ?>" <?php echo selected($query_chk_list->currency_code, $curr_code); ?>>
											<?php echo $curr_name; ?> (<?php echo get_woocommerce_currency_symbol($curr_code); ?>)
										</option>

										<?php } else { ?>
										<option value="<?php echo $curr_code; ?>" data-value="<?php echo $curr_name; ?>">
											<?php echo $curr_name; ?> (<?php echo get_woocommerce_currency_symbol($curr_code); ?>)
										</option>
										<?php } ?>
									<?php } ?>
								</select>
								<p class="fma_error" id="select_currency_first"><?php _e('Please Select Currency','fmamc'); ?></p>
							</td>
							<?php if(isset($query_chk_list->currency_name) && $query_chk_list->currency_name!='') { ?>
								<input type="hidden" name="currencyname" id="currencyname" value="<?php echo $query_chk_list->currency_name; ?>">
							<?php } else { ?>
								<input type="hidden" name="currencyname" id="currencyname" value="">
							<?php } ?>
							
						</tr>


						<tr valign="top">
							<th scope="row"><label for="currency_value"><?php _e('Value:','fmamc'); ?></label></th>
							<td>
								<?php if(isset($query_chk_list->currency_value) && $query_chk_list->currency_value!='') { ?>
									<input id="currency_value" type="text" name="currency_value" value="<?php echo $query_chk_list->currency_value; ?>">
								<?php } else { ?>
									<input id="currency_value" type="text" name="currency_value" value="">
								<?php } ?>
								
								<input type="button" value="Get Value Online" class="button button-primary button-large" onclick="updateValue()">
								<p style="font-size: 10px;"><?php _e('You can add currency value manually or you can click on get value online button to get currency value form latest forex rates from <a href="https://finance.yahoo.com/currency-converter/" target="_blank">Yahoo Finance Xchange</a>. Your currency value will be set according to base currency.','fmamc'); ?></p>
								<p class="fma_error" id="select_currency_value"><?php _e('Please Enter Currency Value','fmamc'); ?></p>
							</td>
							
						</tr>


						<tr valign="top">
							<th scope="row"><label for="base_currency"><?php _e('Base Currency:','fmamc'); ?></label></th>
							<td>
								<b><?php echo get_woocommerce_currency(); ?>(<?php echo get_woocommerce_currency_symbol(get_woocommerce_currency()); ?>)</b>
								<p style="font-size: 10px;"><?php _e('You can change base currency from woocommerce settings. Every time when you change base currency then you have to update currency value.','fmamc'); ?></p>
							</td>
							
						</tr>

						<tr valign="top">
							<th scope="row"><label for="status"><?php _e('Status:','fmamc'); ?></label></th>
							<td>
								<select name="status">
									<?php if(isset($query_chk_list->currency_status) && $query_chk_list->currency_status!='') { ?>
										<option value="Published" <?php echo selected($query_chk_list->currency_status, 'Published'); ?>><?php _e('Published', 'fmamc'); ?></option>
										<option value="Unpublished" <?php echo selected($query_chk_list->currency_status, 'Unpublished'); ?>><?php _e('Unpublished', 'fmamc'); ?></option>
									<?php } else { ?>
										<option value="Published"><?php _e('Published', 'fmamc'); ?></option>
									<option value="Unpublished"><?php _e('Unpublished', 'fmamc'); ?></option>
									<?php } ?>
									
								</select>
							</td>
							
						</tr>


						<tr valign="top">
							
							<td>
								<?php if(isset($_GET['currency_id']) && $_GET['currency_id']!='') { ?>
									<input name="currency_id" type="hidden" id="currency_id" value="<?php echo $_GET['currency_id']; ?>" class="regular-text code">
								<?php } else { ?>
									<input name="currency_id" type="hidden" id="currency_id" value="" class="regular-text code">
								<?php } ?>
								
								<input name="check_sub" type="hidden" id="check_sub" value="1" class="regular-text code">
								<p class="submit"><input type="button" onclick="vForm()" name="" id="" class="button button-primary button-large" value="Save"> </p>	
							</td>
							
						</tr>

					</tbody>
				</table>
			</form>
		</div>
	</div>
	<?php } ?>



	<?php if($add!= 1) { ?>
		
		<?php
	   		global $wpdb;
			$pagenum = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 0;
			if ( empty( $pagenum ) )
				$pagenum = 1;
		
			$per_page = (int) get_user_option( 'ms_users_per_page' );
			if ( empty( $per_page ) || $per_page < 1 )
				$per_page = 15;
		
			$per_page = apply_filters( 'ms_users_per_page', $per_page );

			if((isset($_GET['orderby']) && $_GET['orderby'] != '') && (isset($_GET['order']) && $_GET['order'] != '')){
				$orderby = 'order by '.$_GET['orderby'].' '.$_GET['order'];	
				if($_GET['order'] == 'asc'){
					$actionOrder = 'admin.php?page=fma-multi-currency&orderby=currency_name&amp;order=desc';
				}
				if($_GET['order'] == 'desc'){
					$actionOrder = 'admin.php?page=fma-multi-currency&orderby=currency_name&amp;order=asc';
				}
			}else{
				$orderby = 'order by currency_id desc';	
				$actionOrder = 'admin.php?page=fma-multi-currency&orderby=currency_name&amp;order=asc';	
			}
			
			$where = '';
			if(isset($_POST['s']) && trim($_POST['s']) != ''){
				$where = "WHERE currency_name like '%".$_POST['s']."%' ";
			}
			
			$query = "SELECT * FROM ".$table_name." ".$where.$orderby;
			
			$total = $wpdb->get_var( str_replace( 'SELECT *', 'SELECT COUNT(currency_id)', $query ) );

			$query .= " LIMIT " . intval( ( $pagenum - 1 ) * $per_page) . ", " . intval( $per_page );
		
			$currencies_list = $wpdb->get_results( $query, ARRAY_A );
			
			$num_pages = ceil( $total / $per_page );
			$page_links = paginate_links( array(
				'base' => add_query_arg( 'paged', '%#%' ),
				'format' => '',
				'end_size'     => 1,
				'mid_size'     => 9,
				'prev_text' => __( '&laquo;' ),
				'next_text' => __( '&raquo;' ),
				'total' => $num_pages,
				'current' => $pagenum
			));
	   ?> 

	   <?php if ( $page_links ) { ?>
		      <div class="tablenav-pages">
		        <?php $page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' ) . '</span>%s',
					number_format_i18n( ( $pagenum - 1 ) * $per_page + 1 ),
					number_format_i18n( min( $pagenum * $per_page, $total ) ),
					number_format_i18n( $total ),
					$page_links
					); echo $page_links_text; ?>
		      </div>
	    <?php } ?>


		<table class="wp-list-table widefat fixed users" cellspacing="0">
			<thead>
				<tr>
		        <th scope="col" id="currencyid" class="manage-column column-currencyid sortable desc" style=" width:50px; text-align: center">
		        <span style="padding: 10px;">ID</span>
		        </th>


		        <th scope="col" id="currname" class="manage-column column-currname sortable desc" style=" text-align: center">
		        <a href="<?php echo $actionOrder?>"><span>Currency name</span><span class="sorting-indicator"></span></a>
		        </th>

		        <th scope="col" id="currcode" class="manage-column column-currcode sortable desc" style=" text-align: center">
		        <span>Code</span>
		        </th>
				
				<th scope="col" id="currsymbol" class="manage-column column-currsymbol sortable desc" style=" text-align: center">
		        <span>Symbol</span>
		        </th>

		        <th scope="col" id="currvalue" class="manage-column column-currvalue sortable desc" style=" text-align: center">
		        <span>Value</span>
		        </th>

		        <th scope="col" id="status" class="manage-column column-status sortable desc" style=" text-align: center">
		        <span>Status</span>
		        </th>

				<th scope="col" id="datemodified" class="manage-column column-datemodified sortable desc" style=" text-align: center">
		        <span>Date Modified</span>
		        </th>
				<th scope="col" id="actions" class="manage-column column-counter" style=" text-align: center">
		        <span>Actions</span>
		        </th>
				</tr>
			</thead>

			<tfoot>
				<tr>
		        <th scope="col" id="currencyid" class="manage-column column-currencyid sortable desc" style=" width:50px; text-align: center">
		        <span style="padding: 10px;">ID</span>
		        </th>


		        <th scope="col" id="currname" class="manage-column column-currname sortable desc" style=" text-align: center">
		        <a href="<?php echo $actionOrder?>"><span>Currency name</span><span class="sorting-indicator"></span></a>
		        </th>

		        <th scope="col" id="currcode" class="manage-column column-currcode sortable desc" style=" text-align: center">
		        <span>Code</span>
		        </th>
				
				<th scope="col" id="currsymbol" class="manage-column column-currsymbol sortable desc" style=" text-align: center">
		        <span>Symbol</span>
		        </th>

		        <th scope="col" id="currvalue" class="manage-column column-currvalue sortable desc" style=" text-align: center">
		        <span>Value</span>
		        </th>

		        <th scope="col" id="status" class="manage-column column-status sortable desc" style=" text-align: center">
		        <span>Status</span>
		        </th>

				<th scope="col" id="datemodified" class="manage-column column-datemodified sortable desc" style=" text-align: center">
		        <span>Date Modified</span>
		        </th>
				<th scope="col" id="actions" class="manage-column column-counter" style=" text-align: center">
		        <span>Actions</span>
		        </th>
				</tr>
			</tfoot>

			<?php if(!empty($currencies_list)) { 

				$my_nonce = wp_create_nonce('delete_my_rec');
				 $i= 1;
				 foreach($currencies_list as $_currencies_list) {

				 	$class = 'alternate';
					if($i%2)
						$class='';
			?>
			

			<tr id="user-<?php echo $_currencies_list['currency_id']?>" class="<?php echo $class; ?>">
				<td class="username column-username" style=" text-align: center"><?php echo $_currencies_list['currency_id']; ?></td>
				<td class="username column-username"><a href="admin.php?page=fma-multi-currency&add=1&currency_id=<?php echo $_currencies_list['currency_id']?>"><?php echo $_currencies_list['currency_name']?></a></td>
				<td class="username column-username" style=" text-align: center"><?php echo $_currencies_list['currency_code']; ?></td>
				<td class="username column-username" style=" text-align: center"><?php echo $_currencies_list['currency_symbol']; ?></td>
				<td class="username column-username" style=" text-align: center"><?php echo $_currencies_list['currency_value']; ?></td>
				<td class="username column-username" style=" text-align: center"><?php echo $_currencies_list['currency_status']; ?></td>
				<td class="username column-username" style=" text-align: center"><?php echo $_currencies_list['date_modified']; ?></td>
				<td class="username column-username" style=" text-align: center"><a href="admin.php?page=fma-multi-currency&add=1&currency_id=<?php echo $_currencies_list['currency_id']?>">Edit</a> | <a href="#" onclick="conf('admin.php?page=fma-multi-currency&del_id=<?php echo $_currencies_list['currency_id']?>&_fmamcdelwpnonce=<?php echo $my_nonce ?>')" >Delete</a>
			</tr>

			<?php $i++;}} else{ ?>
		   <tr id="user-1" class="alternate"><td colspan="6"> No record found</td></tr>
		    <?php }
			wp_reset_query();
			?>


		</table>



	<?php } ?>

</div>
<div id="load"></div>

<script type="text/javascript">
jQuery(document).ready(function($) {
  $(".js-example-basic-single").select2();

  $('#currency_name').on('change', function() {
	  
  		var a = $(this).find(':selected').attr('data-value');
  		$('#currencyname').val(a);

	});
});

function updateValue() {
	var currency_name = jQuery('#currency_name').val();
	if(currency_name == '') {
		
		jQuery('#select_currency_first').show();		
		return false;
	} else {
		jQuery('#select_currency_first').hide();
	}
	
	var ajaxurl = "<?php echo admin_url( 'admin-ajax.php'); ?>";

	jQuery('#load').show();
	jQuery.ajax({
		type: "POST",
		url: ajaxurl,
		data: {"action": "getCurrencyValue", "currency_name":currency_name},
		success: function(data) {
			jQuery('#currency_value').val(data);
			jQuery('#load').hide();
		}
	});

}

function vForm() {

	var currency_name = document.getElementById('currency_name').value;
	var currency_value = document.getElementById('currency_value').value;

	if(currency_name == '') {
		
		jQuery('#select_currency_first').show();		
		return false;
	} else {
		jQuery('#select_currency_first').hide();
	}

	if(currency_value == '') {
		
		jQuery('#select_currency_value').show();		
		return false;
	} else {
		jQuery('#select_currency_value').hide();
	}


	document.getElementById("currency_form").submit();
}
</script>