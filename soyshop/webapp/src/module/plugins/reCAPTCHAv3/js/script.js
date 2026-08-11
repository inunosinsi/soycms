function reCAPTCHAOnClick(e) {
	//e.preventDefault();
	grecaptcha.ready(function() {
		grecaptcha.execute('##SITE_KEY##', {action: 'submit'}).then(function(token) {
			let form = document.getElementById("google_recaptcha_form");
			form.setAttribute("onsubmit", "");
			let sendIpt = document.createElement('input');
 			sendIpt.type = "hidden";
 			sendIpt.name = "next";
 			sendIpt.value = 1;
 			form.appendChild(sendIpt);

 			//google reCAPTCHAを追加
 			let reCapIpt = document.createElement('input');
 			reCapIpt.type = "hidden";
 			reCapIpt.name = "order_confirm_module[reCAPTCHAv3]";
 			//reCapIpt.name = "google_recaptcha";
 			reCapIpt.value = token;
 			form.appendChild(reCapIpt);

 			form.submit();
		});
	});
}

(function(){
	let btns = document.getElementsByName("next");
	let btn = btns[0];
	btn.setAttribute("onclick", "reCAPTCHAOnClick(this)");

	let forms = document.forms;
	if(forms.length > 0){
		for(let i = 0; i < forms.length; i++){
			let form = forms[i];
			if(form.action.indexOf("/##CART_URI##")){
				form.setAttribute("onsubmit", "return false;");
				form.setAttribute("id", "google_recaptcha_form");
			}
		}
	}
})();
