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
} else exit;

$url_base = ((empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] == 'off') ? 'http://' : 'https://').$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
$filename = basename(__FILE__);
if (($pos = strpos($url_base, $filename)) !== false) $url_base = substr($url_base, 0, $pos);

if (isset($_REQUEST['action'])) {
	switch ($_REQUEST['action']) {
		case 'get-data':
			$layer_webfonts = array();
			$style = '';
			if (isset($_REQUEST['ulp'])) $str_id = preg_replace('/[^a-zA-Z0-9]/', '', $_REQUEST['ulp']);
			else $str_id = '';
			$popups = $icdb->get_rows("SELECT * FROM ".$icdb->prefix."popups WHERE deleted = '0' AND ".($str_id != '' ? "str_id = '".$str_id."'" : "blocked = '0'"));
			foreach ($popups as $popup) {
				$popup_options = unserialize($popup['options']);
				$popup_options = array_merge($default_popup_options, $popup_options);
				
				$from = get_rgb($popup_options['button_color']);
				$total = $from['r']+$from['g']+$from['b'];
				if ($total == 0) $total = 1;
				$to = array();
				$to['r'] = max(0, $from['r']-intval(48*$from['r']/$total));
				$to['g'] = max(0, $from['g']-intval(48*$from['g']/$total));
				$to['b'] = max(0, $from['b']-intval(48*$from['b']/$total));
				$to_color = '#'.($to['r'] < 16 ? '0' : '').dechex($to['r']).($to['g'] < 16 ? '0' : '').dechex($to['g']).($to['b'] < 16 ? '0' : '').dechex($to['b']);
				$from_color = $popup_options['button_color'];
				if (!empty($popup_options['input_background_color'])) $bg_color = get_rgb($popup_options['input_background_color']);
				$style .= '#ulp-'.$popup['str_id'].' .ulp-submit,#ulp-'.$popup['str_id'].' .ulp-submit:visited{background: '.$from_color.';border:1px solid '.$from_color.';background-image:linear-gradient('.$to_color.','.$from_color.');}';
				$style .= '#ulp-'.$popup['str_id'].' .ulp-submit:hover,#ulp-'.$popup['str_id'].' .ulp-submit:active{background: '.$to_color.';border:1px solid '.$from_color.';background-image:linear-gradient('.$from_color.','.$to_color.');}';
				$style .= '#ulp-'.$popup['str_id'].' .ulp-input,#ulp-'.$popup['str_id'].' .ulp-input:hover,#ulp-'.$popup['str_id'].' .ulp-input:active,#ulp-'.$popup['str_id'].' .ulp-input:focus{border-color:'.(empty($popup_options['input_border_color']) ? 'transparent' : $popup_options['input_border_color']).';background-color:'.(empty($popup_options['input_background_color']) ? 'transparent' : $popup_options['input_background_color']).' !important;background-color:'.(empty($popup_options['input_background_color']) ? 'transparent' : 'rgba('.$bg_color['r'].','.$bg_color['g'].','.$bg_color['b'].','.floatval($popup_options['input_background_opacity'])).') !important;}';
				$style .= '#ulp-'.$popup['str_id'].'-overlay{background:'.(!empty($popup_options['overlay_color']) ? $popup_options['overlay_color'] : 'transparent').';opacity:'.$popup_options['overlay_opacity'].';-ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=\''.intval(100*$popup_options['overlay_opacity']).'\')";filter:alpha(opacity="'.intval(100*$popup_options['overlay_opacity']).'");}';
				$front_footer .= '
					<div class="ulp-overlay" id="ulp-'.$popup['str_id'].'-overlay"></div>
					<div class="ulp-window" id="ulp-'.$popup['str_id'].'" data-width="'.$popup_options['width'].'" data-height="'.$popup_options['height'].'" data-close="'.$popup_options['enable_close'].'">
						<div class="ulp-content">';
				$layers = $icdb->get_rows("SELECT * FROM ".$icdb->prefix."layers WHERE popup_id = '".$popup['id']."' AND deleted = '0'");
				foreach ($layers as $layer) {
					$layer_options = unserialize($layer['details']);
					$content = str_replace(
						array('{subscription-name}', '{subscription-email}', '{subscription-submit}'),
						array(
							'<input class="ulp-input" type="text" name="ulp-name" placeholder="'.htmlspecialchars($popup_options['name_placeholder'], ENT_QUOTES).'" value="" onfocus="jQuery(this).removeClass(\'ulp-input-error\');">',
							'<input class="ulp-input" type="text" name="ulp-email" placeholder="'.htmlspecialchars($popup_options['email_placeholder'], ENT_QUOTES).'" value="" onfocus="jQuery(this).removeClass(\'ulp-input-error\');">',
							'<a class="ulp-submit" onclick="return ulp_subscribe(this);">'.htmlspecialchars($popup_options['button_label'], ENT_QUOTES).'</a>'),
						$layer['content']);
					$base64 = false;
					if (strpos(strtolower($content), '<iframe') !== false) {
						$base64 = true;
						$content = base64_encode($content);
					}
					$front_footer .= '
							<div class="ulp-layer" id="ulp-layer-'.$layer['id'].'" data-left="'.$layer_options['left'].'" data-top="'.$layer_options['top'].'" data-appearance="'.$layer_options['appearance'].'" data-appearance-speed="'.$layer_options['appearance_speed'].'" data-appearance-delay="'.$layer_options['appearance_delay'].'"'.(!empty($layer_options['width']) ? ' data-width="'.$layer_options['width'].'"' : '').(!empty($layer_options['height']) ? ' data-height="'.$layer_options['height'].'"' : '').' data-font-size="'.$layer_options['font_size'].'"'.($base64 ? ' data-base64="yes"' : '').'>'.$content.'</div>';
					if (!empty($layer_options['background_color'])) {
						$rgb = get_rgb($layer_options['background_color']);
						$background = 'background-color:'.$layer_options['background_color'].';background-color:rgba('.$rgb['r'].','.$rgb['g'].','.$rgb['b'].','.$layer_options['background_opacity'].');';
					} else $background = '';
					if (!empty($layer_options['background_image'])) {
						$background .= 'background-image:url('.$layer_options['background_image'].');background-repeat:repeat;';
					}
					$font = "font-family:'".$layer_options['font']."', arial;font-weight:".$layer_options['font_weight'].";color:".$layer_options['font_color'].";".($layer_options['text_shadow_size'] > 0 && !empty($layer_options['text_shadow_color']) ? "text-shadow: ".$layer_options['text_shadow_color']." ".$layer_options['text_shadow_size']."px ".$layer_options['text_shadow_size']."px ".$layer_options['text_shadow_size']."px;" : "");
					$style .= '#ulp-layer-'.$layer['id'].',#ulp-layer-'.$layer['id'].' p,#ulp-layer-'.$layer['id'].' a,#ulp-layer-'.$layer['id'].' span,#ulp-layer-'.$layer['id'].' li,#ulp-layer-'.$layer['id'].' input,#ulp-layer-'.$layer['id'].' button,#ulp-layer-'.$layer['id'].' textarea {'.$font.'}';
					$style .= '#ulp-layer-'.$layer['id'].'{'.$background.'z-index:'.($layer_options['index']+1000002).';text-align:'.$layer_options['content_align'].';'.$layer_options['style'].'}';
					if (!array_key_exists($layer_options['font'], $local_fonts)) $layer_webfonts[] = $layer_options['font'];
				}
				$front_footer .= '
						</div>
					</div>';
			}
			if (!empty($layer_webfonts)) {
				$layer_webfonts = array_unique($layer_webfonts);
				include_once(dirname(__FILE__).'/webfonts.php');
				$webfonts_array = json_decode($fonts, true);
				$used_webfonts = array();
				foreach ($webfonts_array['items'] as $webfont) {
					if (in_array($webfont['family'], $layer_webfonts)) {
						$used_webfonts[] = $webfont;
					}
				}
				if(!empty($used_webfonts)){
					$i = 0;
					$families = array();
					$subsets = array();
					foreach($used_webfonts as $fontvars) {
						if (isset($fontvars['family']) && $fontvars['family']) {
							$words = explode(" ",$fontvars['family']);
							$families[$i] = implode('+', $words);
							if (isset($fontvars['variants']) && !empty($fontvars['variants'])) {
								foreach ($fontvars['variants'] as $key => $var) {
									if ($var == 'regular') $fontvars['variants'][$key] = '400';
									if ($var == 'italic') $fontvars['variants'][$key] = '400italic';
								}
								$families[$i] = $families[$i].":".implode(",", $fontvars['variants']);
							}
							if (isset($fontvars['subsets']) && !empty($fontvars['subsets'])) {
								foreach ($fontvars['subsets'] as $sub) {
									if(!in_array($sub, $subsets)){
										$subsets[] = $sub;
									}
								}
							}
						}
						$i++;
					}
					$query = '?family='.implode('|', $families);
					if (!empty($subsets)){
						$query .= '&subset='.implode(',', $subsets);
					}
					$front_header .= '<link href="http://fonts.googleapis.com/css'.$query.'" rel="stylesheet" type="text/css">';
				}
			}
			$front_header .= '<style>'.$style.'</style>';
			$front_header = $front_header;
			
			$return_data = array();
			$return_data['status'] = 'OK';
			$return_data['cookie_value'] = $options['cookie_value'];
			$return_data['onload_mode'] = $options['onload_mode'];
			$return_data['onload_popup'] = $options['onload_popup'];
			$return_data['onload_delay'] = intval($options['onload_delay']);
			
			$return_data['html'] = $front_header.$front_footer;
			echo $jsonp_callback.'('.json_encode($return_data).')';
			exit;
			break;

		case 'subscribe':
			if (isset($_REQUEST['name'])) $name = str_replace('+', '@', trim(stripslashes($_REQUEST['name'])));
			else $name = '';
			if (isset($_REQUEST['email'])) $email = str_replace('+', '@', trim(stripslashes($_REQUEST['email'])));
			else $email = '';
			if (isset($_REQUEST['ulp'])) $str_id = trim(stripslashes($_REQUEST['ulp']));
			else {
				$return_data = array();
				$return_data['status'] = 'FATAL';
				echo $jsonp_callback.'('.json_encode($return_data).')';
				exit;
			}
			$str_id = preg_replace('/[^a-zA-Z0-9]/', '', $str_id);
			$popup_details = $icdb->get_row("SELECT * FROM ".$icdb->prefix."popups WHERE deleted = '0' AND str_id = '".$str_id."'");
			if (empty($popup_details)) {
				$return_data = array();
				$return_data['status'] = 'FATAL';
				echo $jsonp_callback.'('.json_encode($return_data).')';
				exit;
			}
			$return_data = array();
			if ($email == '' || !preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i", $email)) $return_data['email'] = 'ERROR';
			if (!empty($return_data)) {
				$return_data['status'] = 'ERROR';
				echo $jsonp_callback.'('.json_encode($return_data).')';
				exit;
			}
			$popup_options = unserialize($popup_details['options']);
			$popup_options = array_merge($default_popup_options, $popup_options);

			if (empty($name)) $name = substr($email, 0, strpos($email, '@'));
			$subscriber_details = $icdb->get_row("SELECT * FROM ".$icdb->prefix."subscribers WHERE deleted = '0' AND popup_id = '".$popup_details['id']."' AND email = '".mysql_real_escape_string($email)."'");
			if (empty($subscriber_details)) {
				$sql = "INSERT INTO ".$icdb->prefix."subscribers (
					popup_id, name, email, created, deleted) VALUES (
					'".$popup_details['id']."',
					'".mysql_real_escape_string($name)."',
					'".mysql_real_escape_string($email)."',
					'".time()."', '0')";
			} else {
				$sql = "UPDATE ".$icdb->prefix."subscribers SET name = '".mysql_real_escape_string($name)."', created = '".time()."' WHERE id = '".$subscriber_details['id']."'";
			}
			$icdb->query($sql);
			if ($popup_options['mailchimp_enable'] == 'on') {
				$list_id = $popup_options['mailchimp_list_id'];
				$dc = "us1";
				if (strstr($popup_options['mailchimp_api_key'], "-")) {
					list($key, $dc) = explode("-", $popup_options['mailchimp_api_key'], 2);
					if (!$dc) $dc = "us1";
				}
				$mailchimp_url = 'http://'.$dc.'.api.mailchimp.com/1.3/?method=listSubscribe&apikey='.$popup_options['mailchimp_api_key'].'&id='.$list_id.'&email_address='.urlencode($email).'&merge_vars[FNAME]='.urlencode($name).'&merge_vars[LNAME]='.urlencode($name).'&merge_vars[NAME]='.urlencode($name).'&merge_vars[OPTIN_IP]='.$_SERVER['REMOTE_ADDR'].'&output=php&double_optin='.($popup_options['mailchimp_double'] == 'on' ? '1' : '0').'&send_welcome='.($popup_options['mailchimp_welcome'] == 'on' ? '1' : '0');

				$ch = curl_init($mailchimp_url);
				curl_setopt($ch, CURLOPT_URL, $mailchimp_url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($ch, CURLOPT_ENCODING, "");
				curl_setopt($ch, CURLOPT_USERAGENT, 'User-Agent: MCAPI/1.3');
				curl_setopt($ch, CURLOPT_TIMEOUT, 30);
				curl_setopt($ch, CURLOPT_FAILONERROR, 1);
				curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, null);
				$data = curl_exec( $ch );
				curl_close( $ch );
			}
			if ($popup_options['icontact_enable'] == 'on') {
				icontact_addcontact($popup_options['icontact_appid'], $popup_options['icontact_apiusername'], $popup_options['icontact_apipassword'], $popup_options['icontact_listid'], $name, $email);
			}
			if ($popup_options['campaignmonitor_enable'] == 'on') {
				$options['EmailAddress'] = $email;
				$options['Name'] = $name;
				$options['Resubscribe'] = 'true';
				$options['RestartSubscriptionBasedAutoresponders'] = 'true';
				$post = json_encode($options);

				$curl = curl_init('https://api.createsend.com/api/v3/subscribers/'.urlencode($popup_options['campaignmonitor_list_id']).'.json');
				curl_setopt($curl, CURLOPT_POST, 1);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
					
				$header = array(
					'Content-Type: application/json',
					'Content-Length: '.strlen($post),
					'Authorization: Basic '.base64_encode($popup_options['campaignmonitor_api_key'])
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
						
				$response = curl_exec($curl);
				curl_close($curl);
			}
			if ($popup_options['getresponse_enable'] == 'on') {
				$request = json_encode(
					array(
						'method' => 'add_contact',
						'params' => array(
							$popup_options['getresponse_api_key'],
							array(
								'campaign' => $popup_options['getresponse_campaign_id'],
								'action' => 'standard',
								'name' => $name,
								'email' => $email,
								'ip' => $_SERVER['REMOTE_ADDR']
							)
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
				curl_close($curl);
			}
			if ($options['aweber_access_secret']) {
				if ($popup_options['aweber_enable'] == 'on') {
					$account = null;
					if (!class_exists('AWeberAPI')) {
						require_once(dirname(__FILE__).'/aweber_api/aweber_api.php');
					}
					try {
						$aweber = new AWeberAPI($options['aweber_consumer_key'], $options['aweber_consumer_secret']);
						$account = $aweber->getAccount($options['aweber_access_key'], $options['aweber_access_secret']);
						$subscribers = $account->loadFromUrl('/accounts/' . $account->id . '/lists/' . $popup_options['aweber_listid'] . '/subscribers');
						$subscribers->create(array(
							'email' => $email,
							'ip_address' => $_SERVER['REMOTE_ADDR'],
							'name' => $name,
							'ad_tracking' => 'Layered Popups',
						));
					} catch (Exception $e) {
						$account = null;
					}
				}
			}
			
			setcookie('ulp-'.$popup_details['str_id'], $options['cookie_value'], time()+3600*24*180, "/");
			$return_data = array();
			$return_data['status'] = 'OK';
			$return_data['return_url'] = $popup_options['return_url'];
			echo $jsonp_callback.'('.json_encode($return_data).')';
			exit;
			break;
			
		default:
			break;
	}
}
?>