var udb_objects = new Array();
var udb_types = new Array();
var udb_urls = new Array();
var udb_ids = new Array();
var udb_idx = 0;
var udb_baseurl = (function() {
	var re = new RegExp('js/udb-jsonp(\.min)?\.js.*'),
	scripts = document.getElementsByTagName('script');
	for (var i = 0, ii = scripts.length; i < ii; i++) {
		var path = scripts[i].getAttribute('src');
		if(re.test(path)) return path.replace(re, '');
	}
})();

jQuery(document).ready(function() {
	var udb_boxes = jQuery("div.udb-box");
	jQuery.each(udb_boxes, function() {
		var rel = jQuery(this).attr("data-rel");
		udb_types[udb_idx] = rel;
		var url = jQuery(this).attr("data-url");
		udb_urls[udb_idx] = url;
		var id = jQuery(this).attr("data-id");
		udb_ids[udb_idx] = id;
		udb_objects[udb_idx] = this;
		udb_idx++;
	});
	if (udb_idx > 0) udb_getbox(0);
});

function udb_getbox(idx) {
	var action = udb_baseurl + "ajax.php";
	jQuery.ajax({
		url: action, 
		data: {
			udb_rel: udb_types[idx],
			udb_url: "_"+udb_urls[idx],
			udb_id: udb_ids[idx],
			action: "udb_getbox"
		},
		dataType: "jsonp",
		success: function(data) {
			var html_data = data.html;
			if(html_data.match("udb_container") != null) {
				jQuery(udb_objects[idx]).css("display", "none");
				jQuery(udb_objects[idx]).append(html_data);
				jQuery(udb_objects[idx]).slideDown(600);
			}
			if (idx+1 < udb_idx) {
				udb_getbox(idx+1);
			}
		}
	});
}

var udb_suffix = "";
var udb_busy = false;
function udb_clickhandler(suffix, active_method, return_url) {
	if (udb_busy == true) return;
	udb_busy = true;
	udb_suffix = suffix;
	jQuery("#submit"+suffix).attr("disabled","disabled");
	jQuery("#loading"+suffix).fadeIn(300);
	jQuery("#message"+suffix).slideUp("slow");
	var method = active_method;
	if (jQuery("#method_paypal"+suffix).is(":checked")) method = "paypal";
	else if (jQuery("#method_payza"+suffix).is(":checked")) method = "payza";
	else if (jQuery("#method_interkassa"+suffix).is(":checked")) method = "interkassa";
	else if (jQuery("#method_authnet"+suffix).is(":checked")) method = "authnet";
	else if (jQuery("#method_egopay"+suffix).is(":checked")) method = "egopay";
	else if (jQuery("#method_perfect"+suffix).is(":checked")) method = "perfect";
	else if (jQuery("#method_skrill"+suffix).is(":checked")) method = "skrill";
	else if (jQuery("#method_bitpay"+suffix).is(":checked")) method = "bitpay";
	else if (jQuery("#method_stripe"+suffix).is(":checked")) method = "stripe";
	
	var url = jQuery("#url"+suffix).val();
	if (url === undefined) url = "";
	else url = udb_encode64(url);
	return_url = udb_encode64(return_url);
	
	jQuery.ajax({
		url: udb_baseurl+"ajax.php", 
		data: {
			udb_name: jQuery("#name"+suffix).val(),
			udb_email: jQuery("#email"+suffix).val(),
			udb_url: url,
			udb_amount: jQuery("#amount"+suffix).val(),
			udb_id: jQuery("#campaign"+suffix).val(),
			udb_method: method,
			udb_suffix: suffix,
			udb_return: return_url,
			action: "udb_submit"
		},
		dataType: "jsonp",
		success: function(data) {
			var html_data = data.html;
			jQuery("#loading"+udb_suffix).fadeOut(200);
			jQuery("#submit"+udb_suffix).removeAttr("disabled");
			if(html_data.match("udb_confirmation_info") != null) {
				jQuery("#udb_signup_form"+udb_suffix).fadeOut(500, function() {
					jQuery("#udb_confirmation_container"+udb_suffix).html(html_data);
					jQuery("#udb_confirmation_container"+udb_suffix).fadeIn(500, function() {});
				});
			} else {
				jQuery("#message"+udb_suffix).html(html_data);
				jQuery("#message"+udb_suffix).slideDown("slow");
			}
			udb_busy = false;
		}
	});
}
function udb_edit(suffix) {
	if (udb_busy == true) return;
	udb_suffix = suffix;
	jQuery("#udb_confirmation_container"+udb_suffix).fadeOut(500, function() {
		jQuery("#udb_signup_form"+udb_suffix).fadeIn(500, function() {});
	});
}
function udb_bitpay(donor_id, amount, email, suffix, return_url) {
	if (udb_busy == true) return;
	udb_busy = true;
	udb_suffix = suffix;
	var button_label = jQuery("#udb_bitpay"+udb_suffix).val();
	jQuery("#udb_bitpay"+udb_suffix).val("Processing...");
	jQuery("#udb_bitpay"+udb_suffix).attr("disabled","disabled");
	jQuery("#udb_bitpay_edit"+udb_suffix).attr("disabled","disabled");
	jQuery("#udb_loading2"+udb_suffix).fadeIn(300);
	jQuery("#udb_message"+udb_suffix).slideUp("slow");
	jQuery.ajax({
		url: udb_baseurl+"ajax.php", 
		data: {
			udb_id: donor_id,
			action: "udb_bitpayurl"
		},
		dataType: "jsonp",
		success: function(data) {
			var html_data = data.html;
			if(html_data.match("udb_confirmation_info") != null) {
				jQuery("#udb_loading2"+udb_suffix).fadeOut(200);
				jQuery("#udb_bitpay"+udb_suffix).removeAttr("disabled");
				jQuery("#udb_bitpay_edit"+udb_suffix).removeAttr("disabled");
				jQuery("#udb_confirmation_container"+udb_suffix).fadeOut(500, function() {
					jQuery("#udb_confirmation_container"+udb_suffix).html(html_data);
					jQuery("#udb_confirmation_container"+udb_suffix).fadeIn(500, function() {});
				});
			} else if(html_data.match("https://") != null) {
				location.href = html_data;
			} else {
				jQuery("#udb_bitpay"+udb_suffix).val(button_label);
				jQuery("#udb_loading2"+udb_suffix).fadeOut(200);
				jQuery("#udb_bitpay"+udb_suffix).removeAttr("disabled");
				jQuery("#udb_bitpay_edit"+udb_suffix).removeAttr("disabled");
				jQuery("#udb_message"+udb_suffix).html(html_data);
				jQuery("#udb_message"+udb_suffix).slideDown("slow");
			}
			udb_busy = false;
		}
	});
}
var udb_stripe_suffix = "";
function udb_stripe(donor_id, suffix, return_url) {
	udb_stripe_suffix = suffix;
	var token = function(res) {
		if (res && res.id) {
			var button_label = jQuery("#udb_stripe"+udb_stripe_suffix).val();
			jQuery("#udb_stripe"+udb_stripe_suffix).val("Processing...");
			jQuery("#udb_stripe"+udb_stripe_suffix).attr("disabled","disabled");
			jQuery("#udb_stripe_edit"+udb_stripe_suffix).attr("disabled","disabled");
			jQuery("#udb_loading2"+udb_stripe_suffix).fadeIn(300);
			jQuery("#udb_message"+udb_stripe_suffix).slideUp("slow");
			jQuery.ajax({
				url: udb_baseurl+"ajax.php", 
				data: {
					udb_id: donor_id,
					udb_token: res.id,
					action: "udb_stripecharge"
				},
				dataType: "jsonp",
				success: function(data) {
					var html_data = data.html;
					jQuery("#udb_loading2"+udb_stripe_suffix).fadeOut(200);
					jQuery("#udb_stripe"+udb_stripe_suffix).removeAttr("disabled");
					jQuery("#udb_stripe_edit"+udb_stripe_suffix).removeAttr("disabled");
					if(html_data.match("udb_confirmation_info") != null) {
						jQuery("#udb_confirmation_container"+udb_stripe_suffix).fadeOut(500, function() {
							jQuery("#udb_confirmation_container"+udb_stripe_suffix).html(html_data);
							jQuery("#udb_confirmation_container"+udb_stripe_suffix).fadeIn(500);
						});
					} else {
						jQuery("#udb_stripe"+udb_stripe_suffix).val(button_label);
						jQuery("#udb_message"+udb_stripe_suffix).html(html_data);
						jQuery("#udb_message"+udb_stripe_suffix).slideDown("slow");
					}
				}
			});
		}
	};
	StripeCheckout.open({
		key:         jQuery("#udb_stripe_publishable"+udb_stripe_suffix).val(),
		address:     false,
		amount:      jQuery("#udb_stripe_amount"+udb_stripe_suffix).val(),
		currency:    jQuery("#udb_stripe_currency"+udb_stripe_suffix).val(),
		name:        jQuery("#udb_stripe_label"+udb_stripe_suffix).val(),
		description: jQuery("#udb_stripe_label"+udb_stripe_suffix).val(),
		panelLabel:  'Checkout',
		token:       token
	});
}

function udb_encode64 (data) {
	var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
	var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
	ac = 0,
	enc = "",
	tmp_arr = [];
	if (!data) return data;
	do {
		o1 = data.charCodeAt(i++);
		o2 = data.charCodeAt(i++);
		o3 = data.charCodeAt(i++);

		bits = o1 << 16 | o2 << 8 | o3;

		h1 = bits >> 18 & 0x3f;
		h2 = bits >> 12 & 0x3f;
		h3 = bits >> 6 & 0x3f;
		h4 = bits & 0x3f;

		tmp_arr[ac++] = b64.charAt(h1) + b64.charAt(h2) + b64.charAt(h3) + b64.charAt(h4);
	} while (i < data.length);
	enc = tmp_arr.join('');
	var r = data.length % 3;
	return (r ? enc.slice(0, r - 3) : enc) + '==='.slice(r || 3);
}
