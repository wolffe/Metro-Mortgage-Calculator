<?php
require_once('../../../wp-config.php');

if($_POST['wp_mtg_calc_submit']) {
	check_ajax_referer('wp_mtg_calc_form');

	$fPrincipal = trim(strip_tags($_POST['wp_mtg_calc_princ']));
	$fRate = trim(strip_tags($_POST['wp_mtg_calc_int']));
	$iTerm = trim(strip_tags($_POST['wp_mtg_calc_term']));
	$myEmail = trim(strip_tags($_POST['wp_mtg_email']));

	// convert percentages to decimal form
	$fRate /= 100;

	// calculate results
	$aResult = tl_calc_payment($fPrincipal, $fRate, $iTerm);

	// build output string
	$sOutputString = getOutputString($aResult, 'simple', $myEmail);

	die($sOutputString);
} elseif($_POST['wp_mtg_calc_dpymt_submit']) {
	check_ajax_referer('wp_mtg_calc_form', '_wpnonce1');

	$fPrincipal = trim(strip_tags($_POST['wp_mtg_calc_dpymt_princ']));
	$fDown = trim(strip_tags($_POST['wp_mtg_calc_dpymt_down']));
	$fRate = trim(strip_tags($_POST['wp_mtg_calc_dpymt_int']));
	$iTerm = trim(strip_tags($_POST['wp_mtg_calc_dpymt_term']));
	$fClosingCosts = trim(strip_tags($_POST['wp_mtg_calc_dpymt_cc']));
	$sType = trim(strip_tags($_POST['wp_mtg_calc_dpymt_type']));
	$myEmail = trim(strip_tags($_POST['wp_mtg_email']));

	if($fDown == '') $fDown = 0;
	if($fClosingCosts == '') $fClosingCosts = 0;

	// convert percentages to decimal form
	$fDown /= 100;
	$fRate /= 100;

	// calculate payment
	$aResult = tl_calc_payment($fPrincipal, $fRate, $iTerm, $fDown, $fClosingCosts, $sType);

	// build the output string
	$sOutputString = getOutputString($aResult, 'detailed', $myEmail);

	die($sOutputString);
}

/**
* Calculates a mortgage payment based on
* Principle, Interest rate, and Amortization period (in years)
*
*/
function tl_calc_payment($fHomePrice, $fYRate, $iYTerm, $fDown = 0, $fClosingCosts = 0, $sType = 'pi') {
	$fDownPaymentAmount = $fHomePrice * $fDown;

	// if down payment included, subtract it from principal
	if($fDown !== 0) $fAmountBorrowed = $fHomePrice - ($fHomePrice * $fDown);
	else $fAmountBorrowed = $fHomePrice;    

	// set principal equal to amount borrowed for now
	$fPrincipal = $fAmountBorrowed;

	// add closing costs to principal & amount borrowed if included
	if($fClosingCosts !== 0) $fPrincipal += $fClosingCosts;

	// calculate monthly rate and term from yearly rate and term
	$iMTerm = $iYTerm * 12;
	$fMRate = $fYRate / 12;

	// add variables that won't change to array
	$aResult['down_pymt'] = $fDown;
	$aResult['down_pymt_amt'] = $fDownPaymentAmount;
	$aResult['annual_int_rate'] = $fYRate;
	$aResult['term_years'] = $iYTerm;
	$aResult['closing_costs'] = $fClosingCosts;
	$aResult['loan_type'] = $sType;
	$aResult['amt_borrowed'] = $fAmountBorrowed;
	$aResult['principal'] = $fPrincipal;
	$aResult['home_price'] = $fHomePrice;
	$aResult['term_months'] = $iMTerm;

	// calculate P&I payment for use in APR calculation (from http://www.hughchou.org/calc/formula.html)
	$fPiPymt = $fPrincipal * ($fMRate / (1 - pow((1 + $fMRate), -$iMTerm)));

	// assign P&I or IO payment to monthly payment key of results array
	if($sType == 'io') $aResult['monthly_pymt'] = $fMRate * $fPrincipal;
	else $aResult['monthly_pymt'] = $fPiPymt;

	// calculate interest paid for P&I payments
	if($sType == 'io') $aResult['int_paid'] = $aResult['monthly_pymt'] * $iMTerm;
	elseif($sType == 'pi') $aResult['int_paid'] = ($aResult['monthly_pymt'] * $iMTerm) - $fAmountBorrowed;

	// calculate APR
	$aApr = calcApr(12, $iMTerm, $fAmountBorrowed, $fPiPymt, $fYRate);

	// add results to array
	$aResult['apr'] = $aApr;

	return $aResult;
}

function getOutputString($aValues, $sCalcType = 'simple', $myEmail) {
	$mCurrency = get_option('wp_mtg_calc_currency');
	$mCurrencySymbol = get_option('wp_mtg_calc_currency_symbol');
	if($mCurrencySymbol == '') { $mCurrencySymbol = $mCurrency; }

    // both calcs show monthly payment
    $sReturnString = '<ul><li class="wp_mtg_calc_monthly_pymt"><span class="wp_mtg_calc_label">'.__('Monthly Payment', 'wpmm').':</span> ' .
    '<span class="wp_mtg_calc_answer">'.$mCurrencySymbol . number_format($aValues['monthly_pymt'], 2) . '</span></li>';
    
    // if we're using detailed calc, add these values
    if($sCalcType == 'detailed') {

        // add APR, down pymt amt, interest paid to detailed calc
        $sReturnString .= '<li class="wp_mtg_calc_apr"><span class="wp_mtg_calc_label">'.__('APR', 'wpmm').':</span> ' .
        '<span class="wp_mtg_calc_answer">' . number_format($aValues['apr'][sizeof($aValues['apr']) - 1] * 100, 4) . '%' . '</span></li>' .
        
        '<li class="wp_mtg_calc_down_pymt_amt"><span class="wp_mtg_calc_label">'.__('Down Payment', 'wpmm').':</span> ' . 
        '<span class="wp_mtg_calc_answer">'.$mCurrencySymbol . number_format($aValues['down_pymt_amt'], 2) . '</span></li>' .
        
        '<li class="wp_mtg_calc_amount_borrowed"><span class="wp_mtg_calc_label">'.__('Loan Amount', 'wpmm').':</span> ' . 
        '<span class="wp_mtg_calc_answer">'.$mCurrencySymbol . number_format($aValues['amt_borrowed'], 2) . '</span></li>' .
        
        '<li class="wp_mtg_calc_interest_paid"><span class="wp_mtg_calc_label">'.__('Interest paid', 'wpmm').':</span> ' . 
        '<span class="wp_mtg_calc_answer">'.$mCurrencySymbol . number_format($aValues['int_paid'], 2) . '</span></li>';
    }
    
	$wp_mtg_calc_advanced = get_option('wp_mtg_calc_advanced');
    if($wp_mtg_calc_advanced == 1) {
        $sReturnString .= '<li class="wp_mtg_calc_down_pymt_perc"><span class="wp_mtg_calc_label">'.__('Down Payment', 'wpmm').':</span> ' . 
        '<span class="wp_mtg_calc_answer">' . number_format($aValues['down_pymt'] * 100, 2) . '%</span></li>' .
        
        '<li class="wp_mtg_calc_annual_int_rate"><span class="wp_mtg_calc_label">'.__('Annual Interest Rate', 'wpmm').':</span> ' . 
        '<span class="wp_mtg_calc_answer">' . number_format($aValues['annual_int_rate'] * 100, 2) . '%</span></li>' .
        
        '<li class="wp_mtg_calc_term_years"><span class="wp_mtg_calc_label">'.__('Loan Term', 'wpmm').':</span> ' . 
        '<span class="wp_mtg_calc_answer">' . $aValues['term_years'] . ' '.__('years', 'wpmm').'</span></li>' .
        
        '<li class="wp_mtg_calc_term_months"><span class="wp_mtg_calc_label">'.__('Loan Term', 'wpmm').':</span> ' . 
        '<span class="wp_mtg_calc_answer">' . $aValues['term_months'] . ' '.__('months', 'wpmm').'</span></li>' .
       
        '<li class="wp_mtg_calc_closing_costs"><span class="wp_mtg_calc_label">'.__('Closing Costs', 'wpmm').':</span> ' . 
        '<span class="wp_mtg_calc_answer">'.$mCurrencySymbol . number_format($aValues['closing_costs'], 2) . '</span></li>' .
        
        '<li class="wp_mtg_calc_loan_type"><span class="wp_mtg_calc_label">'.__('Payment Type', 'wpmm').':</span> ' . 
        '<span class="wp_mtg_calc_answer">' . $aValues['loan_type'] . '</span></li>' .
       
        '<li class="wp_mtg_calc_amt_borrowed"><span class="wp_mtg_calc_label">'.__('Amount Borrowed', 'wpmm').':</span> ' . 
        '<span class="wp_mtg_calc_answer">'.$mCurrencySymbol . number_format($aValues['amt_borrowed'], 2) . '</span></li>' .
        
        '<li class="wp_mtg_calc_principal"><span class="wp_mtg_calc_label">'.__('Loan Amount', 'wpmm').':</span> ' . 
        '<span class="wp_mtg_calc_answer">'.$mCurrencySymbol . number_format($aValues['principal'], 2) . '</span></li>' .
        
        '<li class="wp_mtg_calc_home_price"><span class="wp_mtg_calc_label">'.__('Home Price', 'wpmm').':</span> ' . 
        '<span class="wp_mtg_calc_answer">'.$mCurrencySymbol . number_format($aValues['home_price'], 2) . '</span></li>';

        // init counter
        $count = 0;
        foreach($aValues['apr'] as $fApr) {
            $sReturnString .= '<li class="wp_mtg_calc_apr_' . $count . '"><span class="wp_mtg_calc_label">APR' . $count . '</span> ' .
                '<span class="wp_mtg_calc_answer">' . number_format($fApr * 100, 2) . '%</span></li>';
            $count++;
        }
    }
    
    $sReturnString .= '</ul>';

	// this string is ready to be sent via email
	if($myEmail != '') {
		$wp_mtg_calc_email_name = get_option('wp_mtg_calc_email_name');
		$wp_mtg_calc_email_from = get_option('wp_mtg_calc_email_from');
		$wp_mtg_calc_email_subject = get_option('wp_mtg_calc_email_subject');

		$wp_mtg_calc_email_extra_top = get_option('wp_mtg_calc_email_extra_top');
		$wp_mtg_calc_email_extra_bottom = get_option('wp_mtg_calc_email_extra_bottom');

		$sEmailString = $wp_mtg_calc_email_extra_top . '<br>' . $sReturnString . '<br>' . $wp_mtg_calc_email_extra_bottom;

		function res_fromemail($email) {
			$wpfrom = get_option('admin_email');
			return $wpfrom;
		}
		function res_fromname($email) {
			$wpfrom = get_option('blogname');
			return $wpfrom;
		}

		$headers = 'From: '.$wp_mtg_calc_email_name.' <'.$wp_mtg_calc_email_from.'>' . "\r\n";

		add_filter('wp_mail_from', 'res_fromemail');
		add_filter('wp_mail_from_name', 'res_fromname');

		add_filter('wp_mail_content_type',create_function('', 'return "text/html";'));
		wp_mail($myEmail, $wp_mtg_calc_email_subject, $sEmailString, $headers);
	}

	return $sReturnString;
}

/**
* Calculate APR based on Actuary Method, also known as Iteration method
* From (http://www.fdic.gov/regulations/laws/rules/6500-1950.html#fdic6500appendixjtopart226)
* 
* @param int $w Yearly payment periods
* @param int $n Total number of payment periods
* @param float $A Actual loan amount (after closing costs + down payment)
* @param float $P Monthly payment amount
* @param float $I1 Annual rate of interest (stated)
*/
function calcApr($w = 12, $n, $A, $P, $I1) {
	//echo '<pre>'; print_r("w = $w, n = $n, A = $A, P = $P, I1 = $I1"); echo '</pre>'; die();
    // begin iterations to calc APR via actuary method
    do {

        // assign APR2 for comparison
        if(isset($apr1)) $apr2 = $apr1;

        // solve for i
        $i = $I1 / (100 * $w);

        // reset $ASum1
        $ASum1 = 0;

        // solve for A1
        for($x = 0; $x < $n; $x++) {
            $ASum1 += 1 / (pow(1 + $i, $x));
        }
        $A1 = $P * $ASum1 / (1 + $i);

        // init I2
        $I2 = $I1 + .1;

        /** STEP 2 **/

        // solve for i
        $i = $I2 / (100 * $w);

        // reset $ASum2
        $ASum2 = 0;

        // solve for A2
        for($x = 0; $x < $n; $x++) {
            $ASum2 += 1 / (pow(1 + $i, $x));
        }
        $A2 = $P * $ASum2 / (1 + $i);

        /** STEP 3 **/

        // interpolate for APR
        $apr1 = $I1 + .1 * (($A - $A1) / ($A2 - $A1));

        // assign new I1 as APR1
        $I1 = $apr1;

        // print APR
        $apr1 = number_format($apr1, 4);

        // add result to apr array
        $aApr[] = $apr1 / 100;
        
    }while(abs($apr1 - $apr2) != 0);
    
    // return APR array
    //echo '<pre>'; print_r($aApr); echo '</pre>'; die();
    return $aApr;
}