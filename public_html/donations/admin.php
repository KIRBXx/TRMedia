<?php
session_start();
if (isset($_GET['debug'])) error_reporting(-1);
else error_reporting(0);
include_once(dirname(__FILE__).'/inc/config.php');
include_once(dirname(__FILE__).'/inc/settings.php');
include_once(dirname(__FILE__).'/inc/icdb.php');
include_once(dirname(__FILE__).'/inc/functions.php');
$icdb = new ICDB(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD, TABLE_PREFIX);

install();

$is_logged = false;
$session_id = '';
if (isset($_COOKIE['udb-auth'])) {
	$session_id = preg_replace('/[^a-zA-Z0-9]/', '', $_COOKIE['udb-auth']);
	$session_details = $icdb->get_row("SELECT * FROM ".$icdb->prefix."sessions WHERE session_id = '".$session_id."' AND registered + valid_period > '".time()."'");
	if ($session_details) {
		$icdb->query("UPDATE ".$icdb->prefix."sessions SET registered = '".time()."', ip = '".$_SERVER['REMOTE_ADDR']."' WHERE session_id = '".$session_id."'");
		$is_logged = true;
	}
}

get_options();

$currency_list = array_unique(array_merge($paypal_currency_list, $payza_currency_list, $interkassa_currency_list, $egopay_currency_list, $perfect_currency_list, $skrill_currency_list, $bitpay_currency_list, $stripe_currency_list));
sort($currency_list);
$currency_list = array_unique(array_merge(array("USD"), $currency_list));

$url_base = ((empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] == 'off') ? 'http://' : 'https://').$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
$filename = basename(__FILE__);
if (($pos = strpos($url_base, $filename)) !== false) $url_base = substr($url_base, 0, $pos);

if (isset($_SESSION['error'])) {
	$error_message = $_SESSION['error'];
	unset($_SESSION['error']);
} else $error_message = '';
if (isset($_SESSION['ok'])) {
	$ok_message = $_SESSION['ok'];
	unset($_SESSION['ok']);
} else $ok_message = '';

$pages = array (
	'settings' => array('title' => 'Settings', 'menu' => true),
	'campaigns' => array('title' => 'Campaigns', 'menu' => true),
	'donors' => array('title' => 'Donors', 'menu' => true),
	'transactions' => array('title' => 'Transactions', 'menu' => true),
	'embed' => array('title' => 'Embedding', 'menu' => true),
	'edit' => array('title' => 'Edit Donor', 'menu' => false),
	'editcampaign' => array('title' => 'Edit Campaign', 'menu' => false)
);
$deafult_page = 'campaigns';
if ($is_logged) {
	if (isset($_GET['action'])) {
		switch ($_GET['action']) {
			case 'logout':
				if (!empty($session_id)) {
					$icdb->query("UPDATE ".$icdb->prefix."sessions SET valid_period = '0' WHERE session_id = '".$session_id."'");
				}
				header('Location: admin.php');
				exit;
				break;

			case 'update-options':
				if (DEMO_MODE) {
					$_SESSION['error'] = '<strong>Demo mode.</strong> This operation is disabled.';
					header('Location: admin.php?page=settings');
					exit;
				}
				if (empty($_POST["login"])) {
					$_SESSION['error'] = '<strong>Invalid login value.</strong> Empty admin login value is not allowed.';
					header('Location: admin.php?page=settings');
					exit;
				}
				if (isset($_POST['action']) && $_POST['action'] == 'update-options') {
					populate_options();
					if (isset($_POST["enable_paypal"])) $options['enable_paypal'] = "on";
					else $options['enable_paypal'] = "off";
					if (isset($_POST["enable_payza"])) $options['enable_payza'] = "on";
					else $options['enable_payza'] = "off";
					if (isset($_POST["payza_sandbox"])) $options['payza_sandbox'] = "on";
					else $options['payza_sandbox'] = "off";
					if (isset($_POST["enable_skrill"])) $options['enable_skrill'] = "on";
					else $options['enable_skrill'] = "off";
					if (isset($_POST["enable_interkassa"])) $options['enable_interkassa'] = "on";
					else $options['enable_interkassa'] = "off";
					if (isset($_POST["enable_authnet"])) $options['enable_authnet'] = "on";
					else $options['enable_authnet'] = "off";
					if (isset($_POST["authnet_sandbox"])) $options['authnet_sandbox'] = "on";
					else $options['authnet_sandbox'] = "off";
					if (isset($_POST["enable_egopay"])) $options['enable_egopay'] = "on";
					else $options['enable_egopay'] = "off";
					if (isset($_POST["enable_perfect"])) $options['enable_perfect'] = "on";
					else $options['enable_perfect'] = "off";
					if (isset($_POST["enable_bitpay"])) $options['enable_bitpay'] = "on";
					else $options['enable_bitpay'] = "off";
					if (isset($_POST["enable_stripe"])) $options['enable_stripe'] = "on";
					else $options['enable_stripe'] = "off";
					$errors = check_options();
					if (isset($_POST['password'])) $password = trim($_POST['password']);
					else $password = '';
					if (isset($_POST['confirm_password'])) $confirm_password = trim($_POST['confirm_password']);
					else $confirm_password = '';
					if (get_magic_quotes_gpc()) {
						$password = stripslashes($password);
						$confirm_password = stripslashes($confirm_password);
					}
					if (!empty($password)) {
						if ($password == $confirm_password) {
							$options['password'] = md5($password);
						} else {
							if ($errors === true) $errors = array('Password and its confirmation are not equal');
							else $errors[] = 'Password and its confirmation are not equal';
						}
					}
					update_options();
					if (is_array($errors)) {
						$_SESSION['error'] = 'The following error(s) exists:<ul><li>'.implode('</li><li>', $errors).'</li></ul>';
					} else {
						$_SESSION['ok'] = 'Settings successfully saved!';
					}
				}
				header('Location: admin.php?page=settings');
				exit;
				break;

			case 'update-campaign':
				if (DEMO_MODE) {
					$_SESSION['error'] = '<strong>Demo mode.</strong> This operation is disabled.';
					header('Location: admin.php?page=campaigns');
					exit;
				}
				if (isset($_POST['action']) && $_POST['action'] == 'update-campaign') {
					unset($id);
					if (isset($_POST["id"]) && !empty($_POST["id"])) {
						$id = intval($_POST["id"]);
						$campaign_details = $icdb->get_row("SELECT t1.*, t2.total_donors, t2.total_amount FROM ".$icdb->prefix."campaigns t1 LEFT JOIN (SELECT campaign_id, SUM(amount) AS total_amount, COUNT(*) AS total_donors FROM ".$icdb->prefix."donors WHERE status != '".STATUS_DRAFT."' AND deleted = '0' GROUP BY campaign_id) t2 ON t2.campaign_id = t1.id WHERE t1.id = '".$id."' AND t1.deleted = '0'");
						if (!$campaign_details) unset($id);
					}
					
					$title = trim($_POST['title']);
					$min_amount = trim($_POST['min_amount']);
					$currency = trim($_POST['currency']);
					$form_intro = trim($_POST['form_intro']);
					$form_terms = trim($_POST['form_terms']);
					$top_intro = trim($_POST['top_intro']);
					$recent_intro = trim($_POST['recent_intro']);
					
					if (get_magic_quotes_gpc()) {
						$title = stripslashes($title);
						$min_amount = stripslashes($min_amount);
						$currency = stripslashes($currency);
						$form_intro = stripslashes($form_intro);
						$form_terms = stripslashes($form_terms);
						$top_intro = stripslashes($top_intro);
						$recent_intro = stripslashes($recent_intro);
					}
					$error = array();
					if (empty($title)) $errors[] = 'Campaign title is too short';
					else if (strlen($title) > 48) $errors[] = 'Campaign title is too long';
					if (!is_numeric($min_amount) || floatval($min_amount) <= 0) $errors[] = 'Invalid minimum donation amount';
					if (isset($id)) {
						if ($currency != $campaign_details['currency'] && $campaign_details['total_donors'] > 0) $errors[] = 'Currency must be '.$campaign_details['total_donors'];
					}
					
					if (!empty($errors)) {
						$_SESSION['error'] = 'The following error(s) exists:<ul><li>'.implode('</li><li>', $errors).'</li></ul>';
						$_SESSION['title'] = $title;
						$_SESSION['min_amount'] = $min_amount;
						$_SESSION['currency'] = $currency;
						$_SESSION['form_intro'] = $form_intro;
						$_SESSION['form_terms'] = $form_terms;
						$_SESSION['top_intro'] = $top_intro;
						$_SESSION['recent_intro'] = $recent_intro;
						header('Location: admin.php?page=editcampaign'.(empty($id) ? '' : '&id='.$id));
						exit;
					} else {
						if (!empty($id)) {
							$icdb->query("UPDATE ".$icdb->prefix."campaigns SET title = '".mysql_real_escape_string($title)."', min_amount = '".number_format(floatval($min_amount), 2, ".", "")."', currency = '".mysql_real_escape_string($currency)."', form_intro = '".mysql_real_escape_string($form_intro)."', form_terms = '".mysql_real_escape_string($form_terms)."', top_intro = '".mysql_real_escape_string($top_intro)."', recent_intro = '".mysql_real_escape_string($recent_intro)."' WHERE id = '".$id."'");
							$_SESSION['ok'] = 'Campaign details successfully updated!';
						} else {
							$icdb->query("INSERT INTO ".$icdb->prefix."campaigns (title, min_amount, currency, form_intro, form_terms, top_intro, recent_intro, status, details, registered, deleted) VALUES ( '".mysql_real_escape_string($title)."', '".number_format(floatval($min_amount), 2, ".", "")."', '".mysql_real_escape_string($currency)."', '".mysql_real_escape_string($form_intro)."', '".mysql_real_escape_string($form_terms)."', '".mysql_real_escape_string($top_intro)."', '".mysql_real_escape_string($recent_intro)."', '".STATUS_ACTIVE."', '', '".time()."', '0')");
							$_SESSION['ok'] = 'New campaign successfully added!';
						}
					}
				}
				header('Location: admin.php?page=campaigns');
				exit;
				break;

			case 'delete-campaign':
				if (DEMO_MODE) {
					$_SESSION['error'] = '<strong>Demo mode.</strong> This operation is disabled.';
					header('Location: admin.php?page=campaigns');
					exit;
				}
				$id = intval($_GET["id"]);
				$campaign_details = $icdb->get_row("SELECT * FROM ".$icdb->prefix."campaigns WHERE id = '".$id."' AND deleted = '0'");
				if (intval($campaign_details["id"]) == 0) {
					$_SESSION['error'] = 'Record not found!';
					header('Location: admin.php?page=campaigns');
					exit;
				}
				$sql = "UPDATE ".$icdb->prefix."campaigns SET deleted = '1' WHERE id = '".$id."'";
				if ($icdb->query($sql) !== false) {
					$_SESSION['ok'] = 'Record successfully deleted!';
				} else {
					$_SESSION['error'] = 'Record can not be deleted!';
				}
				header('Location: admin.php?page=campaigns');
				exit;
				break;

			case 'delete-all-campaigns':
				if (DEMO_MODE) {
					$_SESSION['error'] = '<strong>Demo mode.</strong> This operation is disabled.';
					header('Location: admin.php?page=campaigns');
					exit;
				}
				$sql = "UPDATE ".$icdb->prefix."campaigns SET deleted = '1' WHERE deleted != '1'";
				if ($icdb->query($sql) !== false) {
					$_SESSION['ok'] = 'Records successfully deleted!';
				} else {
					$_SESSION['error'] = 'Records can not be deleted!';
				}
				header('Location: admin.php?page=campaigns');
				exit;
				break;

			case 'block-campaign':
				if (DEMO_MODE) {
					$_SESSION['error'] = '<strong>Demo mode.</strong> This operation is disabled.';
					header('Location: admin.php?page=campaigns');
					exit;
				}
				$id = intval($_GET["id"]);
				$campaign_details = $icdb->get_row("SELECT * FROM ".$icdb->prefix."campaigns WHERE id = '".$id."' AND deleted = '0'");
				if (intval($campaign_details["id"]) == 0) {
					$_SESSION['error'] = 'Record not found!';
					header('Location: admin.php?page=campaigns');
					exit;
				}
				$sql = "UPDATE ".$icdb->prefix."campaigns SET status = '".STATUS_PENDING."' WHERE id = '".$id."'";
				if ($icdb->query($sql) !== false) {
					$_SESSION['ok'] = 'Campaign successfully blocked!';
				} else {
					$_SESSION['error'] = 'Campaign can not be blocked!';
				}
				header('Location: admin.php?page=campaigns');
				exit;
				break;

			case 'unblock-campaign':
				if (DEMO_MODE) {
					$_SESSION['error'] = '<strong>Demo mode.</strong> This operation is disabled.';
					header('Location: admin.php?page=campaigns');
					exit;
				}
				$id = intval($_GET["id"]);
				$campaign_details = $icdb->get_row("SELECT * FROM ".$icdb->prefix."campaigns WHERE id = '".$id."' AND deleted = '0'");
				if (intval($campaign_details["id"]) == 0) {
					$_SESSION['error'] = 'Record not found!';
					header('Location: admin.php?page=campaigns');
					exit;
				}
				$sql = "UPDATE ".$icdb->prefix."campaigns SET status = '".STATUS_ACTIVE."' WHERE id = '".$id."'";
				if ($icdb->query($sql) !== false) {
					$_SESSION['ok'] = 'Campaign successfully unblocked!';
				} else {
					$_SESSION['error'] = 'Campaign can not be unblocked!';
				}
				header('Location: admin.php?page=campaigns');
				exit;
				break;

			case 'update-donor':
				if (DEMO_MODE) {
					$_SESSION['error'] = '<strong>Demo mode.</strong> This operation is disabled.';
					header('Location: admin.php?page=donors');
					exit;
				}
				if (isset($_POST['action']) && $_POST['action'] == 'update-donor') {
					if (isset($_POST["cid"])) $cid = intval(trim(stripslashes($_POST["cid"])));
					else $cid = 0;
					unset($id);
					if (isset($_POST["id"]) && !empty($_POST["id"])) {
						$id = intval($_POST["id"]);
						$donor_details = $icdb->get_row("SELECT * FROM ".$icdb->prefix."donors WHERE id = '".$id."' AND deleted = '0'");
						if (!$donor_details) unset($id);
					}

					$campaign_id = intval($_POST['campaign_id']);
					$campaign_details = $icdb->get_row("SELECT * FROM ".$icdb->prefix."campaigns WHERE id = '".$campaign_id."' AND deleted = '0'");
					if (!$campaign_details) {
						if (isset($id)) {
							$campaign_id = $donor_details['campaign_id'];
							$currency = $donor_details['currency'];
						} else {
							$_SESSION['error'] = 'Campaign not found!';
							header('Location: admin.php?page=edit'.($cid == 0 ? '' : '&cid='.$cid));
							exit;
						}
					} else $currency = $campaign_details['currency'];

					$name = trim($_POST['name']);
					$email = trim($_POST['email']);
					$url = trim($_POST['url']);
					$amount = trim($_POST['amount']);
					
					if (get_magic_quotes_gpc()) {
						$name = stripslashes($name);
						$email = stripslashes($email);
						$url = stripslashes($url);
						$amount = stripslashes($amount);
					}
					$error = array();
					if (!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i", $email) || strlen($email) == 0) $errors[] = 'Donor\'s e-mail must be valid e-mail address';
					if (!preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url) && strlen($url) > 0) $errors[] = 'Donor\'s URL must be valid URL';
					if (!is_numeric($amount) || floatval($amount) <= 0) $errors[] = 'Invalid donation amount';
					
					if (!empty($errors)) {
						$_SESSION['error'] = 'The following error(s) exists:<ul><li>'.implode('</li><li>', $errors).'</li></ul>';
						$_SESSION['name'] = $name;
						$_SESSION['email'] = $email;
						$_SESSION['url'] = $url;
						$_SESSION['amount'] = $amount;
						$_SESSION['campaign_id'] = $campaign_id;
						header('Location: admin.php?page=edit'.(empty($id) ? '' : '&id='.$id).($cid == 0 ? '' : '&cid='.$cid));
						exit;
					} else {
						if (!empty($id)) {
							$icdb->query("UPDATE ".$icdb->prefix."donors SET campaign_id = '".$campaign_id."', name = '".mysql_real_escape_string($name)."', email = '".mysql_real_escape_string($email)."', url = '".mysql_real_escape_string($url)."', amount = '".number_format(floatval($amount), 2, ".", "")."', currency = '".mysql_real_escape_string($currency)."' WHERE id = '".$id."'");
							$_SESSION['ok'] = 'Donor details successfully updated!';
						} else {
							$icdb->query("INSERT INTO ".$icdb->prefix."donors (campaign_id, name, email, url, amount, currency, status, details, registered, deleted) VALUES ('".$campaign_id."', '".mysql_real_escape_string($name)."', '".mysql_real_escape_string($email)."', '".mysql_real_escape_string($url)."', '".number_format(floatval($amount), 2, ".", "")."', '".mysql_real_escape_string($currency)."', '".STATUS_ACTIVE."', '', '".time()."', '0')");
							$_SESSION['ok'] = 'New donor successfully added!';
						}
					}
				}
				header('Location: admin.php?page=donors'.($cid == 0 ? '' : '&cid='.$campaign_id));
				exit;
				break;

			case 'delete-donor':
				if (DEMO_MODE) {
					$_SESSION['error'] = '<strong>Demo mode.</strong> This operation is disabled.';
					header('Location: admin.php?page=donors');
					exit;
				}
				if (isset($_GET["cid"])) $cid = intval($_GET["cid"]);
				else $cid = 0;
				$id = intval($_GET["id"]);
				$donor_details = $icdb->get_row("SELECT * FROM ".$icdb->prefix."donors WHERE id = '".$id."' AND deleted = '0'");
				if (intval($donor_details["id"]) == 0) {
					$_SESSION['error'] = 'Record not found!';
					header('Location: admin.php?page=donors'.($cid == 0 ? '' : '&cid='.$cid));
					exit;
				}
				$sql = "UPDATE ".$icdb->prefix."donors SET deleted = '1' WHERE id = '".$id."'";
				if ($icdb->query($sql) !== false) {
					$_SESSION['ok'] = 'Record successfully deleted!';
				} else {
					$_SESSION['error'] = 'Record can not be deleted!';
				}
				header('Location: admin.php?page=donors'.($cid == 0 ? '' : '&cid='.$cid));
				exit;
				break;

			case 'delete-all-donors':
				if (DEMO_MODE) {
					$_SESSION['error'] = '<strong>Demo mode.</strong> This operation is disabled.';
					header('Location: admin.php?page=donors');
					exit;
				}
				if (isset($_GET["cid"])) $cid = intval($_GET["cid"]);
				else $cid = 0;
				$sql = "UPDATE ".$icdb->prefix."donors SET deleted = '1' WHERE deleted != '1'".($cid == 0 ? '' : " AND campaign_id = '".$cid."'");
				if ($icdb->query($sql) !== false) {
					$_SESSION['ok'] = 'Records successfully deleted!';
				} else {
					$_SESSION['error'] = 'Records can not be deleted!';
				}
				header('Location: admin.php?page=donors'.($cid == 0 ? '' : '&cid='.$cid));
				exit;
				break;

			case 'block-donor':
				if (DEMO_MODE) {
					$_SESSION['error'] = '<strong>Demo mode.</strong> This operation is disabled.';
					header('Location: admin.php?page=donors');
					exit;
				}
				if (isset($_GET["cid"])) $cid = intval($_GET["cid"]);
				else $cid = 0;
				$id = intval($_GET["id"]);
				$donor_details = $icdb->get_row("SELECT * FROM ".$icdb->prefix."donors WHERE id = '".$id."' AND deleted = '0'");
				if (intval($donor_details["id"]) == 0) {
					$_SESSION['error'] = 'Record not found!';
					header('Location: admin.php?page=donors'.($cid == 0 ? '' : '&cid='.$cid));
					exit;
				}
				$sql = "UPDATE ".$icdb->prefix."donors SET status = '".STATUS_PENDING."' WHERE id = '".$id."'";
				if ($icdb->query($sql) !== false) {
					$_SESSION['ok'] = 'Donor successfully blocked!';
				} else {
					$_SESSION['error'] = 'Donor can not be blocked!';
				}
				header('Location: admin.php?page=donors'.($cid == 0 ? '' : '&cid='.$cid));
				exit;
				break;

			case 'unblock-donor':
				if (DEMO_MODE) {
					$_SESSION['error'] = '<strong>Demo mode.</strong> This operation is disabled.';
					header('Location: admin.php?page=donors');
					exit;
				}
				if (isset($_GET["cid"])) $cid = intval($_GET["cid"]);
				else $cid = 0;
				$id = intval($_GET["id"]);
				$donor_details = $icdb->get_row("SELECT * FROM ".$icdb->prefix."donors WHERE id = '".$id."' AND deleted = '0'");
				if (intval($donor_details["id"]) == 0) {
					$_SESSION['error'] = 'Record not found!';
					header('Location: admin.php?page=donors'.($cid == 0 ? '' : '&cid='.$cid));
					exit;
				}
				$sql = "UPDATE ".$icdb->prefix."donors SET status = '".STATUS_ACTIVE."' WHERE id = '".$id."'";
				if ($icdb->query($sql) !== false) {
					$_SESSION['ok'] = 'Donor successfully unblocked!';
				} else {
					$_SESSION['error'] = 'Donor can not be unblocked!';
				}
				header('Location: admin.php?page=donors'.($cid == 0 ? '' : '&cid='.$cid));
				exit;
				break;

			case 'delete-transaction':
				if (DEMO_MODE) {
					$_SESSION['error'] = '<strong>Demo mode.</strong> This operation is disabled.';
					header('Location: admin.php?page=transactions');
					exit;
				}
				$id = intval($_GET["id"]);
				$transaction_details = $icdb->get_row("SELECT * FROM ".$icdb->prefix."transactions WHERE id = '".$id."' AND deleted = '0'");
				if (intval($transaction_details["id"]) == 0) {
					$_SESSION['error'] = 'Record not found!';
					header('Location: admin.php?page=transactions');
					exit;
				}
				$sql = "UPDATE ".$icdb->prefix."transactions SET deleted = '1' WHERE id = '".$id."'";
				if ($icdb->query($sql) !== false) {
					$_SESSION['ok'] = 'Record successfully deleted!';
				} else {
					$_SESSION['error'] = 'Record can not be deleted!';
				}
				header('Location: admin.php?page=transactions');
				exit;
				break;

			case 'export':
				if (DEMO_MODE) {
					$_SESSION['error'] = '<strong>Demo mode.</strong> This operation is disabled.';
					header('Location: admin.php?page=donors');
					exit;
				}
				if (isset($_GET["cid"])) $cid = intval($_GET["cid"]);
				else $cid = 0;
				$rows = $icdb->get_rows("SELECT t1.*, t2.title AS campaign_title FROM ".$icdb->prefix."donors t1 LEFT JOIN ".$icdb->prefix."campaigns t2 ON t1.campaign_id = t2.id WHERE t1.deleted = '0'".($cid == 0 ? '' : " AND t1.campaign_id = '".$cid."'")." ORDER BY t1.registered DESC");
				if (sizeof($rows) > 0) {
					if (strstr($_SERVER["HTTP_USER_AGENT"],"MSIE")) {
						header("Pragma: public");
						header("Expires: 0");
						header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
						header("Content-type: application-download");
						header("Content-Disposition: attachment; filename=\"donors.csv\"");
						header("Content-Transfer-Encoding: binary");
					} else {
						header("Content-type: application-download");
						header("Content-Disposition: attachment; filename=\"donors.csv\"");
					}
					$separator = $options['csv_separator'];
					if ($separator == 'tab') $separator = "\t";
					echo '"Campaign"'.$separator.'"Name"'.$separator.'"E-Mail"'.$separator.'"URL"'.$separator.'"Amount"'.$separator.'"Currency"'.$separator.'"Registered"'."\r\n";
					foreach ($rows as $row) {
						echo '"'.str_replace('"', '', $row["campaign_title"]).'"'.$separator.'"'.str_replace('"', '', $row["name"]).'"'.$separator.'"'.str_replace('"', "", $row["email"]).'"'.$separator.'"'.str_replace('"', "", $row["url"]).'"'.$separator.'"'.str_replace('"', "", $row["amount"]).'"'.$separator.'"'.str_replace('"', "", $row["currency"]).'"'.$separator.'"'.date("Y-m-d H:i:s", $row["registered"]).'"'."\r\n";
					}
					exit;
	            }
	            header("Location: admin.php?page=donors".($cid == 0 ? '' : '&cid='.$cid));
				exit;
				break;
			
			default:
				break;
		}
		header('Location: admin.php');
		exit;
	}
	if (isset($_GET['page'])) {
		$page = preg_replace('/[^a-zA-Z0-9]/', '', $_GET['page']);
		if (!array_key_exists($_GET['page'], $pages)) $page = $deafult_page;
	} else $page = $deafult_page;
} else {
	if (isset($_GET['action'])) {
		switch ($_GET['action']) {
			case 'login':
				if (isset($_POST['password'])) $password = trim($_POST['password']);
				else $password = '';
				if (isset($_POST['login'])) $login = trim($_POST['login']);
				else $login = '';
				if (get_magic_quotes_gpc()) {
					$password = stripslashes($password);
					$login = stripslashes($login);
				}
				sleep(3);
				
				if ($login == $options['login'] && md5($password) == $options['password']) {
					$session_id = random_string(16);
					$icdb->query("INSERT INTO ".$icdb->prefix."sessions (ip, session_id, registered, valid_period) VALUES ('".$_SERVER['REMOTE_ADDR']."', '".$session_id."', '".time()."', '900')");
					setcookie('udb-auth', $session_id, time()+3600*24*180);
					$_SESSION['ok'] = 'Welcome to admin panel!'.(DEMO_MODE ? ' Admin Panel operates in <strong>demo mode</strong> for security reasons.' : '');
				} else $_SESSION['error'] = 'Invalid login or password!';
				header('Location: admin.php');
				exit;
				break;
				
			default:
				break;
		}
		header('Location: admin.php');
		exit;
	}
	$page = 'login';
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset='utf-8'>
	<title><?php echo (array_key_exists($page, $pages) ? $pages[$page]['title'] : 'Login').' - '; ?>Universal Donation Box</title>
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/style.css" rel="stylesheet">
	<script src="js/jquery-1.8.0.min.js"></script>
	<script src="js/bootstrap-dropdown.js"></script>
	<script src="js/bootstrap-modal.js"></script>
<body>
<!-- Header - begin -->
<div class="navbar navbar-fixed-top navbar-inverse">
	<div class="navbar-inner">
		<div class="container">
			<a class="brand" href="#">Universal Donation Box</a>
<?php
			if ($is_logged) echo '
			<a class="navbar-text pull-right" href="admin.php?action=logout">Logout</a>';
			else echo '
			<a class="navbar-text pull-right" href="admin.php?page=login">Login</a>';
			if ($is_logged) {
?>
			<ul class="nav">
<?php
				foreach ($pages as $key => $value) {
					if ($value['menu']) echo '
				<li'.($key == $page ? ' class="active"' : '').'><a href="admin.php?page='.$key.'">'.$value['title'].'</a></li>';
				}
?>
			</ul>
<?php
			}
?>
		</div>
	</div>
</div>
<!-- Header - end -->

<div class="container" style="margin-top: 40px; margin-bottom: 40px;">
<?php
	if ($page == 'settings') {
		if (empty($error_message)) {
			$errors = check_options();
			if (is_array($errors)) $message = '<div class="alert alert-error">The following error(s) exists:<ul><li>'.implode('</li><li>', $errors).'</li></ul></div>';
			else if (!empty($ok_message)) $message = '<div class="alert alert-success">'.$ok_message.'</div>';
			else if (DEMO_MODE) $message = '<div class="alert alert-warning"><strong>Demo mode.</strong> Real e-mails and payment details are hidden.</div>';
			else $message = '';
		} else $message = '<div class="alert alert-error">'.$error_message.'</div>';
		echo $message;
		if (DEMO_MODE) {
			$hidden_options = array (
				"owner_email" => "<hidden>",
				"mail_from_email" => "<hidden>",
				"smtp_server" => "<hidden>",
				"smtp_username" => "<hidden>",
				"smtp_password" => "<hidden>",
				"paypal_id" => "<hidden>",
				"payza_id" => "<hidden>",
				"interkassa_shop_id" => "<hidden>",
				"interkassa_secret_key" => "<hidden>",
				"authnet_login" => "<hidden>",
				"authnet_key" => "<hidden>",
				"authnet_md5hash" => "<hidden>",
				"skrill_id" => "<hidden>",
				"skrill_secret_word" => "<hidden>",
				"egopay_store_id" => "<hidden>",
				"egopay_store_pass" => "<hidden>",
				"perfect_account_id" => "<hidden>",
				"perfect_payee_name" => "<hidden>",
				"perfect_passphrase" => "<hidden>",
				"bitpay_key" => "<hidden>",
				"stripe_secret" => "<hidden>",
				"stripe_publishable" => "<hidden>"
			);
			$options = array_merge($options, $hidden_options);
		}
?>
	<form enctype="multipart/form-data" method="post" action="admin.php?action=update-options">
		<h3>General Settings</h3>
		<div class="row">
			<div class="span3"><strong>E-mail for notifications:</strong></div>
			<div class="span9">
				<input type="text" class="span9" id="owner_email" name="owner_email" value="<?php echo htmlspecialchars($options['owner_email'], ENT_QUOTES); ?>">
				<br /><small>Please enter e-mail address. All alerts about completed/failed payments are sent to this e-mail address.</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>Mailing method:</strong></div>
			<div class="span9">
				<select id="mail_method" name="mail_method" class="span4" title="Mailing method" onchange="switch_mail_settings();">
<?php
			foreach ($mail_methods as $key => $value) {
				echo '
					<option value="'.$key.'"'.($key == $options['mail_method'] ? ' selected="selected"' : '').'>'.htmlspecialchars($value, ENT_QUOTES).'</option>';
			}
?>
				</select>
				<br /><small>All messages to users are sent using this mailing method.</small>
			</div>
		</div>
		<div class="row mail-method-mail">
			<div class="span3"><strong>Sender name:</strong></div>
			<div class="span9">
				<input type="text" class="span9" id="mail_from_name" name="mail_from_name" value="<?php echo htmlspecialchars($options['mail_from_name'], ENT_QUOTES); ?>">
				<br /><small>Please enter sender name. All messages to donors are sent using this name as "FROM:" header value.</small>
			</div>
		</div>
		<div class="row mail-method-mail">
			<div class="span3"><strong>Sender e-mail:</strong></div>
			<div class="span9">
				<input type="text" class="span9" id="mail_from_email" name="mail_from_email" value="<?php echo htmlspecialchars($options['mail_from_email'], ENT_QUOTES); ?>">
				<br /><small>Please enter sender e-mail. All messages to donors are sent using this e-mail as "FROM:" header value. It is recommended to set existing e-mail address.</small>
			</div>
		</div>
		<div class="row mail-method-smtp">
			<div class="span3"><strong>Encryption:</strong></div>
			<div class="span9">
				<select id="smtp_secure" name="smtp_secure" class="span2" title="SMTP Connection security">';
<?php
			foreach ($smtp_secures as $key => $value) {
				echo '
					<option value="'.$key.'"'.($key == $options['smtp_secure'] ? ' selected="selected"' : '').'>'.htmlspecialchars($value, ENT_QUOTES).'</option>';
			}
?>
				</select>
				<br /><small>SMTP connection encryption system.</small>
			</div>
		</div>
		<div class="row mail-method-smtp">
			<div class="span3"><strong>SMTP server:</strong></div>
			<div class="span9">
				<input type="text" class="span4" id="smtp_server" name="smtp_server" value="<?php echo htmlspecialchars($options['smtp_server'], ENT_QUOTES); ?>">
				<br /><small>Hostname of the mail server.</small>
			</div>
		</div>
		<div class="row mail-method-smtp">
			<div class="span3"><strong>SMTP port number:</strong></div>
			<div class="span9">
				<input type="text" class="span2"  style="text-align: right;" id="smtp_port" name="smtp_port" value="<?php echo htmlspecialchars($options['smtp_port'], ENT_QUOTES); ?>">
				<br /><small>Port number.</small>
			</div>
		</div>
		<div class="row mail-method-smtp">
			<div class="span3"><strong>SMTP username:</strong></div>
			<div class="span9">
				<input type="text" class="span4" id="smtp_username" name="smtp_username" value="<?php echo htmlspecialchars($options['smtp_username'], ENT_QUOTES); ?>">
				<br /><small>Username to use for SMTP authentication.</small>
			</div>
		</div>
		<div class="row mail-method-smtp">
			<div class="span3"><strong>SMTP password:</strong></div>
			<div class="span9">
				<input type="text" class="span4" id="smtp_password" name="smtp_password" value="<?php echo htmlspecialchars($options['smtp_password'], ENT_QUOTES); ?>">
				<br /><small>Password to use for SMTP authentication..</small>
			</div>
		</div>
		<hr>
		<div class="row">
			<div class="span3"><strong>Thanksgivig e-mail subject:</strong></div>
			<div class="span9">
				<input type="text" class="span9" id="success_email_subject" name="success_email_subject" value="<?php echo htmlspecialchars($options['success_email_subject'], ENT_QUOTES); ?>">
				<br /><small>All donors receive thanksgiving e-mail message. This is subject field of the message.</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>Thanksgivig e-mail body:</strong></div>
			<div class="span9">
				<textarea class="span9" rows="5" id="success_email_body" name="success_email_body"><?php echo htmlspecialchars($options['success_email_body'], ENT_QUOTES); ?></textarea>
				<br /><small>Thanksgiving e-mail message. You can use the following keywords: {payer_name}, {payer_email}, {amount}, {currency}, {campaign_title}.</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>Failed e-mail subject:</strong></div>
			<div class="span9">
				<input type="text" class="span9" id="failed_email_subject" name="failed_email_subject" value="<?php echo htmlspecialchars($options['failed_email_subject'], ENT_QUOTES); ?>">
				<br /><small>In case of any problems with donation processing, donors receive failed e-mail message. This is subject field of the message.</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>Failed e-mail body:</strong></div>
			<div class="span9">
				<textarea class="span9" rows="5" id="failed_email_body" name="failed_email_body"><?php echo htmlspecialchars($options['failed_email_body'], ENT_QUOTES); ?></textarea>
				<br /><small>Failed e-mail message. You can use the following keywords: {payer_name}, {payer_email}, {amount}, {currency}, {payment_status}.</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>CSV column separator:</strong></div>
			<div class="span9">
				<select id="csv_separator" name="csv_separator">
					<option value=";"<?php echo ($options['csv_separator'] == ';' ? ' selected="selected"' : ''); ?>>Semicolon - ";"</option>
					<option value=","<?php echo ($options['csv_separator'] == ',' ? ' selected="selected"' : ''); ?>>Comma - ","</option>
					<option value="tab"<?php echo ($options['csv_separator'] == 'tab' ? ' selected="selected"' : ''); ?>>Tab</option>
				</select>
				<br /><small>Please select CSV column separator.</small>
			</div>
		</div>
		<div class="row">
			<div class="span12">
				<input type="hidden" name="action" value="update-options" />
				<input type="hidden" name="version" value="<?php echo VERSION; ?>" />
				<input type="submit" class="btn btn-primary pull-right" name="submit" value="Update Settings">
			</div>
		</div>
		<script>
			function switch_mail_settings() {
				var method = jQuery("#mail_method").val();
				if (method == 'mail') {
					jQuery(".mail-method-mail").fadeIn(0);
					jQuery(".mail-method-smtp").fadeOut(0);
				} else {
					jQuery(".mail-method-smtp").fadeIn(0);
					jQuery(".mail-method-mail").fadeOut(0);
				}
			}
			switch_mail_settings();
		</script>
		<hr>
		<h3>PayPal Settings</h3>
		<div class="row">
			<div class="span3"><strong>Enable:</strong></div>
			<div class="span9">
				<label class="checkbox">
					<input type="checkbox" id="enable_paypal" name="enable_paypal"<?php echo ($options['enable_paypal'] == 'on' ? ' checked="checked"' : ''); ?>"> Accept payments via PayPal
				</label>
				<small>Please tick checkbox if you would like to accept payments via PayPal.</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>PayPal ID:</strong></div>
			<div class="span9">
				<input type="text" class="span9" id="paypal_id" name="paypal_id" value="<?php echo htmlspecialchars($options['paypal_id'], ENT_QUOTES); ?>">
				<br /><small>Please enter valid PayPal e-mail or <a href="https://www.paypal.com/webapps/customerprofile/summary.view" traget="_blank">Merchant ID</a>.</small>
			</div>
		</div>
		<div class="row">
			<div class="span12">
				<input type="submit" class="btn btn-primary pull-right" name="submit" value="Update Settings">
			</div>
		</div>
		<hr>
		<h3>Payza/AlertPay Settings</h3>
		<div class="row">
			<div class="span12">
				<strong>IMPORTANT! Set <span class="label label-info">IPN Status</span> as <span class="label label-info">Enabled</span> on <a target="_blank" href="https://secure.payza.com/ManageIPN.aspx">Payza IPN Setup</a>.</strong>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>Enable:</strong></div>
			<div class="span9">
				<label class="checkbox">
					<input type="checkbox" id="enable_payza" name="enable_payza"<?php echo ($options['enable_payza'] == 'on' ? ' checked="checked"' : ''); ?>"> Accept payments via Payza/AlertPay
				</label>
				<small>Please tick checkbox if you would like to accept payments via Payza/AlertPay. CURL is required.</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>Payza ID:</strong></div>
			<div class="span9">
				<input type="text" class="span9" id="payza_id" name="payza_id" value="<?php echo htmlspecialchars($options['payza_id'], ENT_QUOTES); ?>">
				<br /><small>Please enter valid Payza e-mail.</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>Sandbox mode:</strong></div>
			<div class="span9">
				<label class="checkbox">
					<input type="checkbox" id="payza_sandbox" name="payza_sandbox"<?php echo ($options['payza_sandbox'] == 'on' ? ' checked="checked"' : ''); ?>"> Enable Payza sandbox mode
				</label>
				<small>Please tick checkbox if you would like to test Payza service.</small>
			</div>
		</div>
		<div class="row">
			<div class="span12">
				<input type="submit" class="btn btn-primary pull-right" name="submit" value="Update Settings">
			</div>
		</div>
		<hr>
		<h3>Skrill/Moneybookers Settings</h3>
		<div class="row">
			<div class="span3"><strong>Enable:</strong></div>
			<div class="span9">
				<label class="checkbox">
					<input type="checkbox" id="enable_skrill" name="enable_skrill"<?php echo ($options['enable_skrill'] == 'on' ? ' checked="checked"' : ''); ?>"> Accept payments via Skrill/Moneybookers
				</label>
				<small>Please tick checkbox if you would like to accept payments via Skrill/Moneybookers.</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>Skrill ID:</strong></div>
			<div class="span9">
				<input type="text" class="span9" id="skrill_id" name="skrill_id" value="<?php echo htmlspecialchars($options['skrill_id'], ENT_QUOTES); ?>">
				<br /><small>Please enter valid Skrill e-mail.</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>Secret Word:</strong></div>
			<div class="span9">
				<input type="text" class="span9" id="skrill_secret_word" name="skrill_secret_word" value="<?php echo htmlspecialchars($options['skrill_secret_word'], ENT_QUOTES); ?>">
				<br /><small>Please enter valid Skrill Secret Word. You must set it on <a target="_blank" href="https://www.moneybookers.com/app/profile.pl?view=merchant_tools">merchant tools</a> page.</small>
			</div>
		</div>
		<div class="row">
			<div class="span12">
				<input type="submit" class="btn btn-primary pull-right" name="submit" value="Update Settings">
			</div>
		</div>
		<hr>
		<h3>InterKassa Settings</h3>
		<div class="row">
			<div class="span3"><strong>Enable:</strong></div>
			<div class="span9">
				<label class="checkbox">
					<input type="checkbox" id="enable_interkassa" name="enable_interkassa"<?php echo ($options['enable_interkassa'] == 'on' ? ' checked="checked"' : ''); ?>"> Accept payments via InterKassa
				</label>
				<small>Please tick checkbox if you would like to accept payments via InterKassa. CURL is required.</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>Shop ID:</strong></div>
			<div class="span9">
				<input type="text" class="span9" id="interkassa_shop_id" name="interkassa_shop_id" value="<?php echo htmlspecialchars($options['interkassa_shop_id'], ENT_QUOTES); ?>">
				<br /><small>Please enter valid InterKassa shop ID. Ex.: 64C18529-4B94-0B5D-7405-F2752F2B716C.</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>Currency:</strong></div>
			<div class="span9">
				<select name="interkassa_currency" id="interkassa_currency" class="input-small" onchange="supportedmethods();">
<?php				
		for ($i=0; $i<sizeof($interkassa_currency_list); $i++) {
			echo '
					<option value="'.$interkassa_currency_list[$i].'"'.($interkassa_currency_list[$i] == $options['interkassa_currency'] ? ' selected="selected"' : '').'>'.$interkassa_currency_list[$i].'</option>';
		}
?>
				</select>
				<br /><small>Set the currency of InterKassa shop. You can get it on <a target="_blank" href="https://interkassa.com/managment.php">shop settings</a> page.</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>Secret Key:</strong></div>
			<div class="span9">
				<input type="text" class="span9" id="interkassa_secret_key" name="interkassa_secret_key" value="<?php echo htmlspecialchars($options['interkassa_secret_key'], ENT_QUOTES); ?>">
				<br /><small>Please enter Secret Key. You can get it on <a target="_blank" href="https://interkassa.com/managment.php">shop settings</a> page.</small>
			</div>
		</div>
		<div class="row">
			<div class="span12">
				<input type="submit" class="btn btn-primary pull-right" name="submit" value="Update Settings">
			</div>
		</div>
		<hr>
		<h3>Authorize.Net Settings</h3>
		<div class="row">
			<div class="span3"><strong>Enable:</strong></div>
			<div class="span9">
				<label class="checkbox">
					<input type="checkbox" id="enable_authnet" name="enable_authnet"<?php echo ($options['enable_authnet'] == 'on' ? ' checked="checked"' : ''); ?>"> Accept payments via Authorize.Net
				</label>
				<small>Please tick checkbox if you would like to accept payments via Authorize.Net.</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>API Login ID:</strong></div>
			<div class="span9">
				<input type="text" class="span9" id="authnet_login" name="authnet_login" value="<?php echo htmlspecialchars($options['authnet_login'], ENT_QUOTES); ?>">
				<br /><small>Please enter valid Authorize.Net login.</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>Transaction Key:</strong></div>
			<div class="span9">
				<input type="text" class="span9" id="authnet_key" name="authnet_key" value="<?php echo htmlspecialchars($options['authnet_key'], ENT_QUOTES); ?>">
				<br /><small>Please enter Transaction Key. If you do not know Transaction Key, go to your Authorize.Net account settings and click "API Login ID and Transaction Key".</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>MD5 Hash:</strong></div>
			<div class="span9">
				<input type="text" class="span9" id="authnet_md5hash" name="authnet_md5hash" value="<?php echo htmlspecialchars($options['authnet_md5hash'], ENT_QUOTES); ?>">
				<br /><small>Please enter MD5 Hash. If you do not know MD5 Hash, go to your Authorize.Net account settings and click "MD5-Hash".</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>Sandbox mode:</strong></div>
			<div class="span9">
				<label class="checkbox">
					<input type="checkbox" id="authnet_sandbox" name="authnet_sandbox"<?php echo ($options['authnet_sandbox'] == 'on' ? ' checked="checked"' : ''); ?>"> Enable Authorize.Net sandbox mode
				</label>
				<small>Please tick checkbox if you would like to test Authorize.Net service.</small>
			</div>
		</div>
		<div class="row">
			<div class="span12">
				<input type="submit" class="btn btn-primary pull-right" name="submit" value="Update Settings">
			</div>
		</div>
		<hr>
		<h3>EgoPay Settings</h3>
		<div class="row">
			<div class="span12">
				<strong>IMPORTANT! Register your store on <a target="_blank" href="https://www.egopay.com/store/list">EgoPay Stores</a> page. Set <span class="label label-info"><?php echo $url_base; ?>ipn.php?method=egopay</span> as <span class="label label-info">CallBack URL</span>.</strong>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>Enable:</strong></div>
			<div class="span9">
				<label class="checkbox">
					<input type="checkbox" id="enable_egopay" name="enable_egopay"<?php echo ($options['enable_egopay'] == 'on' ? ' checked="checked"' : ''); ?>"> Accept payments via EgoPay
				</label>
				<small>Please tick checkbox if you would like to accept payments via EgoPay. CURL is required.</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>Store ID:</strong></div>
			<div class="span9">
				<input type="text" class="span9" id="egopay_store_id" name="egopay_store_id" value="<?php echo htmlspecialchars($options['egopay_store_id'], ENT_QUOTES); ?>">
				<br /><small>Please enter valid EgoPay Store ID. You can get this parameter <a href="https://www.egopay.com/store/list" target="_blank">here</a>.</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>Store Pass:</strong></div>
			<div class="span9">
				<input type="text" class="span9" id="egopay_store_pass" name="egopay_store_pass" value="<?php echo htmlspecialchars($options['egopay_store_pass'], ENT_QUOTES); ?>">
				<br /><small>Please enter valid EgoPay Store Pass. You can get this parameter <a href="https://www.egopay.com/store/list" target="_blank">here</a>.</small>
			</div>
		</div>
		<div class="row">
			<div class="span12">
				<input type="submit" class="btn btn-primary pull-right" name="submit" value="Update Settings">
			</div>
		</div>
		<hr>
		<h3>Perfect Money® Settings</h3>
		<div class="row">
			<div class="span3"><strong>Enable:</strong></div>
			<div class="span9">
				<label class="checkbox">
					<input type="checkbox" id="enable_perfect" name="enable_perfect"<?php echo ($options['enable_perfect'] == 'on' ? ' checked="checked"' : ''); ?>"> Accept payments via Perfect Money
				</label>
				<small>Please tick checkbox if you would like to accept payments via Perfect Money.</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>Payee Account:</strong></div>
			<div class="span9">
				<input type="text" class="span9" id="perfect_account_id" name="perfect_account_id" value="<?php echo htmlspecialchars($options['perfect_account_id'], ENT_QUOTES); ?>">
				<br /><small>The merchant's PerfectMoney® account to which the payment is to be made.</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>Payee Name:</strong></div>
			<div class="span9">
				<input type="text" class="span9" id="perfect_payee_name" name="perfect_payee_name" value="<?php echo htmlspecialchars($options['perfect_payee_name'], ENT_QUOTES); ?>">
				<br /><small>The name the merchant wishes to have displayed as the Payee on the PerfectMoney® payment form.</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>Alternate Passphrase:</strong></div>
			<div class="span9">
				<input type="text" class="span9" id="perfect_passphrase" name="perfect_passphrase" value="<?php echo htmlspecialchars($options['perfect_passphrase'], ENT_QUOTES); ?>">
				<br /><small>Please enter valid Alternate Passphrase. You can get this parameter <a href="https://perfectmoney.is/settings.html" target="_blank">here</a>.</small>
			</div>
		</div>
		<div class="row">
			<div class="span12">
				<input type="submit" class="btn btn-primary pull-right" name="submit" value="Update Settings">
			</div>
		</div>
		<hr>
		<h3>BitPay Settings</h3>
		<div class="row">
			<div class="span3"><strong>Enable:</strong></div>
			<div class="span9">
				<label class="checkbox">
					<input type="checkbox" id="enable_bitpay" name="enable_bitpay"<?php echo ($options['enable_bitpay'] == 'on' ? ' checked="checked"' : ''); ?>"> Accept payments via BitPay
				</label>
				<small>Please tick checkbox if you would like to accept payments via BitPay.</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>API Key:</strong></div>
			<div class="span9">
				<input type="text" class="span9" id="bitpay_key" name="bitpay_key" value="<?php echo htmlspecialchars($options['bitpay_key'], ENT_QUOTES); ?>">
				<br /><small>Please enter valid API Key. You can get this parameter <a href="https://bitpay.com/api-keys" target="_blank">here</a>.</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>Transaction speed:</strong></div>
			<div class="span9">
				<select id="bitpay_speed" name="bitpay_speed">
					<option value="high"<?php echo ($options['bitpay_speed'] == 'high' ? ' selected="selected"' : '');?>>Immediate</option>
					<option value="medium"<?php echo ($options['bitpay_speed'] == 'medium' ? ' selected="selected"' : '');?>>After 1 block confirmation</option>
					<option value="low"<?php echo ($options['bitpay_speed'] == 'low' ? ' selected="selected"' : '');?>>After 6 block confirmations</option>
				</select>
				<br /><small>Please set how fast an invoice is considered to be "confirmed".</small>
			</div>
		</div>
		<div class="row">
			<div class="span12">
				<input type="submit" class="btn btn-primary pull-right" name="submit" value="Update Settings">
			</div>
		</div>
		<hr>
		<h3>Stripe Settings</h3>
		<div class="row">
			<div class="span3"><strong>Enable:</strong></div>
			<div class="span9">
				<label class="checkbox">
					<input type="checkbox" id="enable_stripe" name="enable_stripe"<?php echo ($options['enable_stripe'] == 'on' ? ' checked="checked"' : ''); ?>"> Accept payments via Stripe
				</label>
				<small>Please tick checkbox if you would like to accept payments via Stripe.</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>Secret Key:</strong></div>
			<div class="span9">
				<input type="text" class="span9" id="stripe_secret" name="stripe_secret" value="<?php echo htmlspecialchars($options['stripe_secret'], ENT_QUOTES); ?>">
				<br /><small>Please enter valid Secret Key. You can get this parameter <a href="https://manage.stripe.com/account/apikeys" target="_blank">here</a>.</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>Publishable Key:</strong></div>
			<div class="span9">
				<input type="text" class="span9" id="stripe_publishable" name="stripe_publishable" value="<?php echo htmlspecialchars($options['stripe_publishable'], ENT_QUOTES); ?>">
				<br /><small>Please enter valid Secret Key. You can get this parameter <a href="https://manage.stripe.com/account/apikeys" target="_blank">here</a>.</small>
			</div>
		</div>
		<div class="row">
			<div class="span12">
				<input type="submit" class="btn btn-primary pull-right" name="submit" value="Update Settings">
			</div>
		</div>
		<hr>
		<h3>Admin Settings</h3>
		<div class="row">
			<div class="span3"><strong>Login:</strong></div>
			<div class="span9">
				<input type="text" class="input-large" name="login" value="<?php echo htmlspecialchars($options['login'], ENT_QUOTES); ?>">
				<br /><small>Please set admin access login.</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>Password:</strong></div>
			<div class="span9">
				<input type="password" class="input-large" name="password" value="">
				<br /><small>Please set admin access password. Leave this field blank if you don't want to change current password.</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>Confirm password:</strong></div>
			<div class="span9">
				<input type="password" class="input-large" name="confirm_password" value="">
				<br /><small>Please confirm admin access password.</small>
			</div>
		</div>
		<div class="row">
			<div class="span12">
				<input type="submit" class="btn btn-primary pull-right" name="submit" value="Update Settings">
			</div>
		</div>
		<hr>
	</form>
<?php 
	} else if ($page == 'campaigns') {
		if (empty($error_message)) {
			if (!empty($ok_message)) $message = '<div class="alert alert-success">'.$ok_message.'</div>';
			else $message = '';
		} else $message = '<div class="alert alert-error">'.$error_message.'</div>';
		echo $message;

		if (isset($_GET["s"])) $search_query = trim(stripslashes($_GET["s"]));
		else $search_query = "";
		
		$tmp = $icdb->get_row("SELECT COUNT(*) AS total FROM ".$icdb->prefix."campaigns WHERE deleted = '0'".((strlen($search_query) > 0) ? " AND title LIKE '%".addslashes($search_query)."%'" : ""));
		$total = $tmp["total"];
		$totalpages = ceil($total/RECORDS_PER_PAGE);
		if ($totalpages == 0) $totalpages = 1;
		if (isset($_GET["p"])) $page = intval($_GET["p"]);
		else $page = 1;
		if ($page < 1 || $page > $totalpages) $page = 1;
		$switcher = page_switcher("admin.php?page=campaigns".((strlen($search_query) > 0) ? "&s=".rawurlencode($search_query) : ""), $page, $totalpages);

		$sql = "SELECT t1.*, t2.total_donors, t2.total_amount FROM ".$icdb->prefix."campaigns t1 LEFT JOIN (SELECT campaign_id, SUM(amount) AS total_amount, COUNT(*) AS total_donors FROM ".$icdb->prefix."donors WHERE status != '".STATUS_DRAFT."' && deleted = '0' GROUP BY campaign_id) t2 ON t2.campaign_id = t1.id WHERE t1.deleted = '0'".((strlen($search_query) > 0) ? " AND t1.title LIKE '%".addslashes($search_query)."%'" : "")." ORDER BY registered DESC LIMIT ".(($page-1)*RECORDS_PER_PAGE).", ".RECORDS_PER_PAGE;
		$rows = $icdb->get_rows($sql);
?>
	<h3>Campaigns</h3>
	<form action="admin.php" method="get" style="margin-bottom: 10px;">
		<input type="hidden" name="page" value="campaigns" />
		<input type="text" name="s" value="<?php echo htmlspecialchars($search_query, ENT_QUOTES); ?>">
		<input type="submit" class="btn" value="Search" />
		<?php echo (strlen($search_query) > 0 ? '<input type="button" class="btn" value="Reset search results" onclick="window.location.href=\'admin.php?page=campaigns\';" />' : ''); ?>
	</form>
	<div class="row">
		<div class="span12">
			<div class="btn-group pull-right">
				<a class="btn btn-primary" href="admin.php?page=editcampaign">Add New Campaign</a>
				<button class="btn btn-primary dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
				<ul class="dropdown-menu">
					<li><a href="admin.php?page=editcampaign">Add New Campaign</a></li>
					<?php echo (!$rows ? '<li class="disabled"><a>Delete All Campaigns</a></li>' : '<li><a href="admin.php?action=delete-all-campaigns" onclick="return submitOperation();">Delete All Campaigns</a></li>'); ?>
				</ul>
			</div>		
		</div>
	</div>
	<table class="table table-striped">
		<tr>
			<th>Title</th>
			<th>Shortcode</th>
			<th style="width: 100px; text-align: right;">Donors</th>
			<th style="width: 100px; text-align: right;">Donated</th>
			<th style="width: 120px;">Registered</th>
			<th style="width: 80px;"></th>
		</tr>
<?php		
		if (sizeof($rows) > 0) {
			foreach ($rows as $row) {
				print ('
		<tr'.($row['status'] == STATUS_PENDING ? ' class="error"' : '').'>
			<td>'.htmlspecialchars($row['title'], ENT_QUOTES).'</td>
			<td><code>'.htmlspecialchars('<div class="udb-box" data-id="'.$row['id'].'"></div>', ENT_QUOTES).'</code></td>
			<td style="text-align: right;">'.intval($row['total_donors']).'</td>
			<td style="text-align: right;">'.number_format($row['total_amount'], 2, ".", "").' '.$row['currency'].'</td>
			<td>'.date("Y-m-d H:i", $row['registered']).'</td>
			<td style="text-align: center;">
				<a href="admin.php?page=editcampaign&id='.$row['id'].'" title="Edit campaign details"><img src="img/edit.png" alt="Edit campaign details" border="0"></a>
				<a href="admin.php?page=donors&cid='.$row['id'].'" title="Donors"><img src="img/users.png" alt="Donors" border="0"></a>
				'.($row["status"] == STATUS_ACTIVE ? '<a href="admin.php?action=block-campaign&id='.$row['id'].'" title="Block campaign"><img src="img/block.png" alt="Block campaign" border="0"></a>' : '').'
				'.($row["status"] == STATUS_PENDING ? '<a href="admin.php?action=unblock-campaign&id='.$row['id'].'" title="Unblock campaign"><img src="img/unblock.png" alt="Unblock campaign" border="0"></a>' : '').'
				<a href="admin.php?action=delete-campaign&id='.$row['id'].'" title="Delete record" onclick="return submitOperation();"><img src="img/delete.png" alt="Delete record" border="0"></a>
			</td>
		</tr>');
			}
		} else {
			print ('
				<tr><td colspan="6" style="padding: 20px; text-align: center;">'.((strlen($search_query) > 0) ? 'No results found for "<strong>'.htmlspecialchars($search_query, ENT_QUOTES).'</strong>"' : 'List is empty.').'</td></tr>');
		}
?>
	</table>
	<div class="row">
		<div class="span6">
			<div class="pull-left">
			<?php echo $switcher; ?>
			&nbsp;
			</div>
		</div>
		<div class="span6">
			<div class="btn-group pull-right">
				<a class="btn btn-primary" href="admin.php?page=editcampaign">Add New Campaign</a>
				<button class="btn btn-primary dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
				<ul class="dropdown-menu">
					<li><a href="admin.php?page=editcampaign">Add New Campaign</a></li>
					<?php echo (!$rows ? '<li class="disabled"><a>Delete All Campaigns</a></li>' : '<li><a href="admin.php?action=delete-all-campaigns" onclick="return submitOperation();">Delete All Campaigns</a></li>'); ?>
				</ul>
			</div>		
		</div>
	</div>
	<hr>
	<script type="text/javascript">
		function submitOperation() {
			var answer = confirm("Do you really want to continue?");
			if (answer) return true;
			else return false;
		}
	</script>
<?php
	} else if ($page == 'editcampaign') {
		if (empty($error_message)) {
			if (!empty($ok_message)) $message = '<div class="alert alert-success">'.$ok_message.'</div>';
			else $message = '';
		} else $message = '<div class="alert alert-error">'.$error_message.'</div>';
		echo $message;

		unset($id);
		if (isset($_GET["id"]) && !empty($_GET["id"])) {
			$id = intval($_GET["id"]);
			$campaign_details = $icdb->get_row("SELECT * FROM ".$icdb->prefix."campaigns WHERE id = '".$id."' AND deleted = '0'");
			if (!$campaign_details) unset($id);
		}
		$values = array();
		foreach (array('title', 'min_amount', 'currency', 'form_intro', 'form_terms', 'top_intro', 'recent_intro') as $value) {
			if (isset($_SESSION[$value])) {
				$values[$value] = $_SESSION[$value];
				unset($_SESSION[$value]);
			} else if (!empty($id)) $values[$value] = $campaign_details[$value];
			else $values[$value] = '';
		}
?>
	<form enctype="multipart/form-data" method="post" action="admin.php?action=update-campaign">
		<h3><?php echo (empty($id) ? 'Add new campaign' : 'Edit campaign details'); ?></h3>
		<div class="row">
			<div class="span3"><strong>Title:</strong></div>
			<div class="span9">
				<input type="text" class="span9" id="title" name="title" value="<?php echo htmlspecialchars($values['title'], ENT_QUOTES); ?>">
				<br /><small>Please enter campaign title. This is for your reference only.</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>Min amount and currency:</strong></div>
			<div class="span9">
				<input type="text" class="input-small" id="min_amount" name="min_amount" value="<?php echo number_format(floatval($values['min_amount']), 2, ".", ""); ?>" style="text-align: right;">
				<select id="currency" name="currency" class="input-small" onchange="supportedmethods();">
<?php
		foreach ($currency_list as $currency) {
			echo '
					<option value="'.$currency.'"'.($currency == $values['currency'] ? ' selected="selected"' : '').'>'.$currency.'</option>';
		}
?>
				</select>
				<span id="supported"></span>
				<br /><small>Set the minimum donation amount and currency.</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>Donation form box content:</strong></div>
			<div class="span9">
				<textarea class="span9" rows="5" id="form_intro" name="form_intro"><?php echo htmlspecialchars($values['form_intro'], ENT_QUOTES); ?></textarea>
				<br /><small>Please enter content of donation form box. HTML allowed. Donation form is inserted below this content. You can use the following keywords: {total_donors}, {total_amount}.</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>Terms & Conditions:</strong></div>
			<div class="span9">
				<textarea class="span9" rows="5" id="form_terms" name="form_terms"><?php echo htmlspecialchars($values['form_terms'], ENT_QUOTES); ?></textarea>
				<br /><small>Your donors must be agree with Terms & Conditions before donating. Leave this field blank if you do not need Terms & Conditions box to be shown.</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>Top donors box content:</strong></div>
			<div class="span9">
				<textarea class="span9" rows="5" id="top_intro" name="top_intro"><?php echo htmlspecialchars($values['top_intro'], ENT_QUOTES); ?></textarea>
				<br /><small>Please enter content of top donors box. HTML allowed. Top donors are inserted below this content.</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>Recent donors box content:</strong></div>
			<div class="span9">
				<textarea class="span9" rows="5" id="recent_intro" name="recent_intro"><?php echo htmlspecialchars($values['recent_intro'], ENT_QUOTES); ?></textarea>
				<br /><small>Please enter content of recent donors box. HTML allowed. Recent donors are inserted below this content.</small>
			</div>
		</div>
		<div class="row">
			<div class="span12">
				<input type="hidden" name="action" value="update-campaign" />
				<?php echo (empty($id) ? '' : '<input type="hidden" name="id" value="'.$id.'" />'); ?>
				<input type="submit" class="btn btn-primary pull-right" name="submit" value="Update Details">
			</div>
		</div>
		<hr>
	</form>
	<script type="text/javascript">
		function supportedmethods() {
			var paypal_currencies = new Array(<?php echo '"'.implode('", "', $paypal_currency_list).'"'; ?>);
			var payza_currencies = new Array(<?php echo '"'.implode('", "', $payza_currency_list).'"'; ?>);
			var egopay_currencies = new Array(<?php echo '"'.implode('", "', $egopay_currency_list).'"'; ?>);
			var perfect_currencies = new Array(<?php echo '"'.implode('", "', $perfect_currency_list).'"'; ?>);
			var skrill_currencies = new Array(<?php echo '"'.implode('", "', $skrill_currency_list).'"'; ?>);
			var bitpay_currencies = new Array(<?php echo '"'.implode('", "', $bitpay_currency_list).'"'; ?>);
			var stripe_currencies = new Array(<?php echo '"'.implode('", "', $stripe_currency_list).'"'; ?>);
			var currency = jQuery("#currency").val();
			var supported = "";
			if (currency == "USD") supported = supported + '<span class="label label-success">Authorize.Net</span> ';
			if (jQuery.inArray(currency, bitpay_currencies) >= 0) supported = supported + '<span class="label label-success">BitPay</span> ';
			if (jQuery.inArray(currency, egopay_currencies) >= 0) supported = supported + '<span class="label label-success">EgoPay</span> ';
			if (currency == "<?php echo $options['interkassa_currency']; ?>") supported = supported + '<span class="label label-success">InterKassa</span> ';
			if (jQuery.inArray(currency, perfect_currencies) >= 0) supported = supported + '<span class="label label-success">Perfect Money</span> ';
			if (jQuery.inArray(currency, paypal_currencies) >= 0) supported = supported + '<span class="label label-success">PayPal</span> ';
			if (jQuery.inArray(currency, payza_currencies) >= 0) supported = supported + '<span class="label label-success">Payza</span> ';
			if (jQuery.inArray(currency, skrill_currencies) >= 0) supported = supported + '<span class="label label-success">Skrill</span> ';
			if (jQuery.inArray(currency, stripe_currencies) >= 0) supported = supported + '<span class="label label-success">Stripe</span> ';
			jQuery("#supported").html("Supported: " + supported.substring(0, supported.length));
		}
		supportedmethods();
	</script>
<?php 
	} else if ($page == 'donors') {
		if (empty($error_message)) {
			if (!empty($ok_message)) $message = '<div class="alert alert-success">'.$ok_message.'</div>';
			else if (DEMO_MODE) $message = '<div class="alert alert-warning"><strong>Demo mode.</strong> Real e-mails are hidden.</div>';
			else $message = '';
		} else $message = '<div class="alert alert-error">'.$error_message.'</div>';
		echo $message;

		if (isset($_GET["s"])) $search_query = trim(stripslashes($_GET["s"]));
		else $search_query = "";
		if (isset($_GET["cid"])) {
			$campaign_id = intval($_GET["cid"]);
			$campaign_details = $icdb->get_row("SELECT * FROM ".$icdb->prefix."campaigns WHERE deleted = '0' AND id = '".$campaign_id."'");
			if (!$campaign_details) $campaign_id = 0;
		}
		else $campaign_id = 0;

		$tmp = $icdb->get_row("SELECT COUNT(*) AS total FROM ".$icdb->prefix."campaigns WHERE deleted = '0'");
		$total_campaigns = $tmp["total"];

		$tmp = $icdb->get_row("SELECT COUNT(*) AS total FROM ".$icdb->prefix."donors WHERE deleted = '0' AND status != '".STATUS_DRAFT."'".($campaign_id > 0 ? " AND campaign_id = '".$campaign_id."'" : "").((strlen($search_query) > 0) ? " AND (name LIKE '%".addslashes($search_query)."%' OR email LIKE '%".addslashes($search_query)."%')" : ""));
		$total = $tmp["total"];
		$totalpages = ceil($total/RECORDS_PER_PAGE);
		if ($totalpages == 0) $totalpages = 1;
		if (isset($_GET["p"])) $page = intval($_GET["p"]);
		else $page = 1;
		if ($page < 1 || $page > $totalpages) $page = 1;
		$switcher = page_switcher("admin.php?page=donors".($campaign_id > 0 ? "&cid=".$campaign_id : "").((strlen($search_query) > 0) ? "&s=".rawurlencode($search_query) : ""), $page, $totalpages);

		$sql = "SELECT t1.*, t2.title AS campaign_title, t2.deleted AS campaign_deleted FROM ".$icdb->prefix."donors t1 LEFT JOIN ".$icdb->prefix."campaigns t2 ON t1.campaign_id = t2.id WHERE t1.deleted = '0' AND t1.status != '".STATUS_DRAFT."'".($campaign_id > 0 ? " AND t1.campaign_id = '".$campaign_id."'" : "").((strlen($search_query) > 0) ? " AND (t1.name LIKE '%".addslashes($search_query)."%' OR t1.email LIKE '%".addslashes($search_query)."%')" : "")." ORDER BY t1.registered DESC LIMIT ".(($page-1)*RECORDS_PER_PAGE).", ".RECORDS_PER_PAGE;
		$rows = $icdb->get_rows($sql);
?>
	<h3>Donors<?php echo ($campaign_id > 0 ? ' <span class="label label-info">'.htmlspecialchars($campaign_details['title'], ENT_QUOTES).'</span>' : ''); ?></h3>
	<form action="admin.php" method="get" style="margin-bottom: 10px;">
		<input type="hidden" name="page" value="donors" />
		<input type="text" name="s" value="<?php echo htmlspecialchars($search_query, ENT_QUOTES); ?>">
		<?php echo ($campaign_id > 0 ? '<input type="hidden" name="cid" value="'.$campaign_id.'" />' : ''); ?>
		<input type="submit" class="btn" value="Search" />
		<?php echo (strlen($search_query) > 0 ? '<input type="button" class="btn" value="Reset search results" onclick="window.location.href=\'admin.php?page=donors'.($campaign_id > 0 ? "&cid=".$campaign_id : "").'\';" />' : ''); ?>
	</form>
	<div class="row">
		<div class="span12">
			<div class="btn-group pull-right">
				<?php echo ($total_campaigns == 0 ? '<a class="btn btn-primary disabled" href="#" onclick="return false;">Add New Donor</a>' : '<a class="btn btn-primary" href="admin.php?page=edit'.($campaign_id > 0 ? '&cid='.$campaign_id : '').'">Add New Donor</a>'); ?>
				<button class="btn btn-primary dropdown-toggle<?php echo ($total_campaigns == 0 ? ' disabled' : ''); ?>" data-toggle="dropdown"><span class="caret"></span></button>
				<ul class="dropdown-menu">
					<?php echo ($total_campaigns == 0 ? '<li class="disabled"><a>Add New Donor</a></li>' : '<li><a href="admin.php?page=edit'.($campaign_id > 0 ? '&cid='.$campaign_id : '').'">Add New Donor</a></li>'); ?>
					<?php echo (!$rows ? '<li class="disabled"><a>CSV Export</a></li>' : '<li><a href="admin.php?action=export'.($campaign_id > 0 ? '&cid='.$campaign_id : '').'">CSV Export</a></li>'); ?>
					<li class="divider"></li>
					<?php echo (!$rows ? '<li class="disabled"><a>Delete All Donors</a></li>' : '<li><a href="admin.php?action=delete-all-donors'.($campaign_id > 0 ? '&cid='.$campaign_id : '').'" onclick="return submitOperation();">Delete All Donors</a></li>'); ?>
				</ul>
			</div>		
		</div>
	</div>
	<table class="table table-striped">
		<tr>
			<th>Campaign</th>
			<th>Name</th>
			<th>E-mail</th>
			<th style="width: 100px; text-align: right;">Amount</th>
			<th style="width: 120px;">Registered</th>
			<th style="width: 80px;"></th>
		</tr>
<?php		
		if (sizeof($rows) > 0) {
			foreach ($rows as $row) {
				$email = $row['email'];
				if (DEMO_MODE) {
					if (($pos = strpos($email, "@")) !== false) {
						$name = substr($email, 0, strpos($email, "@"));
						$email = substr($name, 0, 1).'*****'.substr($email, $pos);
					}
				}
				print ('
		<tr'.($row['status'] == STATUS_PENDING ? ' class="error"' : '').'>
			<td>'.htmlspecialchars($row['campaign_title'], ENT_QUOTES).($row['campaign_deleted'] ? ' <span class="label label-important">DEL</span>' : '').'</td>
			<td>'.(!empty($row['url']) ? '<a href="'.$row['url'].'" target="_blank">' : '').(empty($row['name']) ? '-' : htmlspecialchars($row['name'], ENT_QUOTES)).(!empty($row['url']) ? '</a>' : '').'</td>
			<td>'.htmlspecialchars($email, ENT_QUOTES).'</td>
			<td style="text-align: right;">'.number_format($row['amount'], 2, ".", "").' '.$row['currency'].'</td>
			<td>'.date("Y-m-d H:i", $row['registered']).'</td>
			<td style="text-align: center;">
				<a href="admin.php?page=edit&id='.$row['id'].($campaign_id > 0 ? '&cid='.$campaign_id : '').'" title="Edit donor details"><img src="img/edit.png" alt="Edit donor details" border="0"></a>
				<a href="admin.php?page=transactions&did='.$row['id'].'" title="Payment transactions"><img src="img/transactions.png" alt="" border="0"></a>
				'.($row["status"] == STATUS_ACTIVE ? '<a href="admin.php?action=block-donor&id='.$row['id'].($campaign_id > 0 ? '&cid='.$campaign_id : '').'" title="Block donor"><img src="img/block.png" alt="Block donor" border="0"></a>' : '').'
				'.($row["status"] == STATUS_PENDING ? '<a href="admin.php?action=unblock-donor&id='.$row['id'].($campaign_id > 0 ? '&cid='.$campaign_id : '').'" title="Unblock donor"><img src="img/unblock.png" alt="Unblock donor" border="0"></a>' : '').'
				<a href="admin.php?action=delete-donor&id='.$row['id'].($campaign_id > 0 ? '&cid='.$campaign_id : '').'" title="Delete record" onclick="return submitOperation();"><img src="img/delete.png" alt="Delete record" border="0"></a>
			</td>
		</tr>');
			}
		} else {
			print ('
				<tr><td colspan="6" style="padding: 20px; text-align: center;">'.((strlen($search_query) > 0) ? 'No results found for "<strong>'.htmlspecialchars($search_query, ENT_QUOTES).'</strong>"' : 'List is empty.').'</td></tr>');
		}
?>
	</table>
	<div class="row">
		<div class="span6">
			<div class="pull-left">
			<?php echo $switcher; ?>
			&nbsp;
			</div>
		</div>
		<div class="span6">
			<div class="btn-group pull-right">
				<?php echo ($total_campaigns == 0 ? '<a class="btn btn-primary disabled" href="#" onclick="return false;">Add New Donor</a>' : '<a class="btn btn-primary" href="admin.php?page=edit'.($campaign_id > 0 ? '&cid='.$campaign_id : '').'">Add New Donor</a>'); ?>
				<button class="btn btn-primary dropdown-toggle<?php echo ($total_campaigns == 0 ? ' disabled' : ''); ?>" data-toggle="dropdown"><span class="caret"></span></button>
				<ul class="dropdown-menu">
					<?php echo ($total_campaigns == 0 ? '<li class="disabled"><a>Add New Donor</a></li>' : '<li><a href="admin.php?page=edit'.($campaign_id > 0 ? '&cid='.$campaign_id : '').'">Add New Donor</a></li>'); ?>
					<?php echo (!$rows ? '<li class="disabled"><a>CSV Export</a></li>' : '<li><a href="admin.php?action=export'.($campaign_id > 0 ? '&cid='.$campaign_id : '').'">CSV Export</a></li>'); ?>
					<li class="divider"></li>
					<?php echo (!$rows ? '<li class="disabled"><a>Delete All Donors</a></li>' : '<li><a href="admin.php?action=delete-all-donors'.($campaign_id > 0 ? '&cid='.$campaign_id : '').'" onclick="return submitOperation();">Delete All Donors</a></li>'); ?>
				</ul>
			</div>		
		</div>
	</div>
	<hr>
	<script type="text/javascript">
		function submitOperation() {
			var answer = confirm("Do you really want to continue?");
			if (answer) return true;
			else return false;
		}
	</script>
<?php
	} else if ($page == 'edit') {
		if (empty($error_message)) {
			if (!empty($ok_message)) $message = '<div class="alert alert-success">'.$ok_message.'</div>';
			else if (DEMO_MODE) $message = '<div class="alert alert-warning"><strong>Demo mode.</strong> Real e-mail is hidden.</div>';
			else $message = '';
		} else $message = '<div class="alert alert-error">'.$error_message.'</div>';
		echo $message;

		$campaigns = $icdb->get_rows("SELECT * FROM ".$icdb->prefix."campaigns WHERE deleted = '0'");

		if (isset($_GET["cid"])) $campaign_id = intval($_GET["cid"]);
		else $campaign_id = 0;

		unset($id);
		if (isset($_GET["id"]) && !empty($_GET["id"])) {
			$id = intval($_GET["id"]);
			$donor_details = $icdb->get_row("SELECT * FROM ".$icdb->prefix."donors WHERE id = '".$id."' AND deleted = '0'");
			if (!$donor_details) unset($id);
		}
		$values = array();
		foreach (array('name', 'email', 'url', 'amount', 'campaign_id') as $value) {
			if (isset($_SESSION[$value])) {
				$values[$value] = $_SESSION[$value];
				unset($_SESSION[$value]);
			} else if (!empty($id)) $values[$value] = $donor_details[$value];
			else $values[$value] = '';
		}
		if ($values['campaign_id'] == '') $values['campaign_id'] = $campaign_id;
		
		if (DEMO_MODE) {
			if (($pos = strpos($values['email'], "@")) !== false) {
				$nickname = substr($values['email'], 0, strpos($values['email'], "@"));
				$values['email'] = substr($nickname, 0, 1).'*****'.substr($values['email'], $pos);
			}
		}

?>
	<form enctype="multipart/form-data" method="post" action="admin.php?action=update-donor">
		<h3><?php echo (empty($id) ? 'Add new donor' : 'Edit donor details'); ?></h3>
		<div class="row">
			<div class="span3"><strong>Campaign:</strong></div>
			<div class="span9">
				<select id="campaign_id" name="campaign_id" class="span5" onchange="changecurrency();">
<?php
		if (sizeof($campaigns) > 0) {
			foreach ($campaigns as $campaign) {
				echo '
					<option value="'.$campaign['id'].'"'.($campaign['id'] == $values['campaign_id'] ? ' selected="selected"' : '').'>'.htmlspecialchars($campaign['title'], ENT_QUOTES).'</option>';
			}
		} else {
			echo '
				<option value="0">No campaigns found</option>';
		}
?>
				</select>
				<br /><?php echo (sizeof($campaigns) > 0 ? '<small>Select campaign.</small>' : '<span class="label label-important">Please create at least one campaign</span>'); ?>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>Name:</strong></div>
			<div class="span9">
				<input type="text" class="span9" id="name" name="name" value="<?php echo htmlspecialchars($values['name'], ENT_QUOTES); ?>">
				<br /><small>Please enter donor's name.</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>E-Mail:</strong></div>
			<div class="span9">
				<input type="text" class="span9" id="email" name="email" value="<?php echo htmlspecialchars($values['email'], ENT_QUOTES); ?>">
				<br /><small>Please enter donor's e-mail.</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>Website URL:</strong></div>
			<div class="span9">
				<input type="text" class="span9" id="url" name="url" value="<?php echo htmlspecialchars($values['url'], ENT_QUOTES); ?>">
				<br /><small>Please enter donor's website URL.</small>
			</div>
		</div>
		<div class="row">
			<div class="span3"><strong>Donated amount:</strong></div>
			<div class="span9">
				<input type="text" class="input-small" id="amount" name="amount" value="<?php echo ($values['amount'] == 0 ? number_format(floatval($options['min_amount']), 2, ".", "") : number_format(floatval($values['amount']), 2, ".", "")); ?>" style="text-align: right;">
				<span id="currency"><?php echo (isset($id) ? $donor_details['currency'] : ''); ?></span>
				<br /><small>Please enter donation amount.</small>
			</div>
		</div>
		<div class="row">
			<div class="span12">
				<input type="hidden" name="action" value="update-donor" />
				<?php echo (empty($id) ? '' : '<input type="hidden" name="id" value="'.$id.'" />'); ?>
				<?php echo ($campaign_id == 0 ? '' : '<input type="hidden" name="cid" value="'.$campaign_id.'" />'); ?>
				<input type="submit" class="btn btn-primary pull-right" name="submit" value="Update Details">
			</div>
		</div>
		<hr>
	</form>
	<script type="text/javascript">
		function changecurrency() {
<?php
			$ids = array();
			$currencies = array();
			foreach($campaigns as $campaign) {
				$ids[] = $campaign['id'];
				$currencies[] = $campaign['currency'];
			}
			echo '
			var ids = new Array("'.implode('", "', $ids).'");
			var currencies = new Array("'.implode('", "', $currencies).'");';
?>
			var campaign_id = jQuery("#campaign_id").val();
			var id = jQuery.inArray(campaign_id, ids);
			if (id >= 0) {
				jQuery("#currency").html(currencies[id]);
			}
		}
		changecurrency();
	</script>
<?php 
	} else if ($page == 'transactions') {
		if (empty($error_message)) {
			if (!empty($ok_message)) $message = '<div class="alert alert-success">'.$ok_message.'</div>';
			else if (DEMO_MODE) $message = '<div class="alert alert-warning"><strong>Demo mode.</strong> Real e-mails and transaction details are hidden.</div>';
			else $message = '';
		} else $message = '<div class="alert alert-error">'.$error_message.'</div>';
		echo $message;
		
		if (isset($_GET["s"])) $search_query = trim(stripslashes($_GET["s"]));
		else $search_query = "";
		
		if (isset($_GET["did"])) $donor_id = intval(trim(stripslashes($_GET["did"])));
		else $donor_id = 0;
		$tmp = $icdb->get_row("SELECT COUNT(*) AS total FROM ".$icdb->prefix."transactions WHERE deleted = '0'".($donor_id > 0 ? " AND donor_id = '".$donor_id."'" : "").((strlen($search_query) > 0) ? " AND (payer_name LIKE '%".addslashes($search_query)."%' OR payer_email LIKE '%".addslashes($search_query)."%')" : ""));
		$total = $tmp["total"];
		$totalpages = ceil($total/RECORDS_PER_PAGE);
		if ($totalpages == 0) $totalpages = 1;
		if (isset($_GET["p"])) $page = intval($_GET["p"]);
		else $page = 1;
		if ($page < 1 || $page > $totalpages) $page = 1;
		$switcher = page_switcher("admin.php?page=transactions".($donor_id > 0 ? "&did = '".$donor_id."'" : "").((strlen($search_query) > 0) ? "&s=".rawurlencode($search_query) : ""), $page, $totalpages);

		$rows = $icdb->get_rows("SELECT t1.*, t2.name AS donor_name, t2.url AS donor_url FROM ".$icdb->prefix."transactions t1 LEFT JOIN ".$icdb->prefix."donors t2 ON t1.donor_id = t2.id WHERE t1.deleted = '0'".($donor_id > 0 ? " AND t1.donor_id = '".$donor_id."'" : "").((strlen($search_query) > 0) ? " AND (t1.payer_name LIKE '%".addslashes($search_query)."%' OR t1.payer_email LIKE '%".addslashes($search_query)."%')" : "")." ORDER BY t1.created DESC LIMIT ".(($page-1)*RECORDS_PER_PAGE).", ".RECORDS_PER_PAGE);
?>
	<h3>Transactions</h3>
	<form action="admin.php" method="get" style="margin-bottom: 10px;">
		<input type="hidden" name="page" value="transactions" />
		<input type="text" name="s" value="<?php echo htmlspecialchars($search_query, ENT_QUOTES); ?>">
		<?php echo ($donor_id > 0 ? '<input type="hidden" name="did" value="'.$donor_id.'" />' : ''); ?>
		<input type="submit" class="btn" value="Search" />
		<?php echo (strlen($search_query) > 0 ? '<input type="button" class="btn" value="Reset search results" onclick="window.location.href=\'admin.php?page=transactions'.($donor_id > 0 ? "&did = '".$donor_id."'" : "").'\';" />' : ''); ?>
	</form>
	<table class="table table-striped">
		<tr>
			<th>Donor</th>
			<th>Payer</th>
			<th style="width: 100px; text-align: right;">Amount</th>
			<th style="width: 160px;">Status</th>
			<th style="width: 120px;">Created</th>
			<th style="width: 20px;"></th>
		</tr>
<?php		
		$modals = '';
		if (sizeof($rows) > 0) {
			foreach ($rows as $row) {
				$email = $row['payer_email'];
				$name = $row['payer_name'];
				if (DEMO_MODE) {
					if (($pos = strpos($email, "@")) !== false) {
						$nickname = substr($email, 0, strpos($email, "@"));
						$email = substr($nickname, 0, 1).'*****'.substr($email, $pos);
					}
					if (($pos = strpos($name, "@")) !== false) {
						$nickname = substr($name, 0, strpos($name, "@"));
						$name = substr($nickname, 0, 1).'*****'.substr($name, $pos);
					}
				}
				$modals .= '
				<div style="display: none;" class="modal" id="details_'.$row['id'].'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h3 id="myModalLabel">Transaction Details</h3>
					</div>
					<div class="modal-body">
						<table class="table table-striped">';
				$details = explode("&", $row["details"]);
				foreach ($details as $param) {
					$param = trim($param);
					if (!empty($param)) {
						$data = explode("=", $param, 2);
						$modals .= '
							<tr>
								<td style="width: 170px; font-weight: bold;">'.htmlspecialchars($data[0], ENT_QUOTES).'</td>
								<td>'.(DEMO_MODE ? '*****' : htmlspecialchars(urldecode($data[1]), ENT_QUOTES)).'</td>
							</tr>';
					}
				}
				$modals .= '
						</table>						
					</div>
				</div>';
				echo '
		<tr>
			<td>'.(!empty($row['donor_url']) ? '<a href="'.$row['donor_url'].'" target="_blank">' : '').(empty($row['donor_name']) ? '-' : htmlspecialchars($row['donor_name'], ENT_QUOTES)).(!empty($row['donor_url']) ? '</a>' : '').'</td>
			<td>'.htmlspecialchars($name, ENT_QUOTES).'<br /><em>'.htmlspecialchars($email, ENT_QUOTES).'</em></td>
			<td style="text-align: right;">'.number_format($row['gross'], 2, ".", "").' '.$row['currency'].'</td>
			<td><a href="#details_'.$row['id'].'" data-toggle="modal">'.$row["payment_status"].'</a><br /><em>'.$row["transaction_type"].'</em></td>
			<td>'.date("Y-m-d H:i", $row['created']).'</td>
			<td style="text-align: center;">
				<a href="admin.php?action=delete-transaction&id='.$row['id'].'" title="Delete record" onclick="return submitOperation();"><img src="img/delete.png" alt="Delete record" border="0"></a>
			</td>
		</tr>';
			}
		} else {
			print ('
				<tr><td colspan="6" style="padding: 20px; text-align: center;">'.((strlen($search_query) > 0) ? 'No results found for "<strong>'.htmlspecialchars($search_query, ENT_QUOTES).'</strong>"' : 'List is empty.').'</td></tr>');
		}
?>
	</table>
	<div class="row">
		<div class="span6">
			<div class="pull-left">
			<?php echo $switcher; ?>
			&nbsp;
			</div>
		</div>
	</div>
<?php echo $modals; ?>
	<hr>
	<script type="text/javascript">
		function submitOperation() {
			var answer = confirm("Do you really want to continue?");
			if (answer) return true;
			else return false;
		}
	</script>
<?php
	} else if ($page == 'embed') {
		if (empty($error_message)) {
			if (!empty($ok_message)) $message = '<div class="alert alert-success">'.$ok_message.'</div>';
			else $message = '';
		} else $message = '<div class="alert alert-error">'.$error_message.'</div>';
		echo $message;
		$url_base = '//'.$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
		$filename = basename(__FILE__);
		if (($pos = strpos($url_base, $filename)) !== false) $url_base = substr($url_base, 0, $pos);
?>
	<h3>Embed Universal Donation Box into website</h3>
	<ol class="embed-list">
		<li>
		Make sure that your website loads jQuery. If it doesn't, just add this line into <code>head</code> section:
		<br /><code>&lt;script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"&gt;&lt;/script&gt;</code>
		</li>
		<li>
		If you plan to use Stripe, add this line into <code>head</code> section too:
		<br /><code>&lt;script src="https://checkout.stripe.com/v2/checkout.js"&gt;&lt;/script&gt;</code>
		</li>
		<li>
		Add these lines into <code>head</code> section (before <code>&lt;/head&gt;</code> tag and below jQuery):
		<br /><code>&lt;link href="<?php echo $url_base; ?>css/udb.css?ver=<?php echo VERSION; ?>" rel="stylesheet"&gt;</code>
		<br /><code>&lt;script src="<?php echo $url_base; ?>js/udb-jsonp.js?ver=<?php echo VERSION; ?>"&gt;&lt;/script&gt;</code>
		</li>
		<li>
		Insert campaign shortcode (take it on <a href="admin.php?page=campaigns">Campaigns</a> page) in the place where you want to see donation box. Example:
		<br /><code>&lt;div class="udb-box" data-id="X"&gt;&lt;/div&gt;</code>
		</li>
		<li>
		That's it! Enjoy!
		</li>
	</ol>
	<h3>Customization</h3>
	By default, donation box contains donation form only. You can customize donation box using <code>data-rel</code>
	attribute on step #3. Below you can see available basic values for this attribute:
	<ul class="data-rel-attributes">
		<li><code>form</code> - display donation form</li>
		<li><code>form-nourl</code> - display donation form without "URL" field</li>
		<li><code>top-X</code> - display top X donors</li>
		<li><code>recent-X</code> - display recent X donors</li>
	</ul>
	You can create complex value by mixing basic values (ex. <code>data-rel="form,top-5"</code> or <code>data-rel="top-5,recent-5"</code>).
	<h3>Examples</h3>
	<ol class="embed-list">
		<li>
		This code generates donation box which contains donation form and list of top 10 donors:
		<br /><code>&lt;div class="udb-box" data-id="X" data-rel="form,top-10"&gt;&lt;/div&gt;</code>
		</li>
		<li>
		This code generates donation box which contains list of 10 recent donors and list of top 5 donors:
		<br /><code>&lt;div class="udb-box" data-id="X" data-rel="recent-10,top-5"&gt;&lt;/div&gt;</code>
		</li>
		<li>
		This code generates donation box which contains list of top 10 donors and form without "URL" field:
		<br /><code>&lt;div class="udb-box" data-id="X" data-rel="top-10,form-nourl"&gt;&lt;/div&gt;</code>
		</li>
	</ol>
	
	<hr>
<?php
	} else if ($page == 'login') {
		if (empty($error_message)) {
			if (!empty($ok_message)) $message = '<div class="alert alert-success">'.$ok_message.'</div>';
			else $message = '';
		} else $message = '<div class="alert alert-error">'.$error_message.'</div>';
		echo $message;
?>
	<div class="loginbox">
		<h4 style="text-align: center;">Admin Panel</h4>
		<div style="margin-top: 10px; margin-left: 20px;">
		<form class="form-inline" method="post" action="admin.php?action=login">
			<input type="text" class="input-medium" name="login" placeholder="Login" title="Login">
			<input type="password" class="input-medium" name="password" placeholder="Password" title="Password">
			<button type="submit" class="btn btn-primary">Sign in</button>
		</form>
		</div>
	</div>
<?php
	}
?>

</div>

<!-- Footer - begin -->
<div class="navbar navbar-fixed-bottom navbar-inverse">
	<div class="navbar-inner">
		<div class="container">
			<p class="navbar-text pull-left">Universal Donation Box, ver. <?php echo VERSION; ?></p>
			<p class="navbar-text pull-right">Copyright &copy; 2011-<?php echo date('Y'); ?> <a href="http://www.icprojects.net/" target="_blank">Ivan Churakov</a></p>
		</div>
	</div>
</div>
<!-- Footer - end -->
</body>
</html>