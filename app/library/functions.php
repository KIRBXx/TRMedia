<?php

function t($string)
{
    return trans('text.' . $string);
}

function checkFavorite($id)
{
    if (Auth::check() == FALSE) {
        return FALSE;
    }

    if (Favorite::where('image_id', '=', DB::raw($id))->where('user_id', '=', DB::raw(Auth::user()->id))->count() == 1) {
        return TRUE;
    }
    return FALSE;
}

function getFeaturedUser()
{
    return User::where('is_featured', '=', DB::raw(1))->orderBy(DB::raw('RAND()'))->remember(3, 'featuredAuthor')->limit(1)->get();
}

function getFeaturedImage()
{
    return Images::where('is_featured', '=', DB::raw(1))->orderBy(DB::raw('RAND()'))->remember(3, 'featuredImage')->limit(1)->get();
}

function checkFollow($id)
{
    if (Auth::check() == FALSE) {
        return FALSE;
    }
    $count = Follow::where('user_id', '=', Auth::user()->id)->where('follow_id', '=', $id)->count();
    if ($count >= 1) {
        return TRUE;
    }
    return FALSE;
}


function zoomCrop($image, $width = 200, $height = 150, $type = NULL, $library = 'gd')
{
    if (!file_exists($image)) {
        $image = 'avatar/user.jpg';
    }
    if ($library == 'imagick') {
        if ($type != NULL) {
            return ImageMagic::open($image)->cropThumbnailImage($width, $height)->$type(90);
        } else {
            return ImageMagic::open($image)->cropThumbnailImage($width, $height)->png(90);
        }
    }
    if ($type != NULL) {
        return Image::open($image)->zoomCrop($width, $height)->$type(90);
    }
    return Image::open($image)->zoomCrop($width, $height)->jpeg(90);
}

function cropResize($image, $width = 800, $height = 600, $type = NULL, $library = 'gd')
{
    if (!file_exists($image)) {
        $image = 'avatar/user.jpg';
    }

    if ($library == 'imagick') {
        if ($type != NULL) {
            return ImageMagic::open($image)->enlargeSafeResize($width, $height)->$type(90);
        } else {
            return ImageMagic::open($image)->enlargeSafeResize($width, $height)->png(90);
        }
    }
    if ($type != NULL) {
        return Image::open($image)->cropResize($width, $height)->$type(90);
    }
    return Image::open($image)->cropResize($width, $height)->png(90);
}

function avatar($avatar, $width, $height)
{
    return asset(zoomCrop('avatar/' . $avatar . '.jpg', $width, $height, 'jpeg'));
}


function imagesFromUser($user_id)
{
    return Images::where('user_id', '=', $user_id)->where('deleted_at', '=', NULL)->orderBy(DB::raw('RAND()'))->limit(9)->remember(10)->get();
}

function countImagesFromUser($user_id)
{
    return Images::where('user_id', '=', $user_id)->count();
}

function moreFromSite()
{
    return Images::where('approved', '=', 1)->where('deleted_at', '=', NULL)->orderBy(DB::raw('RAND()'))->limit(9)->get();
}

function numberOfUsers()
{
    return User::where('deleted_at', '=', NULL)->count();
}

function numberOfImages()
{
    return Images::where('approved', '=', 1)->where('deleted_at', '=', NULL)->count();
}

function numberOfComments()
{
    return Comment::where('deleted_at', '=', NULL)->count();
}

function numberOfBanedUsers()
{
    return User::where('permission', '=', 'ban')->count();
}

function numberOfFeaturedImages()
{
    return Images::where('is_featured', '=', '1')->count();
}

function numberOfFeaturedUsers()
{
    return User::where('is_featured', '=', '1')->count();
}

function numberOfReports()
{
    return Report::where('solved', '=', '0')->count();
}

function numberOfImagesApprovalRequired()
{
    return Images::where('approved', '=', 0)->where('deleted_at', '=', NULL)->count();
}

function numberOfNotifications()
{
    if (Auth::check() == FALSE)
        return '';
    return Notification::where('user_id', '=', Auth::user()->id)->where('is_read', '=', '0')->count();
}

function makeLinks($text)
{
    return preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" rel="nofollow" target="_blank">$1</a>', $text);
}

function addhttp($url)
{
    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
        $url = "http://" . $url;
    }
    return $url;
}

function countryIsoCodeMatch($input)
{
    if (preg_match('/AF|AL|DZ|AS|AD|AG|AI|AG|AR|AA|AW|AU|AT|AZ|BS|BH|BD|BB|BY|BE|BZ|BJ|BM|BT|BO|BL|BA|BW|BR|BC|BN|BG|BF|BI|KH|CM|CA|IC|CV|KY|CF|TD|CD|CL|CN|CI|CS|CO|CC|CG|CK|CR|CT|HR|CU|CB|CY|CZ|DK|DJ|DM|DO|TM|EC|EG|SV|GQ|ER|EE|ET|FA|FO|FJ|FI|FR|GF|PF|FS|GA|GM|GE|DE|GH|GI|GB|GR|GL|GD|GP|GU|GT|GN|GY|HT|HW|HN|HK|HU|IS|IN|ID|IA|IQ|IR|IM|IL|IT|JM|JP|JO|KZ|KE|KI|NK|KS|KW|KG|LA|LV|LB|LS|LR|LY|LI|LT|LU|MO|MK|MG|MY|MW|MV|ML|MT|MH|MQ|MR|MU|ME|MX|MI|MD|MC|MN|MS|MA|MZ|MM|NA|NU|NP|AN|NL|NV|NC|NZ|NI|NE|NG|NW|NF|NO|OM|PK|PW|PS|PA|PG|PY|PE|PH|PO|PL|PT|PR|QA|ME|RS|RE|RO|RU|RW|NT|EU|HE|KN|LC|MB|PM|VC|SP|SO|AS|SM|ST|SA|SN|RS|SC|SL|SG|SK|SI|SB|OI|ZA|ES|LK|SD|SR|SZ|SE|CH|SY|TA|TW|TJ|TZ|TH|TG|TK|TO|TT|TN|TR|TU|TC|TV|UG|UA|AE|GB|US|UY|UZ|VU|VS|VE|VN|VB|VA|WK|WF|YE|ZR|ZM|ZW/', $input)) {
        return TRUE;
    }
    return FALSE;
}

function countryResolver($input)
{
    switch ($input) {
        case "AF":
            return "Afghanistan";
        case "AL":
            return "Albania";
        case "DZ":
            return "Algeria";
        case "AS":
            return "American Samoa";
        case "AD":
            return "Andorra";
        case "AG":
            return "Angola";
        case "AI":
            return "Anguilla";
        case "AG":
            return "Antigua &amp; Barbuda";
        case "AR":
            return "Argentina";
        case "AA":
            return "Armenia";
        case "AW":
            return "Aruba";
        case "AU":
            return "Australia";
        case "AT":
            return "Austria";
        case "AZ":
            return "Azerbaijan";
        case "BS":
            return "Bahamas";
        case "BH":
            return "Bahrain";
        case "BD":
            return "Bangladesh";
        case "BB":
            return "Barbados";
        case "BY":
            return "Belarus";
        case "BE":
            return "Belgium";
        case "BZ":
            return "Belize";
        case "BJ":
            return "Benin";
        case "BM":
            return "Bermuda";
        case "BT":
            return "Bhutan";
        case "BO":
            return "Bolivia";
        case "BL":
            return "Bonaire";
        case "BA":
            return "Bosnia &amp; Herzegovina";
        case "BW":
            return "Botswana";
        case "BR":
            return "Brazil";
        case "BC":
            return "British Indian Ocean Ter";
        case "BN":
            return "Brunei";
        case "BG":
            return "Bulgaria";
        case "BF":
            return "Burkina Faso";
        case "BI":
            return "Burundi";
        case "KH":
            return "Cambodia";
        case "CM":
            return "Cameroon";
        case "CA":
            return "Canada";
        case "IC":
            return "Canary Islands";
        case "CV":
            return "Cape Verde";
        case "KY":
            return "Cayman Islands";
        case "CF":
            return "Central African Republic";
        case "TD":
            return "Chad";
        case "CD":
            return "Channel Islands";
        case "CL":
            return "Chile";
        case "CN":
            return "China";
        case "CI":
            return "Christmas Island";
        case "CS":
            return "Cocos Island";
        case "CO":
            return "Colombia";
        case "CC":
            return "Comoros";
        case "CG":
            return "Congo";
        case "CK":
            return "Cook Islands";
        case "CR":
            return "Costa Rica";
        case "CT":
            return "Cote D'Ivoire";
        case "HR":
            return "Croatia";
        case "CU":
            return "Cuba";
        case "CB":
            return "Curacao";
        case "CY":
            return "Cyprus";
        case "CZ":
            return "Czech Republic";
        case "DK":
            return "Denmark";
        case "DJ":
            return "Djibouti";
        case "DM":
            return "Dominica";
        case "DO":
            return "Dominican Republic";
        case "TM":
            return "East Timor";
        case "EC":
            return "Ecuador";
        case "EG":
            return "Egypt";
        case "SV":
            return "El Salvador";
        case "GQ":
            return "Equatorial Guinea";
        case "ER":
            return "Eritrea";
        case "EE":
            return "Estonia";
        case "ET":
            return "Ethiopia";
        case "FA":
            return "Falkland Islands";
        case "FO":
            return "Faroe Islands";
        case "FJ":
            return "Fiji";
        case "FI":
            return "Finland";
        case "FR":
            return "France";
        case "GF":
            return "French Guiana";
        case "PF":
            return "French Polynesia";
        case "FS":
            return "French Southern Ter";
        case "GA":
            return "Gabon";
        case "GM":
            return "Gambia";
        case "GE":
            return "Georgia";
        case "DE":
            return "Germany";
        case "GH":
            return "Ghana";
        case "GI":
            return "Gibraltar";
        case "GB":
            return "Great Britain";
        case "GR":
            return "Greece";
        case "GL":
            return "Greenland";
        case "GD":
            return "Grenada";
        case "GP":
            return "Guadeloupe";
        case "GU":
            return "Guam";
        case "GT":
            return "Guatemala";
        case "GN":
            return "Guinea";
        case "GY":
            return "Guyana";
        case "HT":
            return "Haiti";
        case "HW":
            return "Hawaii";
        case "HN":
            return "Honduras";
        case "HK":
            return "Hong Kong";
        case "HU":
            return "Hungary";
        case "IS":
            return "Iceland";
        case "IN":
            return "India";
        case "ID":
            return "Indonesia";
        case "IA":
            return "Iran";
        case "IQ":
            return "Iraq";
        case "IR":
            return "Ireland";
        case "IM":
            return "Isle of Man";
        case "IL":
            return "Israel";
        case "IT":
            return "Italy";
        case "JM":
            return "Jamaica";
        case "JP":
            return "Japan";
        case "JO":
            return "Jordan";
        case "KZ":
            return "Kazakhstan";
        case "KE":
            return "Kenya";
        case "KI":
            return "Kiribati";
        case "NK":
            return "Korea North";
        case "KS":
            return "Korea South";
        case "KW":
            return "Kuwait";
        case "KG":
            return "Kyrgyzstan";
        case "LA":
            return "Laos";
        case "LV":
            return "Latvia";
        case "LB":
            return "Lebanon";
        case "LS":
            return "Lesotho";
        case "LR":
            return "Liberia";
        case "LY":
            return "Libya";
        case "LI":
            return "Liechtenstein";
        case "LT":
            return "Lithuania";
        case "LU":
            return "Luxembourg";
        case "MO":
            return "Macau";
        case "MK":
            return "Macedonia";
        case "MG":
            return "Madagascar";
        case "MY":
            return "Malaysia";
        case "MW":
            return "Malawi";
        case "MV":
            return "Maldives";
        case "ML":
            return "Mali";
        case "MT":
            return "Malta";
        case "MH":
            return "Marshall Islands";
        case "MQ":
            return "Martinique";
        case "MR":
            return "Mauritania";
        case "MU":
            return "Mauritius";
        case "ME":
            return "Mayotte";
        case "MX":
            return "Mexico";
        case "MI":
            return "Midway Islands";
        case "MD":
            return "Moldova";
        case "MC":
            return "Monaco";
        case "MN":
            return "Mongolia";
        case "MS":
            return "Montserrat";
        case "MA":
            return "Morocco";
        case "MZ":
            return "Mozambique";
        case "MM":
            return "Myanmar";
        case "NA":
            return "Nambia";
        case "NU":
            return "Nauru";
        case "NP":
            return "Nepal";
        case "AN":
            return "Netherland Antilles";
        case "NL":
            return "Netherlands (Holland, Europe)";
        case "NV":
            return "Nevis";
        case "NC":
            return "New Caledonia";
        case "NZ":
            return "New Zealand";
        case "NI":
            return "Nicaragua";
        case "NE":
            return "Niger";
        case "NG":
            return "Nigeria";
        case "NW":
            return "Niue";
        case "NF":
            return "Norfolk Island";
        case "NO":
            return "Norway";
        case "OM":
            return "Oman";
        case "PK":
            return "Pakistan";
        case "PW":
            return "Palau Island";
        case "PS":
            return "Palestine";
        case "PA":
            return "Panama";
        case "PG":
            return "Papua New Guinea";
        case "PY":
            return "Paraguay";
        case "PE":
            return "Peru";
        case "PH":
            return "Philippines";
        case "PO":
            return "Pitcairn Island";
        case "PL":
            return "Poland";
        case "PT":
            return "Portugal";
        case "PR":
            return "Puerto Rico";
        case "QA":
            return "Qatar";
        case "ME":
            return "Republic of Montenegro";
        case "RS":
            return "Republic of Serbia";
        case "RE":
            return "Reunion";
        case "RO":
            return "Romania";
        case "RU":
            return "Russia";
        case "RW":
            return "Rwanda";
        case "NT":
            return "St Barthelemy";
        case "EU":
            return "St Eustatius";
        case "HE":
            return "St Helena";
        case "KN":
            return "St Kitts-Nevis";
        case "LC":
            return "St Lucia";
        case "MB":
            return "St Maarten";
        case "PM":
            return "St Pierre &amp; Miquelon";
        case "VC":
            return "St Vincent &amp; Grenadines";
        case "SP":
            return "Saipan";
        case "SO":
            return "Samoa";
        case "AS":
            return "Samoa American";
        case "SM":
            return "San Marino";
        case "ST":
            return "Sao Tome &amp; Principe";
        case "SA":
            return "Saudi Arabia";
        case "SN":
            return "Senegal";
        case "RS":
            return "Serbia";
        case "SC":
            return "Seychelles";
        case "SL":
            return "Sierra Leone";
        case "SG":
            return "Singapore";
        case "SK":
            return "Slovakia";
        case "SI":
            return "Slovenia";
        case "SB":
            return "Solomon Islands";
        case "OI":
            return "Somalia";
        case "ZA":
            return "South Africa";
        case "ES":
            return "Spain";
        case "LK":
            return "Sri Lanka";
        case "SD":
            return "Sudan";
        case "SR":
            return "Suriname";
        case "SZ":
            return "Swaziland";
        case "SE":
            return "Sweden";
        case "CH":
            return "Switzerland";
        case "SY":
            return "Syria";
        case "TA":
            return "Tahiti";
        case "TW":
            return "Taiwan";
        case "TJ":
            return "Tajikistan";
        case "TZ":
            return "Tanzania";
        case "TH":
            return "Thailand";
        case "TG":
            return "Togo";
        case "TK":
            return "Tokelau";
        case "TO":
            return "Tonga";
        case "TT":
            return "Trinidad &amp; Tobago";
        case "TN":
            return "Tunisia";
        case "TR":
            return "Turkey";
        case "TU":
            return "Turkmenistan";
        case "TC":
            return "Turks &amp; Caicos Is";
        case "TV":
            return "Tuvalu";
        case "UG":
            return "Uganda";
        case "UA":
            return "Ukraine";
        case "AE":
            return "United Arab Emirates";
        case "GB":
            return "United Kingdom";
        case "US":
            return "United States of America";
        case "UY":
            return "Uruguay";
        case "UZ":
            return "Uzbekistan";
        case "VU":
            return "Vanuatu";
        case "VS":
            return "Vatican City State";
        case "VE":
            return "Venezuela";
        case "VN":
            return "Vietnam";
        case "VB":
            return "Virgin Islands (Brit)";
        case "VA":
            return "Virgin Islands (USA)";
        case "WK":
            return "Wake Island";
        case "WF":
            return "Wallis &amp; Futana Is";
        case "YE":
            return "Yemen";
        case "ZR":
            return "Zaire";
        case "ZM":
            return "Zambia";
        case "ZW":
            return "Zimbabwe";
        default:
            return "Country";
    }
}


function exif_get_float($value)
{
    $pos = strpos($value, '/');
    if ($pos === FALSE) return (float)$value;
    $a = (float)substr($value, 0, $pos);
    $b = (float)substr($value, $pos + 1);
    return ($b == 0) ? ($a) : ($a / $b);
}

function exif_get_shutter(&$exif)
{
    if (!isset($exif['ShutterSpeedValue'])) return FALSE;
    $apex = exif_get_float($exif['ShutterSpeedValue']);
    $shutter = pow(2, -$apex);
    if ($shutter == 0) return FALSE;
    if ($shutter >= 1) return round($shutter) . ' secs';
    return '1/' . round(1 / $shutter) . ' secs';
}

function exif_get_fstop(&$exif)
{
    if (!isset($exif['ApertureValue'])) return FALSE;
    $apex = exif_get_float($exif['ApertureValue']);
    $fstop = pow(2, $apex / 2);
    if ($fstop == 0) return FALSE;
    return 'f/' . round($fstop, 1);
}

function exif_focal($exif)
{
    $exif = explode('/', $exif);
    return round($exif[0] / $exif[1]);
}

function langDecode($lang)
{
    switch ($lang) {
        case "af":
            return "kaans";
        case "ak":
            return "Akan";
        case "sq":
            return "Albanian";
        case "am":
            return "Amharic";
        case "ar":
            return "Arabic";
        case "hy":
            return "Armenian";
        case "az":
            return "Azerbaijani";
        case "eu":
            return "Basque";
        case "be":
            return "Belarusian";
        case "bem":
            return "Bemba";
        case "bn":
            return "Bengali";
        case "bh":
            return "Bihari";
        case "xx-bork":
            return "Bork, bork, bork!";
        case "bs":
            return "Bosnian";
        case "br":
            return "Breton";
        case "bg":
            return "Bulgarian";
        case "km":
            return "Cambodian";
        case "ca":
            return "Catalan";
        case "chr":
            return "Cherokee";
        case "ny":
            return "Chichewa";
        case "zh-CN":
            return "Chinese (Simplified)";
        case "zh-TW":
            return "Chinese (Traditional)";
        case "co":
            return "Corsican";
        case "hr":
            return "Croatian";
        case "cs":
            return "Czech";
        case "da":
            return "Danish";
        case "nl":
            return "Dutch";
        case "xx-elmer":
            return "Elmer Fudd";
        case "en":
            return "English";
        case "eo":
            return "Esperanto";
        case "et":
            return "Estonian";
        case "ee":
            return "Ewe";
        case "fo":
            return "Faroese";
        case "tl":
            return "Filipino";
        case "fi":
            return "Finnish";
        case "fr":
            return "French";
        case "fy":
            return "Frisian";
        case "gaa":
            return "Ga";
        case "gl":
            return "Galician";
        case "ka":
            return "Georgian";
        case "de":
            return "German";
        case "el":
            return "Greek";
        case "gn":
            return "Guarani";
        case "gu":
            return "Gujarati";
        case "xx-hacker":
            return "Hacker";
        case "ht":
            return "Haitian Creole";
        case "ha":
            return "Hausa";
        case "haw":
            return "Hawaiian";
        case "iw":
            return "Hebrew";
        case "hi":
            return "Hindi";
        case "hu":
            return "Hungarian";
        case "is":
            return "Icelandic";
        case "ig":
            return "Igbo";
        case "id":
            return "Indonesian";
        case "ia":
            return "Interlingua";
        case "ga":
            return "Irish";
        case "it":
            return "Italian";
        case "ja":
            return "Japanese";
        case "jw":
            return "Javanese";
        case "kn":
            return "Kannada";
        case "kk":
            return "Kazakh";
        case "rw":
            return "Kinyarwanda";
        case "rn":
            return "Kirundi";
        case "xx-klingon":
            return "Klingon";
        case "kg":
            return "Kongo";
        case "ko":
            return "Korean";
        case "kri":
            return "Krio (Sierra Leone)";
        case "ku":
            return "Kurdish";
        case "ckb":
            return "Kurdish (Soranî)";
        case "ky":
            return "Kyrgyz";
        case "lo":
            return "Laothian";
        case "la":
            return "Latin";
        case "lv":
            return "Latvian";
        case "ln":
            return "Lingala";
        case "lt":
            return "Lithuanian";
        case "loz":
            return "Lozi";
        case "lg":
            return "Luganda";
        case "ach":
            return "Luo";
        case "mk":
            return "Macedonian";
        case "mg":
            return "Malagasy";
        case "ms":
            return "Malay";
        case "ml":
            return "Malayalam";
        case "mt":
            return "Maltese";
        case "mi":
            return "Maori";
        case "mr":
            return "Marathi";
        case "mfe":
            return "Mauritian Creole";
        case "mo":
            return "Moldavian";
        case "mn":
            return "Mongolian";
        case "sr-ME":
            return "Montenegrin";
        case "ne":
            return "Nepali";
        case "pcm":
            return "Nigerian Pidgin";
        case "nso":
            return "Northern Sotho";
        case "no":
            return "Norwegian";
        case "nn":
            return "Norwegian (Nynorsk)";
        case "oc":
            return "Occitan";
        case "or":
            return "Oriya";
        case "om":
            return "Oromo";
        case "ps":
            return "Pashto";
        case "fa":
            return "Persian";
        case "xx-pirate":
            return "Pirate";
        case "pl":
            return "Polish";
        case "pt-BR":
            return "Portuguese (Brazil)";
        case "pt-PT":
            return "Portuguese (Portugal)";
        case "pa":
            return "Punjabi";
        case "qu":
            return "Quechua";
        case "ro":
            return "Romanian";
        case "rm":
            return "Romansh";
        case "nyn":
            return "Runyakitara";
        case "ru":
            return "Russian";
        case "gd":
            return "Scots Gaelic";
        case "sr":
            return "Serbian";
        case "sh":
            return "Serbo-Croatian";
        case "st":
            return "Sesotho";
        case "tn":
            return "Setswana";
        case "crs":
            return "Seychellois Creole";
        case "sn":
            return "Shona";
        case "sd":
            return "Sindhi";
        case "si":
            return "Sinhalese";
        case "sk":
            return "Slovak";
        case "sl":
            return "Slovenian";
        case "so":
            return "Somali";
        case "es":
            return "Spanish";
        case "es-419":
            return "Spanish (Latin American)";
        case "su":
            return "Sundanese";
        case "sw":
            return "Swahili";
        case "sv":
            return "Swedish";
        case "tg":
            return "Tajik";
        case "ta":
            return "Tamil";
        case "tt":
            return "Tatar";
        case "te":
            return "Telugu";
        case "th":
            return "Thai";
        case "ti":
            return "Tigrinya";
        case "to":
            return "Tonga";
        case "lua":
            return "Tshiluba";
        case "tum":
            return "Tumbuka";
        case "tr":
            return "Turkish";
        case "tk":
            return "Turkmen";
        case "tw":
            return "Twi";
        case "ug":
            return "Uighur";
        case "uk":
            return "Ukrainian";
        case "ur":
            return "Urdu";
        case "uz":
            return "Uzbek";
        case "vi":
            return "Vietnamese";
        case "cy":
            return "Welsh";
        case "wo":
            return "Wolof";
        case "xh":
            return "Xhosa";
        case "yi":
            return "Yiddish";
        case "yo":
            return "Yoruba";
        case "zu":
            return "Zulu";
        default:
            return "English";
    }
}

function languageArray()
{
    return array('af', 'ak', 'sq', 'am', 'ar', 'hy', 'az', 'eu', 'be', 'bem', 'bn', 'bh', 'xx-bork', 'bs', 'br', 'bg', 'km', 'ca', 'chr', 'ny', 'zh-CN', 'zh-TW', 'co', 'hr', 'cs', 'da', 'nl', 'xx-elmer', 'en', 'eo', 'et', 'ee', 'fo', 'tl', 'fi', 'fr', 'fy', 'gaa', 'gl', 'ka', 'de', 'el', 'gn', 'gu', 'xx-hacker', 'ht', 'ha', 'haw', 'iw', 'hi', 'hu', 'is', 'ig', 'id', 'ia', 'ga', 'it', 'ja', 'jw', 'kn', 'kk', 'rw', 'rn', 'xx-klingon', 'kg', 'ko', 'kri', 'ku', 'ckb', 'ky', 'lo', 'la', 'lv', 'ln', 'lt', 'loz', 'lg', 'ach', 'mk', 'mg', 'ms', 'ml', 'mt', 'mi', 'mr', 'mfe', 'mo', 'mn', 'sr-ME', 'ne', 'pcm', 'nso', 'no', 'nn', 'oc', 'or', 'om', 'ps', 'fa', 'xx-pirate', 'pl', 'pt-BR', 'pt-PT', 'pa', 'qu', 'ro', 'rm', 'nyn', 'ru', 'gd', 'sr', 'sh', 'st', 'tn', 'crs', 'sn', 'sd', 'si', 'sk', 'sl', 'so', 'es', 'es-419', 'su', 'sw', 'sv', 'tg', 'ta', 'tt', 'te', 'th', 'ti', 'to', 'lua', 'tum', 'tr', 'tk', 'tw', 'ug', 'uk', 'ur', 'uz', 'vi', 'cy', 'wo', 'xh', 'yi', 'yo', 'zu');
}