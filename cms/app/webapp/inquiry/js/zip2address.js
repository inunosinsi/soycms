/*
 * zip2address.js
 *
 * Copyright (c) 2010 Kazuhito Hokamura
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 *
 * @author   Kazuhito Hokamura (http://webtech-walker.com/)
 * @version  0.0.1
 * @edit Tsuyoshi Saito (https://github.com/inunosinsi)
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


(function(){
	if(document.querySelector(".search-btn")){
		document.querySelector(".search-btn").addEventListener("click", function(e){
			if(document.querySelector(".input-zip")){
				var zip = inquiry_convert_zipcode(document.querySelector(".input-zip").value);
			}else{
				var zip = document.querySelector(".input-zip1").value + document.querySelector(".input-zip2").value;
			}


			zip2address(zip, function(address) {
				if (address) {
					document.querySelector(".input-pref").value = prefs[address.pref - 1];
					document.querySelector(".input-city").value = address.city;
					document.querySelector(".input-town").value = address.town;
				} else {
					alert('正しい郵便番号を入力して下さい。');
				}
			});
		});
	//自動検索モード
	}else{
		if(document.querySelector(".input-zip")){
			document.querySelector(".input-zip").addEventListener("keyup", function(e){
				inquiry_search_address();
			});
		}

		if(document.querySelector(".input-zip1")){
			document.querySelector(".input-zip1").addEventListener("keyup", function(e){
				inquiry_search_address();
			});
		}

		if(document.querySelector(".input-zip2")){
			document.querySelector(".input-zip2").addEventListener("keyup", function(e){
				inquiry_search_address();
			});
		}
	}
}());

function inquiry_search_address(){
	if((document.querySelector(".input-zip"))){
		var zip = (document.querySelector(".input-zip")) ? inquiry_convert_zipcode(document.querySelector(".input-zip").value) : "";

		if(zip.length === 7){
			inquiry_zip2address(zip);
		}else{
			inquiry_insert_empty_values();
		}
	}else if(document.querySelector(".input-zip1")){
		var zip1 = (document.querySelector(".input-zip1")) ? document.querySelector(".input-zip1").value : "";
		var zip2 = (document.querySelector(".input-zip2")) ? document.querySelector(".input-zip2").value : "";

		if(zip1.length === 3 && zip2.length === 4){
			inquiry_zip2address(zip1 + zip2);
		}else{
			inquiry_insert_empty_values();
		}
	}
}

function inquiry_zip2address(zip){
	zip2address(zip, function(address) {
		if (address) {
			document.querySelector(".input-pref").value = prefs[address.pref - 1];
			document.querySelector(".input-city").value = address.city;
			document.querySelector(".input-town").value = address.town;
		}
	});
}

function inquiry_insert_empty_values(){
	document.querySelector(".input-pref").value = "";
	document.querySelector(".input-city").value = "";
	zdocument.querySelector(".input-town").value = "";
}

function inquiry_convert_zipcode(zip){
	if(zip.length === 0) return "";
	if(zip.indexOf("-") >= 0) zip = zip.replace("-", "");
	if(zip.indexOf("ー") >= 0) zip = zip.replace("ー", "");
	return zip;
}
