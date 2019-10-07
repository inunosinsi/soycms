function onSubmit(event){
	var formElement = this;
	//ここに送信ボタンの二重押下制御処理を記載
	grecaptcha.ready(function() {
		try{
			grecaptcha.execute("##SITE_KEY##", {action: 'homepage'})
			.then(function(token) {
				//トークン取得が成功した場合
				//ここに、formのhiddenなどにtokenを格納する処理を記載
				//console.log(token);
				var form = document.getElementById("soy_inquiry_form");
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

				form.submit();
			}, function(reason) {
				//トークン取得が失敗した場合（then関数のエラー処理、現状reasonは返されない）
				//ここにエラー処理を記載（メッセージを表示し送信ボタンの押下制御を戻す）
			});
		}catch(e){
			//ここにエラー処理を記載（メッセージを表示し送信ボタンの押下制御を戻す）
			alert("failed");
		}
	});
	event.preventDefault();
}
window.addEventListener('load', function() {
	//var forms = document.forms;
	var form = document.getElementById("soy_inquiry_form");
	if(form && form.send){
		form.send.addEventListener('click', onSubmit, false);
	}
})
