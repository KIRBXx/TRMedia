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
if (isset($_COOKIE['ulp-auth'])) {
	$session_id = preg_replace('/[^a-zA-Z0-9]/', '', $_COOKIE['ulp-auth']);
	$session_details = $icdb->get_row("SELECT * FROM ".$icdb->prefix."sessions WHERE session_id = '".$session_id."' AND registered + valid_period > '".time()."'");
	if ($session_details) {
		$icdb->query("UPDATE ".$icdb->prefix."sessions SET registered = '".time()."', ip = '".$_SERVER['REMOTE_ADDR']."' WHERE session_id = '".$session_id."'");
		$is_logged = true;
	}
}

get_options();

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
	'popups' => array('title' => 'Popups', 'menu' => true),
	'subscribers' => array('title' => 'Subscribers', 'menu' => true),
	'faq' => array('title' => 'FAQ', 'menu' => true),
	'embed' => array('title' => 'How To Use', 'menu' => true),
	'create' => array('title' => 'Create Popup', 'menu' => false)
);
$deafult_page = 'popups';
if ($is_logged) {
	if (isset($_REQUEST['action'])) {
		switch ($_REQUEST['action']) {
			case 'logout':
				if (!empty($session_id)) {
					$icdb->query("UPDATE ".$icdb->prefix."sessions SET valid_period = '0' WHERE session_id = '".$session_id."'");
				}
				header('Location: admin.php');
				exit;
				break;

			case 'save-settings':
				if (DEMO_MODE) {
					$return_object = array();
					$return_object['status'] = 'ERROR';
					$return_object['message'] = '<strong>Demo mode.</strong> This operation is disabled.';
					echo json_encode($return_object);
					exit;
				}
				populate_options();
				$errors = array();
				if (strlen($options['onload_delay']) > 0 && $options['onload_delay'] != preg_replace('/[^0-9]/', '', $options['onload_delay'])) $errors[] = 'Invalid OnLoad delay value.';
				if (isset($_POST['ulp_password'])) $password = trim(stripslashes($_POST['ulp_password']));
				else $password = '';
				if (isset($_POST['ulp_confirm_password'])) $confirm_password = trim(stripslashes($_POST['ulp_confirm_password']));
				else $confirm_password = '';
				if (!empty($password)) {
					if ($password == $confirm_password) {
						$options['password'] = md5($password);
					} else {
						if ($errors === true) $errors = array('Password and its confirmation are not equal');
						else $errors[] = 'Password and its confirmation are not equal';
					}
				}
				$login = trim(stripslashes($_POST['ulp_login']));
				if (empty($login)) $options['login'] = 'admin';
				if (!empty($errors)) {
					$return_object = array();
					$return_object['status'] = 'ERROR';
					$return_object['message'] = 'Attention! Please correct the errors below and try again.<ul><li>'.implode('</li><li>', $errors).'</li></ul>';
					echo json_encode($return_object);
					exit;
				}
				update_options();
				$_SESSION['ok'] = 'Settings successfully <strong>saved</strong>.';
				
				$return_object = array();
				$return_object['status'] = 'OK';
				$return_object['return_url'] = 'admin.php?page=settings';
				echo json_encode($return_object);
				exit;
				break;
			
			case 'reset-cookie':
				if (DEMO_MODE) exit;
				$options["cookie_value"] = time();
				update_options();
				echo 'OK';
				exit;
				break;

			case 'aweber-connect':
				if (DEMO_MODE) {
					$return_object = array();
					$return_object['status'] = 'ERROR';
					$return_object['message'] = '<strong>Demo mode.</strong> This operation is disabled.';
					echo json_encode($return_object);
					exit;
				}
				if (!isset($_POST['aweber-oauth-id']) || empty($_POST['aweber-oauth-id'])) {
					$return_object = array();
					$return_object['status'] = 'ERROR';
					$return_object['message'] = 'Authorization Code not found.';
					echo json_encode($return_object);
					exit;
				}
				$code = trim(stripslashes($_POST['aweber-oauth-id']));
				if (!class_exists('AWeberAPI')) {
					require_once(dirname(__FILE__).'/aweber_api/aweber_api.php');
				}
				$account = null;
				try {
					list($consumer_key, $consumer_secret, $access_key, $access_secret) = AWeberAPI::getDataFromAweberID($code);
				} catch (AWeberAPIException $exc) {
					list($consumer_key, $consumer_secret, $access_key, $access_secret) = null;
				} catch (AWeberOAuthDataMissing $exc) {
					list($consumer_key, $consumer_secret, $access_key, $access_secret) = null;
				} catch (AWeberException $exc) {
					list($consumer_key, $consumer_secret, $access_key, $access_secret) = null;
				}
				if (!$access_secret) {
					$return_object = array();
					$return_object['status'] = 'ERROR';
					$return_object['message'] = 'Invalid Authorization Code!';
					echo json_encode($return_object);
					exit;
				} else {
					try {
						$aweber = new AWeberAPI($consumer_key, $consumer_secret);
						$account = $aweber->getAccount($access_key, $access_secret);
					} catch (AWeberException $e) {
						$return_object = array();
						$return_object['status'] = 'ERROR';
						$return_object['message'] = 'Can not access AWeber account!';
						echo json_encode($return_object);
						exit;
					}
				}
				$options['aweber_consumer_key'] = $consumer_key;
				$options['aweber_consumer_secret'] = $consumer_secret;
				$options['aweber_access_key'] = $access_key;
				$options['aweber_access_secret'] = $access_secret;
				update_options();
				$return_object = array();
				$return_object['status'] = 'OK';
				$return_object['html'] = '
					<table class="ulp_useroptions">
						<tr>
							<th>Connected:</th>
							<td>
								<input type="button" class="ulp_button button-secondary" value="Disconnect" onclick="return ulp_aweber_disconnect();" >
								<img id="ulp-aweber-loading" src="images/loading.gif">
								<br /><em>Click the button to disconnect.</em>
							</td>
						</tr>
					</table>';
				echo json_encode($return_object);
				exit;
				break;

			case 'aweber-disconnect':
				if (DEMO_MODE) {
					$return_object = array();
					$return_object['status'] = 'ERROR';
					$return_object['message'] = '<strong>Demo mode.</strong> This operation is disabled.';
					echo json_encode($return_object);
					exit;
				}
				$options['aweber_consumer_key'] = '';
				$options['aweber_consumer_secret'] = '';
				$options['aweber_access_key'] = '';
				$options['aweber_access_secret'] = '';
				update_options();
				$return_object = array();
				$return_object['status'] = 'OK';
				$return_object['html'] = '
					<table class="ulp_useroptions">
						<tr>
							<th>Authorization code:</th>
							<td>
								<input type="text" id="ulp_aweber_oauth_id" value="" class="widefat" placeholder="AWeber authorization code">
								<br />Get your authorization code <a target="_blank" href="https://auth.aweber.com/1.0/oauth/authorize_app/'.AWEBER_APPID.'">here</a>.
							</td>
						</tr>
						<tr>
							<th></th>
							<td style="vertical-align: middle;">
								<input type="button" class="ulp_button button-secondary" value="Make Connection" onclick="return ulp_aweber_connect();" >
								<img id="ulp-aweber-loading" src="images/loading.gif">
							</td>
						</tr>
					</table>';
				echo json_encode($return_object);
				exit;
				break;
				
			case 'delete':
				if (DEMO_MODE) {
					$_SESSION['error'] = '<strong>Demo mode.</strong> This operation is disabled.';
					header('Location: admin.php?page=popups');
					exit;
				}
				$id = intval($_GET["id"]);
				$popup_details = $icdb->get_row("SELECT * FROM ".$icdb->prefix."popups WHERE id = '".$id."' AND deleted = '0'");
				if (intval($popup_details["id"]) == 0) {
					$_SESSION['error'] = '<strong>Invalid</strong> service call.';
					header('Location: admin.php?page=popups');
					exit;
				}
				$sql = "UPDATE ".$icdb->prefix."popups SET deleted = '1' WHERE id = '".$id."'";
				if ($icdb->query($sql) !== false) {
					$_SESSION['ok'] = 'Popup successfully <strong>removed</strong>.';
					header('Location: admin.php?page=popups');
					exit;
				} else {
					$_SESSION['error'] = '<strong>Invalid</strong> service call.';
					header('Location: admin.php?page=popups');
					exit;
				}
				exit;
				break;

			case 'block':
				if (DEMO_MODE) {
					$_SESSION['error'] = '<strong>Demo mode.</strong> This operation is disabled.';
					header('Location: admin.php?page=popups');
					exit;
				}
				$id = intval($_GET["id"]);
				$popup_details = $icdb->get_row("SELECT * FROM ".$icdb->prefix."popups WHERE id = '".$id."' AND deleted = '0'");
				if (intval($popup_details["id"]) == 0) {
					$_SESSION['error'] = '<strong>Invalid</strong> service call.';
					header('Location: admin.php?page=popups');
					exit;
				}
				$sql = "UPDATE ".$icdb->prefix."popups SET blocked = '1' WHERE id = '".$id."'";
				if ($icdb->query($sql) !== false) {
					$_SESSION['ok'] = 'Popup successfully <strong>blocked</strong>.';
					header('Location: admin.php?page=popups');
					exit;
				} else {
					$_SESSION['error'] = '<strong>Invalid</strong> service call.';
					header('Location: admin.php?page=popups');
					exit;
				}
				exit;
				break;

			case 'unblock':
				if (DEMO_MODE) {
					$_SESSION['error'] = '<strong>Demo mode.</strong> This operation is disabled.';
					header('Location: admin.php?page=popups');
					exit;
				}
				$id = intval($_GET["id"]);
				$popup_details = $icdb->get_row("SELECT * FROM ".$icdb->prefix."popups WHERE id = '".$id."' AND deleted = '0'");
				if (intval($popup_details["id"]) == 0) {
					$_SESSION['error'] = '<strong>Invalid</strong> service call.';
					header('Location: admin.php?page=popups');
					exit;
				}
				$sql = "UPDATE ".$icdb->prefix."popups SET blocked = '0' WHERE id = '".$id."'";
				if ($icdb->query($sql) !== false) {
					$_SESSION['ok'] = 'Popup successfully <strong>unblocked</strong>.';
					header('Location: admin.php?page=popups');
					exit;
				} else {
					$_SESSION['error'] = '<strong>Invalid</strong> service call.';
					header('Location: admin.php?page=popups');
					exit;
				}
				exit;
				break;

			case 'export':
				if (DEMO_MODE) {
					$_SESSION['error'] = '<strong>Demo mode.</strong> This operation is disabled.';
					header('Location: admin.php?page=popups');
					exit;
				}
				error_reporting(0);
				$id = intval($_GET["id"]);
				$popup_details = $icdb->get_row("SELECT * FROM ".$icdb->prefix."popups WHERE id = '".$id."' AND deleted = '0'");
				$popup_full = array();
				if (!empty($popup_details)) {
					$popup_full = array();
					$popup_full['popup'] = $popup_details;
					$popup_full['layers'] = $icdb->get_rows("SELECT * FROM ".$icdb->prefix."layers WHERE popup_id = '".$id."' AND deleted = '0'");
					$popup_data = serialize($popup_full);
					$output = EXPORT_VERSION.PHP_EOL.md5($popup_data).PHP_EOL.base64_encode($popup_data);
					if (strstr($_SERVER["HTTP_USER_AGENT"],"MSIE")) {
						header("Pragma: public");
						header("Expires: 0");
						header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
						header("Content-type: application-download");
						header('Content-Disposition: attachment; filename="'.$popup_details['str_id'].'.txt"');
						header("Content-Transfer-Encoding: binary");
					} else {
						header("Content-type: application-download");
						header('Content-Disposition: attachment; filename="'.$popup_details['str_id'].'.txt"');
					}
					echo $output;
					flush();
					ob_flush();
					exit;
	            }
	            header('Location: admin.php?page=popups');
				exit;
				break;
				
			case 'import':
				if (DEMO_MODE) {
					$_SESSION['error'] = '<strong>Demo mode.</strong> This operation is disabled.';
					header('Location: admin.php?page=popups');
					exit;
				}
				if (is_uploaded_file($_FILES["ulp-file"]["tmp_name"])) {
					$lines = file($_FILES["ulp-file"]["tmp_name"]);
					if (sizeof($lines) != 3) {
						$_SESSION['error'] = '<strong>Invalid</strong> popup file.';
						header('Location: admin.php?page=popups');
						exit;
					}
					$version = intval(trim($lines[0]));
					if ($version > intval(EXPORT_VERSION)) {
						$_SESSION['error'] = 'Popup file version <strong>is not supported</strong>.';
						header('Location: admin.php?page=popups');
						exit;
					}
					$md5_hash = trim($lines[1]);
					$popup_data = trim($lines[2]);
					$popup_data = base64_decode($popup_data);
					if (!$popup_data || md5($popup_data) != $md5_hash) {
						$_SESSION['error'] = 'Popup file <strong>corrupted</strong>.';
						header('Location: admin.php?page=popups');
						exit;
					}
					$popup = unserialize($popup_data);
					$popup_details = $popup['popup'];
					$str_id = random_string(16);
					$sql = "INSERT INTO ".$icdb->prefix."popups (str_id, title, width, height, options, created, blocked, deleted) 
						VALUES (
						'".$str_id."', 
						'".mysql_real_escape_string($popup_details['title'])."', 
						'".intval($popup_details['width'])."', 
						'".intval($popup_details['height'])."', 
						'".mysql_real_escape_string($popup_details['options'])."', 
						'".time()."', '1', '0')";
					$icdb->query($sql);
					$popup_id = $icdb->insert_id;
					$layers = $popup['layers'];
					if (sizeof($layers) > 0) {
						foreach ($layers as $layer) {
							$sql = "INSERT INTO ".$icdb->prefix."layers (
								popup_id, title, content, zindex, details, created, deleted) VALUES (
								'".$popup_id."',
								'".mysql_real_escape_string($layer['title'])."',
								'".mysql_real_escape_string($layer['content'])."',
								'".mysql_real_escape_string($layer['zindex'])."',
								'".mysql_real_escape_string($layer['details'])."',
								'".time()."', '0')";
							$icdb->query($sql);
						}
					}
					$_SESSION['ok'] = 'New popup successfully <strong>imported</strong> and marked as <strong>blocked</strong>.';
					header('Location: admin.php?page=popups');
					exit;
				}
				$_SESSION['error'] = 'Popup file <strong>not uploaded</strong>.';
				header('Location: admin.php?page=popups');
				exit;
				break;
			
			case 'copy':
				if (DEMO_MODE) {
					$_SESSION['error'] = '<strong>Demo mode.</strong> This operation is disabled.';
					header('Location: admin.php?page=popups');
					exit;
				}
				$id = intval($_GET["id"]);
				$popup_details = $icdb->get_row("SELECT * FROM ".$icdb->prefix."popups WHERE id = '".$id."' AND deleted = '0'");
				if (empty($popup_details)) {
					$_SESSION['error'] = '<strong>Invalid</strong> service call.';
					header('Location: admin.php?page=popups');
					exit;
				}
				$str_id = random_string(16);
				$sql = "INSERT INTO ".$icdb->prefix."popups (str_id, title, width, height, options, created, blocked, deleted) 
					VALUES (
					'".$str_id."', 
					'".mysql_real_escape_string($popup_details['title'])."', 
					'".intval($popup_details['width'])."', 
					'".intval($popup_details['height'])."', 
					'".mysql_real_escape_string($popup_details['options'])."', 
					'".time()."', '0', '0')";
				$icdb->query($sql);
				$popup_id = $icdb->insert_id;
				$layers = $icdb->get_rows("SELECT * FROM ".$icdb->prefix."layers WHERE popup_id = '".$popup_details['id']."' AND deleted = '0'");
				if (sizeof($layers) > 0) {
					foreach ($layers as $layer) {
						$sql = "INSERT INTO ".$icdb->prefix."layers (
							popup_id, title, content, zindex, details, created, deleted) VALUES (
							'".$popup_id."',
							'".mysql_real_escape_string($layer['title'])."',
							'".mysql_real_escape_string($layer['content'])."',
							'".mysql_real_escape_string($layer['zindex'])."',
							'".mysql_real_escape_string($layer['details'])."',
							'".time()."', '0')";
						$icdb->query($sql);
					}
				}
				$_SESSION['ok'] = 'Popup successfully <strong>duplicated</strong>.';
				header('Location: admin.php?page=popups');
				exit;
				break;
			
			case 'save-popup':
				if (DEMO_MODE) {
					$return_object = array();
					$return_object['status'] = 'ERROR';
					$return_object['message'] = '<strong>Demo mode.</strong> This operation is disabled.';
					echo json_encode($return_object);
					exit;
				}
				foreach ($default_popup_options as $key => $value) {
					if (isset($_POST['ulp_'.$key])) {
						$popup_options[$key] = stripslashes(trim($_POST['ulp_'.$key]));
					}
				}
				if (isset($_POST["ulp_mailchimp_double"])) $popup_options['mailchimp_double'] = "on";
				else $popup_options['mailchimp_double'] = "off";
				if (isset($_POST["ulp_mailchimp_welcome"])) $popup_options['mailchimp_welcome'] = "on";
				else $popup_options['mailchimp_welcome'] = "off";
				if (isset($_POST["ulp_mailchimp_enable"])) $popup_options['mailchimp_enable'] = "on";
				else $popup_options['mailchimp_enable'] = "off";
				if (isset($_POST["ulp_icontact_enable"])) $popup_options['icontact_enable'] = "on";
				else $popup_options['icontact_enable'] = "off";
				if (isset($_POST["ulp_campaignmonitor_enable"])) $popup_options['campaignmonitor_enable'] = "on";
				else $popup_options['campaignmonitor_enable'] = "off";
				if (isset($_POST["ulp_getresponse_enable"])) $popup_options['getresponse_enable'] = "on";
				else $popup_options['getresponse_enable'] = "off";
				if (isset($_POST["ulp_aweber_enable"])) $popup_options['aweber_enable'] = "on";
				else $popup_options['aweber_enable'] = "off";
				if (isset($_POST["ulp_enable_close"])) $popup_options['enable_close'] = "on";
				else $popup_options['enable_close'] = "off";
				
				if (isset($_POST['ulp_id'])) $popup_id = intval($_POST['ulp_id']);
				else $popup_id = 0;
				$popup_details = $icdb->get_row("SELECT * FROM ".$icdb->prefix."popups WHERE id = '".$popup_id."'");
				if (empty($popup_details)) {
					$return_object = array();
					$return_object['status'] = 'ERROR';
					$return_object['message'] = 'Invalid popup ID. Try again later.';
					echo json_encode($return_object);
					exit;
				}
				$errors = array();
				
				$layers = $icdb->get_row("SELECT * FROM ".$icdb->prefix."layers WHERE popup_id = '".$popup_id."' AND deleted = '0'");
				if (!$layers) $errors[] = 'Create at least one layer.';
				if (strlen($popup_options['title']) < 1) $errors[] = 'Popup title is too short.';
				if (strlen($popup_options['width']) > 0 && $popup_options['width'] != preg_replace('/[^0-9]/', '', $popup_options['width'])) $errors[] = 'Invalid popup basic width.';
				if (strlen($popup_options['height']) > 0 && $popup_options['height'] != preg_replace('/[^0-9]/', '', $popup_options['height'])) $errors[] = 'Invalid popup basic height.';
				if (strlen($popup_options['overlay_color']) > 0 && get_rgb($popup_options['overlay_color']) === false) $errors[] = 'Ovarlay color must be a valid value.';
				if (floatval($popup_options['overlay_opacity']) < 0 || floatval($popup_options['overlay_opacity']) > 1) $errors[] = 'Overlay opacity must be in a range [0...1].';
				if (strlen($popup_options['name_placeholder']) < 1) $errors[] = '"Name" field placeholder is too short.';
				if (strlen($popup_options['email_placeholder']) < 1) $errors[] = '"E-mail" field placeholder is too short.';
				if (strlen($popup_options['input_border_color']) > 0 && get_rgb($popup_options['input_border_color']) === false) $errors[] = 'Input filed border color must be a valid value.';
				if (strlen($popup_options['input_background_color']) > 0 && get_rgb($popup_options['input_background_color']) === false) $errors[] = 'Input filed background color must be a valid value.';
				if (floatval($popup_options['input_background_opacity']) < 0 || floatval($popup_options['input_background_opacity']) > 1) $errors[] = 'Input filed background opacity must be in a range [0...1].';
				if (strlen($popup_options['button_label']) < 1) $errors[] = '"Subscribe" button label is too short.';
				if (strlen($popup_options['button_color']) == 0 || get_rgb($popup_options['button_color']) === false) $errors[] = '"Subscribe" button color must be a valid value.';
				if (strlen($popup_options['return_url']) > 0 && !preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $popup_options['return_url'])) $errors[] = 'Redirect URL must be a valid URL.';
				if ($popup_options['mailchimp_enable'] == 'on') {
					if (empty($popup_options['mailchimp_api_key']) || strpos($popup_options['mailchimp_api_key'], '-') === false) $errors[] = 'Invalid MailChimp API Key.';
					if (empty($popup_options['mailchimp_list_id'])) $errors[] = 'Invalid MailChimp List ID.';
				}
				if ($popup_options['icontact_enable'] == 'on') {
					if (empty($popup_options['icontact_appid'])) $errors[] = 'Invalid iContact App ID.';
					if (empty($popup_options['icontact_apiusername'])) $errors[] = 'Invalid iContact API Username.';
					if (empty($popup_options['icontact_apipassword'])) $errors[] = 'Invalid iContact API Password.';
					if (empty($popup_options['icontact_listid'])) $errors[] = 'Invalid iContact List ID.';
				}
				if ($popup_options['campaignmonitor_enable'] == 'on') {
					if (empty($popup_options['campaignmonitor_api_key'])) $errors[] = 'Invalid Campaign Monitor API Key.';
					if (empty($popup_options['campaignmonitor_list_id'])) $errors[] = 'Invalid Campaign Monitor List ID.';
				}
				if ($popup_options['getresponse_enable'] == 'on') {
					if (empty($popup_options['getresponse_api_key'])) $errors[] = 'Invalid GetResponse API Key.';
					if (empty($popup_options['getresponse_campaign_id'])) $errors[] = 'Invalid GetResponse Campaign ID.';
				}
				if ($popup_options['aweber_enable'] == 'on') {
					if (empty($popup_options['aweber_listid'])) $errors[] = 'Invalid AWeber List ID.';
				}

				if (!empty($errors)) {
					$return_object = array();
					$return_object['status'] = 'ERROR';
					$return_object['message'] = 'Attention! Please correct the errors below and try again.<ul><li>'.implode('</li><li>', $errors).'</li></ul>';
					echo json_encode($return_object);
					exit;
				}
				$sql = "UPDATE ".$icdb->prefix."popups SET
					title = '".mysql_real_escape_string($popup_options['title'])."',
					width = '".intval($popup_options['width'])."',
					height = '".intval($popup_options['height'])."',
					options = '".mysql_real_escape_string(serialize($popup_options))."',
					deleted = '0'
					WHERE id = '".$popup_id."'";
				$icdb->query($sql);

				$_SESSION['ok'] = 'Popup details successfully <strong>saved</strong>.';
				
				$return_object = array();
				$return_object['status'] = 'OK';
				$return_object['return_url'] = 'admin.php?page=popups';
				echo json_encode($return_object);
				exit;
				break;
			
			case 'save-layer':
				if (DEMO_MODE) {
					$return_object = array();
					$return_object['status'] = 'ERROR';
					$return_object['message'] = '<strong>Demo mode.</strong> This operation is disabled.';
					echo json_encode($return_object);
					exit;
				}
				foreach ($default_layer_options as $key => $value) {
					if (isset($_POST['ulp_layer_'.$key])) {
						$layer_options[$key] = stripslashes(trim($_POST['ulp_layer_'.$key]));
					}
				}
				if (isset($_POST['ulp_layer_id'])) $layer_id = intval($_POST['ulp_layer_id']);
				else $layer_id = 0;
				if (isset($_POST['ulp_popup_id'])) $popup_id = intval($_POST['ulp_popup_id']);
				else $popup_id = 0;
				$popup_details = $icdb->get_row("SELECT * FROM ".$icdb->prefix."popups WHERE id = '".$popup_id."'");
				if (empty($popup_details)) {
					$return_object = array();
					$return_object['status'] = 'ERROR';
					$return_object['message'] = 'Invalid popup ID. Try again later.';
					echo json_encode($return_object);
					exit;
				}
				$errors = array();
				if (strlen($layer_options['title']) < 1) $errors[] = 'Layer title is too short.';
				if (strlen($layer_options['width']) > 0 && $layer_options['width'] != preg_replace('/[^0-9]/', '', $layer_options['width'])) $errors[] = 'Invalid layer width.';
				if (strlen($layer_options['height']) > 0 && $layer_options['height'] != preg_replace('/[^0-9]/', '', $layer_options['height'])) $errors[] = 'Invalid layer height.';
				if (strlen($layer_options['left']) == 0 || $layer_options['left'] != preg_replace('/[^0-9\-]/', '', $layer_options['left'])) $errors[] = 'Invalid left position.';
				if (strlen($layer_options['top']) == 0 || $layer_options['top'] != preg_replace('/[^0-9\-]/', '', $layer_options['top'])) $errors[] = 'Invalid top position.';
				if (strlen($layer_options['background_color']) > 0 && get_rgb($layer_options['background_color']) === false) $errors[] = 'Background color must be a valid value.';
				if (floatval($layer_options['background_opacity']) < 0 || floatval($layer_options['background_opacity']) > 1) $errors[] = 'Background opacity must be in a range [0...1].';
				if (strlen($layer_options['background_image']) > 0 && !preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $layer_options['background_image'])) $errors[] = 'Background image URL must be a valid URL.';
				if (strlen($layer_options['index']) > 0 && $layer_options['index'] != preg_replace('/[^0-9]/', '', $layer_options['index']) && $layer_options['index'] > 100) $errors[] = 'Layer index must be in a range [0...100].';
				if (strlen($layer_options['appearance_delay']) > 0 && $layer_options['appearance_delay'] != preg_replace('/[^0-9]/', '', $layer_options['appearance_delay']) && $layer_options['appearance_delay'] > 10000) $errors[] = 'Appearance start delay must be in a range [0...10000].';
				if (strlen($layer_options['appearance_speed']) > 0 && $layer_options['appearance_speed'] != preg_replace('/[^0-9]/', '', $layer_options['appearance_speed']) && $layer_options['appearance_speed'] > 10000) $errors[] = 'Appearance duration speed must be in a range [0...10000].';
				if (strlen($layer_options['font_color']) > 0 && get_rgb($layer_options['font_color']) === false) $errors[] = 'Font color must be a valid value.';
				if (strlen($layer_options['font_size']) > 0 && $layer_options['font_size'] != preg_replace('/[^0-9]/', '', $layer_options['font_size']) && ($layer_options['font_size'] > 72 || $layer_options['font_size'] < 10)) $errors[] = 'Font size must be in a range [10...72].';
				if (strlen($layer_options['text_shadow_color']) > 0 && get_rgb($layer_options['text_shadow_color']) === false) $errors[] = 'Text shadow color must be a valid value.';
				if (strlen($layer_options['text_shadow_size']) > 0 && $layer_options['text_shadow_size'] != preg_replace('/[^0-9]/', '', $layer_options['text_shadow_size']) && $layer_options['text_shadow_size'] > 72) $errors[] = 'Text shadow size must be in a range [0...72].';

				if (!empty($errors)) {
					$return_object = array();
					$return_object['status'] = 'ERROR';
					$return_object['message'] = 'Attention! Please correct the errors below and try again.<ul><li>'.implode('</li><li>', $errors).'</li></ul>';
					echo json_encode($return_object);
					exit;
				}
				if ($layer_id > 0) $layer_details = $icdb->get_row("SELECT * FROM ".$icdb->prefix."layers WHERE id = '".$layer_id."' AND popup_id = '".$popup_id."' AND deleted = '0'");
				if (!empty($layer_details)) {
					$sql = "UPDATE ".$icdb->prefix."layers SET
						title = '".mysql_real_escape_string($layer_options['title'])."',
						content = '".mysql_real_escape_string($layer_options['content'])."',
						zindex = '".mysql_real_escape_string($layer_options['index'])."',
						details = '".mysql_real_escape_string(serialize($layer_options))."'
						WHERE id = '".$layer_id."'";
					$icdb->query($sql);
				} else {
					$sql = "INSERT INTO ".$icdb->prefix."layers (
						popup_id, title, content, zindex, details, created, deleted) VALUES (
						'".$popup_id."',
						'".mysql_real_escape_string($layer_options['title'])."',
						'".mysql_real_escape_string($layer_options['content'])."',
						'".mysql_real_escape_string($layer_options['index'])."',
						'".mysql_real_escape_string(serialize($layer_options))."',
						'".time()."', '0')";
					$icdb->query($sql);
					$layer_id = $icdb->insert_id;
				}
				
				$return_object = array();
				$return_object['status'] = 'OK';
				$return_object['title'] = htmlspecialchars($layer_options['title'], ENT_QUOTES);
				if (strlen($layer_options['content']) == 0) $content = 'No content...';
				else if (strlen($layer_options['content']) > 192) $content = substr($layer_options['content'], 0, 180).'...';
				else $content = $layer_options['content'];
				$return_object['content'] = htmlspecialchars($content, ENT_QUOTES);
				$layer_options_html = '';
				foreach ($layer_options as $key => $value) {
					$layer_options_html .= '<input type="hidden" id="ulp_layer_'.$layer_id.'_'.$key.'" name="ulp_layer_'.$layer_id.'_'.$key.'" value="'.htmlspecialchars($value, ENT_QUOTES).'">';
				}
				$return_object['options_html'] = $layer_options_html;
				$return_object['layer_id'] = $layer_id;
				echo json_encode($return_object);
				exit;
				break;
			
			case 'delete-layer':
				if (DEMO_MODE) exit;
				if (isset($_POST['ulp_layer_id'])) $layer_id = intval($_POST['ulp_layer_id']);
				else $layer_id = 0;
				$sql = "UPDATE ".$icdb->prefix."layers SET deleted = '1' WHERE id = '".$layer_id."'";
				$icdb->query($sql);
				exit;
				break;

			case 'copy-layer':
				if (DEMO_MODE) exit;
				if (isset($_POST['ulp_layer_id'])) $layer_id = intval($_POST['ulp_layer_id']);
				else $layer_id = 0;
				$layer_details = $icdb->get_row("SELECT * FROM ".$icdb->prefix."layers WHERE id = '".$layer_id."' AND deleted = '0'");
				if (empty($layer_details)) {
					$return_object = array();
					$return_object['status'] = 'ERROR';
					$return_object['message'] = 'Layer not found!';
					echo json_encode($return_object);
					exit;
				}
				$layer_options = unserialize($layer_details['details']);
				$sql = "INSERT INTO ".$icdb->prefix."layers (
					popup_id, title, content, zindex, details, created, deleted) VALUES (
					'".$layer_details['popup_id']."',
					'".mysql_real_escape_string($layer_details['title'])."',
					'".mysql_real_escape_string($layer_details['content'])."',
					'".mysql_real_escape_string($layer_details['zindex'])."',
					'".mysql_real_escape_string($layer_details['details'])."',
					'".time()."', '0')";
				$icdb->query($sql);
				$layer_id = $icdb->insert_id;
				$return_object = array();
				$return_object['status'] = 'OK';
				$return_object['title'] = htmlspecialchars($layer_options['title'], ENT_QUOTES);
				if (strlen($layer_options['content']) == 0) $content = 'No content...';
				else if (strlen($layer_options['content']) > 192) $content = substr($layer_options['content'], 0, 180).'...';
				else $content = $layer_options['content'];
				$return_object['content'] = htmlspecialchars($content, ENT_QUOTES);
				$layer_options_html = '';
				foreach ($layer_options as $key => $value) {
					$layer_options_html .= '<input type="hidden" id="ulp_layer_'.$layer_id.'_'.$key.'" name="ulp_layer_'.$layer_id.'_'.$key.'" value="'.htmlspecialchars($value, ENT_QUOTES).'">';
				}
				$return_object['options_html'] = $layer_options_html;
				$return_object['layer_id'] = $layer_id;
				echo json_encode($return_object);
				exit;
				break;

			case 'getresponse-campaigns':
				$api_key = trim(stripslashes($_POST['getresponse_api_key']));
				$campaign_id = trim(stripslashes($_POST['getresponse_campaign_id']));
				$html_object = new stdClass();
				$campaigns = getresponse_getcampaigns($api_key);
				if (sizeof($campaigns) > 0) {
					$getresponse_options = '';
					foreach ($campaigns as $key => $value) {
						$getresponse_options .= '<option value="'.$key.'"'.($key == $options['getresponse_campaign_id'] ? ' selected="selected"' : '').'>'.htmlspecialchars($value, ENT_QUOTES).'</option>';
					}
					$html_object->options = $getresponse_options;
				} else {
					$html_object->options = '<option>-- No campaigns found --</option>';
				}
				echo json_encode($html_object);
				exit;
				break;
		
			case 'icontact-lists':
				$appid = trim(stripslashes($_POST['icontact_appid']));
				$apiusername = trim(stripslashes($_POST['icontact_apiusername']));
				$apipassword = trim(stripslashes($_POST['icontact_apipassword']));
				$listid = trim(stripslashes($_POST['icontact_listid']));
				$html_object = new stdClass();
				$lists = icontact_getlists($appid, $apiusername, $apipassword);
				if (sizeof($lists) > 0) {
					$icontact_options = '';
					foreach ($lists as $key => $value) {
						$icontact_options .= '<option value="'.$key.'"'.($key == $listid ? ' selected="selected"' : '').'>'.htmlspecialchars($value, ENT_QUOTES).'</option>';
					}
					$html_object->options = $icontact_options;
				} else {
					$html_object->options = '<option>-- No lists found --</option>';
				}
				echo json_encode($html_object);
				exit;
				break;
				
			case 'delete-subscriber':
				if (DEMO_MODE) {
					$_SESSION['error'] = '<strong>Demo mode.</strong> This operation is disabled.';
					header('Location: admin.php?page=subscribers');
					exit;
				}
				$id = intval($_GET["id"]);
				$subscriber_details = $icdb->get_row("SELECT * FROM ".$icdb->prefix."subscribers WHERE id = '".$id."' AND deleted = '0'");
				if (intval($subscriber_details["id"]) == 0) {
					$_SESSION['error'] = '<strong>Invalid</strong> service call.';
					header('Location: admin.php?page=subscribers');
					exit;
				}
				$sql = "UPDATE ".$icdb->prefix."subscribers SET deleted = '1' WHERE id = '".$id."'";
				if ($icdb->query($sql) !== false) {
					$_SESSION['ok'] = 'Record successfully <strong>removed</strong>.';
					header('Location: admin.php?page=subscribers');
					exit;
				} else {
					$_SESSION['error'] = '<strong>Invalid</strong> service call.';
					header('Location: admin.php?page=subscribers');
					exit;
				}
				exit;
				break;
			
			case 'delete-subscribers':
				if (DEMO_MODE) {
					$_SESSION['error'] = '<strong>Demo mode.</strong> This operation is disabled.';
					header('Location: admin.php?page=subscribers');
					exit;
				}
				$sql = "UPDATE ".$icdb->prefix."subscribers SET deleted = '1' WHERE deleted != '1'";
				if ($icdb->query($sql) !== false) {
					$_SESSION['ok'] = 'All records successfully <strong>removed</strong>.';
					header('Location: admin.php?page=subscribers');
					exit;
				} else {
					$_SESSION['error'] = '<strong>Invalid</strong> service call.';
					header('Location: admin.php?page=subscribers');
					exit;
				}
				exit;
				break;

			case 'export-subscribers':
				if (DEMO_MODE) {
					$_SESSION['error'] = '<strong>Demo mode.</strong> This operation is disabled.';
					header('Location: admin.php?page=subscribers');
					exit;
				}
				$rows = $icdb->get_rows("SELECT t1.*, t2.title AS popup_title FROM ".$icdb->prefix."subscribers t1 LEFT JOIN ".$icdb->prefix."popups t2 ON t2.id = t1.popup_id WHERE t1.deleted = '0' ORDER BY t1.created DESC");
				if (sizeof($rows) > 0) {
					if (strstr($_SERVER["HTTP_USER_AGENT"],"MSIE")) {
						header("Pragma: public");
						header("Expires: 0");
						header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
						header("Content-type: application-download");
						header("Content-Disposition: attachment; filename=\"emails.csv\"");
						header("Content-Transfer-Encoding: binary");
					} else {
						header("Content-type: application-download");
						header("Content-Disposition: attachment; filename=\"emails.csv\"");
					}
					$separator = $options['csv_separator'];
					if ($separator == 'tab') $separator = "\t";
					echo '"Name"'.$separator.'"E-Mail"'.$separator.'"Popup"'.$separator.'"Created"'."\r\n";
					foreach ($rows as $row) {
						echo '"'.str_replace('"', "'", $row["name"]).'"'.$separator.'"'.str_replace('"', "'", $row["email"]).'"'.$separator.'"'.str_replace('"', "'", $row["popup_title"]).'"'.$separator.'"'.date("Y-m-d H:i:s", $row["created"]).'"'."\r\n";
					}
					exit;
	            }
	            header('Location: admin.php?page=subscribers');
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
				if ($login == $options['login'] && md5($password) == $options['password']) {
					$session_id = random_string(16);
					$icdb->query("INSERT INTO ".$icdb->prefix."sessions (ip, session_id, registered, valid_period) VALUES ('".$_SERVER['REMOTE_ADDR']."', '".$session_id."', '".time()."', '900')");
					setcookie('ulp-auth', $session_id, time()+3600*24*180);
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
	<title><?php echo (array_key_exists($page, $pages) ? $pages[$page]['title'] : 'Login').' - '; ?>Layered Popups</title>
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/admin.css" rel="stylesheet">
	<script src="js/jquery-1.10.2.min.js"></script>
	<script src="js/bootstrap-dropdown.js"></script>
	<script src="js/bootstrap-modal.js"></script>
<body>
<!-- Header - begin -->
<div class="navbar navbar-fixed-top navbar-inverse">
	<div class="navbar-inner">
		<div class="container">
			<a class="brand" href="#">Layered Popups</a>
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
		if (!empty($error_message)) $message = '<div class="alert alert-error">'.$error_message.'</div>';
		else if (!empty($ok_message)) $message = '<div class="alert alert-success">'.$ok_message.'</div>';
		else $message = '';
		//$message = '<div class="alert alert-success">Test Message</div>';
		echo '
		<div class="wrap ulp">
			'.$message.'
			<h2>Settings</h2>
			<form class="ulp-popup-form" enctype="multipart/form-data" method="post" style="margin: 0px" action="admin.php">
			<div class="ulp-options" style="width: 100%; position: relative;">
				<h3>OnLoad Settings</h3>
				<table class="ulp_useroptions">
					<tr>
						<th>Popup:</th>
						<td style="vertical-align: middle; line-height: 1.6;">';
		$popups = $icdb->get_rows("SELECT * FROM ".$icdb->prefix."popups WHERE deleted = '0' ORDER BY created ASC");
		$checked = false;
		foreach($popups as $popup) {
			if ($options['onload_popup'] == $popup['str_id']) {
				$checked = true;
				echo '
							<input type="radio" name="ulp_onload_popup" value="'.$popup['str_id'].'" checked="checked"> '.htmlspecialchars($popup['title'], ENT_QUOTES).'<br />';
			} else {
				echo '
							<input type="radio" name="ulp_onload_popup" value="'.$popup['str_id'].'"> '.htmlspecialchars($popup['title'], ENT_QUOTES).'<br />';
			}
		}
		echo '
							<input type="radio" name="ulp_onload_popup" value=""'.(!$checked ? ' checked="checked"' : '').'> None (disabled)
							<br /><em>Select popup to be displayed on page load.</em>
						</td>
					</tr>
					<tr>
						<th>Display mode:</th>
						<td style="line-height: 1.6;">';
		foreach ($display_modes as $key => $value) {
			echo '
							<input type="radio" name="ulp_onload_mode" id="ulp_onload_mode" value="'.$key.'"'.($options['onload_mode'] == $key ? ' checked="checked"' : '').'> '.$value.'<br />';
		}
		echo '
						</td>
					</tr>
					<tr>
						<th>Reset cookie:</th>
						<td>
							<input type="button" class="ulp_button button-secondary" value="Reset cookie" onclick="return ulp_reset_cookie();" >
							<img id="ulp-reset-loading" src="images/loading.gif">
							<br /><em>Click button to reset cookie. Popup will appear for all users. Do this operation if you changed content in popup and want to display it for returning visitors.</em>
						</td>
					</tr>
					<tr>
						<th>Start delay:</th>
						<td style="vertical-align: middle;">
							<input type="text" name="ulp_onload_delay" value="'.htmlspecialchars($options['onload_delay'], ENT_QUOTES).'" class="ic_input_number" placeholder="Delay"> seconds
							<br /><em>Popup appears with this delay after page loaded. Set "0" for immediate start.</em>
						</td>
					</tr>
				</table>
				<h3>AWeber Connection</h3>';
		$account = null;
		if ($options['aweber_access_secret']) {
			if (!class_exists('AWeberAPI')) {
				require_once(dirname(__FILE__).'/aweber_api/aweber_api.php');
			}
			try {
				$aweber = new AWeberAPI($options['aweber_consumer_key'], $options['aweber_consumer_secret']);
				$account = $aweber->getAccount($options['aweber_access_key'], $options['aweber_access_secret']);
			} catch (AWeberException $e) {
				$account = null;
			}
		}
		if (!$account) {
			echo '
				<div id="ulp-aweber-connection">
				<table class="ulp_useroptions">
					<tr>
						<th>Authorization code:</th>
						<td>
							<input type="text" id="ulp_aweber_oauth_id" value="" class="widefat" placeholder="AWeber authorization code">
							<br />Get your authorization code <a target="_blank" href="https://auth.aweber.com/1.0/oauth/authorize_app/'.AWEBER_APPID.'">here</a>.
						</td>
					</tr>
					<tr>
						<th></th>
						<td style="vertical-align: middle;">
							<input type="button" class="ulp_button button-secondary" value="Make Connection" onclick="return ulp_aweber_connect();" >
							<img id="ulp-aweber-loading" src="images/loading.gif">
						</td>
					</tr>
				</table>
				</div>';
		} else {
			echo '
				<div id="ulp-aweber-connection">
				<table class="ulp_useroptions">
					<tr>
						<th>Connected:</th>
						<td>
							<input type="button" class="ulp_button button-secondary" value="Disconnect" onclick="return ulp_aweber_disconnect();" >
							<img id="ulp-aweber-loading" src="images/loading.gif">
							<br /><em>Click the button to disconnect.</em>
						</td>
					</tr>
				</table>
				</div>';
		}
		echo '
				<div id="ulp-aweber-message"></div>
				<h3>Miscellaneous</h3>
				<table class="ulp_useroptions">
					<tr>
						<th>CSV column separator:</th>
						<td>
							<select id="ulp_csv_separator" name="ulp_csv_separator">
								<option value=";"'.($options['csv_separator'] == ';' ? ' selected="selected"' : '').'>Semicolon - ";"</option>
								<option value=","'.($options['csv_separator'] == ',' ? ' selected="selected"' : '').'>Comma - ","</option>
								<option value="tab"'.($options['csv_separator'] == 'tab' ? ' selected="selected"' : '').'>Tab</option>
							</select>
							<br /><em>Please select CSV column separator.</em>
						</td>
					</tr>
					<tr>
						<th>Login:</th>
						<td>
							<input type="text" class="input-large" name="ulp_login" value="'.htmlspecialchars($options['login'], ENT_QUOTES).'" autocomplete="off">
							<br /><em>Please set admin access login.</em>
						</td>
					</tr>
					<tr>
						<th>Password:</th>
						<td>
							<input type="password" class="input-large" name="ulp_password" value="" autocomplete="off">
							<br /><em>Please set admin access password. Leave this field blank if you do not want to change current password.</em>
						</td>
					</tr>
					<tr>
						<th>Confirm password:</th>
						<td>
							<input type="password" class="input-large" name="ulp_confirm_password" value="" autocomplete="off">
							<br /><em>Please confirm admin access password.</em>
						</td>
					</tr>
				</table>
				<hr>
				<div style="text-align: right; margin-bottom: 5px; margin-top: 20px;">
					<input type="hidden" name="action" value="save-settings" />
					<img class="ulp-loading" src="images/loading.gif">
					<input type="submit" class="btn btn-primary ulp-button" name="submit" value="Save Settings" onclick="return ulp_save_settings();">
				</div>
				<div class="ulp-message"></div>
			</div>
			</form>
			<script type="text/javascript">
				function ulp_reset_cookie() {
					jQuery("#ulp-reset-loading").fadeIn(350);
					var data = {action: "reset-cookie"};
					jQuery.post("admin.php", data, function(data) {
						jQuery("#ulp-reset-loading").fadeOut(350);
					});
					return false;
				}
				function ulp_aweber_connect() {
					jQuery("#ulp-aweber-loading").fadeIn(350);
					jQuery("#ulp-aweber-message").slideUp(350);
					var data = {action: "aweber-connect", "aweber-oauth-id": jQuery("#ulp_aweber_oauth_id").val()};
					jQuery.post("admin.php", data, function(return_data) {
						jQuery("#ulp-aweber-loading").fadeOut(350);
						try {
							//alert(return_data);
							var data = jQuery.parseJSON(return_data);
							var status = data.status;
							if (status == "OK") {
								jQuery("#ulp-aweber-connection").slideUp(350, function() {
									jQuery("#ulp-aweber-connection").html(data.html);
									jQuery("#ulp-aweber-connection").slideDown(350);
								});
							} else if (status == "ERROR") {
								jQuery("#ulp-aweber-message").html(data.message);
								jQuery("#ulp-aweber-message").slideDown(350);
							} else {
								jQuery("#ulp-aweber-message").html("Service is not available.");
								jQuery("#ulp-aweber-message").slideDown(350);
							}
						} catch(error) {
							jQuery("#ulp-aweber-message").html("Service is not available.");
							jQuery("#ulp-aweber-message").slideDown(350);
						}
					});
					return false;
				}
				function ulp_aweber_disconnect() {
					jQuery("#ulp-aweber-loading").fadeIn(350);
					jQuery("#ulp-aweber-message").slideUp(350);
					var data = {action: "aweber-disconnect"};
					jQuery.post("admin.php", data, function(return_data) {
						jQuery("#ulp-aweber-loading").fadeOut(350);
						try {
							//alert(return_data);
							var data = jQuery.parseJSON(return_data);
							var status = data.status;
							if (status == "OK") {
								jQuery("#ulp-aweber-connection").slideUp(350, function() {
									jQuery("#ulp-aweber-connection").html(data.html);
									jQuery("#ulp-aweber-connection").slideDown(350);
								});
							} else if (status == "ERROR") {
								jQuery("#ulp-aweber-message").html(data.message);
								jQuery("#ulp-aweber-message").slideDown(350);
							} else {
								jQuery("#ulp-aweber-message").html("Service is not available.");
								jQuery("#ulp-aweber-message").slideDown(350);
							}
						} catch(error) {
							jQuery("#ulp-aweber-message").html("Service is not available.");
							jQuery("#ulp-aweber-message").slideDown(350);
						}
					});
					return false;
				}
				function ulp_save_settings() {
					jQuery(".ulp-popup-form").find(".ulp-loading").fadeIn(350);
					jQuery(".ulp-popup-form").find(".ulp-message").slideUp(350);
					jQuery(".ulp-popup-form").find(".ulp-button").attr("disabled", "disabled");
					jQuery.post("admin.php", 
						jQuery(".ulp-popup-form").serialize(),
						function(return_data) {
							//alert(return_data);
							jQuery(".ulp-popup-form").find(".ulp-loading").fadeOut(350);
							jQuery(".ulp-popup-form").find(".ulp-button").removeAttr("disabled");
							var data;
							try {
								var data = jQuery.parseJSON(return_data);
								var status = data.status;
								if (status == "OK") {
									location.href = data.return_url;
								} else if (status == "ERROR") {
									jQuery(".ulp-popup-form").find(".ulp-message").html(data.message);
									jQuery(".ulp-popup-form").find(".ulp-message").slideDown(350);
								} else {
									jQuery(".ulp-popup-form").find(".ulp-message").html("Service is not available.");
									jQuery(".ulp-popup-form").find(".ulp-message").slideDown(350);
								}
							} catch(error) {
								jQuery(".ulp-popup-form").find(".ulp-message").html("Service is not available.");
								jQuery(".ulp-popup-form").find(".ulp-message").slideDown(350);
							}
						}
					);
					return false;
				}
			</script>
		</div>';
	} else if ($page == 'popups') {
		if (!empty($error_message)) $message = '<div class="alert alert-error">'.$error_message.'</div>';
		else if (!empty($ok_message)) $message = '<div class="alert alert-success">'.$ok_message.'</div>';
		else $message = '';

		if (isset($_GET["s"])) $search_query = trim(stripslashes($_GET["s"]));
		else $search_query = "";
		$tmp = $icdb->get_row("SELECT COUNT(*) AS total FROM ".$icdb->prefix."popups WHERE deleted = '0'".((strlen($search_query) > 0) ? " AND title LIKE '%".addslashes($search_query)."%'" : ""));
		$total = $tmp["total"];
		$totalpages = ceil($total/RECORDS_PER_PAGE);
		if ($totalpages == 0) $totalpages = 1;
		if (isset($_GET["p"])) $page = intval($_GET["p"]);
		else $page = 1;
		if ($page < 1 || $page > $totalpages) $page = 1;
		$switcher = page_switcher("admin.php?page=files".((strlen($search_query) > 0) ? "&s=".rawurlencode($search_query) : ""), $page, $totalpages);

		$sql = "SELECT t1.*, t2.layers FROM ".$icdb->prefix."popups t1 LEFT JOIN (SELECT COUNT(*) AS layers, popup_id FROM ".$icdb->prefix."layers WHERE deleted = '0' GROUP BY popup_id) t2 ON t2.popup_id = t1.id WHERE t1.deleted = '0'".((strlen($search_query) > 0) ? " AND t1.title LIKE '%".addslashes($search_query)."%'" : "")." ORDER BY t1.created DESC LIMIT ".(($page-1)*RECORDS_PER_PAGE).", ".RECORDS_PER_PAGE;
		$rows = $icdb->get_rows($sql);
		
		echo '
			<div class="wrap ulp">
				'.$message.'
				<h2>Popups</h2><br />
				<form action="admin.php" method="get" style="margin-bottom: 10px;">
				<input type="hidden" name="page" value="popups" />
				Search: <input type="text" name="s" value="'.htmlspecialchars($search_query, ENT_QUOTES).'">
				<input type="submit" class="btn btn-secondary ulp-button" value="Search" />
				'.((strlen($search_query) > 0) ? '<input type="button" class="btn btn-secondary ulp-button" value="Reset search results" onclick="window.location.href=\'admin.php?page=popups\';" />' : '').'
				</form>
				<div class="ulp_buttons"><a class="btn btn-primary ulp-button" href="admin.php?page=create">Create New Popup</a></div>
				<div class="ulp_pageswitcher">'.$switcher.'</div>
				<table class="ulp_records">
				<tr>
					<th>Title</th>
					<th style="width: 160px;">ID</th>
					<th style="width: 80px;">Layers</th>
					<th style="width: 130px;"></th>
				</tr>';
		if (sizeof($rows) > 0) {
			foreach ($rows as $row) {
				$bg_color = "";
				if ($row['blocked'] == 1) $bg_color = "#FFF8F8";
				echo '
				<tr'.(!empty($bg_color) ? ' style="background: '.$bg_color.'"' : '').'>
					<td>'.htmlspecialchars($row['title'], ENT_QUOTES).'</td>
					<td><input type="text" value="'.$row['str_id'].'" readonly="readonly" style="width: 100%;" onclick="this.focus();this.select();"></td>
					<td style="text-align: right;">'.intval($row['layers']).'</td>
					<td style="text-align: center;">
						<a target="ulp-preview" href="preview.html?ulp='.$row['str_id'].'" title="Preview popup"><img src="images/preview.png" alt="Preview popup" border="0"></a>
						<a href="admin.php?page=create&id='.$row['id'].'" title="Edit popup details"><img src="images/edit.png" alt="Edit popup details" border="0"></a>
						<a href="admin.php?action=copy&id='.$row['id'].'" title="Duplicate popup" onclick="return ulp_submitOperation();"><img src="images/copy.png" alt="Duplicate popup" border="0"></a>
						<a href="admin.php?action=export&id='.$row['id'].'" title="Export popup details"><img src="images/export.png" alt="Export popup details" border="0"></a>
						'.($row['blocked'] == 1 ? '<a href="admin.php?action=unblock&id='.$row['id'].'" title="Unblock popup"><img src="images/unblock.png" alt="Unblock popup" border="0"></a>' : '<a href="admin.php?action=block&id='.$row['id'].'" title="Block popup"><img src="images/block.png" alt="Block popup" border="0"></a>').'
						<a href="admin.php?action=delete&id='.$row['id'].'" title="Delete popup" onclick="return ulp_submitOperation();"><img src="images/delete.png" alt="Delete popup" border="0"></a>
					</td>
				</tr>';
			}
		} else {
			echo '
				<tr><td colspan="4" style="padding: 20px; text-align: center;">'.((strlen($search_query) > 0) ? 'No results found for "<strong>'.htmlspecialchars($search_query, ENT_QUOTES).'</strong>"' : 'List is empty.').'</td></tr>';
		}
		echo '
				</table>
				<div class="ulp_buttons">
					<form id="ulp-import-form" enctype="multipart/form-data" method="post" action="admin.php?action=import">
						<div style="position: relative; padding: 10px 20px;">
							<a class="ulp-import-form-close" href="#" onclick="jQuery(\'#ulp-import-form\').fadeOut(350); return false;">×</a>
							<input type="file" name="ulp-file" onchange="jQuery(\'#ulp-import-form\').submit();">
						</div>
					</form>
					<a class="btn btn-primary ulp-button" href="#" onclick="jQuery(\'#ulp-import-form\').fadeIn(350); return false;">Import Popup</a>
					<a class="btn btn-primary ulp-button" href="admin.php?page=create">Create New Popup</a>
				</div>
				<div class="ulp_pageswitcher">'.$switcher.'</div>
				<div class="ulp_legend">
					<strong>Legend:</strong>
					<p><img src="images/preview.png" alt="Preview popup" border="0"> Preview popup</p>
					<p><img src="images/copy.png" alt="Duplicate popup" border="0"> Duplicate popup</p>
					<p><img src="images/export.png" alt="Export popup details" border="0"> Export popup details</p>
					<p><img src="images/edit.png" alt="Edit popup details" border="0"> Edit popup details</p>
					<p><img src="images/block.png" alt="Block popup" border="0"> Block popup</p>
					<p><img src="images/unblock.png" alt="Unblock popup" border="0"> Unblock popup</p>
					<p><img src="images/delete.png" alt="Delete popup" border="0"> Delete popup</p>
				</div>
			</div>
			<script type="text/javascript">
				function ulp_submitOperation() {
					var answer = confirm("Do you really want to continue?")
					if (answer) return true;
					else return false;
				}
			</script>';
	} else if ($page == 'create') {
		if (!empty($error_message)) $message = '<div class="alert alert-error">'.$error_message.'</div>';
		else if (!empty($ok_message)) $message = '<div class="alert alert-success">'.$ok_message.'</div>';
		else $message = '';
		
		if (isset($_GET["id"]) && !empty($_GET["id"])) {
			$id = intval($_GET["id"]);
			$popup_details = $icdb->get_row("SELECT * FROM ".$icdb->prefix."popups WHERE id = '".$id."' AND deleted = '0'");
		}
		if (!empty($popup_details)) {
			$id = $popup_details['id'];
			$popup_options = unserialize($popup_details['options']);
			$popup_options = array_merge($default_popup_options, $popup_options);
		} else {
			$str_id = random_string(16);
			$sql = "INSERT INTO ".$icdb->prefix."popups (str_id, title, width, height, options, created, blocked, deleted) VALUES ('".$str_id."', '', '640', '400', '', '".time()."', '0', '1')";
			$icdb->query($sql);
			$id = $icdb->insert_id;
			$popup_options = $default_popup_options;
		}
		
		include_once(dirname(__FILE__).'/webfonts.php');
		$webfonts_array = json_decode($fonts, true);
		
		echo '
		<div class="wrap ulp">
			'.$message.'
			<h2>'.(!empty($popup_details) ? 'Edit Popup' : 'Create Popup').'</h2>
			<form class="ulp-popup-form" enctype="multipart/form-data" method="post" style="margin: 0px" action="admin.php">
			<div class="ulp-options" style="width: 100%; position: relative;">
				<h3>General Parameters</h3>
				<table class="ulp_useroptions">
					<tr>
						<th>Title:</th>
						<td>
							<input type="text" name="ulp_title" value="'.(!empty($popup_details['title']) ? htmlspecialchars($popup_details['title'], ENT_QUOTES) : htmlspecialchars($default_popup_options['title'], ENT_QUOTES)).'" class="widefat" placeholder="Enter the popup title...">
							<br /><em>Enter the popup title. It is used for your reference.</em>
						</td>
					</tr>
					<tr>
						<th>Basic size:</th>
						<td style="vertical-align: middle;">
							<input type="text" name="ulp_width" value="'.(!empty($popup_details['width']) ? htmlspecialchars($popup_details['width'], ENT_QUOTES) : htmlspecialchars($default_popup_options['width'], ENT_QUOTES)).'" class="ic_input_number" placeholder="Width" onblur="ulp_build_preview();" onchange="ulp_build_preview();"> x
							<input type="text" name="ulp_height" value="'.(!empty($popup_details['height']) ? htmlspecialchars($popup_details['height'], ENT_QUOTES) : htmlspecialchars($default_popup_options['height'], ENT_QUOTES)).'" class="ic_input_number" placeholder="Height" onblur="ulp_build_preview();" onchange="ulp_build_preview();"> pixels
							<br /><em>Enter the size of basic frame. This frame will be centered and all layers will be placed relative to the top-left corner of this frame.</em>
						</td>
					</tr>
					<tr>
						<th>Overlay color:</th>
						<td>
							<input type="text" class="ulp-color ic_input_number" name="ulp_overlay_color" value="'.(!empty($popup_options['overlay_color']) ? htmlspecialchars($popup_options['overlay_color'], ENT_QUOTES) : htmlspecialchars($default_popup_options['overlay_color'], ENT_QUOTES)).'" placeholder="">
							<br /><em>Set the overlay color.</em>
						</td>
					</tr>
					<tr>
						<th>Overlay opacity:</th>
						<td>
							<input type="text" name="ulp_overlay_opacity" value="'.(!empty($popup_options['overlay_opacity']) ? htmlspecialchars($popup_options['overlay_opacity'], ENT_QUOTES) : htmlspecialchars($default_popup_options['overlay_opacity'], ENT_QUOTES)).'" class="ic_input_number" placeholder="Opacity">
							<br /><em>Set the overlay opacity. The value must be in a range [0...1].</em>
						</td>
					</tr>
					<tr>
						<th>Extended closing:</th>
						<td>
							<input type="checkbox" name="ulp_enable_close" '.($popup_options['enable_close'] == "on" ? 'checked="checked"' : '').'"> Close popup window on ESC-button click and overlay click
							<br /><em>Please tick checkbox to enable popup closing on ESC-button click and overlay click.</em>
						</td>
					</tr>
				</table>
				<h3>Layers</h3>
				<div id="ulp-layers-data">';
		$sql = "SELECT * FROM ".$icdb->prefix."layers WHERE deleted = '0' AND popup_id = '".$id."' ORDER BY created ASC";
		$layers = $icdb->get_rows($sql);
		if (sizeof($layers) > 0) {
			foreach ($layers as $layer) {
				$layer_options = unserialize($layer['details']);
				if (strlen($layer_options['content']) == 0) $content = 'No content...';
				else if (strlen($layer_options['content']) > 192) $content = substr($layer_options['content'], 0, 180).'...';
				else $content = $layer_options['content'];
				$layer_options_html = '';
				foreach ($layer_options as $key => $value) {
					$layer_options_html .= '<input type="hidden" id="ulp_layer_'.$layer['id'].'_'.$key.'" name="ulp_layer_'.$layer['id'].'_'.$key.'" value="'.htmlspecialchars($value).'">';
				}
				echo '
					<div class="ulp-layers-item" id="ulp-layer-'.$layer['id'].'">
						<div class="ulp-layers-item-cell ulp-layers-item-cell-info">
							<h4>'.htmlspecialchars($layer_options['title'], ENT_QUOTES).'</h4>
							<p>'.htmlspecialchars($content, ENT_QUOTES).'</p>
						</div>
						<div class="ulp-layers-item-cell" style="width: 70px;">
							<a href="#" title="Edit layer details" onclick="return ulp_edit_layer(this);"><img src="images/edit.png" alt="Edit layer details" border="0"></a>
							<a href="#" title="Duplicate layer" onclick="return ulp_copy_layer(this);"><img src="images/copy.png" alt="Duplicate details" border="0"></a>
							<a href="#" title="Delete layer" onclick="return ulp_delete_layer(this);"><img src="images/delete.png" alt="Delete layer" border="0"></a>
						</div>
						'.$layer_options_html.'
					</div>
					<div class="ulp-edit-layer" id="ulp-edit-layer-'.$layer['id'].'"></div>';
			}
		}
		echo '									
				</div>
				<div id="ulp-new-layer"></div>
				<input type="button" class="btn btn-secondary ulp-button" onclick="return ulp_add_layer();" value="Add New Layer">
				<h3>Live Preview</h3>
				<div class="ulp-preview-container">
					<div class="ulp-preview-window">
						<div class="ulp-preview-content">
						</div>
					</div>
				</div>
				<h3>Subscription Form Parameters</h3>
				<p>The parameters below are used for subscription form only. Please read FAQ section about adding subscription form into layers.</p>
				<table class="ulp_useroptions">
					<tr>
						<th>"Name" field placeholder:</th>
						<td>
							<input type="text" id="ulp_name_placeholder" name="ulp_name_placeholder" value="'.htmlspecialchars($popup_options['name_placeholder'], ENT_QUOTES).'" class="widefat">
							<br /><em>Enter the placeholder for "Name" input field.</em>
						</td>
					</tr>
					<tr>
						<th>"E-mail" field placeholder:</th>
						<td>
							<input type="text" id="ulp_email_placeholder" name="ulp_email_placeholder" value="'.htmlspecialchars($popup_options['email_placeholder'], ENT_QUOTES).'" class="widefat">
							<br /><em>Enter the placeholder for "E-mail" input field.</em>
						</td>
					</tr>
					<tr>
						<th>"Subscribe" button label:</th>
						<td>
							<input type="text" id="ulp_button_label" name="ulp_button_label" value="'.htmlspecialchars($popup_options['button_label'], ENT_QUOTES).'" class="widefat">
							<br /><em>Enter the label for "Subscribe" button.</em>
						</td>
					</tr>
					<tr>
						<th>Input field border color:</th>
						<td>
							<input type="text" class="ulp-color ic_input_number" name="ulp_input_border_color" value="'.htmlspecialchars($popup_options['input_border_color'], ENT_QUOTES).'" placeholder="">
							<br /><em>Set the border color of "Name" and "E-mail" input fields.</em>
						</td>
					</tr>
					<tr>
						<th>Input field background color:</th>
						<td>
							<input type="text" class="ulp-color ic_input_number" name="ulp_input_background_color" value="'.htmlspecialchars($popup_options['input_background_color'], ENT_QUOTES).'" placeholder="">
							<br /><em>Set the background color of "Name" and "E-mail" input fields.</em>
						</td>
					</tr>
					<tr>
						<th>Input field background opacity:</th>
						<td>
							<input type="text" class="ic_input_number" name="ulp_input_background_opacity" value="'.htmlspecialchars($popup_options['input_background_opacity'], ENT_QUOTES).'" placeholder="[0...1]">
							<br /><em>Set the background opacity of "Name" and "E-mail" input fields. The value must be in a range [0...1].</em>
						</td>
					</tr>
					<tr>
						<th>"Subscribe" button color:</th>
						<td>
							<input type="text" class="ulp-color ic_input_number" name="ulp_button_color" value="'.htmlspecialchars($popup_options['button_color'], ENT_QUOTES).'" placeholder="">
							<br /><em>Set the "Subscribe" button color.</em>
						</td>
					</tr>
					<tr>
						<th>Redirect URL:</th>
						<td>
							<input type="text" id="ulp_return_url" name="ulp_return_url" value="'.htmlspecialchars($popup_options['return_url'], ENT_QUOTES).'" class="widefat">
							<br /><em>Enter the redirect URL. After successfull subscribing user is redirected to this URL. Leave blank to stay on the same page.</em>
						</td>
					</tr>
				</table>
				<h3>Autoresponder Parameters</h3>
				<p>The parameters below are used for subscription form only. Please read FAQ section about adding subscription form into layers.</p>
				<table class="ulp_useroptions">
					<tr>
						<th>Enable MailChimp:</th>
						<td>
							<input type="checkbox" id="ulp_mailchimp_enable" name="ulp_mailchimp_enable" '.($popup_options['mailchimp_enable'] == "on" ? 'checked="checked"' : '').'"> Submit contact details to MailChimp
							<br /><em>Please tick checkbox if you want to submit contact details to MailChimp.</em>
						</td>
					</tr>
					<tr>
						<th>MailChimp API Key:</th>
						<td>
							<input type="text" id="ulp_mailchimp_api_key" name="ulp_mailchimp_api_key" value="'.htmlspecialchars($popup_options['mailchimp_api_key'], ENT_QUOTES).'" class="widefat">
							<br /><em>Enter your MailChimp API Key. You can get it <a href="https://admin.mailchimp.com/account/api-key-popup" target="_blank">here</a>.</em>
						</td>
					</tr>
					<tr>
						<th>List ID:</th>
						<td>
							<input type="text" id="ulp_mailchimp_list_id" name="ulp_mailchimp_list_id" value="'.htmlspecialchars($popup_options['mailchimp_list_id'], ENT_QUOTES).'" class="widefat">
							<br /><em>Enter your List ID. You can get it <a href="https://admin.mailchimp.com/lists/" target="_blank">here</a> (click <strong>Settings</strong>).</em>
						</td>
					</tr>
					<tr>
						<th>Double opt-in:</th>
						<td>
							<input type="checkbox" id="ulp_mailchimp_double" name="ulp_mailchimp_double" '.($popup_options['mailchimp_double'] == "on" ? 'checked="checked"' : '').'"> Ask users to confirm their subscription
							<br /><em>Control whether a double opt-in confirmation message is sent.</em>
						</td>
					</tr>
					<tr>
						<th>Send Welcome:</th>
						<td>
							<input type="checkbox" id="ulp_mailchimp_welcome" name="ulp_mailchimp_welcome" '.($popup_options['mailchimp_welcome'] == "on" ? 'checked="checked"' : '').'"> Send Lists Welcome message
							<br /><em>If your <strong>Double opt-in</strong> is disabled and this is enabled, MailChimp will send your lists Welcome Email if this subscribe succeeds. If <strong>Double opt-in</strong> is enabled, this has no effect.</em>
						</td>
					</tr>
					<tr><td colspan="2"><hr></td></tr>
					<tr>
						<th>Enable iContact:</th>
						<td>
							<input type="checkbox" id="ulp_icontact_enable" name="ulp_icontact_enable" '.($popup_options['icontact_enable'] == "on" ? 'checked="checked"' : '').'"> Submit contact details to iContact
							<br /><em>Please tick checkbox if you want to submit contact details to iContact.</em>
						</td>
					</tr>
					<tr>
						<th>AppID:</th>
						<td>
							<input type="text" id="ulp_icontact_appid" name="ulp_icontact_appid" value="'.htmlspecialchars($popup_options['icontact_appid'], ENT_QUOTES).'" class="widefat" onblur="icontact_loadlist();">
							<br /><em>Obtained when you <a href="http://developer.icontact.com/documentation/register-your-app/" target="_blank">Register the API application</a>. This identifier is used to uniquely identify your application.</em>
						</td>
					</tr>
					<tr>
						<th>API Username:</th>
						<td>
							<input type="text" id="ulp_icontact_apiusername" name="ulp_icontact_apiusername" value="'.htmlspecialchars($popup_options['icontact_apiusername'], ENT_QUOTES).'" class="widefat" onblur="icontact_loadlist();">
							<br /><em>The iContact username for logging into your iContact account.</em>
						</td>
					</tr>
					<tr>
						<th>API Password:</th>
						<td>
							<input type="text" id="ulp_icontact_apipassword" name="ulp_icontact_apipassword" value="'.htmlspecialchars($popup_options['icontact_apipassword'], ENT_QUOTES).'" class="widefat" onblur="icontact_loadlist();">
							<br /><em>The API application password set when the application was registered. This API password is used as input when your application authenticates to the API. This password is not the same as the password you use to log in to iContact.</em>
						</td>
					</tr>
					<tr>
						<th>List ID:</th>
						<td>
							<select id="ulp_icontact_listid" name="ulp_icontact_listid" class="ic_input_m">
								<option>-- Select List --</option>
							</select>
							<br /><em>Select your List ID.</em>
						</td>
					</tr>
					<tr><td colspan="2"><hr></td></tr>
					<tr>
						<th>Enable GetResponse:</th>
						<td>
							<input type="checkbox" id="ulp_getresponse_enable" name="ulp_getresponse_enable" '.($popup_options['getresponse_enable'] == "on" ? 'checked="checked"' : '').'"> Submit contact details to GetResponse
							<br /><em>Please tick checkbox if you want to submit contact details to GetResponse.</em>
						</td>
					</tr>
					<tr>
						<th>API Key:</th>
						<td>
							<input type="text" id="ulp_getresponse_api_key" name="ulp_getresponse_api_key" value="'.htmlspecialchars($popup_options['getresponse_api_key'], ENT_QUOTES).'" class="widefat" onblur="getresponse_loadlist();">
							<br /><em>Enter your GetResponse API Key. You can get your API Key <a href="https://app.getresponse.com/my_api_key.html" target="_blank">here</a>.</em>
						</td>
					</tr>
					<tr>
						<th>Campaign ID:</th>
						<td>
							<select id="ulp_getresponse_campaign_id" name="ulp_getresponse_campaign_id" class="ic_input_m">
								<option>-- Select Campaign --</option>
							</select>
							<br /><em>Select your Campaign ID.</em>
						</td>
					</tr>
					<tr><td colspan="2"><hr></td></tr>
					<tr>
						<th>Enable Campaign Monitor:</th>
						<td>
							<input type="checkbox" id="ulp_campaignmonitor_enable" name="ulp_campaignmonitor_enable" '.($popup_options['campaignmonitor_enable'] == "on" ? 'checked="checked"' : '').'"> Submit contact details to Campaign Monitor
							<br /><em>Please tick checkbox if you want to submit contact details to Campaign Monitor.</em>
						</td>
					</tr>
					<tr>
						<th>API Key:</th>
						<td>
							<input type="text" id="ulp_campaignmonitor_api_key" name="ulp_campaignmonitor_api_key" value="'.htmlspecialchars($popup_options['campaignmonitor_api_key'], ENT_QUOTES).'" class="widefat">
							<br /><em>Enter your Campaign Monitor API Key. You can get your API Key from the Account Settings page when logged into your Campaign Monitor account.</em>
						</td>
					</tr>
					<tr>
						<th>List ID:</th>
						<td>
							<input type="text" id="ulp_campaignmonitor_list_id" name="ulp_campaignmonitor_list_id" value="'.htmlspecialchars($popup_options['campaignmonitor_list_id'], ENT_QUOTES).'" class="widefat">
							<br /><em>Enter your List ID. You can get List ID from the list editor page when logged into your Campaign Monitor account.</em>
						</td>
					</tr>
					<tr><td colspan="2"><hr></td></tr>';
		$account = null;
		if ($options['aweber_access_secret']) {
			if (!class_exists('AWeberAPI')) {
				require_once(dirname(__FILE__).'/aweber_api/aweber_api.php');
			}
			try {
				$aweber = new AWeberAPI($options['aweber_consumer_key'], $options['aweber_consumer_secret']);
				$account = $aweber->getAccount($options['aweber_access_key'], $options['aweber_access_secret']);
			} catch (AWeberException $e) {
				$account = null;
			}
		}
		if (!$account) {
			echo '
					<tr>
						<th>Enable AWeber:</th>
						<td>Please connect your AWeber account on <a target="_blank" href="admin.php?page=settings">Settings</a> page.</td>
					</tr>';
		} else {
			$lists = $account->lists;
            if (empty($lists)) {
				echo '
					<tr>
						<th>Enable AWeber:</th>
						<td>This AWeber account does not currently have any lists.</td>
					</tr>';
			} else {
				echo '
					<tr>
						<th>Enable AWeber:</th>
						<td>
							<input type="checkbox" id="ulp_aweber_enable" name="ulp_aweber_enable" '.($popup_options['aweber_enable'] == "on" ? 'checked="checked"' : '').'"> Submit contact details to AWeber
							<br /><em>Please tick checkbox if you want to submit contact details to AWeber.</em>
						</td>
					</tr>
					<tr>
						<th>List ID:</th>
						<td>
							<select name="ulp_aweber_listid" class="ic_input_m">';
				foreach ($lists as $list) {
					echo '
								<option value="'.$list->id.'"'.($list->id == $popup_options['aweber_listid'] ? ' selected="selected"' : '').'>'.$list->name.'</option>';
				}
				echo '
							</select>
							<br /><em>Select your List ID.</em>
						</td>
					</tr>';
			}
		}
		echo '			
				</table>
				<hr>
				<div style="text-align: right; margin-bottom: 5px; margin-top: 20px;">
					<input type="hidden" name="action" value="save-popup" />
					<input type="hidden" name="ulp_id" value="'.$id.'" />
					<img class="ulp-loading" src="images/loading.gif">
					<input type="submit" class="btn btn-primary ulp-button" name="submit" value="Save Popup Details" onclick="return ulp_save_popup();">
				</div>
				<div class="ulp-message"></div>
				<div id="ulp-overlay"></div>
			</div>
			</form>
			<script type="text/javascript">
				var ulp_local_fonts = new Array("'.strtolower(implode('","', $local_fonts)).'");
				var ulp_active_layer = -1;
				var ulp_default_layer_options = {';
		foreach ($default_layer_options as $key => $value) {
			echo '
					"'.$key.'" : "'.htmlspecialchars($value).'",';
		}
		echo '
					"a" : ""
				};
				var active_icontact_appid = "";
				var active_icontact_apiusername = "";
				var active_icontact_apipassword = "";
				function icontact_loadlist() {
					if (active_icontact_appid != jQuery("#ulp_icontact_appid").val() || 
						active_icontact_apiusername != jQuery("#ulp_icontact_apiusername").val() ||
						active_icontact_apipassword != jQuery("#ulp_icontact_apipassword").val()) {
						jQuery("#ulp_icontact_listid").html("<option>-- Loading Lists --</option>");
						jQuery("#ulp_icontact_listid").attr("disabled", "disabled");
						jQuery.post("admin.php?action=icontact-lists", {
								"icontact_appid": jQuery("#ulp_icontact_appid").val(),
								"icontact_apiusername": jQuery("#ulp_icontact_apiusername").val(),
								"icontact_apipassword": jQuery("#ulp_icontact_apipassword").val(),
								"icontact_listid": "'.htmlspecialchars($popup_options['icontact_listid'], ENT_QUOTES).'"
							},
							function(return_data) {
								try {
									data = jQuery.parseJSON(return_data);
									if (data) {
										jQuery("#ulp_icontact_listid").html(data.options);
										jQuery("#ulp_icontact_listid").removeAttr("disabled");
										active_icontact_appid = jQuery("#ulp_icontact_appid").val();
										active_icontact_apiusername = jQuery("#ulp_icontact_apiusername").val();
										active_icontact_apipassword = jQuery("#ulp_icontact_apipassword").val();
									} else jQuery("#ulp_icontact_listid").html("<option>-- Can not get Lists --</option>");
								} catch(e) {
									jQuery("#ulp_icontact_listid").html("<option>-- Can not get Lists --</option>");
								}
							}
						);
					}
				}
				var active_getresponse_api_key = "";
				function getresponse_loadlist() {
					if (active_getresponse_api_key != jQuery("#ulp_getresponse_api_key").val()) {
						jQuery("#ulp_getresponse_campaign_id").html("<option>-- Loading Campaigns --</option>");
						jQuery("#ulp_getresponse_campaign_id").attr("disabled", "disabled");
						jQuery.post("admin.php?action=getresponse-campaigns", {
								"getresponse_api_key": jQuery("#ulp_getresponse_api_key").val(),
								"getresponse_campaign_id": "'.htmlspecialchars($popup_options['getresponse_campaign_id'], ENT_QUOTES).'"
							},
							function(return_data) {
								try {
									data = jQuery.parseJSON(return_data);
									if (data) {
										jQuery("#ulp_getresponse_campaign_id").html(data.options);
										jQuery("#ulp_getresponse_campaign_id").removeAttr("disabled");
										active_getresponse_api_key = jQuery("#ulp_getresponse_api_key").val();
									} else jQuery("#ulp_getresponse_campaign_id").html("<option>-- Can not get Campaigns --</option>");
								} catch(e) {
									jQuery("#ulp_getresponse_campaign_id").html("<option>-- Can not get Campaigns --</option>");
								}
							}
						);
					}
				}
				icontact_loadlist();
				getresponse_loadlist();
				function ulp_save_popup() {
					jQuery(".ulp-popup-form").find(".ulp-loading").fadeIn(350);
					jQuery(".ulp-popup-form").find(".ulp-message").slideUp(350);
					jQuery(".ulp-popup-form").find(".ulp-button").attr("disabled", "disabled");
					jQuery.post("admin.php", 
						jQuery(".ulp-popup-form").serialize(),
						function(return_data) {
							//alert(return_data);
							jQuery(".ulp-popup-form").find(".ulp-loading").fadeOut(350);
							jQuery(".ulp-popup-form").find(".ulp-button").removeAttr("disabled");
							var data;
							try {
								var data = jQuery.parseJSON(return_data);
								var status = data.status;
								if (status == "OK") {
									location.href = data.return_url;
								} else if (status == "ERROR") {
									jQuery(".ulp-popup-form").find(".ulp-message").html(data.message);
									jQuery(".ulp-popup-form").find(".ulp-message").slideDown(350);
								} else {
									jQuery(".ulp-popup-form").find(".ulp-message").html("Service is not available.");
									jQuery(".ulp-popup-form").find(".ulp-message").slideDown(350);
								}
							} catch(error) {
								jQuery(".ulp-popup-form").find(".ulp-message").html("Service is not available.");
								jQuery(".ulp-popup-form").find(".ulp-message").slideDown(350);
							}
						}
					);
					return false;
				}
				function ulp_add_layer() {
					jQuery("#ulp-overlay").fadeIn(350);
					jQuery("#ulp-new-layer").append(jQuery(".ulp-layer-options"));
					jQuery.each(ulp_default_layer_options, function(key, value) {
						jQuery("[name=\'ulp_layer_"+key+"\']").val(value);
					});
					jQuery("[name=\'ulp_layer_id\']").val("0");
					ulp_active_layer = 0;
					jQuery("#ulp-new-layer").slideDown(350);
					return false;
				}
				function ulp_edit_layer(object) {
					var layer_item_id = jQuery(object).parentsUntil(".ulp-layers-item").parent().attr("id");
					layer_item_id = layer_item_id.replace("ulp-layer-", "");
					jQuery("#ulp-overlay").fadeIn(350);
					jQuery("#ulp-edit-layer-"+layer_item_id).append(jQuery(".ulp-layer-options"));
					jQuery.each(ulp_default_layer_options, function(key, value) {
						jQuery("[name=\'ulp_layer_"+key+"\']").val(jQuery("[name=\'ulp_layer_"+layer_item_id+"_"+key+"\']").val());
					});
					jQuery("[name=\'ulp_layer_id\']").val(layer_item_id);
					ulp_active_layer = layer_item_id;
					jQuery("#ulp-preview-layer-"+layer_item_id).addClass("ulp-preview-layer-active");
					jQuery("#ulp-edit-layer-"+layer_item_id).slideDown(350);
					return false;
				}
				function ulp_delete_layer(object) {
					'.(DEMO_MODE ? 'alert("Operation disabled in demo mode."); return;' : '').'
					var answer = confirm("Do you really want to remove this layer?")
					if (answer) {
						var layer_item_id = jQuery(object).parentsUntil(".ulp-layers-item").parent().attr("id");
						layer_item_id = layer_item_id.replace("ulp-layer-", "");
						jQuery("#ulp-edit-layer-"+layer_item_id).remove();
						jQuery("#ulp-layer-"+layer_item_id).fadeOut(350, function() {
							jQuery("#ulp-layer-"+layer_item_id).remove();
							jQuery.post("admin.php", 
								"action=delete-layer&ulp_layer_id="+layer_item_id,
								function(return_data) {
									ulp_build_preview();
								}
							);
						});
					}
					return false;
				}
				function ulp_copy_layer(object) {
					'.(DEMO_MODE ? 'alert("Operation disabled in demo mode."); return;' : '').'
					var answer = confirm("Do you really want to duplicate this layer?")
					if (answer) {
						var layer_item_id = jQuery(object).parentsUntil(".ulp-layers-item").parent().attr("id");
						layer_item_id = layer_item_id.replace("ulp-layer-", "");
						jQuery.post("admin.php", 
							"action=copy-layer&ulp_layer_id="+layer_item_id,
							function(return_data) {
								var data = jQuery.parseJSON(return_data);
								var status = data.status;
								if (status == "OK") {
									jQuery("#ulp-layers-data").append("<div class=\'ulp-layers-item\' id=\'ulp-layer-"+data.layer_id+"\' style=\'display: none;\'></div><div class=\'ulp-edit-layer\' id=\'ulp-edit-layer-"+data.layer_id+"\'></div>");
									jQuery("#ulp-layer-"+data.layer_id).html(jQuery("#ulp-layers-item-container").html());
									jQuery("#ulp-layer-"+data.layer_id).find("h4").html(data.title);
									jQuery("#ulp-layer-"+data.layer_id).find("p").html(data.content);
									jQuery("#ulp-layer-"+data.layer_id).append(data.options_html);
									jQuery("#ulp-layer-"+data.layer_id).slideDown(350);
									ulp_build_preview();
								}
							}
						);
					}
					return false;
				}
				function ulp_cancel_layer(object) {
					jQuery("#ulp-overlay").fadeOut(350);
					var container = jQuery(object).parentsUntil(".ulp-layer-options").parent().parent();
					jQuery("#"+jQuery(container).attr("id")).slideUp(350, function() {
						jQuery("#ulp-layer-options-container").append(jQuery(".ulp-layer-options"));
						jQuery(".ulp-preview-layer-active").removeClass(".ulp-preview-layer-active");
						ulp_active_layer = -1;
						ulp_build_preview();
					});
					return false;
				}
				function ulp_save_layer() {
					jQuery(".ulp-layer-options").find(".ulp-loading").fadeIn(350);
					jQuery(".ulp-layer-options").find(".ulp-message").slideUp(350);
					jQuery(".ulp-layer-options").find(".ulp-button").attr("disabled", "disabled");
					jQuery.post("admin.php", 
						jQuery(".ulp-layer-options input, .ulp-layer-options select, .ulp-layer-options textarea").serialize(),
						function(return_data) {
							//alert(return_data);
							jQuery(".ulp-layer-options").find(".ulp-loading").fadeOut(350);
							jQuery(".ulp-layer-options").find(".ulp-button").removeAttr("disabled");
							var data;
							try {
								var data = jQuery.parseJSON(return_data);
								var status = data.status;
								if (status == "OK") {
									jQuery("#ulp-overlay").fadeOut(350);
									if(jQuery("#ulp-layers-data").find("#ulp-layer-"+data.layer_id).length == 0) {
										jQuery("#ulp-new-layer").slideUp(350, function() {
											jQuery("#ulp-layer-options-container").append(jQuery(".ulp-layer-options"));
										});
										jQuery("#ulp-layers-data").append("<div class=\'ulp-layers-item\' id=\'ulp-layer-"+data.layer_id+"\' style=\'display: none;\'></div><div class=\'ulp-edit-layer\' id=\'ulp-edit-layer-"+data.layer_id+"\'></div>");
										jQuery("#ulp-layer-"+data.layer_id).html(jQuery("#ulp-layers-item-container").html());
										jQuery("#ulp-layer-"+data.layer_id).find("h4").html(data.title);
										jQuery("#ulp-layer-"+data.layer_id).find("p").html(data.content);
										jQuery("#ulp-layer-"+data.layer_id).append(data.options_html);
										jQuery("#ulp-layer-"+data.layer_id).slideDown(350);
										ulp_active_layer = -1;
										jQuery(".ulp-preview-layer-active").removeClass(".ulp-preview-layer-active");
										ulp_build_preview();
									} else {
										jQuery("#ulp-edit-layer-"+data.layer_id).slideUp(350, function() {
											jQuery("#ulp-layer-options-container").append(jQuery(".ulp-layer-options"));
										});
										jQuery("#ulp-layer-"+data.layer_id).fadeOut(350, function() {
											jQuery("#ulp-layer-"+data.layer_id).html(jQuery("#ulp-layers-item-container").html());
											jQuery("#ulp-layer-"+data.layer_id).find("h4").html(data.title);
											jQuery("#ulp-layer-"+data.layer_id).find("p").html(data.content);
											jQuery("#ulp-layer-"+data.layer_id).append(data.options_html);
											jQuery("#ulp-layer-"+data.layer_id).fadeIn(350);
											ulp_active_layer = -1;
											jQuery(".ulp-preview-layer-active").removeClass(".ulp-preview-layer-active");
											ulp_build_preview();
										});
									}
								} else if (status == "ERROR") {
									jQuery(".ulp-layer-options").find(".ulp-message").html(data.message);
									jQuery(".ulp-layer-options").find(".ulp-message").slideDown(350);
								} else {
									jQuery(".ulp-layer-options").find(".ulp-message").html("Service is not available.");
									jQuery(".ulp-layer-options").find(".ulp-message").slideDown(350);
								}
							} catch(error) {
								jQuery(".ulp-layer-options").find(".ulp-message").html("Service is not available.");
								jQuery(".ulp-layer-options").find(".ulp-message").slideDown(350);
							}
						}
					);
					return false;
				}
				function ulp_build_preview() {
					//jQuery(".ulp-preview-container").css({
					//	"background" : jQuery("[name=\'ulp_overlay_color\']").val()
					//});
					jQuery(".ulp-preview-window").css({
						"width" : parseInt(jQuery("[name=\'ulp_width\']").val(), 10) + "px",
						"height" : parseInt(jQuery("[name=\'ulp_height\']").val(), 10) + "px"
					});
					
					var popup_style = "";
					var from_rgb = ulp_hex2rgb(jQuery("[name=\'ulp_button_color\']").val());
					var to_color = "transparent";
					var from_color = "transparent";
					if (from_rgb) {
						var total = parseInt(from_rgb.r, 10)+parseInt(from_rgb.g, 10)+parseInt(from_rgb.b, 10);
						if (total == 0) total = 1;
						var to = {
							r : Math.max(0, parseInt(from_rgb.r, 10) - parseInt(48*from_rgb.r/total, 10)),
							g : Math.max(0, parseInt(from_rgb.g, 10) - parseInt(48*from_rgb.g/total, 10)),
							b : Math.max(0, parseInt(from_rgb.b, 10) - parseInt(48*from_rgb.b/total, 10))
						};
						from_color = jQuery("[name=\'ulp_button_color\']").val();
						to_color = ulp_rgb2hex(to.r, to.g, to.b);
					}
					var input_border_color = "border-color:transparent !important;";
					if (jQuery("[name=\'ulp_input_border_color\']").val() != "") input_border_color = "border-color:"+jQuery("[name=\'ulp_input_border_color\']").val()+" !important;";
					var input_background_color = "background-color: transparent !important;";
					if (jQuery("[name=\'ulp_input_background_color\']").val() != "") {
						var bg_rgb = ulp_hex2rgb(jQuery("[name=\'ulp_input_background_color\']").val());
						input_background_color = "background-color:rgb("+parseInt(bg_rgb.r)+","+parseInt(bg_rgb.g)+","+parseInt(bg_rgb.b)+") !important;background-color:rgba("+parseInt(bg_rgb.r)+","+parseInt(bg_rgb.g)+","+parseInt(bg_rgb.b)+", "+jQuery("[name=\'ulp_input_background_opacity\']").val()+") !important;";
					}
					popup_style += ".ulp-preview-submit,.ulp-preview-submit:visited{background: "+from_color+";border:1px solid "+from_color+";background-image:linear-gradient("+to_color+","+from_color+");}";
					popup_style += ".ulp-preview-submit:hover,.ulp-preview-submit:active{background: "+to_color+";border:1px solid "+from_color+";background-image:linear-gradient("+from_color+","+to_color+");}";
					popup_style += ".ulp-preview-input,.ulp-preview-input:hover,.ulp-preview-input:active,.ulp-preview-input:focus{"+input_border_color+""+input_background_color+"}";
					jQuery(".ulp-preview-content").html("<style>"+popup_style+"</style>");
					jQuery(".ulp-layers-item").each(function() {
						var layer_id = jQuery(this).attr("id").replace("ulp-layer-", "");
						if (ulp_active_layer == layer_id) {
							var content = jQuery("#ulp_layer_content").val();
							content = content.replace("{subscription-name}", "<input class=\'ulp-preview-input\' id=\'ulp-preview-input-name\' type=\'text\'>");
							content = content.replace("{subscription-email}", "<input class=\'ulp-preview-input\' id=\'ulp-preview-input-email\' type=\'text\'>");
							content = content.replace("{subscription-submit}", "<a class=\'ulp-preview-submit\' id=\'ulp-preview-submit\'></a>");
							var style = "#ulp-preview-layer-"+layer_id+" {left:" + parseInt(jQuery("#ulp_layer_left").val(), 10) + "px;top:" + parseInt(jQuery("#ulp_layer_top").val(), 10) + "px;}";
							if (jQuery("#ulp_layer_width").val() != "") style += "#ulp-preview-layer-"+layer_id+" {width:"+parseInt(jQuery("#ulp_layer_width").val(), 10)+"px;}";
							if (jQuery("#ulp_layer_height").val() != "") style += "#ulp-preview-layer-"+layer_id+" {height:"+parseInt(jQuery("#ulp_layer_height").val(), 10)+"px;}";
							var background = "";		
							if (jQuery("#ulp_layer_background_color").val() != "") {
								var rgb = ulp_hex2rgb(jQuery("#ulp_layer_background_color").val());
								if (rgb != false) background = "background-color:"+jQuery("#ulp_layer_background_color").val()+";background-color:rgba("+rgb.r+","+rgb.g+","+rgb.b+","+jQuery("#ulp_layer_background_opacity").val()+");";
							} else $background = "";
							if (jQuery("#ulp_layer_background_image").val() != "") {
								background += "background-image:url("+jQuery("#ulp_layer_background_image").val()+");background-repeat:repeat;";
							}
							var font = "font-family:\'"+jQuery("#ulp_layer_font").val()+"\',arial;font-weight:"+jQuery("#ulp_layer_font_weight").val()+";color:"+jQuery("#ulp_layer_font_color").val()+";font-size:"+parseInt(jQuery("#ulp_layer_font_size").val(), 10)+"px;";
							if (parseInt(jQuery("#ulp_layer_text_shadow_size").val(), 10) != 0 && jQuery("#ulp_layer_text_shadow_color").val() != "") font += "text-shadow:"+jQuery("#ulp_layer_text_shadow_color").val()+" "+jQuery("#ulp_layer_text_shadow_size").val()+"px "+" "+jQuery("#ulp_layer_text_shadow_size").val()+"px "+" "+jQuery("#ulp_layer_text_shadow_size").val()+"px";
							style += "#ulp-preview-layer-"+layer_id+",#ulp-preview-layer-"+layer_id+" p,#ulp-preview-layer-"+layer_id+" a,#ulp-preview-layer-"+layer_id+" span,#ulp-preview-layer-"+layer_id+" li,#ulp-preview-layer-"+layer_id+" input,#ulp-preview-layer-"+layer_id+" button,#ulp-preview-layer-"+layer_id+" textarea {"+font+"}";
							style += "#ulp-preview-layer-"+layer_id+"{"+background+"z-index:"+parseInt(parseInt(jQuery("#ulp_layer_index").val(), 10)+1000, 10)+";text-align:"+jQuery("#ulp_layer_content_align").val()+"}";
							if (jQuery("#ulp_layer_style").val() != "") style += "#ulp-preview-layer-"+layer_id+"{"+jQuery("#ulp_layer_style").val()+"}";
							var font_link = "";
							if (!ulp_inarray(jQuery("#ulp_layer_font").val(), ulp_local_fonts)) font_link = "<link href=\'http://fonts.googleapis.com/css?family="+jQuery("#ulp_layer_font").val().replace(" ", "+")+":100,200,300,400,500,600,700,800,900&subset=latin,latin-ext,cyrillic,cyrillic-ext,greek\' rel=\'stylesheet\' type=\'text/css\'>";
							var layer = font_link+"<style>"+style+"</style><div class=\'ulp-preview-layer ulp-preview-layer-active\' id=\'ulp-preview-layer-"+layer_id+"\'>"+content+"</div>";
						} else {
							var content = jQuery("#ulp_layer_"+layer_id+"_content").val();
							content = content.replace("{subscription-name}", "<input class=\'ulp-preview-input\' id=\'ulp-preview-input-name\' type=\'text\'>");
							content = content.replace("{subscription-email}", "<input class=\'ulp-preview-input\' id=\'ulp-preview-input-email\' type=\'text\'>");
							content = content.replace("{subscription-submit}", "<a class=\'ulp-preview-submit\' id=\'ulp-preview-submit\'></a>");
							var style = "#ulp-preview-layer-"+layer_id+" {left:" + parseInt(jQuery("#ulp_layer_"+layer_id+"_left").val(), 10) + "px;top:" + parseInt(jQuery("#ulp_layer_"+layer_id+"_top").val(), 10) + "px;}";
							if (jQuery("#ulp_layer_"+layer_id+"_width").val() != "") style += "#ulp-preview-layer-"+layer_id+" {width:"+parseInt(jQuery("#ulp_layer_"+layer_id+"_width").val(), 10)+"px;}";
							if (jQuery("#ulp_layer_"+layer_id+"_height").val() != "") style += "#ulp-preview-layer-"+layer_id+" {height:"+parseInt(jQuery("#ulp_layer_"+layer_id+"_height").val(), 10)+"px;}";
							var background = "";		
							if (jQuery("#ulp_layer_"+layer_id+"_background_color").val() != "") {
								var rgb = ulp_hex2rgb(jQuery("#ulp_layer_"+layer_id+"_background_color").val());
								if (rgb != false) background = "background-color:"+jQuery("#ulp_layer_"+layer_id+"_background_color").val()+";background-color:rgba("+rgb.r+","+rgb.g+","+rgb.b+","+jQuery("#ulp_layer_"+layer_id+"_background_opacity").val()+");";
							} else $background = "";
							if (jQuery("#ulp_layer_"+layer_id+"_background_image").val() != "") {
								background += "background-image:url("+jQuery("#ulp_layer_"+layer_id+"_background_image").val()+");background-repeat:repeat;";
							}
							var font = "font-family:\'"+jQuery("#ulp_layer_"+layer_id+"_font").val()+"\',arial;font-weight:"+jQuery("#ulp_layer_"+layer_id+"_font_weight").val()+";color:"+jQuery("#ulp_layer_"+layer_id+"_font_color").val()+";font-size:"+parseInt(jQuery("#ulp_layer_"+layer_id+"_font_size").val(), 10)+"px;";
							if (parseInt(jQuery("#ulp_layer_"+layer_id+"_text_shadow_size").val(), 10) != 0 && jQuery("#ulp_layer_"+layer_id+"_text_shadow_color").val() != "") font += "text-shadow:"+jQuery("#ulp_layer_"+layer_id+"_text_shadow_color").val()+" "+jQuery("#ulp_layer_"+layer_id+"_text_shadow_size").val()+"px "+" "+jQuery("#ulp_layer_"+layer_id+"_text_shadow_size").val()+"px "+" "+jQuery("#ulp_layer_"+layer_id+"_text_shadow_size").val()+"px";
							style += "#ulp-preview-layer-"+layer_id+",#ulp-preview-layer-"+layer_id+" p,#ulp-preview-layer-"+layer_id+" a,#ulp-preview-layer-"+layer_id+" span,#ulp-preview-layer-"+layer_id+" li,#ulp-preview-layer-"+layer_id+" input,#ulp-preview-layer-"+layer_id+" button,#ulp-preview-layer-"+layer_id+" textarea {"+font+"}";
							style += "#ulp-preview-layer-"+layer_id+"{"+background+"z-index:"+parseInt(parseInt(jQuery("#ulp_layer_"+layer_id+"_index").val(), 10)+1000, 10)+";text-align:"+jQuery("#ulp_layer_"+layer_id+"_content_align").val()+";}";
							if (jQuery("#ulp_layer_"+layer_id+"_style").val() != "") style += "#ulp-preview-layer-"+layer_id+"{"+jQuery("#ulp_layer_"+layer_id+"_style").val()+"}";
							var font_link = "";
							if (!ulp_inarray(jQuery("#ulp_layer_"+layer_id+"_font").val(), ulp_local_fonts)) font_link = "<link href=\'http://fonts.googleapis.com/css?family="+jQuery("#ulp_layer_"+layer_id+"_font").val().replace(" ", "+")+":100,200,300,400,500,600,700,800,900&subset=latin,latin-ext,cyrillic,cyrillic-ext,greek\' rel=\'stylesheet\' type=\'text/css\'>";
							var layer = font_link+"<style>"+style+"</style><div class=\'ulp-preview-layer\' id=\'ulp-preview-layer-"+layer_id+"\'>"+content+"</div>";
						}
						jQuery(".ulp-preview-content").append(layer);
					});
					if (ulp_active_layer == 0) {
						layer_id = "0";
						var content = jQuery("#ulp_layer_content").val();
						content = content.replace("{subscription-name}", "<input class=\'ulp-preview-input\' id=\'ulp-preview-input-name\' type=\'text\'>");
						content = content.replace("{subscription-email}", "<input class=\'ulp-preview-input\' id=\'ulp-preview-input-email\' type=\'text\'>");
						content = content.replace("{subscription-submit}", "<a class=\'ulp-preview-submit\' id=\'ulp-preview-submit\'></a>");
						var style = "#ulp-preview-layer-"+layer_id+" {left:" + parseInt(jQuery("#ulp_layer_left").val(), 10) + "px;top:" + parseInt(jQuery("#ulp_layer_top").val(), 10) + "px;}";
						if (jQuery("#ulp_layer_width").val() != "") style += "#ulp-preview-layer-"+layer_id+" {width:"+parseInt(jQuery("#ulp_layer_width").val(), 10)+"px;}";
						if (jQuery("#ulp_layer_height").val() != "") style += "#ulp-preview-layer-"+layer_id+" {height:"+parseInt(jQuery("#ulp_layer_height").val(), 10)+"px;}";
						var background = "";		
						if (jQuery("#ulp_layer_background_color").val() != "") {
							var rgb = ulp_hex2rgb(jQuery("#ulp_layer_background_color").val());
							if (rgb != false) background = "background-color:"+jQuery("#ulp_layer_background_color").val()+";background-color:rgba("+rgb.r+","+rgb.g+","+rgb.b+","+jQuery("#ulp_layer_background_opacity").val()+");";
						} else $background = "";
						if (jQuery("#ulp_layer_background_image").val() != "") {
							background += "background-image:url("+jQuery("#ulp_layer_background_image").val()+");background-repeat:repeat;";
						}
						var font = "font-family:\'"+jQuery("#ulp_layer_font").val()+"\',arial;font-weight:"+jQuery("#ulp_layer_font_weight").val()+";color:"+jQuery("#ulp_layer_font_color").val()+";font-size:"+parseInt(jQuery("#ulp_layer_font_size").val(), 10)+"px;";
						if (parseInt(jQuery("#ulp_layer_text_shadow_size").val(), 10) != 0 && jQuery("#ulp_layer_text_shadow_color").val() != "") font += "text-shadow:"+jQuery("#ulp_layer_text_shadow_color").val()+" "+jQuery("#ulp_layer_text_shadow_size").val()+"px "+" "+jQuery("#ulp_layer_text_shadow_size").val()+"px "+" "+jQuery("#ulp_layer_text_shadow_size").val()+"px;";
						style += "#ulp-preview-layer-"+layer_id+",#ulp-preview-layer-"+layer_id+" p,#ulp-preview-layer-"+layer_id+" a,#ulp-preview-layer-"+layer_id+" span,#ulp-preview-layer-"+layer_id+" li,#ulp-preview-layer-"+layer_id+" input,#ulp-preview-layer-"+layer_id+" button,#ulp-preview-layer-"+layer_id+" textarea {"+font+"}";
						style += "#ulp-preview-layer-"+layer_id+"{"+background+"z-index:"+parseInt(parseInt(jQuery("#ulp_layer_index").val(), 10)+1000, 10)+";text-align:"+jQuery("#ulp_layer_content_align").val()+";}";
						if (jQuery("#ulp_layer_style").val() != "") style += "#ulp-preview-layer-"+layer_id+"{"+jQuery("#ulp_layer_style").val()+"}";
						var font_link = "";
						if (!ulp_inarray(jQuery("#ulp_layer_font").val(), ulp_local_fonts)) font_link = "<link href=\'http://fonts.googleapis.com/css?family="+jQuery("#ulp_layer_font").val().replace(" ", "+")+":100,200,300,400,500,600,700,800,900&subset=latin,latin-ext,cyrillic,cyrillic-ext,greek\' rel=\'stylesheet\' type=\'text/css\'>";
						var layer = font_link+"<style>"+style+"</style><div class=\'ulp-preview-layer ulp-preview-layer-active\' id=\'ulp-preview-layer-"+layer_id+"\'>"+content+"</div>";
						jQuery(".ulp-preview-content").append(layer);
					}
					jQuery("#ulp-preview-input-name").attr("placeholder", jQuery("[name=\'ulp_name_placeholder\']").val());
					jQuery("#ulp-preview-input-email").attr("placeholder", jQuery("[name=\'ulp_email_placeholder\']").val());
					jQuery("#ulp-preview-submit").html(jQuery("[name=\'ulp_button_label\']").val());
				}
				function ulp_2hex(c) {
					var hex = c.toString(16);
					return hex.length == 1 ? "0" + hex : hex;
				}
				function ulp_rgb2hex(r, g, b) {
					return "#" + ulp_2hex(r) + ulp_2hex(g) + ulp_2hex(b);
				}
				function ulp_hex2rgb(hex) {
					var shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;
					hex = hex.replace(shorthandRegex, function(m, r, g, b) {
						return r + r + g + g + b + b;
					});
					var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
					return result ? {
						r: parseInt(result[1], 16),
						g: parseInt(result[2], 16),
						b: parseInt(result[3], 16)
					} : false;
				}
				function ulp_inarray(needle, haystack) {
					var length = haystack.length;
					for(var i = 0; i < length; i++) {
						if(haystack[i] == needle) return true;
					}
					return false;
				}
				function ulp_self_close() {
					return false;
				}
				ulp_build_preview();
				var ulp_keyuprefreshtimer;
				jQuery(document).ready(function(){
					jQuery("input, select, textarea").bind("change", function() {
						clearTimeout(ulp_keyuprefreshtimer);
						ulp_build_preview();
					});
					jQuery("input, select, textarea").bind("keyup", function() {
						clearTimeout(ulp_keyuprefreshtimer);
						ulp_keyuprefreshtimer = setTimeout(function(){ulp_build_preview();}, 1000);
					});
				});
			</script>
		</div>
		<div class="ulp_legend">
			<strong>Legend:</strong>
			<p><img src="images/copy.png" alt="Duplicate layer" border="0"> Duplicate layer</p>
			<p><img src="images/edit.png" alt="Edit layer details" border="0"> Edit layer details</p>
			<p><img src="images/delete.png" alt="Delete layer" border="0"> Delete layer</p>
		</div>
<div id="ulp-layers-item-container" style="display: none;">
	<div class="ulp-layers-item-cell ulp-layers-item-cell-info">
		<h4></h4>
		<p></p>
	</div>
	<div class="ulp-layers-item-cell" style="width: 70px;">
		<a href="#" title="Edit layer details" onclick="return ulp_edit_layer(this);"><img src="images/edit.png" alt="Edit layer details" border="0"></a>
		<a href="#" title="Duplicate layer" onclick="return ulp_copy_layer(this);"><img src="images/copy.png" alt="Duplicate details" border="0"></a>
		<a href="#" title="Delete layer" onclick="return ulp_delete_layer(this);"><img src="images/delete.png" alt="Delete layer" border="0"></a>
	</div>
</div>
<div id="ulp-layer-options-container" style="display: none;">
	<div class="ulp-layer-options">
		<div class="ulp-layer-row">
			<div class="ulp-layer-property">
				<label>Layer title</label>
				<input type="text" id="ulp_layer_title" name="ulp_layer_title" value="" class="widefat" placeholder="Enter the layer title...">
				<br /><em>Enter the layer title. It is used for your reference.</em>
			</div>
		</div>
		<div class="ulp-layer-row">
			<div class="ulp-layer-property">
				<label>Layer content</label>
				<textarea id="ulp_layer_content" name="ulp_layer_content" class="widefat" placeholder="Enter the layer content..."></textarea>
				<br /><em>Enter the layer content. HTML-code allowed.</em>
			</div>
		</div>
		<div class="ulp-layer-row">
			<div class="ulp-layer-property">
				<label>Layer size</label>
				<input type="text" id="ulp_layer_width" name="ulp_layer_width" value="" class="ic_input_number" placeholder="Width"> x
				<input type="text" id="ulp_layer_height" name="ulp_layer_height" value="" class="ic_input_number" placeholder="Height"> pixels
				<br /><em>Enter the layer size, width x height. Leave both or one field empty for auto calculation.</em>
			</div>
			<div class="ulp-layer-property">
				<label>Left position</label>
				<input type="text" id="ulp_layer_left" name="ulp_layer_left" value="" class="ic_input_number" placeholder="Left"> pixels
				<br /><em>Enter the layer left position relative basic frame left edge.</em>
			</div>
			<div class="ulp-layer-property">
				<label>Top position</label>
				<input type="text" id="ulp_layer_top" name="ulp_layer_top" value="" class="ic_input_number" placeholder="Top"> pixels
				<br /><em>Enter the layer top position relative basic frame top edge.</em>
			</div>
			<div class="ulp-layer-property">
				<label>Content alignment</label>
				<select class="ic_input_s" id="ulp_layer_content_align" name="ulp_layer_content_align">';
			foreach ($alignments as $key => $value) {
				echo '
					<option value="'.$key.'">'.htmlspecialchars($value, ENT_QUOTES).'</option>';
			}
			echo '
				</select>
				<br /><em>Set the horizontal content alignment.</em>
			</div>
		</div>
		<div class="ulp-layer-row">
			<div class="ulp-layer-property" style="width: 25%;">
				<label>Appearance</label>
				<select class="ic_input_s" id="ulp_layer_appearance" name="ulp_layer_appearance">';
			foreach ($appearances as $key => $value) {
				echo '
					<option value="'.$key.'">'.htmlspecialchars($value, ENT_QUOTES).'</option>';
			}
			echo '
				</select>
				<br /><em>Set the layer appearance.</em>
			</div>
			<div class="ulp-layer-property" style="width: 25%;">
				<label>Start delay</label>
				<input type="text" id="ulp_layer_appearance_delay" name="ulp_layer_appearance_delay" value="" class="ic_input_number" placeholder="[0...10000]"> milliseconds
				<br /><em>Set the appearance start delay. The value must be in a range [0...1].</em>
			</div>
			<div class="ulp-layer-property" style="width: 25%;">
				<label>Duration speed</label>
				<input type="text" id="ulp_layer_appearance_speed" name="ulp_layer_appearance_speed" value="" class="ic_input_number" placeholder="[0...10000]"> milliseconds
				<br /><em>Set the duration speed in milliseconds.</em>
			</div>
			<div class="ulp-layer-property" style="width: 25%;">
				<label>Layer index</label>
				<input type="text" id="ulp_layer_index" name="ulp_layer_index" value="" class="ic_input_number" placeholder="[0...100]">
				<br /><em>Set the stack order of the layer. A layer with greater stack order is always in front of a layer with a lower stack order.</em>
			</div>
		</div>
		<div class="ulp-layer-row">
			<div class="ulp-layer-property" style="width: 270px;">
				<label>Background color</label>
				<input type="text" class="ulp-color ic_input_number" id="ulp_layer_background_color" name="ulp_layer_background_color" value="" placeholder="">
				<br /><em>Set the background color. Leave empty for transparent background.</em>
			</div>
			<div class="ulp-layer-property" style="width: 200px;">
				<label>Background opacity</label>
				<input type="text" id="ulp_layer_background_opacity" name="ulp_layer_background_opacity" value="" class="ic_input_number" placeholder="[0...1]">
				<br /><em>Set the background opacity. The value must be in a range [0...1].</em>
			</div>
			<div class="ulp-layer-property">
				<label>Background image URL</label>
				<input type="text" id="ulp_layer_background_image" name="ulp_layer_background_image" value="" class="widefat" placeholder="Enter the background image URL...">
				<br /><em>Enter the background image URL.</em>
			</div>
		</div>
		<div class="ulp-layer-row">
			<div class="ulp-layer-property" style="width: 230px;">
				<label>Font</label>
				<select class="ic_input_m" id="ulp_layer_font" name="ulp_layer_font">
					<option disabled="disabled">------ LOCAL FONTS ------</option>';
			foreach ($local_fonts as $key => $value) {
				echo '
					<option value="'.$key.'">'.htmlspecialchars($value, ENT_QUOTES).'</option>';
			}
			if (is_array($webfonts_array['items'])) {
				echo '
					<option disabled="disabled">------ WEB FONTS ------</option>';
				foreach ($webfonts_array['items'] as $webfont) {
					echo '
					<option value="'.htmlspecialchars($webfont['family'], ENT_QUOTES).'">'.htmlspecialchars($webfont['family'], ENT_QUOTES).'</option>';
				}
			}
			echo '
				</select>
				<br /><em>Select the font.</em>
			</div>
			<div class="ulp-layer-property" style="width: 270px;">
				<label>Font color</label>
				<input type="text" class="ulp-color ic_input_number" id="ulp_layer_font_color" name="ulp_layer_font_color" value="" placeholder="">
				<br /><em>Set the font color.</em>
			</div>
			<div class="ulp-layer-property" style="width: 25%;">
				<label>Font size</label>
				<input type="text" id="ulp_layer_font_size" name="ulp_layer_font_size" value="" class="ic_input_number" placeholder="Font size"> pixels
				<br /><em>Set the font size. The value must be in a range [10...64].</em>
			</div>
			<div class="ulp-layer-property" style="width: 25%;">
				<label>Font weight</label>
				<select class="ic_input_s" id="ulp_layer_font_weight" name="ulp_layer_font_weight">';
			foreach ($font_weights as $key => $value) {
				echo '
					<option value="'.$key.'">'.htmlspecialchars($key.' - '.$value, ENT_QUOTES).'</option>';
			}
			echo '
				</select>
				<br /><em>Select the font weight. Some fonts may not support selected font weight.</em>
			</div>
		</div>
		<div class="ulp-layer-row">
			<div class="ulp-layer-property" style="width: 200px;">
				<label>Text shadow size</label>
				<input type="text" id="ulp_layer_text_shadow_size" name="ulp_layer_text_shadow_size" value="" class="ic_input_number" placeholder="Shadow size"> pixels
				<br /><em>Set the text shadow size.</em>
			</div>
			<div class="ulp-layer-property" style="width: 270px;">
				<label>Text shadow color</label>
				<input type="text" class="ulp-color ic_input_number" id="ulp_layer_text_shadow_color" name="ulp_layer_text_shadow_color" value="" placeholder="">
				<br /><em>Set the text shadow color.</em>
			</div>
			<div class="ulp-layer-property">
				<label>Custom style</label>
				<input type="text" id="ulp_layer_style" name="ulp_layer_style" value="" class="widefat" placeholder="Enter the custom style string...">
				<br /><em>Enter the custom style string. This value is added to layer <code>style</code> attribute.</em>
			</div>
		</div>
		<div class="ulp-layer-row">
			<div class="ulp-layer-property">
				<input type="hidden" name="action" value="save-layer">
				<input type="hidden" name="ulp_layer_id" value="0">
				<input type="hidden" name="ulp_popup_id" value="'.$id.'">
				<input type="button" class="btn btn-primary ulp-button" name="submit" value="Save Layer" onclick="return ulp_save_layer();">
				<img class="ulp-loading" src="images/loading.gif">
			</div>
			<div class="ulp-layer-property" style="text-align: right;">
				<input type="button" class="btn btn-secondary ulp-button" name="submit" value="Cancel" onclick="return ulp_cancel_layer(this);">
			</div>
		</div>
		<div class="ulp-message"></div>
	</div>
</div>';
		
	} else if ($page == 'subscribers') {
		if (!empty($error_message)) $message = '<div class="alert alert-error">'.$error_message.'</div>';
		else if (!empty($ok_message)) $message = '<div class="alert alert-success">'.$ok_message.'</div>';
		else if (DEMO_MODE) $message = '<div class="alert alert-success"><strong>Demo mode.</strong> Real e-mails are hidden.</div>';
		else $message = '';

		if (isset($_GET["s"])) $search_query = trim(stripslashes($_GET["s"]));
		else $search_query = "";
		
		$tmp = $icdb->get_row("SELECT COUNT(*) AS total FROM ".$icdb->prefix."subscribers WHERE deleted = '0'".((strlen($search_query) > 0) ? " AND (name LIKE '%".addslashes($search_query)."%' OR email LIKE '%".addslashes($search_query)."%')" : ""));
		$total = $tmp["total"];
		$totalpages = ceil($total/RECORDS_PER_PAGE);
		if ($totalpages == 0) $totalpages = 1;
		if (isset($_GET["p"])) $page = intval($_GET["p"]);
		else $page = 1;
		if ($page < 1 || $page > $totalpages) $page = 1;
		$switcher = page_switcher('admin.php?page=subscribers'.((strlen($search_query) > 0) ? '&s='.rawurlencode($search_query) : ''), $page, $totalpages);

		$sql = "SELECT t1.*, t2.title AS popup_title FROM ".$icdb->prefix."subscribers t1 LEFT JOIN ".$icdb->prefix."popups t2 ON t2.id = t1.popup_id WHERE t1.deleted = '0'".((strlen($search_query) > 0) ? " AND (t1.name LIKE '%".addslashes($search_query)."%' OR t1.email LIKE '%".addslashes($search_query)."%')" : "")." ORDER BY t1.created DESC LIMIT ".(($page-1)*RECORDS_PER_PAGE).", ".RECORDS_PER_PAGE;
		$rows = $icdb->get_rows($sql);

		echo '
			<div class="wrap ulp">
				'.$message.'
				<h2>Subscribers</h2>
				<form action="admin.php" method="get" style="margin-bottom: 10px;">
				<input type="hidden" name="page" value="subscribers" />
				Search: <input type="text" name="s" value="'.htmlspecialchars($search_query, ENT_QUOTES).'">
				<input type="submit" class="btn btn-secondary ulp-button" value="Search" />
				'.((strlen($search_query) > 0) ? '<input type="button" class="btn btn-secondary ulp-button" value="Reset search results" onclick="window.location.href=\'admin.php?page=subscribers\';" />' : '').'
				</form>
				<div class="ulp_buttons"><a class="btn btn-primary ulp-button" href="admin.php?action=export-subscribers">CSV Export</a></div>
				<div class="ulp_pageswitcher">'.$switcher.'</div>
				<table class="ulp_records">
				<tr>
					<th>Name</th>
					<th>E-mail</th>
					<th>Popup</th>
					<th style="width: 120px;">Created</th>
					<th style="width: 25px;"></th>
				</tr>';
		if (sizeof($rows) > 0) {
			foreach ($rows as $row) {
				$email = $row['email'];
				if (DEMO_MODE) {
					if (($pos = strpos($email, "@")) !== false) {
						$name = substr($email, 0, strpos($email, "@"));
						$email = substr($name, 0, 1).'*****'.substr($email, $pos);
					}
				}
				echo '
				<tr>
					<td>'.htmlspecialchars($row['name'], ENT_QUOTES).'</td>
					<td>'.htmlspecialchars($email, ENT_QUOTES).'</td>
					<td>'.htmlspecialchars($row['popup_title'], ENT_QUOTES).'</td>
					<td>'.date("Y-m-d H:i", $row['created']).'</td>
					<td style="text-align: center;">
						<a href="admin.php?action=delete-subscriber&id='.$row['id'].'" title="Delete record" onclick="return ulp_submitOperation();"><img src="images/delete.png" alt="Delete record" border="0"></a>
					</td>
				</tr>';
			}
		} else {
			echo '
				<tr><td colspan="5" style="padding: 20px; text-align: center;">'.((strlen($search_query) > 0) ? 'No results found for "<strong>'.htmlspecialchars($search_query, ENT_QUOTES).'</strong>"' : 'List is empty.').'</td></tr>';
		}
		echo '
				</table>
				<div class="ulp_buttons">
					<a class="btn btn-primary ulp-button" href="admin.php?action=delete-subscribers" onclick="return ulp_submitOperation();">Delete All</a>
					<a class="btn btn-primary ulp-button" href="admin.php?action=export-subscribers">CSV Export</a>
				</div>
				<div class="ulp_pageswitcher">'.$switcher.'</div>
				<div class="ulp_legend">
				<strong>Legend:</strong>
					<p><img src="images/delete.png" alt="Delete record" border="0"> Delete record</p>
				</div>
			</div>
			<script type="text/javascript">
				function ulp_submitOperation() {
					var answer = confirm("Do you really want to continue?")
					if (answer) return true;
					else return false;
				}
			</script>';

	} else if ($page == 'faq') {
		echo '
		<div class="wrap ulp">
			<h2>FAQ</h2>
			<div class="ulp-options" style="width: 100%; position: relative;">
				<h3>How can I create my own popup?</h3>
				<ol>
					<li>
						Go to <a href="admin.php?page=create">Create Popup</a> and enter the size of basic popup frame.
						This frame will be centered and all layers will be placed relative to the top-left corner of this frame.
					</li>
					<li>
						Click button <code>Add New Layer</code> and configure your first layer.
					</li>
					<li>
						Create as many layers as you need.
					</li>
					<li>
						Save popup details.
					</li>
				</ol>
				<h3>How can I raise a popup?</h3>
				<p>There are two ways to raise popup: by clicking certain element, on every page load.</p>
				<ol>
					<li>
						If you want to raise popup by clicking certain element, add the following <code>onclick</code> handler to the element:
						<br /><code>onclick="return ulp_open(\'POPUP_ID\');"</code>
						<br /><code>POPUP_ID</code> is a popup ID taken form relevant column on <a href="admin.php?page=popups">this page</a>.
						<br />Example: <code>&lt;a href="#" onclick="return ulp_open(\'POPUP_ID\');"&gt;Raise the popup&lt;/a&gt;</code>
					</li>
					<li>
						To raise popup on page load, go to <a href="admin.php?page=settings">Settings</a> page and set OnLoad parameters.
					</li>
				</ol>
				<h3>How can I raise a popup on certain page load?</h3>
				<ol>
					<li>
						Go to <a href="admin.php?page=settings">Settings</a> page and make sure that <code>Display mode</code> is not <code>Disable popup</code>.
					</li>
					<li>
						Add the following code to certain page:
						<br /><code>&lt;script&gt;var ulp_custom_onload_popup = "POPUP_ID";&lt;/script&gt;</code>
						<br /><code>POPUP_ID</code> is a popup ID taken form relevant column on <a href="admin.php?page=popups">this page</a>.
					</li>
				</ol>
				<h3>How can I add subscription form to popup?</h3>
				<p>
					First of all, please remember that subscription form consists of 3 elements: "name" input field, "e-mail" input field and "submit" button.
					Each element has relevant shortcode:
					<br /><code>{subscription-name}</code> - optional
					<br /><code>{subscription-email}</code> - mandatory
					<br /><code>{subscription-submit}</code> - mandatory
					<br />All you have to do is to insert shortcodes into popup layers.
				</p>
				<h3>How can I add "close" icon to popup?</h3>
				<p>
					You can add and customize "close" icon as you wish. Create new layer with content like that:
					<br /><code>&lt;a href="#" onclick="return ulp_self_close();"&gt;&lt;img src="http://url-to-my-wonderful-close-icon" alt=""&gt;&lt;/a&gt;</code>
					<br />The important part of the this string is <code>onclick</code> handler: <code>onclick="return ulp_self_close();"</code>. It runs JavaScript
					function called <code>ulp_self_close()</code> which closes popup.
				</p>
				<h3>Credits</h3>
				<ol>
					<li><a href="http://p.yusukekamiyamane.com/" target="_blank">Fugue Icons</a> [icons]</li>
					<li><a href="http://www.google.com/fonts/specimen/Mountains+of+Christmas" target="_blank">Mountains of Christmas</a> [font]</li>
					<li><a href="http://www.google.com/fonts/specimen/Open+Sans" target="_blank">Open Sans</a> [font]</li>
					<li><a href="http://www.google.com/fonts/specimen/Walter+Turncoat" target="_blank">Walter Turncoat</a> [font]</li>
					<li><a href="http://www.rockettheme.com/" target="_blank">Christmas Icon Set</a> [icons]</li>
					<li><a href="http://www.flickr.com/photos/duncanh1/8506986371/in/photolist-dXJwEP-7ZogK1-8bHpxi-eoL5K2-dU8WLK-7Zk6DD-dyBCL2-dyH6vN-87oTAm-dVq9ex-bax8Fe-a3sk3a-dyBCG8-dyBCye-dxoaup-aFxFtK-a25d6s-cA1TLd-fEy7Vh-a25t97-a3sk3i-a25t9d-bt324c-9eWYyv-e9v5L6-9ZYJCb-7YgSdJ-aow783-dV8L1k-9dB9zs-8A5WTw-9ZvMxn-b9HKsk-bp15Kf-ecHEZB-bPkHhK-8Ebh3A-a1S7W5-e3vpbv-9Zz3hW-a7uaQT-egTcNK-a1S7Wh-7PsHJT-fEuMRY-fq7Cz9-aEQRuu-cz4kYU-8WrG2Q-dxtAQA-brkWsD/" target="_blank">The City from the Shard</a> [image]</li>
					<li><a href="http://www.fasticon.com" target="_blank">Fast Icon</a> [icons]</li>
					<li><a href="http://www.wallsave.com" target="_blank">Wallpapers Business Graph</a> [image]</li>
					<li><a href="http://wakpaper.com/id164530/digital-art-with-high-business-buildings-and-a-tree-green-falling-1600x1000-pixel.html" target="_blank">Wakpaper.com</a> [image]</li>
				</ol>
			</div>
		</div>';
	} else if ($page == 'embed') {
		echo '
	<h3>Embed Layered Popups into website</h3>
	<ol class="embed-list">
		<li>
		Make sure that your website loads jQuery. If it does not, just add this line into <code>head</code> section:
		<br /><code>&lt;script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"&gt;&lt;/script&gt;</code>
		</li>
		<li>
		Add these lines into <code>head</code> section, below <code>jQuery</code> (before <code>&lt;/head&gt;</code> tag):
		<br /><code>&lt;link href="'.$url_base.'css/style.css?ver='.VERSION.'" rel="stylesheet" type="text/css"&gt;</code>
		<br /><code>&lt;script src="'.$url_base.'js/ulp-jsonp.js?ver='.VERSION.'"&gt;&lt;/script&gt;</code>
		</li>
		<li>
		Enjoy!
		</li>
	</ol>
	<hr>';
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
			<p class="navbar-text pull-left">Layered Popups, version <?php echo VERSION; ?></p>
			<p class="navbar-text pull-right">Copyright &copy; 2011-<?php echo date('Y'); ?> <a href="http://www.icprojects.net/" target="_blank">Ivan Churakov</a></p>
		</div>
	</div>
</div>
<!-- Footer - end -->
</body>
</html>