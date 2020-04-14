setTimeout(function(){
    var head = document.getElementsByTagName('head')[0];
    var script = document.createElement('script');
    script.type = 'text/javascript';
    script.onload = function() {
		grecaptcha.ready(function() {
			try{
	            grecaptcha.execute('##SITE_KEY##', {action: 'homepage'}).then(function(token) {
					var form = document.getElementById("soy_inquiry_form");
					if(form && form.send){
						var sendIpt = document.createElement('input');
		 				sendIpt.type = "hidden";
		 				sendIpt.name = "send";
		 				sendIpt.value = 1;
		 				form.appendChild(sendIpt);

		 				//google reCAPTCHAを追加
		 				var reCapIpt = document.createElement('input');
		 				reCapIpt.type = "hidden";
		 				reCapIpt.name = "google_recaptcha";
		 				reCapIpt.value = token;
		 				form.appendChild(reCapIpt);
					}

	            });
			}catch(e){
				//ここにエラー処理を記載（メッセージを表示し送信ボタンの押下制御を戻す）
				alert("failed");
			}
        });
    }
    script.src = "https://www.google.com/recaptcha/api.js?render=##SITE_KEY##";
    head.appendChild(script);
}, 2000);
