var FacebookLoginPlugin = {
	"checkLoginState" : function(){
		FB.getLoginStatus(function(response) {
			FacebookLoginPlugin.statusChangeCallback(response);
		});
	},
	"encodeHTMLForm" : function(data){
		var params = [];

		for(var name in data){
			var value = data[name];
			var param = encodeURIComponent(name) + '=' + encodeURIComponent(value);

			params.push(param);
		}

		return params.join('&').replace(/%20/g, '+');
	},
	statusChangeCallback : function(response){
		if(response.status === 'connected'){
			var token = response.authResponse.accessToken;

			if(token.length > 0){
				var xhr = new XMLHttpRequest();

				var pathname = location.pathname;
				if(pathname.indexOf("/login")) {
					pathname = pathname.replace("/login", "");
				}

				forms = document.querySelectorAll('[name=soy2_token]');
				var soy2_token = (forms[0].value) ? forms[0].value : "";
				var url = location.origin + pathname + "?soyshop_download=facebook_login&soy2_token=" + soy2_token;
				var data = {"facebook_id": response.authResponse.userID, "access_token": token};

				xhr.open('POST', url);
				xhr.setRequestHeader('content-type', 'application/x-www-form-urlencoded');
				xhr.send(FacebookLoginPlugin.encodeHTMLForm(data));

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
						}else{
							alert(res.message);
						}

						// @ToDo 失敗した場合はどうしよう？
					}
				});

				//タイムアウトした時対策
				xhr.addEventListener("timeout", function(){
					// @ToDo どうしよう？
				});
			}
		}
	}
}
