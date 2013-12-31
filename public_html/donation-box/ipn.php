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

$url_base = ((empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] == 'off') ? 'http://' : 'https://').$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
$filename = basename(__FILE__);
if (($pos = strpos($url_base, $filename)) !== false) $url_base = substr($url_base, 0, $pos);

$campaign_title = 'Unknown Campaign';

if (!isset($_REQUEST['method'])) exit;
switch ($_REQUEST['method']) {
	case 'payza':
		if (!isset($_POST['token'])) exit;
		$token = "token=".urlencode($_POST['token']);
		$response = '';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, ($options['payza_sandbox'] == 'on' ? "https://sandbox.payza.com/sandbox/IPN2.ashx" : "https://secure.payza.com/ipn2.ashx"));
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $token);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec($ch);
		curl_close($ch);                

		if(strlen($response) > 0) {
			if(urldecode($response) == "INVALID TOKEN") {
				//the token is not valid
			} else {
				$response = urldecode($response);
				$aps = explode("&", $response);
				$info = array();
				foreach ($aps as $ap) {
					$ele = explode("=", $ap);
					$info[$ele[0]] = $ele[1];
				}

				$item_number = intval(str_replace("ID", "", $info['ap_itemcode']));
				$item_name = $info['ap_itemname'];
				$payment_status = $info['ap_status'];
				$transaction_type = $info['ap_transactiontype'];
				$txn_id = $info['ap_referencenumber'];
				$seller_id = $info['ap_merchant'];
				$payer_id = $info['ap_custemailaddress'];
				$gross_total = $info['ap_totalamount'];
				$mc_currency = $info['ap_currency'];
				$payer_name = $info['ap_custfirstname'].' '.$info['ap_custlastname'];
				$payer_email = $payer_id;

				if ($payment_status == "Success") {
					$donor_details = $icdb->get_row("SELECT t1.*, t2.title AS campaign_title FROM ".$icdb->prefix."donors t1 LEFT JOIN ".$icdb->prefix."campaigns t2 ON t1.campaign_id = t2.id WHERE t1.id = '".$item_number."'");
					if ($donor_details) $campaign_title = $donor_details["campaign_title"];
					if (!$donor_details) $payment_status = "Unrecognized";
					else {
						$payer_email = $donor_details["email"];
						if (strtolower($seller_id) != strtolower($options['payza_id'])) $payment_status = "Unrecognized";
						else {
							if (floatval($gross_total) < floatval($donor_details["amount"]) || $mc_currency != $donor_details["currency"]) $payment_status = "Unrecognized";
						}
					}
				}
				$sql = "INSERT INTO ".$icdb->prefix."transactions (
					donor_id, payer_name, payer_email, gross, currency, payment_status, transaction_type, txn_id, details, created) VALUES (
					'".$item_number."',
					'".mysql_real_escape_string($payer_name)."',
					'".mysql_real_escape_string($payer_id)."',
					'".floatval($gross_total)."',
					'".$mc_currency."',
					'".$payment_status."',
					'Payza: ".$transaction_type."',
					'".$txn_id."',
					'".mysql_real_escape_string($response)."',
					'".time()."'
				)";
				$icdb->query($sql);
				if ($payment_status == "Success") {
					$icdb->query("UPDATE ".$icdb->prefix."donors SET status = '".STATUS_ACTIVE."' WHERE id = '".$item_number."'");
					$tags = array("{payer_name}", "{payer_email}", "{amount}", "{currency}", "{campaign_title}", "{transaction_date}", "{gateway}");
					$vals = array($payer_name, $payer_id, $gross_total, $mc_currency, $campaign_title, date("Y-m-d H:i:s")." (server time)", "Payza");
					send_thanksgiving_email($tags, $vals, $payer_email);
				} else {
					$icdb->query("UPDATE ".$icdb->prefix."donors SET status = '".STATUS_PENDING."' WHERE id = '".$item_number."'");
					$tags = array("{payer_name}", "{payer_email}", "{amount}", "{currency}", "{campaign_title}", "{payment_status}", "{transaction_date}", "{gateway}");
					$vals = array($payer_name, $payer_id, $gross_total, $mc_currency, $campaign_title, $payment_status, date("Y-m-d H:i:s")." (server time)", "Payza");
					send_failed_email($tags, $vals, $payer_email);
				}
			}
		}
		exit;
		break;
	
	case 'egopay':
		if (!isset($_POST['product_id'])) exit;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://www.egopay.com/api/request");
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, 
			"product_id=".urlencode($_POST['product_id'])
			."&security_password=".urlencode($options['egopay_store_pass'])
			."&store_id=".urlencode($options['egopay_store_id'])
		);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec($ch);
		curl_close($ch);                
		if ($response == 'INVALID') die();
		$info = array();
		parse_str($response, $info);	

		$item_number = intval($info['cf_1']);
		$payment_status = $info['sStatus'];
		$transaction_type = $info['sType'];
		$txn_id = $info['sId'];
		$payer_id = $info['sEmail'];
		$gross_total = $info['fAmount'];
		$mc_currency = $info['sCurrency'];
		$payer_name = $info['sEmail'];
		$payer_email = $info['sEmail'];
		if ($payment_status == 'TEST SUCCESS') $payment_status = "Completed";
		if ($payment_status == "Completed") {
			$donor_details = $icdb->get_row("SELECT t1.*, t2.title AS campaign_title FROM ".$icdb->prefix."donors t1 LEFT JOIN ".$icdb->prefix."campaigns t2 ON t1.campaign_id = t2.id WHERE t1.id = '".$item_number."'");
			if ($donor_details) $campaign_title = $donor_details["campaign_title"];
			if (!$donor_details) $payment_status = "Unrecognized";
			else {
				$payer_email = $donor_details["email"];
				if (floatval($gross_total) < floatval($donor_details["amount"]) || $mc_currency != $donor_details["currency"]) $payment_status = "Unrecognized";
			}
		}

		$sql = "INSERT INTO ".$icdb->prefix."transactions (
			donor_id, payer_name, payer_email, gross, currency, payment_status, transaction_type, txn_id, details, created) VALUES (
			'".$item_number."',
			'".mysql_real_escape_string($payer_name)."',
			'".mysql_real_escape_string($payer_id)."',
			'".floatval($gross_total)."',
			'".$mc_currency."',
			'".$payment_status."',
			'EgoPay: ".$transaction_type."',
			'".$txn_id."',
			'".mysql_real_escape_string($response)."',
			'".time()."'
		)";
		$icdb->query($sql);
		if ($payment_status == "Completed") {
			$icdb->query("UPDATE ".$icdb->prefix."donors SET status = '".STATUS_ACTIVE."' WHERE id = '".$item_number."'");
			$tags = array("{payer_name}", "{payer_email}", "{amount}", "{currency}", "{campaign_title}", "{transaction_date}", "{gateway}");
			$vals = array($payer_name, $payer_id, $gross_total, $mc_currency, $campaign_title, date("Y-m-d H:i:s")." (server time)", "EgoPay");
			send_thanksgiving_email($tags, $vals, $payer_email);
		} else {
			$icdb->query("UPDATE ".$icdb->prefix."donors SET status = '".STATUS_PENDING."' WHERE id = '".$item_number."'");
			$tags = array("{payer_name}", "{payer_email}", "{amount}", "{currency}", "{campaign_title}", "{payment_status}", "{transaction_date}", "{gateway}");
			$vals = array($payer_name, $payer_id, $gross_total, $mc_currency, $campaign_title, $payment_status, date("Y-m-d H:i:s")." (server time)", "EgoPay");
			send_failed_email($tags, $vals, $payer_email);
		}
		exit;
		break;
	
	case 'perfect':
		if (empty($_POST['PAYMENT_ID']) || empty($_POST['PAYEE_ACCOUNT'])) die();
		$response = "";
		foreach ($_POST as $key => $value) {
			$value = urlencode(stripslashes($value));
			$response .= "&".$key."=".$value;
		}

		$str = $_POST['PAYMENT_ID'].':'.$_POST['PAYEE_ACCOUNT'].':'.
			$_POST['PAYMENT_AMOUNT'].':'.$_POST['PAYMENT_UNITS'].':'.
			$_POST['PAYMENT_BATCH_NUM'].':'.
			$_POST['PAYER_ACCOUNT'].':'.strtoupper(md5($options['perfect_passphrase'])).':'.
			$_POST['TIMESTAMPGMT'];

		$hash = strtoupper(md5($str));

		$item_number = intval($_POST['PAYMENT_ID']);
		$payment_status = "Completed";
		$transaction_type = "payment";
		$txn_id = stripslashes($_POST['PAYMENT_BATCH_NUM']);
		$seller_id = stripslashes($_POST['PAYEE_ACCOUNT']);
		$v2_hash = stripslashes($_POST['V2_HASH']);
		$gross_total = stripslashes($_POST['PAYMENT_AMOUNT']);
		$mc_currency = stripslashes($_POST['PAYMENT_UNITS']);
		$payer_name = stripslashes($_POST['PAYER_ACCOUNT']);
		$payer_email = '';

		$donor_details = $icdb->get_row("SELECT t1.*, t2.title AS campaign_title FROM ".$icdb->prefix."donors t1 LEFT JOIN ".$icdb->prefix."campaigns t2 ON t1.campaign_id = t2.id WHERE t1.id = '".$item_number."'");
		if (!$donor_details) $payment_status = "Unrecognized";
		else {
			$campaign_title = $donor_details["campaign_title"];
			$payer_email = $donor_details["email"];
			if ($v2_hash != $hash) $payment_status = "Invalid HASH";
			else if (floatval($gross_total) < floatval($donor_details["amount"]) || $mc_currency != $donor_details["currency"] || $seller_id != $options['perfect_account_id']) $payment_status = "Unrecognized";
		}

		$sql = "INSERT INTO ".$icdb->prefix."transactions (
			donor_id, payer_name, payer_email, gross, currency, payment_status, transaction_type, txn_id, details, created) VALUES (
			'".$item_number."',
			'".mysql_real_escape_string($payer_name)."',
			'".mysql_real_escape_string($payer_email)."',
			'".floatval($gross_total)."',
			'".$mc_currency."',
			'".$payment_status."',
			'PM: ".$transaction_type."',
			'".$txn_id."',
			'".mysql_real_escape_string($response)."',
			'".time()."'
		)";
		$icdb->query($sql);
		
		if ($payment_status == "Completed") {
			$icdb->query("UPDATE ".$icdb->prefix."donors SET status = '".STATUS_ACTIVE."' WHERE id = '".$item_number."'");
			$tags = array("{payer_name}", "{payer_email}", "{amount}", "{currency}", "{campaign_title}", "{transaction_date}", "{gateway}");
			$vals = array($payer_name, $payer_email, $gross_total, $mc_currency, $campaign_title, date("Y-m-d H:i:s")." (server time)", "Perfect Money");
			send_thanksgiving_email($tags, $vals, $payer_email);
		} else {
			$icdb->query("UPDATE ".$icdb->prefix."donors SET status = '".STATUS_PENDING."' WHERE id = '".$item_number."'");
			$tags = array("{payer_name}", "{payer_email}", "{amount}", "{currency}", "{campaign_title}", "{payment_status}", "{transaction_date}", "{gateway}");
			$vals = array($payer_name, $payer_email, $gross_total, $mc_currency, $campaign_title, $payment_status, date("Y-m-d H:i:s")." (server time)", "Perfect Money");
			send_failed_email($tags, $vals, $payer_email);
		}
		exit;
		break;
	
	case 'skrill':
		if (empty($_POST['pay_to_email']) || empty($_POST['pay_from_email'])) die();
		$response = "";
		foreach ($_POST as $key => $value) {
			$value = urlencode(stripslashes($value));
			$response .= "&".$key."=".$value;
		}

		$item_number = intval($_POST['donor_id']);
		$txn_id = stripslashes($_POST['mb_transaction_id']);
		$seller_id = stripslashes($_POST['pay_to_email']);
		$payer_id = stripslashes($_POST['pay_from_email']);
		$gross_total = stripslashes($_POST['amount']);
		$mc_currency = stripslashes($_POST['currency']);
		$payment_status = stripslashes($_POST['status']);
		$transaction_type = "donation";
		$md5sig = stripslashes($_POST['md5sig']);
		$payer_name = stripslashes($_POST['pay_from_email']);
		$payer_email = stripslashes($_POST['pay_from_email']);		

		if ($payment_status == 2) $payment_status = 'Completed';
		else if ($payment_status == 0) $payment_status = 'Pending';
		else if ($payment_status == -1) $payment_status = 'Cancelled';
		else if ($payment_status == -2) $payment_status = 'Failed';
		else if ($payment_status == -3) $payment_status = 'Chargeback';
			
		$hash = strtoupper(md5($_POST['merchant_id'].$_POST['transaction_id'].strtoupper(md5($options['skrill_secret_word'])).$_POST['mb_amount'].$_POST['mb_currency'].$_POST['status']));

		$donor_details = $icdb->get_row("SELECT t1.*, t2.title AS campaign_title FROM ".$icdb->prefix."donors t1 LEFT JOIN ".$icdb->prefix."campaigns t2 ON t1.campaign_id = t2.id WHERE t1.id = '".$item_number."'");
		if ($donor_details) $campaign_title = $donor_details["campaign_title"];
		if (!$donor_details) $payment_status = "Unrecognized";
		else {
			$payer_email = $donor_details["email"];
			if (strtolower($seller_id) != strtolower($options['skrill_id']) || $md5sig != $hash) $payment_status = "Unrecognized";
			else if (floatval($gross_total) < floatval($donor_details["amount"]) || $mc_currency != $donor_details["currency"]) $payment_status = "Unrecognized";
		}

		$sql = "INSERT INTO ".$icdb->prefix."transactions (
			donor_id, payer_name, payer_email, gross, currency, payment_status, transaction_type, txn_id, details, created) VALUES (
			'".$item_number."',
			'".mysql_real_escape_string($payer_name)."',
			'".mysql_real_escape_string($payer_id)."',
			'".floatval($gross_total)."',
			'".$mc_currency."',
			'".$payment_status."',
			'Skrill: ".$transaction_type."',
			'".$txn_id."',
			'".mysql_real_escape_string($response)."',
			'".time()."'
		)";
		$icdb->query($sql);
		if ($payment_status == "Completed") {
			$icdb->query("UPDATE ".$icdb->prefix."donors SET status = '".STATUS_ACTIVE."' WHERE id = '".$item_number."'");
			$tags = array("{payer_name}", "{payer_email}", "{amount}", "{currency}", "{campaign_title}", "{transaction_date}", "{gateway}");
			$vals = array($payer_name, $payer_id, $gross_total, $mc_currency, $campaign_title, date("Y-m-d H:i:s")." (server time)", "Skrill");
			send_thanksgiving_email($tags, $vals, $payer_email);
		} else {
			$icdb->query("UPDATE ".$icdb->prefix."donors SET status = '".STATUS_PENDING."' WHERE id = '".$item_number."'");
			$tags = array("{payer_name}", "{payer_email}", "{amount}", "{currency}", "{campaign_title}", "{payment_status}", "{transaction_date}", "{gateway}");
			$vals = array($payer_name, $payer_id, $gross_total, $mc_currency, $campaign_title, $payment_status, date("Y-m-d H:i:s")." (server time)", "Skrill");
			send_failed_email($tags, $vals, $payer_email);
		}
		exit;
		break;
	
	case 'bitpay':
		$json = file_get_contents("php://input");
		if (empty($json)) die();
			
		$post = json_decode($json, true);
		if (!is_array($post)) die();
			
		$txn_id = stripslashes($post['id']);
		$curl = curl_init('https://bitpay.com/api/invoice/'.$txn_id);
			
		$header = array(
			'Content-Type: application/json',
			'Content-Length: 0',
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
			
		if ($json === false) die();
			
		$post = json_decode($json, true);
		if (!$post) die();
		if (isset($post['error'])) die();
			
		$response = "";
		foreach ($post as $key => $value) {
			$value = urlencode(stripslashes($value));
			$response .= "&".$key."=".$value;
		}

		$posData = json_decode($post['posData'], true);

		$payment_status = stripslashes($post['status']);
		$item_number = intval($posData['donor_id']);
		$payer_id = $posData['payer_email'];
		$payer_email = $posData['payer_email'];
		$transaction_type = "bitcoins";
		$payer_name = $posData['payer_email'];
		$gross_total = number_format($post['price'], 2, '.', '');
		$mc_currency = $post['currency'];

		$donor_details = $icdb->get_row("SELECT t1.*, t2.title AS campaign_title FROM ".$icdb->prefix."donors t1 LEFT JOIN ".$icdb->prefix."campaigns t2 ON t1.campaign_id = t2.id WHERE t1.id = '".$item_number."'");
		if (!$donor_details) $payment_status = "Unrecognized";
		else {
			$campaign_title = $donor_details["campaign_title"];
			$payer_email = $donor_details["email"];
			if (floatval($gross_total) < floatval($donor_details["amount"]) || $mc_currency != $donor_details["currency"]) $payment_status = "Unrecognized";
		}

		$sql = "INSERT INTO ".$icdb->prefix."transactions (
			donor_id, payer_name, payer_email, gross, currency, payment_status, transaction_type, txn_id, details, created) VALUES (
			'".$item_number."',
			'".mysql_real_escape_string($payer_name)."',
			'".mysql_real_escape_string($payer_email)."',
			'".floatval($gross_total)."',
			'".$mc_currency."',
			'".$payment_status."',
			'BitPay: ".$transaction_type."',
			'".$txn_id."',
			'".mysql_real_escape_string($response)."',
			'".time()."'
		)";
		$icdb->query($sql);

		if ($payment_status == "confirmed" || $payment_status == "complete") {
			$icdb->query("UPDATE ".$icdb->prefix."donors SET status = '".STATUS_ACTIVE."' WHERE id = '".$item_number."'");
			$tags = array("{payer_name}", "{payer_email}", "{amount}", "{currency}", "{campaign_title}", "{transaction_date}", "{gateway}");
			$vals = array($payer_name, $payer_email, $gross_total, $mc_currency, $campaign_title, date("Y-m-d H:i:s")." (server time)", "BitPay");
			send_thanksgiving_email($tags, $vals, $payer_email);
		} else {
			$icdb->query("UPDATE ".$icdb->prefix."donors SET status = '".STATUS_PENDING."' WHERE id = '".$item_number."'");
			$tags = array("{payer_name}", "{payer_email}", "{amount}", "{currency}", "{campaign_title}", "{payment_status}", "{transaction_date}", "{gateway}");
			$vals = array($payer_name, $payer_email, $gross_total, $mc_currency, $campaign_title, $payment_status, date("Y-m-d H:i:s")." (server time)", "BitPay");
			send_failed_email($tags, $vals, $payer_email);
		}
		exit;
		break;
		
	case 'authnet':
		$request = "";
		foreach ($_POST as $key => $value) {
			$value = urlencode(stripslashes($value));
			$request .= "&".$key."=".$value;
		}
		if(strlen($request) == 0) die();

		$item_number = str_replace("ID", "", stripslashes($_POST['x_invoice_num']));
		if (($pos = strpos($item_number, "N")) !== false) $item_number = substr($item_number, 0, $pos);
		$item_number = intval($item_number);
		$item_name = stripslashes($_POST['x_description']);
		$payment_status = stripslashes($_POST['x_response_code']);
		$transaction_type = stripslashes($_POST['x_card_type']);
		$txn_id = stripslashes($_POST['x_trans_id']);
		$seller_id = stripslashes($_POST['x_login']);
		$payer_id = stripslashes($_POST['x_email']);
		$gross_total = stripslashes($_POST['x_amount']);
		$mc_currency = "USD";
		$payer_name = trim(stripslashes($_POST['x_first_name']).' '.stripslashes($_POST['x_last_name']));
		$test_mode = strtolower(stripslashes($_POST['x_test_request']));
		$md5hash = strtolower(stripslashes($_POST['x_MD5_Hash']));
		if (empty($payer_name)) {
			$payer_name = $payer_id;
		}
		$payer_email = stripslashes($_POST['x_email']);

		if ($payment_status == "1") $payment_status = "Completed";
		if ($payment_status == "Completed") {
			$donor_details = $icdb->get_row("SELECT t1.*, t2.title AS campaign_title FROM ".$icdb->prefix."donors t1 LEFT JOIN ".$icdb->prefix."campaigns t2 ON t1.campaign_id = t2.id WHERE t1.id = '".$item_number."'");
			if ($donor_details) $campaign_title = $donor_details["campaign_title"];
			if (!$donor_details) $payment_status = "Unrecognized";
			else {
				$payer_email = $donor_details["email"];
				if ($test_mode == "true" && $options['authnet_sandbox'] != 'on') $payment_status = "Test mode";
				if (floatval($gross_total) < floatval($donor_details["amount"]) || $mc_currency != $donor_details["currency"]) $payment_status = "Unrecognized";
				else {
					$md5source = $options["authnet_md5hash"] . $options["authnet_login"] . $txn_id . $gross_total;
					$md5 = strtolower(md5($md5source));
					if ($md5 != $md5hash) $payment_status = "Invalid MD5 Hash";
				}
			}
		}
				
		$sql = "INSERT INTO ".$icdb->prefix."transactions (
			donor_id, payer_name, payer_email, gross, currency, payment_status, transaction_type, txn_id, details, created) VALUES (
			'".$item_number."',
			'".mysql_real_escape_string($payer_name)."',
			'".mysql_real_escape_string($payer_id)."',
			'".floatval($gross_total)."',
			'".$mc_currency."',
			'".$payment_status."',
			'Autorize.Net: ".$transaction_type."',
			'".$txn_id."',
			'".mysql_real_escape_string($request)."',
			'".time()."'
		)";
		$icdb->query($sql);
		if (isset($donor_details["details"])) {
			$return_url = $donor_details["details"];
			if (!preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $return_url) || strlen($return_url) == 0) $return_url = '#';
		} else $return_url = '#';
		if ($payment_status == "Completed") {
			$icdb->query("UPDATE ".$icdb->prefix."donors SET status = '".STATUS_ACTIVE."' WHERE id = '".$item_number."'");
			$tags = array("{payer_name}", "{payer_email}", "{amount}", "{currency}", "{campaign_title}", "{transaction_date}", "{gateway}");
			$vals = array($payer_name, $payer_id, $gross_total, $mc_currency, $campaign_title, date("Y-m-d H:i:s")." (server time)", "Authorize.Net");
			send_thanksgiving_email($tags, $vals, $payer_email);
			echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Transaction completed</title>'.get_auth_style().'</head><body><div class="page udb_returnbox">Payment successfully completed.<br /><a class="udb_return" href="'.$return_url.'">Return to Merchant</a></div></body></html>';
		} else {
			$icdb->query("UPDATE ".$icdb->prefix."donors SET status = '".STATUS_PENDING."' WHERE id = '".$item_number."'");
			$tags = array("{payer_name}", "{payer_email}", "{amount}", "{currency}", "{campaign_title}", "{payment_status}", "{transaction_date}", "{gateway}");
			$vals = array($payer_name, $payer_id, $gross_total, $mc_currency, $campaign_title, $payment_status, date("Y-m-d H:i:s")." (server time)", "Authorize.Net");
			send_failed_email($tags, $vals, $payer_email);
			echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Error</title>'.get_auth_style().'</head><body><div class="page udb_returnbox">Payment failed. Please contact Merchant about transaction <strong>'.$txn_id.'</strong>.<br /><a class="udb_return" href="'.$return_url.'">Return to Merchant</a></div></body></html>';
		}
		exit;
		break;

	case 'interkassa':
		if (!isset($_POST['ik_shop_id']) || !isset($_POST['ik_sign_hash'])) die();
		$request = '';
		foreach ($_POST as $key => $value) {
			$request .= "&".$key."=".$value;
		}
		$item_number = $_POST['ik_payment_id'];
		if (($pos = strpos($item_number, "_")) !== false) $item_number = intval(substr($item_number, 0, $pos));
		$item_name = $_POST['ik_payment_desc'];
		$payment_status = $_POST['ik_payment_state'];
		$transaction_type = $_POST['ik_paysystem_alias'];
		$txn_id = $_POST['ik_trans_id'];
		$seller_id = $_POST['ik_shop_id'];
		$payer_id = $_POST['ik_baggage_fields'];
		$gross_total = $_POST['ik_payment_amount'];
		$mc_currency = $options['interkassa_currency'];
		$payer_name = $payer_id;
		$payer_email = $_POST['ik_baggage_fields'];

		if ($payment_status == "success") {
			$donor_details = $icdb->get_row("SELECT t1.*, t2.title AS campaign_title FROM ".$icdb->prefix."donors t1 LEFT JOIN ".$icdb->prefix."campaigns t2 ON t1.campaign_id = t2.id WHERE t1.id = '".$item_number."'");
			if ($donor_details) $campaign_title = $donor_details["campaign_title"];
			if (!$donor_details) $payment_status = "Unrecognized";
			else {
				$payer_email = $donor_details["email"];
				if (strtolower($seller_id) != strtolower($options['interkassa_shop_id'])) $payment_status = "Unrecognized";
				else {
					if (floatval($gross_total) < $donor_details["amount"]) $payment_status = "Unrecognized";
					else {
						$sing_hash_str = $_POST['ik_shop_id'].':'.
							$_POST['ik_payment_amount'].':'.
							$_POST['ik_payment_id'].':'.
							$_POST['ik_paysystem_alias'].':'.
							$_POST['ik_baggage_fields'].':'.
							$_POST['ik_payment_state'].':'.
							$_POST['ik_trans_id'].':'.
							$_POST['ik_currency_exch'].':'.
							$_POST['ik_fees_payer'].':'.
							$options['interkassa_secret_key'];
						$sign_hash = strtoupper(md5($sing_hash_str));
						if ($_POST['ik_sign_hash'] != $sign_hash) $payment_status = "Unrecognized";					
					}
				}
			}
		}
		$sql = "INSERT INTO ".$icdb->prefix."transactions (
			donor_id, payer_name, payer_email, gross, currency, payment_status, transaction_type, txn_id, details, created) VALUES (
			'".$item_number."',
			'".mysql_real_escape_string($payer_name)."',
			'".mysql_real_escape_string($payer_id)."',
			'".floatval($gross_total)."',
			'".$mc_currency."',
			'".$payment_status."',
			'InterKassa: ".$transaction_type."',
			'".$txn_id."',
			'".mysql_real_escape_string($request)."',
			'".time()."'
		)";
		$icdb->query($sql);
		if ($payment_status == "success") {
			$icdb->query("UPDATE ".$icdb->prefix."donors SET status = '".STATUS_ACTIVE."' WHERE id = '".$item_number."'");
			$tags = array("{payer_name}", "{payer_email}", "{amount}", "{currency}", "{campaign_title}", "{transaction_date}", "{gateway}");
			$vals = array($payer_name, $payer_id, $gross_total, $mc_currency, $campaign_title, date("Y-m-d H:i:s")." (server time)", "InterKassa");
			send_thanksgiving_email($tags, $vals, $payer_email);
		} else {
			$icdb->query("UPDATE ".$icdb->prefix."donors SET status = '".STATUS_PENDING."' WHERE id = '".$item_number."'");
			$tags = array("{payer_name}", "{payer_email}", "{amount}", "{currency}", "{campaign_title}", "{payment_status}", "{transaction_date}", "{gateway}");
			$vals = array($payer_name, $payer_id, $gross_total, $mc_currency, $campaign_title, $payment_status, date("Y-m-d H:i:s")." (server time)", "InterKassa");
			send_failed_email($tags, $vals, $payer_email);
		}
		exit;
		break;

	case 'paypal':
		$request = "cmd=_notify-validate";
		foreach ($_POST as $key => $value) {
			$value = urlencode(stripslashes($value));
			$request .= "&".$key."=".$value;
		}
		$paypalurl = ($options['paypal_sandbox'] == "on" ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr');
		$ch = curl_init($paypalurl);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
		$result = curl_exec($ch);
		curl_close($ch);                
		if (substr(trim($result), 0, 8) != "VERIFIED") die();
			
		$item_number = intval($_POST['item_number']);
		$item_name = stripslashes($_POST['item_name']);
		$payment_status = stripslashes($_POST['payment_status']);
		$transaction_type = stripslashes($_POST['txn_type']);
		$txn_id = stripslashes($_POST['txn_id']);
		$seller_paypal = stripslashes($_POST['business']);
		$seller_id = stripslashes($_POST['receiver_id']);
		$payer_id = stripslashes($_POST['payer_email']);
		$payer_email = stripslashes($_POST['custom']);
		$gross_total = stripslashes($_POST['mc_gross']);
		$mc_currency = stripslashes($_POST['mc_currency']);
		$payer_name = stripslashes($_POST['first_name']).' '.stripslashes($_POST['last_name']);
		$payer_status = stripslashes($_POST['payer_status']);
					
		if ($transaction_type == "web_accept" && $payment_status == "Completed") {
			$donor_details = $icdb->get_row("SELECT t1.*, t2.title AS campaign_title FROM ".$icdb->prefix."donors t1 LEFT JOIN ".$icdb->prefix."campaigns t2 ON t1.campaign_id = t2.id WHERE t1.id = '".$item_number."'");
			if ($donor_details) $campaign_title = $donor_details["campaign_title"];
			if (!$donor_details) $payment_status = "Unrecognized";
			else {
				$payer_email = $donor_details["email"];
				if (empty($seller_paypal)) {
					$tx_details = $icdb->get_row("SELECT * FROM ".$icdb->prefix."transactions WHERE details LIKE '%txn_id=".$txn_id."%' AND payment_status != 'Unrecognized'");
					if ($tx_details) $seller_paypal = $options['paypal_id'];
				}
				if ((strtolower($seller_paypal) != strtolower($options['paypal_id'])) && (strtolower($seller_id) != strtolower($options['paypal_id']))) $payment_status = "Unrecognized";
				else {
					if (floatval($gross_total) < floatval($donor_details["amount"]) || $mc_currency != $donor_details["currency"]) $payment_status = "Unrecognized";
				}
			}
		}
		$sql = "INSERT INTO ".$icdb->prefix."transactions (
			donor_id, payer_name, payer_email, gross, currency, payment_status, transaction_type, txn_id, details, created) VALUES (
			'".$item_number."',
			'".mysql_real_escape_string($payer_name)."',
			'".mysql_real_escape_string($payer_id)."',
			'".floatval($gross_total)."',
			'".$mc_currency."',
			'".$payment_status."',
			'PayPal: ".$transaction_type."',
			'".$txn_id."',
			'".mysql_real_escape_string($request)."',
			'".time()."'
		)";
		$icdb->query($sql);
		if ($transaction_type == "web_accept") {
			if ($payment_status == "Completed") {
				$icdb->query("UPDATE ".$icdb->prefix."donors SET status = '".STATUS_ACTIVE."' WHERE id = '".$item_number."'");
				$tags = array("{payer_name}", "{payer_email}", "{amount}", "{currency}", "{campaign_title}", "{transaction_date}", "{gateway}");
				$vals = array($payer_name, $payer_id, $gross_total, $mc_currency, $campaign_title, date("Y-m-d H:i:s")." (server time)", "PayPal");
				send_thanksgiving_email($tags, $vals, $payer_email);
			} else {
				$icdb->query("UPDATE ".$icdb->prefix."donors SET status = '".STATUS_PENDING."' WHERE id = '".$item_number."'");
				$tags = array("{payer_name}", "{payer_email}", "{amount}", "{currency}", "{campaign_title}", "{payment_status}", "{transaction_date}", "{gateway}");
				$vals = array($payer_name, $payer_id, $gross_total, $mc_currency, $campaign_title, $payment_status, date("Y-m-d H:i:s")." (server time)", "PayPal");
				send_failed_email($tags, $vals, $payer_email);
			}
		}
		exit;
		break;
		
	default:
		break;
}


?>