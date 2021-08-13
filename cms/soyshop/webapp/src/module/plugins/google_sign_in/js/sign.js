var GoogleSignInPlugin = {
	"encodeHTMLForm" : function(data){
		var params = [];

		for(var name in data){
			var value = data[name];
			var param = encodeURIComponent(name) + '=' + encodeURIComponent(value);

			params.push(param);
		}

		return params.join('&').replace(/%20/g, '+');
	}
}

/**
 * @param CredentialResponse
 * https://developers.google.com/identity/gsi/web/reference/js-reference#CredentialResponse
 */
function onSignIn(credentialResponse) {
	//https://developers.google.com/identity/gsi/web/reference/js-reference#CredentialResponse Json Web Tokenをbase64デコードで読めるようにする
	var json = parseJwt(credentialResponse.credential);

	//取得できればAjaxで登録とログインを試みる json.subでgoogle_id
	if(json.sub.length > 0){
		var xhr = new XMLHttpRequest();

		var pathname = location.pathname;
		if(pathname.indexOf("/login")) {
			pathname = pathname.replace("/login", "");
		}

		forms = document.querySelectorAll('[name=soy2_token]');
		var token = (forms[0].value) ? forms[0].value : "";
		var url = location.origin + pathname + "?soyshop_download=google_sign_in&soy2_token=" + token;
		var data = {"google_id": json.sub, "name": json.name, "mail":json.email};

		xhr.open('POST', url);
		xhr.setRequestHeader('content-type', 'application/x-www-form-urlencoded');
		xhr.send(GoogleSignInPlugin.encodeHTMLForm(data));

		xhr.addEventListener("load", function(){
			var resp = xhr.response;
			if(resp){
				var res = JSON.parse(resp);
				if(res.result == 1){
					if(location.search.length > 0 && location.search.indexOf("?r=") >= 0){
						location.href = location.search.replace("?r=", "");
					}else{
						location.href = location.pathname;
					}
				// 仮登録モードの場合は他の場所に飛ばす result == 2で返ってくる
				} else if (res.result == 2){
					location.href = location.origin + pathname + "/register/tmp";
				// 失敗した場合はどうしよう？
				} else {
					alert("resultの取得に失敗しました");
				}
			}
		});

		//タイムアウトした時対策
		xhr.addEventListener("timeout", function(){
			// @ToDo どうしよう？
			var resp = xhr.response;
			if(resp){
				var res = JSON.parse(resp);
				console.log(res);
			}else{
				console.log(resp);
			}
			alert("タイムアウトしました");
		});
	}
}

function onFailure(err){
	console.log(err);
}

function parseJwt(tk) {
    var base64Url = tk.split('.')[1];
    var base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
    var jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
        return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
    }).join(''));
    return JSON.parse(jsonPayload);
};
