var ulp_active_window_id = false;
var ulp_subscribing = false;
var ulp_initialized = false;
var ulp_cookie_value;
var ulp_onload_mode;
var ulp_onload_popup;
var ulp_onload_delay;

var ulp_baseurl = (function() {
	var re = new RegExp('js/ulp-jsonp(\.min)?\.js.*'),
	scripts = document.getElementsByTagName('script');
	for (var i = 0, ii = scripts.length; i < ii; i++) {
		var path = scripts[i].getAttribute('src');
		if(re.test(path)) return path.replace(re, '');
	}
})();
var ulp_ajax_url = ulp_baseurl + "ajax.php";

function ulp_init() {
	if (ulp_initialized) return;
	ulp_initialized = true;
	var str_id = decodeURIComponent((new RegExp('[?|&]ulp=' + '([^&;]+?)(&|#|;|$)').exec(location.search)||[,""])[1].replace(/\+/g, '%20'))||null;
	if (!str_id) str_id = '';
	jQuery.ajax({
		url: ulp_ajax_url, 
		data: {
			action: "get-data",
			ulp: str_id
		},
		dataType: "jsonp",
		success: function(data) {
			//alert(data.html);
			try {
				if (data.status == "OK") {
					ulp_cookie_value = data.cookie_value;
					ulp_onload_mode = data.onload_mode;
					ulp_onload_popup = data.onload_popup;
					ulp_onload_delay = parseInt(data.onload_delay, 10);
					if (typeof ulp_custom_onload_popup !== 'undefined') {
						ulp_onload_popup = ulp_custom_onload_popup;
					}
					jQuery('body').append(data.html);
					if (str_id == '') ulp_start();
					else ulp_open(str_id);
				}
			} catch(error) {
				alert(error);
			}
		}
	});
}

function ulp_start() {
	if (ulp_onload_popup == "") return;
	if (jQuery("#ulp-"+ulp_onload_popup).length == 0) return;
	if (ulp_onload_mode == "none") return;
	ulp_cookie = ulp_read_cookie("ulp-"+ulp_onload_popup);
	if (ulp_cookie == ulp_cookie_value) return;
	if (parseInt(ulp_onload_delay, 10) <= 0) {
		if (ulp_onload_mode == "once-session") ulp_write_cookie("ulp-"+ulp_onload_popup, ulp_cookie_value, 0);
		else if (ulp_onload_mode == "once-only") ulp_write_cookie("ulp-"+ulp_onload_popup, ulp_cookie_value, 180);
		ulp_open(ulp_onload_popup);
	} else {
		setTimeout(function() {
			if (ulp_onload_mode == "once-session") ulp_write_cookie("ulp-"+ulp_onload_popup, ulp_cookie_value, 0);
			else if (ulp_onload_mode == "once-only") ulp_write_cookie("ulp-"+ulp_onload_popup, ulp_cookie_value, 180);
			ulp_open(ulp_onload_popup);
		}, parseInt(ulp_onload_delay, 10)*1000);
	}
}
function ulp_open(id) {
	jQuery("#ulp-"+id).each(function() {
		ulp_active_window_id = id;
		jQuery("#ulp-"+id+"-overlay").fadeIn(300);
		if (jQuery(this).attr("data-close") == "on") {
			jQuery("#ulp-"+id+"-overlay").bind("click", function($) {
				ulp_close(id);
			});
		}
		var viewport = {
			width: Math.max(320, jQuery(window).width()),
			height: Math.max(320, jQuery(window).height())
		};
		var width = parseInt(jQuery(this).attr("data-width"), 10);
		var height = parseInt(jQuery(this).attr("data-height"), 10);
		
		var scale = Math.min((viewport.width-20)/width, viewport.height/height);
		if (scale > 1) scale = 1;
		width = parseInt(width*scale, 10);
		height = parseInt(height*scale, 10);
		jQuery(this).css({
			"width": width+"px",
			"height": height+"px",
			"margin-left": "-"+parseInt(width/2, 10)+"px",
			"margin-top": "-"+parseInt(height/2, 10)+"px"
		});
		var content = jQuery(this).find(".ulp-content");
		jQuery(content).css({
			"width": width+"px",
			"height": height+"px",
		});
		jQuery(content).find(".ulp-layer").each(function() {
			var layer = this;
			var layer_content_encoded = jQuery(layer).attr("data-base64");
			if (layer_content_encoded) {
				jQuery(layer).html(ulp_decode64(jQuery(layer).html()));
			}
			var layer_left = jQuery(layer).attr("data-left");
			var layer_top = jQuery(layer).attr("data-top");
			var layer_width = jQuery(layer).attr("data-width");
			var layer_height = jQuery(layer).attr("data-height");
			var layer_font_size = jQuery(layer).attr("data-font-size");
			var layer_appearance = jQuery(layer).attr("data-appearance");
			var layer_appearance_delay = parseInt(jQuery(layer).attr("data-appearance-delay"), 10);
			var layer_appearance_speed = parseInt(jQuery(layer).attr("data-appearance-speed"), 10);
			if (layer_width) jQuery(layer).css("width", parseInt(layer_width*scale, 10)+"px");
			if (layer_height) jQuery(layer).css("height", parseInt(layer_height*scale, 10)+"px");
			if (layer_font_size) jQuery(layer).css("font-size", Math.max(4, parseInt(layer_font_size*scale, 10))+"px");
			switch (layer_appearance) {
				case "slide-down":
					jQuery(layer).css({
						"left": parseInt(layer_left*scale, 10)+"px",
						"top": "-"+parseInt(2*viewport.height)+"px"
					});
					setTimeout(function() {
						jQuery(layer).animate({
							"top": parseInt(layer_top*scale, 10)+"px"
						}, layer_appearance_speed);
					}, layer_appearance_delay);
					break;
				case "slide-up":
					jQuery(layer).css({
						"left": parseInt(layer_left*scale, 10)+"px",
						"top": parseInt(2*viewport.height)+"px"
					});
					setTimeout(function() {
						jQuery(layer).animate({
							"top": parseInt(layer_top*scale, 10)+"px"
						}, layer_appearance_speed);
					}, layer_appearance_delay);
					break;
				case "slide-left":
					jQuery(layer).css({
						"left": parseInt(2*viewport.width)+"px",
						"top": parseInt(layer_top*scale, 10)+"px"
					});
					setTimeout(function() {
						jQuery(layer).animate({
							"left": parseInt(layer_left*scale, 10)+"px"
						}, layer_appearance_speed);
					}, layer_appearance_delay);
					break;
				case "slide-right":
					jQuery(layer).css({
						"left": "-"+parseInt(2*viewport.width)+"px",
						"top": parseInt(layer_top*scale, 10)+"px"
					});
					setTimeout(function() {
						jQuery(layer).animate({
							"left": parseInt(layer_left*scale, 10)+"px"
						}, layer_appearance_speed);
					}, layer_appearance_delay);
					break;
				case "fade-in":
					jQuery(layer).css({
						"left": parseInt(layer_left*scale, 10)+"px",
						"top": parseInt(layer_top*scale, 10)+"px",
						"display": "none"
					});
					setTimeout(function() {
						jQuery(layer).fadeIn(layer_appearance_speed);
					}, layer_appearance_delay);
					break;
				default:
					jQuery(layer).css({
						"left": parseInt(layer_left*scale, 10)+"px",
						"top": parseInt(layer_top*scale, 10)+"px"
					});
					break;
			}
		});
		jQuery(this).show();
	});
	return false;
}
function ulp_close(id) {
	jQuery("#ulp-"+id).each(function() {
		ulp_active_window_id = false;
		var layer_appearance_speed = 500;
		var content = jQuery(this).find(".ulp-content");
		var viewport = {
			width: Math.max(320, jQuery(window).width()),
			height: Math.max(320, jQuery(window).height())
		};
		jQuery("#ulp-"+id+"-overlay").unbind("click");
		jQuery(content).find(".ulp-layer").each(function() {
			var layer = this;
			var layer_appearance = jQuery(layer).attr("data-appearance");
			switch (layer_appearance) {
				case "slide-down":
					jQuery(layer).animate({
						"top": "-"+parseInt(2*viewport.height)+"px"
					}, layer_appearance_speed);
					break;
				case "slide-up":
					jQuery(layer).animate({
						"top": parseInt(2*viewport.height)+"px"
					}, layer_appearance_speed);
					break;
				case "slide-left":
					jQuery(layer).animate({
						"left": parseInt(2*viewport.width)+"px"
					}, layer_appearance_speed);
					break;
				case "slide-right":
					jQuery(layer).animate({
						"left": "-"+parseInt(2*viewport.width)+"px"
					}, layer_appearance_speed);
					break;
				case "fade-in":
					jQuery(layer).fadeOut(layer_appearance_speed);
					break;
				default:
					jQuery(layer).css({
						"display": "none"
					});
					break;
			}
			setTimeout(function() {
				var layer_content_encoded = jQuery(layer).attr("data-base64");
				if (layer_content_encoded) {
					jQuery(layer).html(ulp_encode64(jQuery(layer).html()));
				}
			}, layer_appearance_speed);		
		});
		setTimeout(function() {
			jQuery("#ulp-"+id).hide();
			jQuery("#ulp-"+id+"-overlay").fadeOut(300);
		}, layer_appearance_speed);		
	});
	return false;
}
function ulp_self_close() {
	ulp_close(ulp_active_window_id);
	return false;
}
function ulp_subscribe(object) {
	if (ulp_subscribing) return false;
	ulp_subscribing = true;
	jQuery(".ulp-input-error").removeClass("ulp-input-error");
	var button_label = jQuery("#ulp-"+ulp_active_window_id).find('.ulp-submit').html();
	jQuery("#ulp-"+ulp_active_window_id).find('.ulp-submit').html("Loading...");
	var name = jQuery("#ulp-"+ulp_active_window_id).find('[name="ulp-name"]').val();
	var email = jQuery("#ulp-"+ulp_active_window_id).find('[name="ulp-email"]').val();
	jQuery.ajax({
		url: ulp_ajax_url, 
		data: {
			name: name.replace('@', '+'),
			email: email.replace('@', '+'),
			action: "subscribe",
			ulp: ulp_active_window_id
		},
		dataType: "jsonp",
		success: function(data) {
			jQuery("#ulp-"+ulp_active_window_id).find('.ulp-submit').html(button_label);
			ulp_subscribing = false;
			try {
				var status = data.status;
				if (status == "OK") {
					ulp_write_cookie("ulp-"+ulp_onload_popup, ulp_cookie_value, 180);
					var redirect_url = data.return_url;
					if (redirect_url.length > 0) location.href = redirect_url;
					else ulp_self_close();
				} else if (status == "ERROR") {
					if (data.name == 'ERROR') jQuery("#ulp-"+ulp_active_window_id).find('[name="ulp-name"]').addClass("ulp-input-error");
					if (data.email == 'ERROR') jQuery("#ulp-"+ulp_active_window_id).find('[name="ulp-email"]').addClass("ulp-input-error");
				} else {
					jQuery("#ulp-"+ulp_active_window_id).find('.ulp-submit').html("Error!");
				}
			} catch(error) {
				jQuery("#ulp-"+ulp_active_window_id).find('.ulp-submit').html("Error!");
			}
		}
	});
	return false;
}
function ulp_read_cookie(key) {
	var pairs = document.cookie.split("; ");
	for (var i = 0, pair; pair = pairs[i] && pairs[i].split("="); i++) {
		if (pair[0] === key) return pair[1] || "";
	}
	return null;
}
function ulp_write_cookie(key, value, days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	} else var expires = "";
	document.cookie = key+"="+value+expires+"; path=/";
}
function ulp_encode64 (data) {
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
function ulp_decode64(input) {
	var output = "";
	var chr1, chr2, chr3 = "";
	var enc1, enc2, enc3, enc4 = "";
	var i = 0;
	var keyStr = "ABCDEFGHIJKLMNOP" +
		"QRSTUVWXYZabcdef" +
		"ghijklmnopqrstuv" +
		"wxyz0123456789+/" +
		"=";
	var base64test = /[^A-Za-z0-9\+\/\=]/g;
	if (base64test.exec(input)) return "";
	input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

	do {
		enc1 = keyStr.indexOf(input.charAt(i++));
		enc2 = keyStr.indexOf(input.charAt(i++));
		enc3 = keyStr.indexOf(input.charAt(i++));
		enc4 = keyStr.indexOf(input.charAt(i++));

		chr1 = (enc1 << 2) | (enc2 >> 4);
		chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
		chr3 = ((enc3 & 3) << 6) | enc4;

		output = output + String.fromCharCode(chr1);

		if (enc3 != 64) {
			output = output + String.fromCharCode(chr2);
		}
		if (enc4 != 64) {
			output = output + String.fromCharCode(chr3);
		}

		chr1 = chr2 = chr3 = "";
		enc1 = enc2 = enc3 = enc4 = "";

	} while (i < input.length);
	return unescape(output);
}
jQuery(window).resize(function() {
	if (ulp_active_window_id) {
		var viewport = {
			width: Math.max(320, jQuery(window).width()),
			height: Math.max(320, jQuery(window).height())
		};
		var width = parseInt(jQuery("#ulp-"+ulp_active_window_id).attr("data-width"), 10);
		var height = parseInt(jQuery("#ulp-"+ulp_active_window_id).attr("data-height"), 10);
		var scale = Math.min((viewport.width-20)/width, viewport.height/height);
		if (scale > 1) scale = 1;
		width = parseInt(width*scale, 10);
		height = parseInt(height*scale, 10);
		jQuery("#ulp-"+ulp_active_window_id).css({
			"width": width+"px",
			"height": height+"px",
			"margin-left": "-"+parseInt(width/2, 10)+"px",
			"margin-top": "-"+parseInt(height/2, 10)+"px"
		});
		var content = jQuery("#ulp-"+ulp_active_window_id).find(".ulp-content");
		jQuery(content).css({
			"width": width+"px",
			"height": height+"px",
		});
		jQuery(content).find(".ulp-layer").each(function() {
			var layer = this;
			var layer_left = jQuery(layer).attr("data-left");
			var layer_top = jQuery(layer).attr("data-top");
			var layer_width = jQuery(layer).attr("data-width");
			var layer_height = jQuery(layer).attr("data-height");
			var layer_font_size = jQuery(layer).attr("data-font-size");
			if (layer_width) jQuery(layer).css("width", parseInt(layer_width*scale, 10)+"px");
			if (layer_height) jQuery(layer).css("height", parseInt(layer_height*scale, 10)+"px");
			if (layer_font_size) jQuery(layer).css("font-size", Math.max(4, parseInt(layer_font_size*scale, 10))+"px");
			jQuery(layer).css({
				"left": parseInt(layer_left*scale, 10)+"px",
				"top": parseInt(layer_top*scale, 10)+"px"
			});
		});
	}
});
jQuery(document).keyup(function(e) {
	if (ulp_active_window_id) {
		if (jQuery("#ulp-"+ulp_active_window_id).attr("data-close") == "on") {
			if (e.keyCode == 27) ulp_self_close();
		}
	}
});
//ulp_init();
jQuery(document).ready(function() {
	ulp_init();
});