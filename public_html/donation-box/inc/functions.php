<?php
function get_options() {
	global $icdb, $options;
	$rows = $icdb->get_rows("SELECT * FROM ".$icdb->prefix."options");
	foreach ($rows as $row) {
		if (array_key_exists($row['options_key'], $options)) $options[$row['options_key']] = $row['options_value'];
	}
}

function update_options() {
	global $icdb, $options;
	foreach ($options as $key => $value) {
		$option = $icdb->get_row("SELECT * FROM ".$icdb->prefix."options WHERE options_key = '".mysql_real_escape_string($key)."'");
		if ($option) {
			$icdb->query("UPDATE ".$icdb->prefix."options SET options_value = '".mysql_real_escape_string($value)."' WHERE options_key = '".mysql_real_escape_string($key)."'");
		} else {
			$icdb->query("INSERT INTO ".$icdb->prefix."options (options_key, options_value) VALUES ('".mysql_real_escape_string($key)."', '".mysql_real_escape_string($value)."')");
		}
	}
}

function populate_options() {
	global $icdb, $options;
	foreach ($options as $key => $value) {
		if ($key != 'password') {
			if (isset($_POST[$key])) {
				if (get_magic_quotes_gpc()) {
					$options[$key] = stripslashes($_POST[$key]);
				}
				else $options[$key] = $_POST[$key];
			}
		}
	}
}

function check_options() {
	global $icdb, $options;
	$errors = array();
	if ($options['enable_payza'] != "on" && $options['enable_paypal'] != "on" && $options['enable_interkassa'] != "on" && $options['enable_authnet'] != "on" && $options['enable_egopay'] != "on" && $options['enable_perfect'] != "on" && $options['enable_skrill'] != "on" && $options['enable_bitpay'] != "on" && $options['enable_stripe'] != "on") $errors[] = 'Select at least one payment method';
	if ($options['enable_paypal'] == "on") {
		if ((!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i", $options['paypal_id']) && !preg_match("/^([A-Z0-9]+)$/i", $options['paypal_id'])) || strlen($options['paypal_id']) == 0) $errors[] = 'PayPal ID must be valid e-mail address or Merchant ID';
	}
	if ($options['enable_payza'] == "on") {
		if (!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i", $options['payza_id']) || strlen($options['payza_id']) == 0) $errors[] = 'Payza ID must be valid e-mail address';
	}
	if ($options['enable_interkassa'] == "on") {
		if (strlen($options['interkassa_shop_id']) < 3) $errors[] = 'InterKassa Shop ID is required';
		if (strlen($options['interkassa_secret_key']) < 3) $errors[] = 'InterKassa Secret Key is required';
	}
	if ($options['enable_authnet'] == "on") {
		if (strlen($options['authnet_login']) < 3) $errors[] = 'Authorize.Net API Login ID is required';
		if (strlen($options['authnet_key']) < 1) $errors[] = 'Authorize.Net Transaction Key is required';
		if (strlen($options['authnet_md5hash']) < 1) $errors[] = 'Authorize.Net MD5 Hash is required';
	}
	if ($options['enable_egopay'] == "on") {
		if (strlen($options['egopay_store_id']) < 1) $errors[] = 'EgoPay Store ID is required';
		if (strlen($options['egopay_store_pass']) < 1) $errors[] = 'EgoPay Store Pass is required';
	}
	if ($options['enable_perfect'] == "on") {
		if (strlen($options['perfect_account_id']) < 1) $errors[] = 'Perfect Money Payee Account is required';
		if (strlen($options['perfect_payee_name']) < 1) $errors[] = 'Perfect Money Payee Name is required';
		if (strlen($options['perfect_passphrase']) < 1) $errors[] = 'Perfect Money Alternate Passphrase is required';
	}
	if ($options['enable_skrill'] == "on") {
		if (!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i", $options['skrill_id']) || strlen($options['skrill_id']) == 0) $errors[] = 'Skrill ID must be valid e-mail address';
		if (strlen($options['skrill_secret_word']) < 1) $errors[] = 'Skrill Secret Word is required';
	}
	if ($options['enable_bitpay'] == "on") {
		if (strlen($options['bitpay_key']) < 1) $errors[] = 'BitPay API Key is required';
	}
	if ($options['enable_stripe'] == "on") {
		if (strlen($options['stripe_secret']) < 1) $errors[] = 'Stripe Secret Key is required';
		if (strlen($options['stripe_publishable']) < 1) $errors[] = 'Stripe Publishable Key is required';
	}
	if (!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i", $options['owner_email']) || strlen($options['owner_email']) == 0) $errors[] = 'E-mail for notifications must be valid e-mail address';
	if ($options['mail_method'] == 'mail') {
		if (!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i", $options['mail_from_email']) || strlen($options['mail_from_email']) == 0) $errors[] = 'Sender e-mail must be valid e-mail address';
		if (strlen($options['mail_from_email']) < 3) $errors[] = 'Sender name is too short';
	} else if ($options['mail_method'] == 'smtp') {
		if (strlen($options['smtp_server']) < 2) $errors[] = 'SMTP server can not be empty.';
		if (intval($options['smtp_port']) != $options['smtp_port'] || intval($options['smtp_port']) < 1 || intval($options['smtp_port']) > 65535) $errors[] = 'SMTP port must be valid integer value in range [1...65535].';
		if (strlen($options['smtp_username']) < 2) $errors[] = 'SMTP username can not be empty.';
		if (strlen($options['smtp_password']) < 1) $errors[] = 'SMTP password can not be empty.';
	}
	if (strlen($options['success_email_subject']) < 3) $errors[] = 'Successful donation e-mail subject must contain at least 3 characters';
	else if (strlen($options['success_email_subject']) > 64) $errors[] = 'Successful donation e-mail subject must contain maximum 64 characters';
	if (strlen($options['success_email_body']) < 3) $errors[] = 'Successful donation e-mail body must contain at least 3 characters';
	if (strlen($options['failed_email_subject']) < 3) $errors[] = 'Failed donation e-mail subject must contain at least 3 characters';
	else if (strlen($options['failed_email_subject']) > 64) $errors[] = 'Failed donation e-mail subject must contain maximum 64 characters';
	if (strlen($options['failed_email_body']) < 3) $errors[] = 'Failed donation e-mail body must contain at least 3 characters';
	if (empty($errors)) return true;
	return $errors;
}

function get_fingerprint($api_login_id, $transaction_key, $amount, $fp_sequence, $fp_timestamp) {
	if (function_exists('hash_hmac')) {
		return hash_hmac("md5", $api_login_id . "^" . $fp_sequence . "^" . $fp_timestamp . "^" . $amount . "^", $transaction_key); 
	}
	return bin2hex(mhash(MHASH_MD5, $api_login_id . "^" . $fp_sequence . "^" . $fp_timestamp . "^" . $amount . "^", $transaction_key));
}

function page_switcher ($_urlbase, $_currentpage, $_totalpages) {
	$pageswitcher = "";
	if ($_totalpages > 1) {
		$pageswitcher = '<div class="tablenav bottom"><div class="tablenav-pages">Pages: <span class="pagiation-links">';
		if (strpos($_urlbase,"?") !== false) $_urlbase .= "&amp;";
		else $_urlbase .= "?";
		if ($_currentpage == 1) $pageswitcher .= "<strong>1</strong> ";
		else $pageswitcher .= " <a class='page' href='".$_urlbase."p=1'>1</a> ";

		$start = max($_currentpage-3, 2);
		$end = min(max($_currentpage+3,$start+6), $_totalpages-1);
		$start = max(min($start,$end-6), 2);
		if ($start > 2) $pageswitcher .= " <b>...</b> ";
		for ($i=$start; $i<=$end; $i++) {
			if ($_currentpage == $i) $pageswitcher .= " <strong>".$i."</strong> ";
			else $pageswitcher .= " <a class='page' href='".$_urlbase."p=".$i."'>".$i."</a> ";
		}
		if ($end < $_totalpages-1) $pageswitcher .= " <b>...</b> ";

		if ($_currentpage == $_totalpages) $pageswitcher .= " <strong>".$_totalpages."</strong> ";
		else $pageswitcher .= " <a class='page' href='".$_urlbase."p=".$_totalpages."'>".$_totalpages."</a> ";
		$pageswitcher .= "</span></div></div>";
	}
	return $pageswitcher;
}

function random_string($_length = 16) {
	$symbols = '123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$string = "";
	for ($i=0; $i<$_length; $i++) {
		$string .= $symbols[rand(0, strlen($symbols)-1)];
	}
	return $string;
}

function send_thanksgiving_email($tags, $vals, $payer_email) {
	global $options;
	$body = str_replace($tags, $vals, $options['success_email_body']);
	if (!empty($payer_email)) {
		ic_mail($payer_email, $options['success_email_subject'], $body);
	}
	$body = str_replace($tags, $vals, 'Dear Administrator!'.PHP_EOL.PHP_EOL.'We would like to inform you that {payer_name} ({payer_email}) donated {amount} {currency} for "{campaign_title}" via {gateway} on {transaction_date}.'.PHP_EOL.PHP_EOL.'Thanks,'.PHP_EOL.'Universal Donation Box');
	ic_mail($options['owner_email'], 'Completed transaction', $body);
}

function send_failed_email($tags, $vals, $payer_email) {
	global $options;
	$body = str_replace($tags, $vals, $options['failed_email_body']);
	if (!empty($payer_email)) {
		ic_mail($payer_email, $options['failed_email_subject'], $body);
	}
	$body = str_replace($tags, $vals, 'Dear Administrator!'.PHP_EOL.PHP_EOL.'We would like to inform you that {payer_name} ({payer_email}) donated {amount} {currency} for "{campaign_title}" via {gateway} on {transaction_date}. This is non-completed donation.'.PHP_EOL.'Donation status: {payment_status}'.PHP_EOL.PHP_EOL.'Thanks,'.PHP_EOL.'Universal Donation Box');
	ic_mail($options['owner_email'], 'Non-completed transaction', $body);
}

function ic_mail($_email, $_subject, $_body) {
	global $options;
	$_body = str_replace(array("\n", "\r"), array("<br />", ""), $_body);
	if ($options['mail_method'] == 'mail') {
		$mail_headers = "Content-Type: text/html; charset=utf-8\r\n";
		$mail_headers .= "From: ".$options['mail_from_name']." <".$options['mail_from_email'].">\r\n";
		$mail_headers .= "X-Mailer: PHP/".phpversion()."\r\n";
		$result = mail($_email, $_subject, $_body, $mail_headers);
	} else if ($options['mail_method'] == 'smtp') {
		include_once(dirname(__FILE__).'/class.phpmailer.php');
		$mail = new PHPMailer();
		$mail->IsSMTP();
		$mail->IsHTML(true);
		$mail->SMTPDebug  = 0;
		$mail->Host       = $options['smtp_server'];
		$mail->Port       = $options['smtp_port'];
		if ($options['smtp_secure'] != 'none') {
			$mail->SMTPSecure = $options['smtp_secure'];
		}
		$mail->SMTPAuth   = true;
		$mail->Username   = $options['smtp_username'];
		$mail->Password   = $options['smtp_password'];
		$mail->SetFrom($options['smtp_username'], $options['smtp_username']);
		$mail->AddAddress($_email, $_email);
		$mail->Subject = $_subject;
		$mail->CharSet = 'utf-8';
		$mail->Body = $_body;
		$mail->AltBody = $_body;
		$mail->Send();
	}
}

function get_auth_style() {
	$style = '
<style>body {
	background-color: #F8F8F8;
	color: #444;
	position: relative;
}
div.page {
	border: 1px solid #AAA;
	background-color: #FFF;
	border-radius: 5px;
	-moz-border-radius: 5px;
	-webkit-border-radius: 5px;
	-o-border-radius: 5px;
	-ms-border-radius: 5px;
	-khtml-border-radius: 5px;
	box-shadow: rgba(128, 128, 128, 1) 0 4px 30px;
	-moz-box-shadow: rgba(128,128,128,1) 0 4px 30px;
	-webkit-box-shadow: rgba(128, 128, 128, 1) 0 4px 30px;
	-khtml-box-shadow: rgba(128,128,128,1) 0 4px 30px;	
	-o-box-shadow: rgba(128,128,128,1) 0 4px 30px;	
	-ms-box-shadow: rgba(128,128,128,1) 0 4px 30px;	
	padding: 10px 20px 10px 20px;
	margin-top: 30px;	
}
td.label {
	font-weight: bold;
}
input.input_text {
	line-height: 18px !important;
	font-weight: normal;
	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
	-o-border-radius: 3px;
	-ms-border-radius: 3px;
	-khtml-border-radius: 3px;
	border-radius: 3px;
	padding: 4px 6px;
	border: 1px solid #AAA;
	border-spacing: 0;
	font-family: arial, verdana;
	-moz-box-sizing: border-box;
	-webkit-box-sizing: border-box;
	-ms-box-sizing: border-box;
	box-sizing: border-box;
	margin: 0px;
	background-color: #FFF !important;
	margin-right: 5px;
}
span.comment {
	color: #AAA;
}
input#x_card_num {
	width: 200px;
}
input#x_exp_date {
	width: 80px;
}
div.grayboxouter {
	background-color: transparent;
	padding: 0px;
}
div.graybox {
	line-height: 22px; 
	padding: 3px 10px 3px 10px; 
	color:#8a1f11;
	border: 1px solid #FBC2C4; 
	border-radius: 5px; 
	-moz-border-radius: 5px; 
	-webkit-border-radius:5px; 
	font-size: 13px;
	font-family: arial, verdana;
	background: #FBE3E4;
}
div.grayboxhdr {
	font-weight: bold;
	font-size: 14px;
}
div#diverrormsgs ul {
	padding: 0px 0px 0px 15px;
}
a.udb_return, input[type="submit"] {
	display: inline-block;
	*display: inline;
	padding: 4px 14px;
	margin-bottom: 0;
	margin-top: 10px;
	*margin-left: .3em;
	font-size: 14px;
	line-height: 20px;
	*line-height: 20px;
	text-align: center;
	vertical-align: middle;
	cursor: pointer;
	border: 1px solid #bbbbbb;
	*border: 0;
	border-bottom-color: #a2a2a2;
	-webkit-border-radius: 4px;
	-moz-border-radius: 4px;
	border-radius: 4px;
	*zoom: 1;
	-webkit-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.05);
	-moz-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.05);
	box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.05);
	color: #ffffff;
	text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
	background-color: #5bb75b;
	*background-color: #51a351;
	background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#62c462), to(#51a351));
	background-image: -webkit-linear-gradient(top, #62c462, #51a351);
	background-image: -o-linear-gradient(top, #62c462, #51a351);
	background-image: linear-gradient(to bottom, #62c462, #51a351);
	background-image: -moz-linear-gradient(top, #62c462, #51a351);
	background-repeat: repeat-x;
	border-color: #51a351 #51a351 #387038;
	border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
	filter: progid:dximagetransform.microsoft.gradient(startColorstr="#ff62c462", endColorstr="#ff51a351", GradientType=0);
	filter: progid:dximagetransform.microsoft.gradient(enabled=false);
	font: 14px/18px Tahoma, Geneva, sans-serif !important;
}
a.udb_return:hover, input[type="submit"]:hover {
	text-decoration: none;
	background-position: 0 -15px;
	-webkit-transition: background-position 0.1s linear;
	-moz-transition: background-position 0.1s linear;
	-o-transition: background-position 0.1s linear;
	transition: background-position 0.1s linear;
	color: #ffffff;
	background-color: #51a351;
	*background-color: #499249;
}
a.udb_return {
	text-decoration: none;
}
div.udb_returnbox {
	margin: 30px auto;
	width: 600px;
	padding: 15px 20px;
}</style>';
	return str_replace(array("\n", "\r", "\t"), array("", "", ""), $style);
}

function install() {
	global $icdb;
	$table_name = $icdb->prefix."campaigns";
	if($icdb->get_var("SHOW TABLES LIKE '".$table_name."'") != $table_name) {
		$sql = "CREATE TABLE IF NOT EXISTS ".$table_name." (
			id int(11) NOT NULL AUTO_INCREMENT,
			title varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			form_intro text COLLATE utf8_unicode_ci NOT NULL,
			form_terms text COLLATE utf8_unicode_ci NOT NULL,
			top_intro text COLLATE utf8_unicode_ci NOT NULL,
			recent_intro text COLLATE utf8_unicode_ci NOT NULL,
			min_amount float NOT NULL,
			currency varchar(15) COLLATE utf8_unicode_ci NOT NULL,
			status int(11) NOT NULL DEFAULT '".STATUS_ACTIVE."',
			details text COLLATE utf8_unicode_ci NOT NULL,
			registered int(11) NOT NULL,
			deleted int(11) NOT NULL DEFAULT '0',
			UNIQUE KEY id (id)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$icdb->query($sql);
	}
	$table_name = $icdb->prefix."donors";
	if($icdb->get_var("SHOW TABLES LIKE '".$table_name."'") != $table_name) {
		$sql = "CREATE TABLE IF NOT EXISTS ".$table_name." (
			id int(11) NOT NULL AUTO_INCREMENT,
			campaign_id int(11) NOT NULL,
			name varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			email varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			url varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			amount float NOT NULL,
			currency varchar(15) COLLATE utf8_unicode_ci NOT NULL,
			status int(11) NOT NULL DEFAULT '".STATUS_DRAFT."',
			details text COLLATE utf8_unicode_ci NOT NULL,
			registered int(11) NOT NULL,
			deleted int(11) NOT NULL DEFAULT '0',
			UNIQUE KEY id (id)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$icdb->query($sql);
	}
	$table_name = $icdb->prefix."transactions";
	if($icdb->get_var("SHOW TABLES LIKE '".$table_name."'") != $table_name) {
		$sql = "CREATE TABLE IF NOT EXISTS ".$table_name." (
			id int(11) NOT NULL AUTO_INCREMENT,
			donor_id int(11) NOT NULL,
			payer_name varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			payer_email varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			gross float NOT NULL,
			currency varchar(15) COLLATE utf8_unicode_ci NOT NULL,
			payment_status varchar(63) COLLATE utf8_unicode_ci NOT NULL,
			transaction_type varchar(63) COLLATE utf8_unicode_ci NOT NULL,
			txn_id varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
			details text COLLATE utf8_unicode_ci NOT NULL,
			created int(11) NOT NULL,
			deleted int(11) NOT NULL DEFAULT '0',
			UNIQUE KEY id (id)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$icdb->query($sql);
	}
	$table_name = $icdb->prefix."options";
	if($icdb->get_var("SHOW TABLES LIKE '".$table_name."'") != $table_name) {
		$sql = "CREATE TABLE IF NOT EXISTS ".$table_name." (
			id int(11) NOT NULL AUTO_INCREMENT,
			options_key varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			options_value text COLLATE utf8_unicode_ci NOT NULL,
			UNIQUE KEY id (id)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$icdb->query($sql);
	}
	$table_name = $icdb->prefix."sessions";
	if($icdb->get_var("SHOW TABLES LIKE '".$table_name."'") != $table_name) {
		$sql = "CREATE TABLE IF NOT EXISTS ".$table_name." (
			id int(11) NOT NULL AUTO_INCREMENT,
			ip varchar(31) COLLATE utf8_unicode_ci NOT NULL,
			session_id varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			registered int(11) NOT NULL,
			valid_period int(11) NOT NULL,
			UNIQUE KEY id (id)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$icdb->query($sql);
	}
}
?>