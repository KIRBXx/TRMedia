<?php
define('VERSION', '1.65');
define('RECORDS_PER_PAGE', 50);
define('AWEBER_APPID', '0e193739');
define('EXPORT_VERSION', '0001');
define('DEMO_MODE', false);
define('ABSPATH', dirname(dirname(__FILE__)));
define('TABLE_PREFIX', 'ulp_');


$local_fonts = array(
		'arial' => 'Arial',
		'verdana' => 'Verdana'
	);
$alignments = array(
		'left' => 'Left',
		'right' => 'Right',
		'center' => 'Center',
		'justify' => 'Justify'
	);
$display_modes = array(
		'none' => 'Disable popup',
		'every-time' => 'Every time', 
		'once-session' => 'Once per session',
		'once-only' => 'Only once'
	);
$appearances = array(
		'fade-in' => 'Fade In',
		'slide-up' => 'Slide Up',
		'slide-down' => 'Slide Down',
		'slide-left' => 'Slide Left',
		'slide-right' => 'Slide Right'
	);
$font_weights = array(
		'100' => 'Thin',
		'200' => 'Extra-light',
		'300' => 'Light',
		'400' => 'Normal',
		'500' => 'Medium',
		'600' => 'Demi-bold',
		'700' => 'Bold',
		'800' => 'Heavy',
		'900' => 'Black'
	);
$default_popup_options = array(
		"title" => "",
		"width" => "640",
		"height" => "400",
		"overlay_color" => "#333333",
		"overlay_opacity" => 0.8,
		"enable_close" => "on",
		"mailchimp_enable" => "off",
		"mailchimp_api_key" => "",
		"mailchimp_list_id" => "",
		"mailchimp_double" => "off",
		"mailchimp_welcome" => "off",
		"icontact_enable" => "off",
		"icontact_appid" => "",
		"icontact_apiusername" => "",
		"icontact_apipassword" => "",
		"icontact_listid" => "",
		'campaignmonitor_enable' => "off",
		'campaignmonitor_api_key' => '',
		'campaignmonitor_list_id' => '',
		'getresponse_enable' => "off",
		'getresponse_api_key' => '',
		'getresponse_campaign_id' => '',
		'aweber_enable' => "off",
		'aweber_listid' => "",
		'name_placeholder' => 'Enter your name...',
		'email_placeholder' => 'Enter your e-mail...',
		'button_label' => 'Subscribe',
		'button_color' => '#0147A3',
		'input_border_color' => '#444444',
		'input_background_color' => '#FFFFFF',
		'input_background_opacity' => 0.7,
		'return_url' => ''
	);
$default_layer_options = array(
		"title" => "",
		"content" => "",
		"width" => "",
		"height" => "",
		"left" => 20,
		"top" => 20,
		"background_color" => "",
		"background_opacity" => 0.9,
		"background_image" => "",
		"content_align" => "left",
		"index" => 5,
		"appearance" => "fade-in",
		"appearance_delay" => "200",
		"appearance_speed" => "1000",
		"font" => "arial",
		"font_color" => "#000000",
		"font_weight" => "400",
		"font_size" => 14,
		"text_shadow_size" => 0,
		"text_shadow_color" => "#000000",
		"style" => ""
	);
$options = array (
		"version" => VERSION,
		"cookie_value" => 'ilovelencha',
		"onload_mode" => 'none',
		"onload_delay" => 0,
		"onload_popup" => '',
		"csv_separator" => ";",
		"aweber_consumer_key" => "",
		"aweber_consumer_secret" => "",
		"aweber_access_key" => "",
		"aweber_access_secret" => "",
		"login" => "admin",
		"password" => md5("admin")
	);

?>