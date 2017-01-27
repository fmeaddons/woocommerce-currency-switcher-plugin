

function setCurrency(curr) {
	var ajaxurl = fma_ajax_url.ajax_url;

	jQuery.ajax({
		type: "POST",
		url: ajaxurl,
		data: {"action": "setChoosenCurrency", "currency_name":curr},
		success: function(data) {
			

			jQuery('#acart').trigger('click');
			
			setTimeout(location.reload.bind(location), 280);
			
			

		}
	});
}



	

	

		
	











