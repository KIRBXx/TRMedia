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
			if (isset($_POST['ulp_'.$key])) {
				$options[$key] = trim(stripslashes($_POST['ulp_'.$key]));
			}
		}
	}
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

function get_rgb($_color) {
	if (strlen($_color) != 7 && strlen($_color) != 4) return false;
	$color = preg_replace('/[^#a-fA-F0-9]/', '', $_color);
	if (strlen($color) != strlen($_color)) return false;
	if (strlen($color) == 7) list($r, $g, $b) = array($color[1].$color[2], $color[3].$color[4], $color[5].$color[6]);
	else list($r, $g, $b) = array($color[1].$color[1], $color[2].$color[2], $color[3].$color[3]);
	return array("r" => hexdec($r), "g" => hexdec($g), "b" => hexdec($b));
}

function random_string($_length = 16) {
	$symbols = '123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$string = "";
	for ($i=0; $i<$_length; $i++) {
		$string .= $symbols[rand(0, strlen($symbols)-1)];
	}
	return $string;
}

function icontact_addcontact($appid, $apiusername, $apipassword, $listid, $name, $email) {
	global $options;
	$data = icontact_makecall($appid, $apiusername, $apipassword, '/a/', null, 'accounts');
	if (!empty($data['errors'])) return;
	$account = $data['response'][0];
	if (empty($account) || intval($account->enabled != 1)) return;
	$data = icontact_makecall($appid, $apiusername, $apipassword, '/a/'.$account->accountId.'/c/', null, 'clientfolders');
	if (!empty($data['errors'])) return;
	$client = $data['response'][0];
	if (empty($client)) return;
	$contact['email'] = $email;
	if ($options['disable_name'] != 'on') $contact['firstName'] = $name;
	$contact['status'] = 'normal';
	$data = icontact_makecall($appid, $apiusername, $apipassword, '/a/'.$account->accountId.'/c/'.$client->clientFolderId.'/contacts', array($contact), 'contacts');
	if (!empty($data['errors'])) return;
	$contact = $data['response'][0];
	if (empty($contact)) return;
	$subscriber['contactId'] = $contact->contactId;
	$subscriber['listId'] = $listid;
	$subscriber['status'] = 'normal';
	$data = icontact_makecall($appid, $apiusername, $apipassword, '/a/'.$account->accountId.'/c/'.$client->clientFolderId.'/subscriptions', array($subscriber), 'subscriptions');
}

function icontact_getlists($appid, $apiusername, $apipassword) {
	global $options;
	$data = icontact_makecall($appid, $apiusername, $apipassword, '/a/', null, 'accounts');
	if (!empty($data['errors'])) return array();
	$account = $data['response'][0];
	if (empty($account) || intval($account->enabled != 1)) return;
	$data = icontact_makecall($appid, $apiusername, $apipassword, '/a/'.$account->accountId.'/c/', null, 'clientfolders');
	if (!empty($data['errors'])) return array();
	$client = $data['response'][0];
	if (empty($client)) return array();
	$data = icontact_makecall($appid, $apiusername, $apipassword, '/a/'.$account->accountId.'/c/'.$client->clientFolderId.'/lists', array(), 'lists');
	if (!empty($data['errors'])) return array();
	if (!is_array($data['response'])) return array();
	$lists = array();
	foreach ($data['response'] as $list) {
		$lists[$list->listId] = $list->name;
	}
	return $lists;
}

function icontact_makecall($appid, $apiusername, $apipassword, $resource, $postdata = null, $returnkey = null) {
	$return = array();
	$url = "https://app.icontact.com/icp".$resource;
	$headers = array(
		'Except:', 
		'Accept:  application/json', 
		'Content-type:  application/json', 
		'Api-Version:  2.2',
		'Api-AppId:  '.$appid, 
		'Api-Username:  '.$apiusername, 
		'Api-Password:  '.$apipassword
	);
	$handle = curl_init();
	curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
	if (!empty($postdata)) {
		curl_setopt($handle, CURLOPT_POST, true);
		curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($postdata));
	}
	curl_setopt($handle, CURLOPT_URL, $url);
	if (!$response_json = curl_exec($handle)) {
		$return['errors'][] = 'Unable to execute the cURL handle.';
	}
	if (!$response = json_decode($response_json)) {
		$return['errors'][] = 'The iContact API did not return valid JSON.';
	}
	curl_close($handle);
	if (!empty($response->errors)) {
		foreach ($response->errors as $error) {
			$return['errors'][] = $error;
		}
	}
	if (!empty($return['errors'])) return $return;
	if (empty($returnkey)) {
		$return['response'] = $response;
	} else {
		$return['response'] = $response->$returnkey;
	}
	return $return;
}

function getresponse_getcampaigns($api_key) {
	global $options;
	$request = json_encode(
		array(
			'method' => 'get_campaigns',
			'params' => array(
				$api_key
			),
			'id' => ''
		)
	);

	$curl = curl_init('https://api2.getresponse.com/');
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
	$header = array(
		'Content-Type: application/json',
		'Content-Length: '.strlen($request)
	);
	curl_setopt($curl, CURLOPT_PORT, 443);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
	curl_setopt($curl, CURLOPT_TIMEOUT, 10);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1); // verify certificate
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // check existence of CN and verify that it matches hostname
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
	curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
	curl_setopt($curl, CURLOPT_HEADER, 0);
									
	$response = curl_exec($curl);
						
	if (curl_error($curl)) return array();
	$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	if ($httpCode != '200') return array();
	curl_close($curl);
						
	$post = json_decode($response, true);
	if(!empty($post['error'])) return array();
	if (empty($post['result'])) return array();
	$campaigns = array();
	foreach ($post['result'] as $key => $value) {
		$campaigns[$key] = $value['name'];
	}
	return $campaigns;
}


function install() {
	global $icdb;
	$add_default = false;
	$table_name = $icdb->prefix."popups";
	if($icdb->get_var("SHOW TABLES LIKE '".$table_name."'") != $table_name) {
		$sql = "CREATE TABLE ".$table_name." (
			id int(11) NOT NULL auto_increment,
			str_id varchar(31) collate latin1_general_cs NULL,
			title varchar(255) collate utf8_unicode_ci NULL,
			width int(11) NULL default '640',
			height int(11) NULL default '400',
			options longtext collate utf8_unicode_ci NULL,
			created int(11) NULL,
			blocked int(11) NULL default '0',
			deleted int(11) NULL default '0',
			UNIQUE KEY  id (id)
		);";
		$icdb->query($sql);
		$add_default = true;
	}
	$table_name = $icdb->prefix."layers";
	if($icdb->get_var("SHOW TABLES LIKE '".$table_name."'") != $table_name) {
		$sql = "CREATE TABLE ".$table_name." (
			id int(11) NOT NULL auto_increment,
			popup_id int(11) NULL,
			title varchar(255) collate utf8_unicode_ci NULL,
			content longtext collate utf8_unicode_ci NULL,
			zindex int(11) NULL default '5',
			details longtext collate utf8_unicode_ci NULL,
			created int(11) NULL,
			deleted int(11) NULL default '0',
			UNIQUE KEY  id (id)
		);";
		$icdb->query($sql);
	}
	$table_name = $icdb->prefix . "subscribers";
	if($icdb->get_var("SHOW TABLES LIKE '".$table_name."'") != $table_name) {
		$sql = "CREATE TABLE ".$table_name." (
			id int(11) NOT NULL auto_increment,
			popup_id int(11) NULL,
			name varchar(255) collate utf8_unicode_ci NULL,
			email varchar(255) collate utf8_unicode_ci NULL,
			created int(11) NULL,
			deleted int(11) NULL default '0',
			UNIQUE KEY  id (id)
		);";
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
	if ($add_default) {
		if (file_exists(dirname(dirname(__FILE__)).'/default') && is_dir(dirname(dirname(__FILE__)).'/default')) {
			$dircontent = scandir(dirname(dirname(__FILE__)).'/default');
			for ($i=0; $i<sizeof($dircontent); $i++) {
				if ($dircontent[$i] != "." && $dircontent[$i] != ".." && $dircontent[$i] != "index.html" && $dircontent[$i] != ".htaccess") {
					if (is_file(dirname(dirname(__FILE__)).'/default/'.$dircontent[$i])) {
						$lines = file(dirname(dirname(__FILE__)).'/default/'.$dircontent[$i]);
						if (sizeof($lines) != 3) continue;
						$version = intval(trim($lines[0]));
						if ($version > intval(EXPORT_VERSION)) continue;
						$md5_hash = trim($lines[1]);
						$popup_data = trim($lines[2]);
						$popup_data = base64_decode($popup_data);
						if (!$popup_data || md5($popup_data) != $md5_hash) continue;
						$popup = unserialize($popup_data);
						$popup_details = $popup['popup'];
						$symbols = '123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
						$str_id = '';
						for ($j=0; $j<16; $j++) {
							$str_id .= $symbols[rand(0, strlen($symbols)-1)];
						}
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
					}
				}
			}
		}
	}
}
?>