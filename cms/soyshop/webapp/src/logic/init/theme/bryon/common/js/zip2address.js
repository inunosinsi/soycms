/*
 * zip2address.js
 *
 * Copyright (c) 2010 Kazuhito Hokamura
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 *
 * @author   Kazuhito Hokamura (http://webtech-walker.com/)
 * @version  0.0.1
 *
 * This script inspired by jQuery.zip2addr. (https://github.com/kotarok/jQuery.zip2addr)
 * Thank you for kotarok.
 *
 */

(function(window) {

var d = document,
	api_url = '//www.google.com/transliterate?langpair=ja-Hira|ja';
	prefs = [
		'北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県',
		'茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県',
		'新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県',
		'静岡県', '愛知県', '三重県', '滋賀県', '京都府', '大阪府', '兵庫県',
		'奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県',
		'徳島県', '香川県', '愛媛県', '高知県', '福岡県', '佐賀県', '長崎県',
		'熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'
	];

//住所検索用の正規表現
var reg_text = "(大和郡山市|蒲郡市|小郡市|郡上市|杵島郡大町町|佐波郡玉村町|(?:[^市]*?|余市|高市)郡.+?[町村]|(?:石狩|伊達|八戸|盛岡|奥州|南相馬|上越|姫路|宇陀|黒部|小諸|富山|岩国|周南|佐伯|西海)市|.*?[^0-9一二三四五六七八九十上下]区|四日市市|廿日市市|.+?市|.+?町|.+?村)(.*?)([0-9-]*?)$";

var zip2address = function(zip, callback) {
	var jsonp_callback = 'zip2address_jsonp' + (new Date()).getTime(),
		url = api_url + '&jsonp=' + jsonp_callback,
		head = d.getElementsByTagName('head')[0],
		script = d.createElement('script');

	// jsonp callback function
	window[ jsonp_callback ] = function(data) {
		var address = {};
		address.all = data[0][1][0];

		// check match pref
		for (var i = 0, l = prefs.length; i < l; i++) {
			var pref = prefs[i];
			if (address.all.indexOf(pref) === 0) {
				address.pref = i+1;
				var address_text = address.all.replace(pref, '');
				var reg = new RegExp(reg_text);
				var res = address_text.match(reg);
				address.city = res[1];
				address.town = res[2];
				break;
			}
		}
		// no match address
		if (!address.pref && !address.city && !address.town) {
			address = undefined;
		}

		// callback function
		callback(address);

		// cleaning
		try {
			delete window[ jsonp_callback ];
		}
		catch (e) {}
		head.removeChild(script);
	};

	// check zip formtting
	if (/^\d{7}$/.test(zip)) {
		zip = zip.toString().replace(/(\d{3})(\d{4})/, '$1-$2');
	}
	/*else if (!/^\d{3}-\d{4}$/.test(zip)) {
		callback(undefined);
	}*/

	// call api by jsonp
	url += '&text=' + encodeURIComponent(zip);
	script.setAttribute('src', url);
	head.appendChild(script);
};

// export function
window.zip2address = zip2address;

})(window);


$(function(){
	var input = $('.input-zip');
	
	$('.search-btn').click(function(e){	
		zip2address(input.val(), function(address) {
			if (address) {
				$('.input-pref').val(address.pref);
				$('.input-city').val(address.city);
				$('.input-town').val(address.town);
			}
			else {
				alert('正しい郵便番号を入力して下さい。');
			}
		});
	}).css({ cursor : 'pointer' });
	
	var sendInput = $('.input-send-zip');
	
	$('.send-search-btn').click(function(e){	
		zip2address(sendInput.val(), function(address) {
			if (address) {
				$('.input-send-pref').val(address.pref);
				$('.input-send-city').val(address.city);
				$('.input-send-town').val(address.town);
			}
			else {
				alert('正しい郵便番号を入力して下さい。');
			}
		});
	}).css({ cursor : 'pointer' });
});