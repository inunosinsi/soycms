// 公開鍵を登録し、起点となるオブジェクトを取得します
var elements = payjp.elements();

// 入力フォームを分解して管理・配置できます
var numberElement = elements.create('cardNumber')
var expiryElement = elements.create('cardExpiry')
var cvcElement = elements.create('cardCvc')
numberElement.mount('#number-form')
expiryElement.mount('#expiry-form')
cvcElement.mount('#cvc-form')

function onSubmit(event) {
	payjp.createToken(numberElement).then(function(r) {
		if(r.error){
			document.getElementById('error').innerText = r.error.message;
		}else{
			var ok = true;

			if(is_three_d_secure){
				if(r.card.three_d_secure_status == "attempted"){
					ok = false;
					document.getElementById('error').innerText = "アテンプト取引は非対応です。";
				}
			}

			if(ok){
				document.getElementById('token').value = r.id;
				document.getElementById("payjp_post_form").submit();
			}
		}
	});
}
