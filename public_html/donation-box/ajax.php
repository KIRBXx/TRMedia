<?php
if (isset($_GET['debug'])) error_reporting(-1);
else error_reporting(0);
include_once(dirname(__FILE__).'/inc/config.php');
include_once(dirname(__FILE__).'/inc/settings.php');
include_once(dirname(__FILE__).'/inc/icdb.php');
include_once(dirname(__FILE__).'/inc/functions.php');
$icdb = new ICDB(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD, TABLE_PREFIX);

install();
get_options();

if (isset($_REQUEST['callback'])) {
	header("Content-type: application/json");
	$jsonp_enabled = true;
	$jsonp_callback = $_REQUEST['callback'];
} else $jsonp_enabled = false;

$url_base = ((empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] == 'off') ? 'http://' : 'https://').$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
$filename = basename(__FILE__);
if (($pos = strpos($url_base, $filename)) !== false) $url_base = substr($url_base, 0, $pos);

if (isset($_REQUEST['action'])) {
	switch ($_REQUEST['action']) {
		case 'udb_getbox':
			$campaign_id = intval($_REQUEST['udb_id']);
			$campaign_details = $icdb->get_row("SELECT t1.*, t2.total_amount, t2.total_donors FROM ".$icdb->prefix."campaigns t1 LEFT JOIN (SELECT campaign_id, SUM(amount) AS total_amount, COUNT(*) AS total_donors FROM ".$icdb->prefix."donors WHERE status != '".STATUS_DRAFT."' AND deleted = '0' GROUP BY campaign_id) t2 ON t2.campaign_id = t1.id WHERE t1.deleted = '0' AND t1.status = '".STATUS_ACTIVE."' AND t1.id = '".$campaign_id."'");
			if (!$campaign_details) {
				if ($jsonp_enabled) {
					$html_object = new stdClass();
					$html_object->html = "";
					echo $jsonp_callback.'('.json_encode($html_object).')';
				} else echo "";
				exit;
			}
			if (isset($_REQUEST['udb_rel'])) $rel = trim(stripslashes($_REQUEST['udb_rel']));
			else $rel = '';
			if (isset($_REQUEST['udb_url'])) $return_url = trim(stripslashes($_REQUEST['udb_url']));
			else $return_url = '';
			if (substr($return_url, 0, 1) == '_') $return_url = substr($return_url, 1);
			
			$params = explode(',', $rel);
			$boxes = array();
			foreach ($params as $param) {
				$data = explode('-', $param);
				$data[0] = trim($data[0]);
				if (in_array(strtolower($data[0]), array('form', 'top', 'recent'))) {
					$boxes[strtolower($data[0])] = isset($data[1]) ? trim($data[1]) : '';
				}
			}
			if (empty($boxes)) $boxes['form'] = 'url';
			$html = '';
			foreach ($boxes as $key => $value) {
				switch ($key) {
					case 'form':
						$form = '';
						if (check_options() === true) {
							if ($campaign_details['currency'] != $options['interkassa_currency']) $options['enable_interkassa'] = "off";
							if (!in_array($campaign_details['currency'], $paypal_currency_list)) $options['enable_paypal'] = "off";
							if (!in_array($campaign_details['currency'], $payza_currency_list)) $options['enable_payza'] = "off";
							if (!in_array($campaign_details['currency'], $egopay_currency_list)) $options['enable_egopay'] = "off";
							if (!in_array($campaign_details['currency'], $perfect_currency_list)) $options['enable_perfect'] = "off";
							if (!in_array($campaign_details['currency'], $skrill_currency_list)) $options['enable_skrill'] = "off";
							if (!in_array($campaign_details['currency'], $bitpay_currency_list)) $options['enable_bitpay'] = "off";
							if (!in_array($campaign_details['currency'], $stripe_currency_list)) $options['enable_stripe'] = "off";
							if ($campaign_details['currency'] != 'USD') $options['enable_authnet'] = "off";
							$methods = 0;
							if ($options['enable_paypal'] == "on") $methods++;
							if ($options['enable_payza'] == "on") $methods++;
							if ($options['enable_interkassa'] == "on") $methods++;
							if ($options['enable_authnet'] == "on") $methods++;
							if ($options['enable_egopay'] == "on") $methods++;
							if ($options['enable_perfect'] == "on") $methods++;
							if ($options['enable_skrill'] == "on") $methods++;
							if ($options['enable_bitpay'] == "on") $methods++;
							if ($options['enable_stripe'] == "on") $methods++;
							if ($methods == 0) exit;
							
							if (!preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $return_url) || strlen($return_url) == 0) $return_url = $_SERVER["HTTP_REFERER"];
							$tac = '';
							$terms = htmlspecialchars($campaign_details['form_terms'], ENT_QUOTES);
							$terms = str_replace("\n", "<br />", $terms);
							$terms = str_replace("\r", "", $terms);
							if (!empty($campaign_details['form_terms'])) {
								$terms_id = "t".random_string(8);
								$tac = '
								<div id="'.$terms_id.'" style="display: none;">
									<div class="udb_terms">'.$terms.'</div>
								</div>
								<div style="margin-top: 5px;">By clicking the button below, I agree with the <a href="#" onclick="jQuery(\'#'.$terms_id.'\').slideToggle(300); return false;">Terms & Conditions</a>.</div>';
							}
							$intro = $campaign_details['form_intro'];
							$intro = str_replace("\n", "<br />", $intro);
							$intro = str_replace("\r", "", $intro);
							$tags = array("{min_amount}", "{currency}", "{total_amount}", "{total_donors}");
							$vals = array(number_format($campaign_details['min_amount'], 2, ".", ""), $campaign_details['currency'], number_format($campaign_details['total_amount'], 2, ".", ""), intval($campaign_details['total_donors']));
							$intro = str_replace($tags, $vals, $intro);
							if (strlen($intro) > 0) $intro = '<div style="margin-bottom: 10px;">'.$intro.'</div>';
								
							$suffix = "_".random_string(6);
								
							if ($options['enable_paypal'] == "on") $active_method = 'paypal';
							else if ($options['enable_payza'] == "on") $active_method = 'payza';
							else if ($options['enable_interkassa'] == "on") $active_method = 'interkassa';
							else if ($options['enable_authnet'] == "on") $active_method = 'authnet';
							else if ($options['enable_egopay'] == "on") $active_method = 'egopay';
							else if ($options['enable_perfect'] == "on") $active_method = 'perfect';
							else if ($options['enable_skrill'] == "on") $active_method = 'skrill';
							else if ($options['enable_bitpay'] == "on") $active_method = 'bitpay';
							else if ($options['enable_stripe'] == "on") $active_method = 'stripe';
								
							$form = '
						<div class="udb_container">
							<div name="udb" class="udb_box" id="udb'.$suffix.'">
								<div class="udb_signup_form" id="udb_signup_form'.$suffix.'">
									'.$intro.'
									'.($value == 'nourl' ? '
									<div style="overflow: hidden; height: 100%; margin-bottom: 10px;">
										<div style="width: 100%; float: left;">
											<div style="padding-right: 14px;">
												<input class="udb_input" type="text" id="name'.$suffix.'" placeholder="Enter your name (optional)" value="Enter your name (optional)" onfocus="if (this.value == \'Enter your name (optional)\') {this.value = \'\';}" onblur="if (this.value == \'\') {this.value = \'Enter your name (optional)\';}" title="Please enter your name." />
											</div>
										</div>
									</div>
									<div style="overflow: hidden; height: 100%;">
										<div style="width: 80%; float: left;">
											<div style="padding-right: 25px;">
												<input required="required" class="udb_input" type="text" id="email'.$suffix.'" placeholder="Enter your e-mail (required)" value="Enter your e-mail (required)" onfocus="if (this.value == \'Enter your e-mail (required)\') {this.value = \'\';}" onblur="if (this.value == \'\') {this.value = \'Enter your e-mail (required)\';}" title="Please enter your e-mail." />
											</div>
										</div>
										<div style="width: 20%; float: left;">
											<div style="padding-right: 14px;">
												<input required="required" class="udb_input" placeholder="'.$campaign_details['currency'].'" type="text" id="amount'.$suffix.'" value="'.$campaign_details['currency'].'" onfocus="if (this.value == \''.$campaign_details['currency'].'\') {this.value = \'\';}" onblur="if (this.value == \'\') {this.value = \''.$campaign_details['currency'].'\';}" title="How much would you like to donate?" style="text-align: right;" />
											</div>
										</div>
									</div>' : '
									<div style="overflow: hidden; height: 100%; margin-bottom: 10px;">
										<div style="width: 50%; float: left;">
											<div style="padding-right: 25px;">
												<input class="udb_input" type="text" id="name'.$suffix.'" placeholder="Enter your name (optional)" value="Enter your name (optional)" onfocus="if (this.value == \'Enter your name (optional)\') {this.value = \'\';}" onblur="if (this.value == \'\') {this.value = \'Enter your name (optional)\';}" title="Please enter your name." />
											</div>
										</div>
										<div style="width: 50%; float: left;">
											<div style="padding-right: 14px;">
												<input required="required" class="udb_input" type="text" id="email'.$suffix.'" placeholder="Enter your e-mail (required)" value="Enter your e-mail (required)" onfocus="if (this.value == \'Enter your e-mail (required)\') {this.value = \'\';}" onblur="if (this.value == \'\') {this.value = \'Enter your e-mail (required)\';}" title="Please enter your e-mail." />
											</div>
										</div>
									</div>
									<div style="overflow: hidden; height: 100%;">
										<div style="width: 80%; float: left;">
											<div style="padding-right: 25px;">
												<input class="udb_input" type="text" id="url'.$suffix.'" placeholder="Enter website URL (optional)" value="Enter website URL (optional)" onfocus="if (this.value == \'Enter website URL (optional)\') {this.value = \'\';}" onblur="if (this.value == \'\') {this.value = \'Enter website URL (optional)\';}" title="Please enter website URL." />
											</div>
										</div>
										<div style="width: 20%; float: left;">
											<div style="padding-right: 14px;">
												<input required="required" class="udb_input" placeholder="'.$campaign_details['currency'].'" type="text" id="amount'.$suffix.'" value="'.$campaign_details['currency'].'" onfocus="if (this.value == \''.$campaign_details['currency'].'\') {this.value = \'\';}" onblur="if (this.value == \'\') {this.value = \''.$campaign_details['currency'].'\';}" title="How much would you like to donate?" style="text-align: right;" />
											</div>
										</div>
									</div>');
							$checked = ' checked="checked"';
							if ($methods > 1) {
								$form .= '
								<div style="overflow: hidden; height: 100%; margin-top: 10px;">';
								if ($options['enable_paypal'] == "on") {
									$form .= '
									<div style="background: transparent url('.$url_base.'img/logo_paypal.png) 25px 1px no-repeat; height: 30px; width: 110px; float: left; margin-right: 30px;">
										<input type="radio" id="method_paypal'.$suffix.'" name="method'.$suffix.'" style="margin: 4px 0px;"'.$checked.'>
									</div>';
									$checked = '';
								}
								if ($options['enable_payza'] == "on") {
									$form .= '
									<div style="background: transparent url('.$url_base.'img/logo_payza.png) 25px 0px no-repeat; height: 30px; width: 123px; float: left; margin-right: 30px;">
										<input type="radio" id="method_payza'.$suffix.'" name="method'.$suffix.'" style="margin: 4px 0px;"'.$checked.'>
									</div>';
									$checked = '';
								}
								if ($options['enable_skrill'] == "on") {
									$form .='
									<div style="background: transparent url('.$url_base.'img/logo_skrill.png) 25px -1px no-repeat; height: 30px; width: 97px; float: left; margin-right: 30px;">
										<input type="radio" id="method_skrill'.$suffix.'" name="method'.$suffix.'" style="margin: 4px 0px;"'.$checked.'>
									</div>';
									$checked = '';
								}
								if ($options['enable_interkassa'] == "on") {
									$form .= '
									<div style="background: transparent url('.$url_base.'img/logo_interkassa.png) 25px 0px no-repeat; height: 30px; width: 120px; float: left; margin-right: 30px;">
										<input type="radio" id="method_interkassa'.$suffix.'" name="method'.$suffix.'" style="margin: 4px 0px;"'.$checked.'>
									</div>';
									$checked = '';
								}
								if ($options['enable_authnet'] == "on") {
									$form .='
									<div style="background: transparent url('.$url_base.'img/logo_authnet.png) 25px 1px no-repeat; height: 30px; width: 147px; float: left; margin-right: 30px;">
										<input type="radio" id="method_authnet'.$suffix.'" name="method'.$suffix.'" style="margin: 4px 0px;"'.$checked.'>
									</div>';
									$checked = '';
								}
								if ($options['enable_egopay'] == "on") {
									$form .='
									<div style="background: transparent url('.$url_base.'img/logo_egopay.png) 25px 0px no-repeat; height: 30px; width: 84px; float: left; margin-right: 30px;">
										<input type="radio" id="method_egopay'.$suffix.'" name="method'.$suffix.'" style="margin: 4px 0px;"'.$checked.'>
									</div>';
									$checked = '';
								}
								if ($options['enable_perfect'] == "on") {
									$form .='
											<div style="background: transparent url('.$url_base.'img/logo_perfect.png) 25px 0px no-repeat; height: 30px; width: 156px; float: left; margin-right: 30px;">
												<input type="radio" id="method_perfect'.$suffix.'" name="method'.$suffix.'" style="margin: 4px 0px;"'.$checked.'>
											</div>';
									$checked = '';
								}
								if ($options['enable_stripe'] == "on") {
									$form .='
											<div style="background: transparent url('.$url_base.'img/logo_cards.png) 25px 0px no-repeat; height: 30px; width: 170px; float: left; margin-right: 30px;">
												<input type="radio" id="method_stripe'.$suffix.'" name="method'.$suffix.'" style="margin: 4px 0px;"'.$checked.'>
											</div>';
									$checked = '';
								}
								if ($options['enable_bitpay'] == "on") {
									$form .='
											<div style="background: transparent url('.$url_base.'img/logo_bitcoin.png) 25px 0px no-repeat; height: 30px; width: 120px; float: left; margin-right: 30px;">
												<input type="radio" id="method_bitpay'.$suffix.'" name="method'.$suffix.'" style="margin: 4px 0px;"'.$checked.'>
											</div>';
									$checked = '';
								}
								$form .= '
								</div>';
							}
							$form .= $tac.'
									<input type="hidden" id="campaign'.$suffix.'" value="'.$campaign_id.'" />
									<input type="button" class="udb_submit" id="submit'.$suffix.'" value="Continue" onclick=\'udb_clickhandler("'.$suffix.'", "'.$active_method.'", "'.$return_url.'");\' />
									<img id="loading'.$suffix.'" class="udb_loading" src="'.$url_base.'img/loading.gif" alt="">
								</div>
								<div class="udb_confirmation_container" id="udb_confirmation_container'.$suffix.'"></div>
								<div id="message'.$suffix.'" class="udb_message"></div>
							</div>
						</div>';
						} else $form = '<div class="udb_container"><div name="udb" class="udb_box"><div class="udb_confirmation_info" style="text-align: center;"><strong>Universal Donation Box.</strong> Please check settings!</div></div></div>';
						$html .= $form;
						break;
					
					case 'top':
						$limit = intval($value);
						if ($limit < 1) $limit = 1;
						else if ($limit > 100) $limit = 100;
						$rows = $icdb->get_rows("SELECT * FROM ".$icdb->prefix."donors WHERE deleted = '0' AND status = '".STATUS_ACTIVE."' AND campaign_id = '".$campaign_id."' ORDER BY amount DESC LIMIT 0, ".$limit);
						$data = '';
						
						$intro = $campaign_details['top_intro'];
						$intro = str_replace("\n", "<br />", $intro);
						$intro = str_replace("\r", "", $intro);
						$tags = array("{min_amount}", "{currency}");
						$vals = array($campaign_details['min_amount'], $campaign_details['currency']);
						$intro = str_replace($tags, $vals, $intro);
						if (strlen($intro) > 0) $intro = '<div style="margin-bottom: 10px;">'.$intro.'</div>';
						
						if (sizeof($rows) > 0) {
							$data .= '
						<div class="udb_container">
							<div name="udb" class="udb_box"">
								<div class="udb_confirmation_info">
									'.$intro.'
									<table class="udb_confirmation_table">';
							foreach ($rows as $row) {
								$data .= '
										<tr>
											<td>'.(!empty($row['url']) ? '<a href="'.$row['url'].'" target="_blank">' : '').(empty($row['name']) ? 'Hidden Donor' : htmlspecialchars($row['name'], ENT_QUOTES)).(!empty($row['url']) ? '</a>' : '').'</td>
											<td style="text-align: right; width: 100px;">'.number_format($row['amount'], 2, ".", "").' '.$row['currency'].'</td>
										</tr>';
							}
							$data .= '
									</table>
								</div>							
							</div>
						</div>';
						}
						$html .= $data;
						break;

					case 'recent':
						$limit = intval($value);
						if ($limit < 1) $limit = 1;
						else if ($limit > 100) $limit = 100;
						$rows = $icdb->get_rows("SELECT * FROM ".$icdb->prefix."donors WHERE deleted = '0' AND status = '".STATUS_ACTIVE."' AND campaign_id = '".$campaign_id."' ORDER BY registered DESC LIMIT 0, ".$limit);
						$data = '';
						
						$intro = $campaign_details['recent_intro'];
						$intro = str_replace("\n", "<br />", $intro);
						$intro = str_replace("\r", "", $intro);
						$tags = array("{min_amount}", "{currency}");
						$vals = array($campaign_details['min_amount'], $campaign_details['currency']);
						$intro = str_replace($tags, $vals, $intro);
						if (strlen($intro) > 0) $intro = '<div style="margin-bottom: 10px;">'.$intro.'</div>';
						
						if (sizeof($rows) > 0) {
							$data .= '
						<div class="udb_container">
							<div name="udb" class="udb_box"">
								<div class="udb_confirmation_info">
									'.$intro.'
									<table class="udb_confirmation_table">';
							foreach ($rows as $row) {
								$data .= '
										<tr>
											<td>'.(!empty($row['url']) ? '<a href="'.$row['url'].'" target="_blank">' : '').(empty($row['name']) ? 'Hidden Donor' : htmlspecialchars($row['name'], ENT_QUOTES)).(!empty($row['url']) ? '</a>' : '').'</td>
											<td style="text-align: right; width: 100px;">'.number_format($row['amount'], 2, ".", "").' '.$row['currency'].'</td>
										</tr>';
							}
							$data .= '
									</table>
								</div>							
							</div>
						</div>';
						}
						$html .= $data;
						break;

					default:
						break;
				}
			}
			if ($jsonp_enabled) {
				$html_object = new stdClass();
				$html_object->html = $html;
				echo $jsonp_callback.'('.json_encode($html_object).')';
			} else echo $html;
			exit;
			break;
		
		case 'udb_submit':
			$html = '';
			$name = trim(stripslashes($_REQUEST['udb_name']));
			$email = trim(stripslashes($_REQUEST['udb_email']));
			if (isset($_REQUEST['udb_url'])) $url = trim(stripslashes($_REQUEST['udb_url']));
			else $url = '';
			if (substr($url, 0, 1) == '_') $url = substr($url, 1);
			
			$amount = floatval(trim($_REQUEST['udb_amount']));
			$payment_method = trim(stripslashes($_REQUEST['udb_method']));
			$suffix = trim(stripslashes($_REQUEST['udb_suffix']));
			$return_url = trim(stripslashes($_REQUEST['udb_return']));
			if (substr($return_url, 0, 1) == '_') $return_url = substr($return_url, 1);
			$campaign_id = intval($_REQUEST['udb_id']);
			$campaign_details = $icdb->get_row("SELECT * FROM ".$icdb->prefix."campaigns WHERE deleted = '0' AND status = '".STATUS_ACTIVE."' AND id = '".$campaign_id."'");
			if (!$campaign_details) $error .= '<li>Campaign not found.</li>';
			if (!preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $return_url) || strlen($return_url) == 0) $return_url = $_SERVER["HTTP_REFERER"];
			if ($name == 'Enter your name (optional)') $name = '';
			if ($url == 'Enter website URL (optional)' || $url == 'undefined') $url = '';
			$error = '';
			if (strlen($name) > 64) $error .= '<li>Your name is too long.</li>';
			if ($email == '') {
				$error .= '<li>Your e-mail address is required.</li>';
			} else if (!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i", $email)) {
				$error .= '<li>You have entered an invalid e-mail address.</li>';
			} else if (strlen($email) > 64) {
				$error .= '<li>Your email is too long.</li>';
			}
			
			if (!is_numeric($amount) || floatval($amount) < $campaign_details['min_amount']) {
				$error .= '<li>Donation amount must be at least '.number_format($campaign_details['min_amount'], 2, ".", "").' '.$campaign_details['currency'].'.</li>';
			}
			if (strlen($url) > 0) {
				if (substr(strtolower($url), 0, 7) != "http://" && substr(strtolower($url), 0, 8) != "https://") {
					$url = 'http://'.$url;
				}
			}
			if (!preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url) && strlen($url) > 0) {
				$error .= '<li>Website URL must be valid URL.</li>';
			} else if (strlen($url) > 192) {
				$error .= '<li>Your website URL is too long.</li>';
			}

			if ($payment_method == 'interkassa') {
				if ($options['enable_interkassa'] != 'on') $error .= '<li>Payment method not supported.</li>';
				$method = '
				<div style="background: transparent url('.$url_base.'img/logo_interkassa.png) 0px 0px no-repeat; height: 26px; width: 120px; float: left;"></div>';
			} else if ($payment_method == 'payza') {
				if ($options['enable_payza'] != 'on') $error .= '<li>Payment method not supported.</li>';
				$method = '
				<div style="background: transparent url('.$url_base.'img/logo_payza.png) 0px 0px no-repeat; height: 26px; width: 155px; float: left;"></div>';
			} else if ($payment_method == 'authnet') {
				if ($options['enable_authnet'] != 'on') $error .= '<li>Donation method not supported.</li>';
				$method = '
				<div style="background: transparent url('.$url_base.'img/logo_authnet.png) 0px 0px no-repeat; height: 30px; width: 147px; float: left;"></div>';
			} else if ($payment_method == 'skrill') {
				if ($options['enable_skrill'] != 'on') $error .= '<li>Payment method not supported.</li>';
				$method = '
				<div style="background: transparent url('.$url_base.'img/logo_skrill.png) 0px 0px no-repeat; height: 30px; width: 97px; float: left;"></div>';
			} else if ($payment_method == 'egopay') {
				if ($options['enable_egopay'] != 'on') $error .= '<li>Payment method not supported.</li>';
				$method = '
				<div style="background: transparent url('.$url_base.'img/logo_egopay.png) 0px 0px no-repeat; height: 30px; width: 84px; float: left;"></div>';
			} else if ($payment_method == 'perfect') {
				if ($options['enable_perfect'] != 'on') $error .= '<li>Payment method not supported.</li>';
				$method = '
				<div style="background: transparent url('.$url_base.'img/logo_perfect.png) 0px 0px no-repeat; height: 30px; width: 156px; float: left;"></div>';
			} else if ($payment_method == 'bitpay') {
				if ($options['enable_bitpay'] != 'on') $error .= '<li>Payment method not supported.</li>';
				$method = '
				<div style="background: transparent url('.$url_base.'img/logo_bitcoin.png) 0px 0px no-repeat; height: 30px; width: 120px; float: left;"></div>';
			} else if ($payment_method == 'stripe') {
				if ($options['enable_stripe'] != 'on') $error .= '<li>Payment method not supported.</li>';
				$method = '
				<div style="background: transparent url('.$url_base.'img/logo_cards.png) 0px 0px no-repeat; height: 30px; width: 170px; float: left;"></div>';
			} else {
				if ($options['enable_paypal'] != 'on') $error .= '<li>Donation method not supported.</li>';
				$method = '
				<div style="background: transparent url('.$url_base.'img/logo_paypal.png) 0px 0px no-repeat; height: 26px; width: 110px; float: left;"></div>';
			}
			if ($error != '') {
				$html .= '<div class="udb_error_message">Attention! Please correct the errors below and try again.';
				$html .= '<ul class="udb_error_messages">'.$error.'</ul>';
				$html .= '</div>';
			} else {
				$amount = number_format($amount, 2, ".", "");
				$icdb->query("INSERT INTO ".$icdb->prefix."donors (campaign_id, name, email, url, amount, currency, status, details, registered, deleted) VALUES ('".$campaign_id."', '".mysql_real_escape_string($name)."', '".mysql_real_escape_string($email)."', '".mysql_real_escape_string($url)."', '".$amount."', '".mysql_real_escape_string($campaign_details['currency'])."', '".STATUS_DRAFT."', '".mysql_real_escape_string($return_url)."', '".time()."', '0')");
				$donor_id = $icdb->insert_id;
				$html .= '
<div class="udb_confirmation_info">
	<table class="udb_confirmation_table">
		<tr><td style="width: 170px"><strong>Name:</strong></td><td class="udb_confirmation_data">'.(empty($name) ? '-' : htmlspecialchars($name, ENT_QUOTES)).'</td></tr>
		<tr><td><strong>E-Mail:</strong></td><td class="udb_confirmation_data">'.htmlspecialchars($email, ENT_QUOTES).'</td></tr>
		'.(empty($url) ? '' : '
		<tr><td><strong>Website:</strong></td><td class="udb_confirmation_data"><a href="'.$url.'" target="_blank">'.htmlspecialchars($url, ENT_QUOTES).'</a></td></tr>').'
		<tr><td><strong>Amount:</strong></td><td class="udb_confirmation_price">'.$amount.' '.$campaign_details['currency'].'</td></tr>
		<tr><td><strong>Payment method:</strong></td><td class="udb_confirmation_data">'.$method.'</td></tr>
	</table>
	<div class="udb_signup_buttons">';
				if ($payment_method == 'bitpay') {
					$html .= '
		<input type="button" class="udb_submit" id="udb_bitpay'.$suffix.'" value="Confirm and pay" onclick="udb_bitpay('.$donor_id.', \''.$amount.'\', \''.$email.'\', \''.$suffix.'\', \''.$return_url.'\');">
		<input type="button" class="udb_submit" id="udb_bitpay_edit'.$suffix.'" value="Edit info" onclick="udb_edit(\''.$suffix.'\');">
		<img id="udb_loading2'.$suffix.'" class="udb_loading" src="'.$url_base.'img/loading.gif" alt="">';
				} else if ($payment_method == 'stripe') {
					$html .= '
		<input type="button" class="udb_submit" id="udb_stripe'.$suffix.'" value="Confirm and pay" onclick="udb_stripe('.$donor_id.', \''.$suffix.'\', \''.$return_url.'\');">
		<input type="button" class="udb_submit" id="udb_stripe_edit'.$suffix.'" value="Edit info" onclick="udb_edit(\''.$suffix.'\');">
		<img id="udb_loading2'.$suffix.'" class="udb_loading" src="'.$url_base.'img/loading.gif" alt="">';
				} else {
					$html .= '
		<input type="button" class="udb_submit" id="udb_signup_pay" value="Confirm and pay" onclick="jQuery(\'#udb_pay'.$suffix.'\').click();">
		<input type="button" class="udb_submit" id="udb_signup_edit" value="Edit info" onclick="udb_edit(\''.$suffix.'\');">';
				}
				$html .= '
	</div>';
				if ($payment_method == 'interkassa') {
					$html .= '
	<form action="https://www.interkassa.com/lib/payment.php" method="post" target="_top" style="display:none;">
		<input type="hidden" name="ik_shop_id" value="'.$options['interkassa_shop_id'].'">
		<input type="hidden" name="ik_payment_amount" value="'.$amount.'">
		<input type="hidden" name="ik_payment_id" value="'.$donor_id.'_'.time().'">
		<input type="hidden" name="ik_payment_desc" value="Donation">
		<input type="hidden" name="ik_paysystem_alias" value="">
		<input type="hidden" name="ik_baggage_fields" value="'.$email.'">
		<input type="hidden" name="ik_success_url" value="'.$return_url.'">
		<input type="hidden" name="ik_success_method" value="LINK">
		<input type="hidden" name="ik_fail_url" value="'.$_SERVER["HTTP_REFERER"].'">
		<input type="hidden" name="ik_fail_method" value="LINK">
		<input type="hidden" name="ik_status_url" value="'.$url_base.'ipn.php?method=interkassa">
		<input type="hidden" name="ik_status_method" value="POST">
		<input type="submit" id="udb_pay'.$suffix.'" value="Submit">
	</form>';
				} else if ($payment_method == 'payza') {
					$html .= '
	<form action="'.($options['payza_sandbox'] == 'on' ? 'https://sandbox.payza.com/sandbox/payprocess.aspx' : 'https://secure.payza.com/PayProcess.aspx').'" method="post" target="_top" style="display:none;">
		<input type="hidden" name="ap_merchant" value="'.$options['payza_id'].'">
		<input type="hidden" name="ap_purchasetype" value="item">
		<input type="hidden" name="ap_itemname" value="Donation">
		<input type="hidden" name="ap_amount" value="'.$amount.'">
		<input type="hidden" name="ap_currency" value="'.$campaign_details['currency'].'">
		<input type="hidden" name="apc_1" value="'.$email.'">
		<input type="hidden" name="ap_itemcode" value="ID'.$donor_id.'">
		<input type="hidden" name="ap_returnurl" value="'.$return_url.'">
		<input type="hidden" name="ap_cancelurl" value="'.$_SERVER["HTTP_REFERER"].'">
		<input type="hidden" name="ap_alerturl" value="'.$url_base.'ipn.php?method=payza">
		<input type="hidden" name="ap_ipnversion" value="2">
		<input type="submit" id="udb_pay'.$suffix.'" value="Submit">
	</form>';
				} else if ($payment_method == 'authnet') {
					$fp_timestamp = time();
					$fp_sequence = $donor_id.time();
					$fingerprint = get_fingerprint($options['authnet_login'], $options['authnet_key'], $amount, $fp_sequence, $fp_timestamp);
					$html .= '
	<form action="'.(($options['authnet_sandbox'] == "on") ? 'https://test.authorize.net/gateway/transact.dll' : 'https://secure.authorize.net/gateway/transact.dll').'" method="post" target="_top" style="display:none;">';
					if ($options['authnet_sandbox'] == "on") {
						$html .= '
		<input type="hidden" name="x_test_request" value="true">';
					} else {
						$html .= '
		<input type="hidden" name="x_test_request" value="false">';
					}
					$style = get_auth_style();
					$html .= '
		<input type="hidden" name="x_version" value="3.1">
		<input type="hidden" name="x_show_form" value="payment_form">
		<input type="hidden" name="x_relay_response" value="true">
		<input type="hidden" name="x_method" value="cc">
		<input type="hidden" name="x_fp_hash" value="'.$fingerprint.'">
		<input type="hidden" name="x_fp_timestamp" value="'.$fp_timestamp.'">
		<input type="hidden" name="x_fp_sequence" value="'.$fp_sequence.'">
		<input type="hidden" name="x_receipt_link_url" value="'.$return_url.'">
		<input type="hidden" name="x_login" value="'.$options['authnet_login'].'">
		<input type="hidden" name="x_email" value="'.$email.'">
		<input type="hidden" name="x_relay_url" value="'.$url_base.'ipn.php?method=authnet">
		<input type="hidden" name="x_description" value="Donation">
		<input type="hidden" name="x_amount" value="'.$amount.'">
		<input type="hidden" name="x_invoice_num" value="ID'.$donor_id.'N'.time().'">
		<input type="hidden" name=x_Header_HTML_Payment_Form value="'.htmlspecialchars($style, ENT_QUOTES).'">
		<input type="submit" id="udb_pay'.$suffix.'" value="Submit">
	</form>';
				} else if ($payment_method == 'skrill') {
					$html .= '
	<form action="https://www.moneybookers.com/app/payment.pl" method="post" target="_top" style="display:none;">
		<input type="hidden" name="pay_to_email" value="'.$options['skrill_id'].'">
		<input type="hidden" name="return_url" value="'.$return_url.'">
		<input type="hidden" name="cancel_url" value="'.$_SERVER["HTTP_REFERER"].'">
		<input type="hidden" name="status_url" value="'.$url_base.'ipn.php?method=skrill">
		<input type="hidden" name="language" value="EN">
		<input type="hidden" name="amount" value="'.$amount.'">
		<input type="hidden" name="currency" value="'.$campaign_details['currency'].'">
		<input type="hidden" name="detail1_description" value="Donation">
		<input type="hidden" name="detail1_text" value="'.$amount.' '.$campaign_details['currency'].'">
		<input type="hidden" name="merchant_fields" value="donor_id">
		<input type="hidden" name="donor_id" value="'.$donor_id.'">
		<input type="submit" id="udb_pay'.$suffix.'" value="Submit">
	</form>';
				} else if ($payment_method == 'egopay') {
						$data = array();
						$data['amount'] = $amount;
						$data['currency'] = $campaign_details['currency'];
						$data['description'] = "Donation";
						$data['cf_1'] = $donor_id;
						$data['success_url'] = $return_url;
						$data['fail_url'] = $_SERVER["HTTP_REFERER"];
						$sdata = serialize($data);
						$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
						$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
						$crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $options['egopay_store_pass'], $sdata, MCRYPT_MODE_ECB, $iv);
						$hash = base64_encode($crypttext);
						$hash = str_replace(array('+','/','='), array('-','_',''), $hash);
						$hash = $options['egopay_store_id'].$hash;
						$html .= '
	<form action="https://www.egopay.com/api/pay" method="post" target="_top" style="display:none;">    
		<input type="hidden" name="hash" value="'.$hash.'" />
		<input type="submit" id="udb_pay'.$suffix.'" value="Submit">
	</form>';
					} else if ($payment_method == 'perfect') {
						$html .= '
	<form action="https://perfectmoney.is/api/step1.asp" method="post" target="_top" style="display:none;">
		<input type="hidden" name="PAYEE_ACCOUNT" value="'.$options['perfect_account_id'].'">
		<input type="hidden" name="PAYEE_NAME" value="'.$options['perfect_payee_name'].'">
		<input type="hidden" name="PAYMENT_AMOUNT" value="'.$amount.'">
		<input type="hidden" name="PAYMENT_UNITS" value="'.$campaign_details['currency'].'">
		<input type="hidden" name="SUGGESTED_MEMO" value="Donation">
		<input type="hidden" name="SUGGESTED_MEMO_NOCHANGE" value="1">
		<input type="hidden" name="PAYMENT_ID" value="'.$donor_id.'">
		<input type="hidden" name="PAYMENT_URL" value="'.$return_url.'">
		<input type="hidden" name="PAYMENT_URL_METHOD" value="LINK">
		<input type="hidden" name="NOPAYMENT_URL" value="'.$_SERVER["HTTP_REFERER"].'">
		<input type="hidden" name="NOPAYMENT_URL_METHOD" value="LINK">
		<input type="hidden" name="STATUS_URL" value="'.$url_base.'ipn.php">
		<input type="hidden" name="BAGGAGE_FIELDS" value="method payer_email">
		<input type="hidden" name="method" value="perfect">
		<input type="hidden" name="payer_email" value="'.$email.'">
		<input type="submit" id="udb_pay'.$suffix.'" value="Submit">
	</form>';
					} else if ($payment_method == 'bitpay') {
					} else if ($payment_method == 'stripe') {
						$html .= '
	<div style="display: none;">
		<input type="hidden" id="udb_stripe_publishable'.$suffix.'" value="'.$options['stripe_publishable'].'">
		<input type="hidden" id="udb_stripe_amount'.$suffix.'" value="'.intval($amount*100).'">
		<input type="hidden" id="udb_stripe_currency'.$suffix.'" value="'.$campaign_details['currency'].'">
		<input type="hidden" id="udb_stripe_label'.$suffix.'" value="Donation">
		<input type="hidden" id="udb_stripe_email'.$suffix.'" value="'.htmlspecialchars($email, ENT_QUOTES).'">
	</div>';
				} else {
						$html .= '
	<form action="'.(($options['paypal_sandbox'] == "on") ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr').'" method="post" target="_top" style="display: none;">
		<input type="hidden" name="cmd" value="_xclick">
		<input type="hidden" name="charset" value="utf-8">					
		<input type="hidden" name="business" value="'.$options['paypal_id'].'">
		<input type="hidden" name="no_shipping" value="1">
		<input type="hidden" name="rm" value="2">
		<input type="hidden" name="item_name" value="Donation">
		<input type="hidden" name="item_number" value="'.$donor_id.'">
		<input type="hidden" name="amount" value="'.$amount.'">
		<input type="hidden" name="currency_code" value="'.$campaign_details['currency'].'">
		<input type="hidden" name="custom" value="'.$email.'">
		<input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynow_LG.gif:NonHostedGuest">
		<input type="hidden" name="return" value="'.$return_url.'">
		<input type="hidden" name="cancel_return" value="'.$_SERVER["HTTP_REFERER"].'">
		<input type="hidden" name="notify_url" value="'.$url_base.'ipn.php?method=paypal">
		<input type="submit" id="udb_pay'.$suffix.'" value="Submit">
	</form>';
				}
				$html .= '
</div>';
			}
			if ($jsonp_enabled) {
				$html_object = new stdClass();
				$html_object->html = $html;
				echo $jsonp_callback.'('.json_encode($html_object).')';
			} else echo $html;
			
			exit;
			break;

		case 'udb_bitpayurl':
			$donor_id = intval($_REQUEST['udb_id']);
			
			$donor_details = $icdb->get_row("SELECT t1.*, t2.title AS campaign_title FROM ".$icdb->prefix."donors t1 LEFT JOIN ".$icdb->prefix."campaigns t2 ON t1.campaign_id = t2.id WHERE t1.id = '".$donor_id."' AND t1.status = '".STATUS_DRAFT."'");
			$error = '';
			if (!$donor_details) $error .= '<li>Something went wrong.</li>';

			if (empty($error)) {
				$bitpay_options['orderID'] = $donor_details['id'].'N'.time();
				$bitpay_options['itemDesc'] = $donor_details['campaign_title'];
				$bitpay_options['itemCode'] = $donor_details['id'];
				$bitpay_options['notificationURL'] = $url_base.'ipn.php?method=bitpay';;
				$bitpay_options['price'] = number_format($donor_details['amount'], 2, ".", "");
				$bitpay_options['currency'] = $donor_details['currency'];
				$bitpay_options['physical'] = 'false';
				$bitpay_options['transactionSpeed'] = $options['bitpay_speed'];
				$bitpay_options['fullNotifications'] = 'false';
				$bitpay_options['redirectURL'] = $donor_details['details'];
				$bitpay_options['posData'] = '{"donor_id" : "'.$donor_details['id'].'", "payer_email" : "'.$donor_details['email'].'"}';

				$post = json_encode($bitpay_options);

				$curl = curl_init('https://bitpay.com/api/invoice/');
				curl_setopt($curl, CURLOPT_POST, 1);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
			
				$header = array(
					'Content-Type: application/json',
					'Content-Length: '.strlen($post),
					'Authorization: Basic '.base64_encode($options['bitpay_key']),
					);

				curl_setopt($curl, CURLOPT_PORT, 443);
				curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
				curl_setopt($curl, CURLOPT_TIMEOUT, 10);
				curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC ) ;
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1); // verify certificate
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // check existence of CN and verify that it matches hostname
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
				curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
				
				$json = curl_exec($curl);
				curl_close($curl);

				if ($json === false) {
					$html = '<div class="udb_error_message">Attention! Please correct the errors below and try again.<ul class="udb_error_messages"><li>Payment gateway is not available now.</li></ul></div>';
				} else {
					$post = json_decode($json, true);
					if (!$post) {
						$html = '<div class="udb_error_message">Attention! Please correct the errors below and try again.<ul class="udb_error_messages"><li>Payment gateway is not available now.</li></ul></div>';
					} else {
						if (isset($post['error'])) {
							$html = '<div class="udb_error_message">Attention! Please correct the errors below and try again.<ul class="udb_error_messages"><li>'.ucfirst($post['error']['message']).'</li></ul></div>';
						} else if ($post['status'] != 'new') {
							$html = '<div class="udb_error_message">Attention! Please correct the errors below and try again.<ul class="udb_error_messages"><li>Payment gateway is not available now.</li></ul></div>';
						} else $html = $post['url'];
					}
				}
			} else {
				$html = '<div class="udb_error_message">Attention! Please correct the errors below and try again.<ul class="udb_error_messages">'.$error.'</ul></div>';
			}
			if ($jsonp_enabled) {
				$html_object = new stdClass();
				$html_object->html = $html;
				echo $jsonp_callback.'('.json_encode($html_object).')';
			} else echo $html;
			
			exit;
			break;

		case 'udb_stripecharge':
			$token = trim(stripslashes($_REQUEST['udb_token']));
			$donor_id = intval($_REQUEST['udb_id']);
			
			$donor_details = $icdb->get_row("SELECT t1.*, t2.title AS campaign_title FROM ".$icdb->prefix."donors t1 LEFT JOIN ".$icdb->prefix."campaigns t2 ON t1.campaign_id = t2.id WHERE t1.id = '".$donor_id."' AND t1.status = '".STATUS_DRAFT."'");
			$error = '';
			if (!$donor_details) $error .= '<li>Something went wrong.</li>';
			if (empty($error)) {
				require_once(dirname(__FILE__).'/lib/Stripe.php');

				try {
					Stripe::setApiKey($options['stripe_secret']);
					
					$charge = Stripe_Charge::create(array(
						"amount" => intval($donor_details['amount']*100),
						"currency" => $donor_details['currency'],
						"card" => $token,
						"description" => $donor_details['email'])
					);

					$post = json_decode($charge, true);
					
					$response = 'token='.$token;
					foreach ($post as $key => $value) {
						if (is_array($value)) $response .= "&".$key."=".urlencode(str_replace('&', ' ', serialize($value)));
						else $response .= "&".$key."=".urlencode($value);
					}

					$icdb->query("UPDATE ".$icdb->prefix."donors SET status = '".STATUS_ACTIVE."' WHERE id = '".$donor_details['id']."'");

					$sql = "INSERT INTO ".$icdb->prefix."transactions (
						donor_id, payer_name, payer_email, gross, currency, payment_status, transaction_type, txn_id, details, created) VALUES (
						'".$donor_details['id']."',
						'".mysql_real_escape_string($donor_details['name'])."',
						'".mysql_real_escape_string($donor_details['email'])."',
						'".floatval($donor_details['amount'])."',
						'".$donor_details['currency']."',
						'Completed',
						'Stripe: ".$post['card']['type']."',
						'".$token."',
						'".mysql_real_escape_string($response)."',
						'".time()."'
					)";
					$icdb->query($sql);

					$tags = array("{payer_name}", "{payer_email}", "{amount}", "{currency}", "{campaign_title}", "{transaction_date}", "{gateway}");
					$vals = array($post['card']['name'], $donor_details['email'], $donor_details['amount'], $donor_details['currency'], $donor_details['campaign_title'], date("Y-m-d H:i:s")." (server time)", "Stripe");
					send_thanksgiving_email($tags, $vals, $donor_details['email']);
					
					$html = '<div class="udb_confirmation_info" style="text-align: center;">Payment successfully <strong>completed</strong>.</div>';
				} catch(Exception $e) {
					$body = $e->getJsonBody();
					$err  = $body['error'];
					$html = '<div class="udb_error_message">Attention! Please correct the errors below and try again.<ul class="udb_error_messages"><li>'.$err['message'].'</li></ul></div>';
				}
			} else {
				$html = '<div class="udb_error_message">Attention! Please correct the errors below and try again.<ul class="udb_error_messages">'.$error.'</ul></div>';
			}
			if ($jsonp_enabled) {
				$html_object = new stdClass();
				$html_object->html = $html;
				echo $jsonp_callback.'('.json_encode($html_object).')';
			} else echo $html;
			
			exit;
			break;

		default:
			break;
	}
}
?>