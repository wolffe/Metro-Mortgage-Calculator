<?php
/*
Plugin Name: Metro Mortgage Calculator
Plugin URI: https://getbutterfly.com/wordpress-plugins/mortgage-calculator/
Description: A modern UI mortgage calculator with email functions, multiple themes, results template and easy integration. It calculates standard mortgage payment, mortgage rate based on payment and term, and APR based on payment, term, and fees paid.
Author: Ciprian Popescu
Author URI: https://getbutterfly.com/
Version: 1.3.1

Metro Mortgage Calculator WordPress Plugin
Copyright (C) 2009, 2010, 2011, 2012, 2013, 2014, 2015 Ciprian Popescu (getbutterfly@gmail.com)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

define('WPMM_PLUGIN_URL', WP_PLUGIN_URL . '/' . dirname(plugin_basename(__FILE__)));
define('WPMM_VERSION', '1.3.1');
//

// plugin localization
$plugin_dir = basename(dirname(__FILE__));
load_plugin_textdomain('wpmm', false, $plugin_dir . '/languages');

// add initialization action
add_action('init', 'wp_mtg_calc_Init');

// add actions to print required javascript
add_action('wp_enqueue_scripts', 'wp_mtg_calc_ScriptsAction');

add_action('admin_menu', 'wp_mtg_calc_plugin_menu');
function wp_mtg_calc_plugin_menu() {
	add_options_page('Mortgage Options', 'Mortgage Options', 'manage_options', 'wp_mtg_calc', 'wp_mtg_calc_plugin_options');
}

function wp_mtg_calc_plugin_options() {
	if(isset($_POST['update'])) {
		update_option('wp_mtg_calc_currency', sanitize_text_field($_POST['wp_mtg_calc_currency']));
		update_option('wp_mtg_calc_currency_symbol', sanitize_text_field($_POST['wp_mtg_calc_currency_symbol']));
		update_option('wp_mtg_calc_theme', sanitize_text_field($_POST['wp_mtg_calc_theme']));
		update_option('wp_mtg_calc_advanced', (int) sanitize_text_field($_POST['wp_mtg_calc_advanced']));
		update_option('wp_mtg_calc_email_name', sanitize_text_field($_POST['wp_mtg_calc_email_name']));
		update_option('wp_mtg_calc_email_from', sanitize_email($_POST['wp_mtg_calc_email_from']));
		update_option('wp_mtg_calc_email_subject', sanitize_text_field($_POST['wp_mtg_calc_email_subject']));
		update_option('wp_mtg_calc_email_extra_top', stripslashes_deep($_POST['wp_mtg_calc_email_extra_top']));
		update_option('wp_mtg_calc_email_extra_bottom', stripslashes_deep($_POST['wp_mtg_calc_email_extra_bottom']));

        echo '<div class="updated"><p>Settings saved.</p></div>';
	}
	echo '<div class="wrap">';
		echo '<h2>' . __('Metro Mortgage Calculator Options', 'wpmm') . '</h2>';
		?>

		<div class="metabox-holder">
			<div class="inner-sidebar">
				<div class="postbox">
					<h3><span><?php _e('Plugin Information', 'wpmm'); ?></span></h3>
					<div class="inside">
						<p>Metro Mortgage Calculator is simple to use. All you need to do is enter the details of the loan amount, interest rate and the loan (or repayment) term. The loan amount is how much you will need to borrow, the interest rate is the rate advertised by the lender and the loan (or repayment) term is the amount of time it takes to repay the loan (generally 15 or 30 years).</p>
						<p>Metro Mortgage Calculator is for educational and informational purposes only. It relies on assumptions and information provided by the user regarding his/her goals, expectations and financial situation. This calculator does not take into account individual circumstances. The user should consult with financial, tax, and legal advisors for any advice relating to his/her personal circumstances.</p>
						<p>In addition to the down payment, the user will also have to pay closing costs - miscellaneous fees charged by those involved with the home sale (such as the lender for processing the loan, the title company for handling the paperwork, a surveyor, local government offices for recording the deed, etc.). The range is from 1% to 8% of the price of the home, though more typically 2-3%. The lender will give the user a more accurate estimate of closing costs on the purchase of a particular house. This is called a "Good Faith Estimate".</p>

						<p>For support, feature requests and bug reporting, please visit the <a href="http://getbutterfly.com/wordpress-plugins/metro-mortgage-calculator/" rel="external">official website</a>.</p>

						<ul>
							<li><small>You are using Metro Mortgage Calculator <strong><?php echo WPMM_VERSION; ?></strong></small></li>
							<li>
								<small>
									<a href="https://getbutterfly.com/wordpress-plugins/mortgage-calculator/"><?php _e('Official Web Site', 'wpmm');?></a> | 
								</small>
							</li>
						</ul>
					</div>
				</div>
				<div class="postbox">
					<h3><span><?php _e('Plugin Help and Support', 'tycoon');?></span></h3>
					<div class="inside">
						<p><?php _e('For more information and updates, visit the', 'tycoon');?> <a href="https://getbutterfly.com/" rel="external"><?php _e('official web site', 'wpmm');?></a></p>
					</div>
				</div>
			</div>
			<div id="post-body">
				<div id="post-body-content">

					<div class="ui-sortable meta-box-sortables">
						<div class="postbox">
							<h3><?php _e('How to use', 'wpmm'); ?></h3>
							<div class="inside">
								<p><?php _e('Add the <code>[metro-mortgage-calculator]</code> shortcode to any post or page to start using the calculator. The calculator will use the default options.', 'wpmm'); ?></p>
							</div>
						</div>
					</div>
					<div class="ui-sortable meta-box-sortables">
						<div class="postbox">
							<h3><?php _e('Calculator Options', 'wpmm'); ?></h3>
							<div class="inside">
								<form name="form1" method="post">
									<h4><?php _e('Calculator Options', 'wpmm'); ?></h4>
									<p>
										<input type="text" name="wp_mtg_calc_currency" id="wp_mtg_calc_currency" value="<?php echo get_option('wp_mtg_calc_currency'); ?>" size="3"> <label for="wp_mtg_calc_currency">Currency</label>
										<br>
										<input type="text" name="wp_mtg_calc_currency_symbol" id="wp_mtg_calc_currency_symbol" value="<?php echo get_option('wp_mtg_calc_currency_symbol'); ?>" size="3" > <label for="wp_mtg_calc_currency_symbol">Currency Symbol</label>
										<br>
										<span class="description">Currency used for display only.<br>Use USD, EUR, GBP, YEN/JPY for currency and characters ($, &euro;, &pound;, &yen;) for symbol.</span>
									</p>
									<p>
										<select name="wp_mtg_calc_advanced">
											<option value="1"<?php if(get_option('wp_mtg_calc_advanced') == 1) echo ' selected="selected"' ;?>>Yes</option>
											<option value="0"<?php if(get_option('wp_mtg_calc_advanced') == 0) echo ' selected="selected"' ;?>>No</option>
										</select> <label for="wp_mtg_calc_advanced">Show advanced results</label>
										<br>
									</p>

									<h4><?php _e('Theme Options', 'wpmm'); ?></h4>
									<p>
										<select name="wp_mtg_calc_theme">
											<option value="metro_light"<?php if(get_option('wp_mtg_calc_theme') == 'metro_light') echo ' selected="selected"'; ?>>Metro (Light)</option>
											<option value="metro_dark"<?php if(get_option('wp_mtg_calc_theme') == 'metro_dark') echo ' selected="selected"'; ?>>Metro (Dark)</option>
											<option value="basic"<?php if(get_option('wp_mtg_calc_theme') == 'basic') echo ' selected="selected"'; ?>>Basic</option>
										</select> <label for="wp_mtg_calc_theme">Calculator theme</label>
									</p>

									<h4><?php _e('Email Options', 'wpmm'); ?></h4>
									<p>
										<input type="text" name="wp_mtg_calc_email_name" id="wp_mtg_calc_email_name" value="<?php echo get_option('wp_mtg_calc_email_name'); ?>" class="regular-text"> <label for="wp_mtg_calc_email_name">From: Name</label>
										<br>
										<input type="text" name="wp_mtg_calc_email_from" id="wp_mtg_calc_email_from" value="<?php echo get_option('wp_mtg_calc_email_from');?>" class="regular-text"> <label for="wp_mtg_calc_email_from">From: Email</label>
										<br>
										<input type="text" name="wp_mtg_calc_email_subject" id="wp_mtg_calc_email_subject" value="<?php echo get_option('wp_mtg_calc_email_subject');?>" class="regular-text"> <label for="wp_mtg_calc_email_subject">Email Subject</label>
										<br>
										<?php wp_editor(get_option('wp_mtg_calc_email_extra_top'), 'wp_mtg_calc_email_extra_top', array('teeny' => true, 'textarea_rows' => 4, 'media_buttons' => false)); ?>
										<small>Email Template: Top</small><br>
										<?php wp_editor(get_option('wp_mtg_calc_email_extra_bottom'), 'wp_mtg_calc_email_extra_bottom', array('teeny' => true, 'textarea_rows' => 4, 'media_buttons' => false)); ?>
										<small>Email Template: Bottom</small>
								</p>
								<p class="submit">
									<input type="submit" name="update" class="button-primary" value="Save Changes">
								</p>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
<?php
}

function wp_mtg_calc_init() {
	wp_register_sidebar_widget('wp_mtg_widget', 'Metro Mortgage Calculator', 'wp_mtg_calc_Widget');
	wp_register_widget_control('wp_mtg_widget', 'Metro Mortgage Calculator', 'wp_mtg_calc_Widget_Control');

	add_option('wp_mtg_calc_currency', 'USD', '', 'no');
	add_option('wp_mtg_calc_currency_symbol', '$', '', 'no');
	add_option('wp_mtg_calc_theme', 'metro_light', '', 'no');
	add_option('wp_mtg_calc_advanced', '0', '', 'no');

	add_option('wp_mtg_calc_email_name', 'Metro Mortgage Calculator', '', 'no');
	add_option('wp_mtg_calc_email_from', 'user@domain.ext', '', 'no');
	add_option('wp_mtg_calc_email_subject', 'Your Mortgage Results', '', 'no');

	add_option('wp_mtg_calc_email_extra_top', 'Hello, these are your results.', '', 'no');
	add_option('wp_mtg_calc_email_extra_bottom', 'Thank you!', '', 'no');
}

function wp_mtg_calc_Widget($args = array()) {
	extract($args);
	$aOptions = get_option('wp_mtg_calc');
	$sTitle = $aOptions['wp_mtg_calc_title'];

	if(trim($sTitle) !== '') {
		echo $before_widget;
		echo $before_title . $sTitle . $after_title;    
	} else {
		echo $sTitle;
	}

	echo wp_mortgage_calculator();
	echo $after_widget;
}

function wp_mtg_calc_Widget_Control() {
	$aOptions = get_option('wp_mtg_calc');

	if($_POST['wp_mtg_calc_submit']) {
		$aOptions['wp_mtg_calc_title'] = strip_tags(stripslashes($_POST['wp_mtg_calc_title']));
		update_option('wp_mtg_calc', $aOptions);
	}

	$sTitle = $aOptions['wp_mtg_calc_title'];

	echo '
		<p>
			<label for="wp_mtg_calc_title">Title:</label>
			<input id="wp_mtg_calc_title" name="wp_mtg_calc_title" type="text" value="'.$sTitle.'">
			<input type="hidden" id="wp_mtg_calc_submit" name="wp_mtg_calc_submit" value="1">
		</p>';
}

/**
 * Ensures all required JavaScripts are included
 * 
 */
function wp_mtg_calc_ScriptsAction() {
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-form');
	wp_enqueue_script('wp_mortgage_calc_script', plugins_url('functions.js', __FILE__), array('jquery', 'jquery-form'));

	if(get_option('wp_mtg_calc_theme') == 'metro_light')
    	wp_enqueue_style('wmmc', plugins_url('style-light.css', __FILE__));
	if(get_option('wp_mtg_calc_theme') == 'metro_dark')
    	wp_enqueue_style('wmmc', plugins_url('style-dark.css', __FILE__));
	if(get_option('wp_mtg_calc_theme') == 'basic')
    	wp_enqueue_style('wmmc', plugins_url('style-basic.css', __FILE__));
}

/**
* Builds the payment calculator form
* 
*/
function wp_mortgage_calculator() {
	$sFormAction = WPMM_PLUGIN_URL . '/mortgage-calculator-ajax.php';

	$mCurrency = get_option('wp_mtg_calc_currency');
	$mCurrencySymbol = get_option('wp_mtg_calc_currency_symbol');
	if($mCurrencySymbol == '') { $mCurrencySymbol = $mCurrency; }

	$sFormString = '<div id="wp_mtg_calc_wrapper">' . "\n" .
        '<a id="wp_mtg_calc_pymt_toggle" class="wp_mtg_calc_toggle" href=""><span>'.__('Mortgage', 'wpmm').'</span><br>'.__('Standard Calculator', 'wpmm').'</a>' .
        '<ul><li id="wp_mtg_calc_pymt_div">' .
            '<form id="wp_mtg_calc_form" name="wp_mtg_calc_form" method="post" action="' . $sFormAction . '">' .
                wp_nonce_field('wp_mtg_calc_form','_wpnonce', true, false) .
                '<p><label for="wp_mtg_calc_princ">'.__('Loan Amount', 'wpmm').' ('.$mCurrencySymbol.')</label><br>' .
                '<input id="wp_mtg_calc_princ" class="wp_mtg_calc_text" name="wp_mtg_calc_princ" type="text" onkeypress="return isNumberKey(event)" required onfocus="this.select();"></p>' .
                '<p><label for="wp_mtg_calc_int">'.__('Interest Rate', 'wpmm').' (%)</label><br>' .
                '<input id="wp_mtg_calc_int" class="wp_mtg_calc_text" name="wp_mtg_calc_int" type="text" onkeypress="return isNumberKey(event)" required onfocus="this.select();"></p>' .
                '<p><label for="wp_mtg_calc_term">'.__('Loan Term (years)', 'wpmm').'</label>' .
                '<input id="wp_mtg_calc_term" class="wp_mtg_calc_text" name="wp_mtg_calc_term" type="text" onkeypress="return isNumberKey(event)" required onfocus="this.select();"></p>' .

                '<p><small>'.__('Send results via email (optional)', 'wpmm').'</small><br><input id="wp_mtg_email" class="wp_mtg_calc_text" name="wp_mtg_email" type="email" onfocus="this.select();" placeholder="'.__('Your email', 'wpmm').'"></p>' .

                '<p><input id="wp_mtg_calc_submit" class="m-btn blue" name="wp_mtg_calc_submit" type="submit" value="'.__('Calculate', 'wpmm').'"></p>' .
            '</form>' .
		    '<ul><li id="wp_mtg_calc_result" class="wp_mtg_calc_result"></li></ul>' .
        '</li></ul>';

    // detailed payment calculator
    $sFormString .= "\n" .
        '<a id="wp_mtg_calc_dpymt_toggle" class="wp_mtg_calc_toggle" href=""><span>'.__('Mortgage', 'wpmm').'</span><br>'.__('Advanced Calculator', 'wpmm').'</a>' .
        '<ul><li id="wp_mtg_calc_dpymt_div">' .
            '<form id="wp_mtg_calc_dpymt_form" name="wp_mtg_calc_dpymt_form" method="post" action="' . $sFormAction . '">' .
                wp_nonce_field('wp_mtg_calc_form', '_wpnonce1', true, false) .
                '<p><label for="wp_mtg_calc_dpymt_princ">'.__('Loan Amount', 'wpmm').' ('.$mCurrencySymbol.')</label><br>' .
                '<input id="wp_mtg_calc_dpymt_princ" class="wp_mtg_calc_text" name="wp_mtg_calc_dpymt_princ" type="text" onkeypress="return isNumberKey(event)" required onfocus="this.select();"></p>' .
                '<p><label for="wp_mtg_calc_dpymt_down">'.__('Down Payment', 'wpmm').' (%)</label><br>' .
                '<input id="wp_mtg_calc_dpymt_down" class="wp_mtg_calc_text" name="wp_mtg_calc_dpymt_down" type="text" onkeypress="return isNumberKey(event)" required onfocus="this.select();"></p>' .
                '<p><label for="wp_mtg_calc_dpymt_int">'.__('Interest Rate', 'wpmm').' (%)</label><br>' .
                '<input id="wp_mtg_calc_dpymt_int" class="wp_mtg_calc_text" name="wp_mtg_calc_dpymt_int" type="text" onkeypress="return isNumberKey(event)" required onfocus="this.select();"></p>' .
                '<p><label for="wp_mtg_calc_dpymt_term">'.__('Loan Term (years)', 'wpmm').'</label><br>' .
                '<input id="wp_mtg_calc_dpymt_term" class="wp_mtg_calc_text" name="wp_mtg_calc_dpymt_term" type="text" onkeypress="return isNumberKey(event)" required onfocus="this.select();"></p>' .
                '<p><label for="wp_mtg_calc_dpymt_cc">'.__('Closing Costs', 'wpmm').' ('.$mCurrencySymbol.')</label><br>' .
                '<input id="wp_mtg_calc_dpymt_cc" class="wp_mtg_calc_text" name="wp_mtg_calc_dpymt_cc" type="text" onkeypress="return isNumberKey(event)" required onfocus="this.select();"></p>' .
                '<p><label for="wp_mtg_calc_dpymt_type">'.__('Payment Type', 'wpmm').'</label><br>' .
                '<input id="pi" class="wp_mtg_calc_calc_radio" name="wp_mtg_calc_dpymt_type" type="radio" value="pi" checked="checked"><label for="pi">'.__('Principal &amp; Interest (pi)', 'wpmm').'</label><br>' .
                '<input id="io" class="wp_mtg_calc_dpymt_radio" name="wp_mtg_calc_dpymt_type" type="radio" value="io"><label for="io">'.__('Interest Only (io)', 'wpmm').'</label></p>' .
        
                '<p><small>'.__('Send results via email (optional)', 'wpmm').'</small><br><input id="wp_mtg_email" class="wp_mtg_calc_text" name="wp_mtg_email" type="email" onfocus="this.select();" placeholder="'.__('Your email', 'wpmm').'"></p>' .

				'<p><input id="wp_mtg_calc_dpymt_submit" class="m-btn blue" name="wp_mtg_calc_dpymt_submit" type="submit" value="'.__('Calculate', 'wpmm').'"></p>' .
            '</form>' .
            '<ul><li class="wp_mtg_calc_result" id="wp_mtg_calc_dpymt_result"></li></ul>' .        
        '</li></ul>' .
        '</div>';

    return $sFormString;
}

add_shortcode('metro-mortgage-calculator', 'wp_mortgage_calculator');
?>
