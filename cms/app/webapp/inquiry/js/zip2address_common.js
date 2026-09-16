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

function click_search_button(columnId){
	if(document.querySelector(".input-zip-"+columnId)){
		var zip = inquiry_convert_zipcode(document.querySelector(".input-zip-"+columnId).value);
	}else{
		var zip = document.querySelector(".input-zip1-"+columnId).value + document.querySelector(".input-zip2-"+columnId).value;
	}

	inquiry_zip2address(columnId, zip, true);
}

function execute_auto_search(columnId){
	if(document.querySelector(".input-zip-"+columnId)){
		document.querySelector(".input-zip-"+columnId).addEventListener("keyup", function(e){
			inquiry_search_address(columnId);
		});
	}

	if(document.querySelector(".input-zip1-"+columnId)){
		document.querySelector(".input-zip1-"+columnId).addEventListener("keyup", function(e){
			inquiry_search_address(columnId);
		});
	}

	if(document.querySelector(".input-zip2-"+columnId)){
		document.querySelector(".input-zip2-"+columnId).addEventListener("keyup", function(e){
			inquiry_search_address(columnId);
		});
	}
}

function inquiry_search_address(columnId){
	if((document.querySelector(".input-zip-"+columnId))){
		var zip = (document.querySelector(".input-zip-"+columnId)) ? inquiry_convert_zipcode(document.querySelector(".input-zip-"+columnId).value) : "";

		if(zip.length === 7){
			inquiry_zip2address(columnId, zip, false);
		}else{
			inquiry_insert_empty_values();
		}
	}else if(document.querySelector(".input-zip1-"+columnId)){
		var zip1 = (document.querySelector(".input-zip1-"+columnId)) ? document.querySelector(".input-zip1-"+columnId).value : "";
		var zip2 = (document.querySelector(".input-zip2-"+columnId)) ? document.querySelector(".input-zip2-"+columnId).value : "";

		if(zip1.length === 3 && zip2.length === 4){
			inquiry_zip2address(columnId, zip1 + zip2, false);
		}else{
			inquiry_insert_empty_values();
		}
	}
}

function inquiry_zip2address(columnId, zip, isAlert){
	zip2address(zip, function(address) {
		if (address) {
			if(document.querySelector(".input-pref-"+columnId)){
				document.querySelector(".input-pref-"+columnId).value = prefs[address.pref - 1];
			}
			if(document.querySelector(".input-city-"+columnId)){
				document.querySelector(".input-city-"+columnId).value = address.city + address.town;
			}
			if(document.querySelector(".input-town-"+columnId)){
				document.querySelector(".input-town-"+columnId).value = "";
			}
		} else {
			if(isAlert){
				alert('正しい郵便番号を入力して下さい。');
			}
		}
	});
}

function inquiry_insert_empty_values(columnId){
	if(document.querySelector(".input-pref-"+columnId)){
		document.querySelector(".input-pref-"+columnId).value = "";
	}
	if(document.querySelector(".input-city-"+columnId)){
		document.querySelector(".input-city-"+columnId).value = "";
	}
	if(document.querySelector(".input-town-"+columnId)){
		document.querySelector(".input-town-"+columnId).value = "";
	}
}

function inquiry_convert_zipcode(zip){
	if(zip.length === 0) return "";
	if(zip.indexOf("-") >= 0) zip = zip.replace("-", "");
	if(zip.indexOf("ー") >= 0) zip = zip.replace("ー", "");
	return zip;
}
