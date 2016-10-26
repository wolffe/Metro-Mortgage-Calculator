function isNumberKey(evt) {
    var charCode = (evt.which) ? evt.which : evt.keyCode;

    if(charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }

    return true;
}

jQuery(document).ready(function($) {  
	// hide detailed payment calculator on load  
	$('#wp_mtg_calc_dpymt_div').css('display', 'none');
	$('.wp_mtg_calc_result').hide();

	// build ajaxForm variables for simple pymt calculator
	var pymtOptions = {
		success: function(responseText, statusText) {
			$('#wp_mtg_calc_result').removeClass('wp_mtg_calc_error');
			$('#wp_mtg_calc_result').addClass('wp_mtg_calc_success');
			$('#wp_mtg_calc_result').html(responseText);
			$('#wp_mtg_calc_result').fadeIn();
		},
		error: function(request) {
			if(request.responseText.search(/<title>WordPress &rsaquo; Error<\/title>/) != -1) {
				var rawdata = request.responseText.match(/<p>(.*)<\/p>/);
				var data = rawdata[1];
			} else {
				var data = 'There was an unknown error. Please notify administrator.';
			}
			$('#wp_mtg_calc_result').removeClass('wp_mtg_calc_success');
			$('#wp_mtg_calc_result').addClass('wp_mtg_calc_error');
			$('#wp_mtg_calc_result').html(data);
			$('#wp_mtg_calc_result').fadeIn();
		},
		beforeSubmit: function(formData, jqForm, options) {
			// clear response div
			$('#wp_mtg_calc_result').hide();
			$('#wp_mtg_calc_result').empty();
		}
	};

	// build ajaxForm variables for down payment calculator
	var downPymtOptions = {
		success: function(responseText, statusText) {
			$('#wp_mtg_calc_dpymt_result').removeClass('wp_mtg_calc_error');
			$('#wp_mtg_calc_dpymt_result').addClass('wp_mtg_calc_success');
			$('#wp_mtg_calc_dpymt_result').html(responseText);
			$('#wp_mtg_calc_dpymt_result').fadeIn();
		},
		error: function(request) {
			if(request.responseText.search(/<title>WordPress &rsaquo; Error<\/title>/) != -1) {
				var rawdata = request.responseText.match(/<p>(.*)<\/p>/);
				var data = rawdata[1];
			} else {
				var data = 'There was an unknown error. Please notify administrator.';
			}
			$('#wp_mtg_calc_dpymt_result').removeClass('wp_mtg_calc_success');
			$('#wp_mtg_calc_dpymt_result').addClass('wp_mtg_calc_error');
			$('#wp_mtg_calc_dpymt_result').html(data);
			$('#wp_mtg_calc_dpymt_result').fadeIn();
		},
		beforeSubmit: function(formData, jqForm, options) {
			// clear response div
			$('#wp_mtg_calc_dpymt_result').hide();
			$('#wp_mtg_calc_dpymt_result').empty();
		}
	};

	// bind ajaxForm function to calculator forms
	$('#wp_mtg_calc_form').ajaxForm(pymtOptions);
	$('#wp_mtg_calc_dpymt_form').ajaxForm(downPymtOptions);

	// bind toggle to accordion links
	$('a.wp_mtg_calc_toggle').click(function() {
		$('#wp_mtg_calc_pymt_div').slideToggle('fast'); // toggle('slow');
		$('#wp_mtg_calc_dpymt_div').slideToggle('fast');
		return false;
	});
});
