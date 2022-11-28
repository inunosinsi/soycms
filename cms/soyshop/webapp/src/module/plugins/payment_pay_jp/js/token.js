const limitCnt = 10;	//上限
var tryCnt = 0;			//挑戦回数

function operateBefore(){
	//ボタンを押せないようにする
	document.getElementById("credit_button").disabled = true;

	//Loadingの表示
	document.getElementById("button_area").visibility = "hidden";
	document.getElementById("loading_area").visibility = "visible";
	console.log(document.getElementById("loading_area").visibility);

	var cardForms = [];
	var cardNum = "";
	for (var i = 0; i < 4; i++) {
		cardForm = document.getElementById("card_" + i);
		cardNum += cardForm.value;
		cardForms.push(cardForm);
	}

	var cvcForm = document.getElementById("cvc");
	var monthForm = document.getElementById("month");
	var yearForm = document.getElementById("year");
	var nameForm = document.getElementById("name");

	var card = {
		number: cardNum,
		cvc: cvcForm.value,
		exp_month: monthForm.value,
		exp_year: "20" + yearForm.value,
		name: nameForm.value
	};

	Payjp.createToken(card, function(s, response) {
		if (response.error) {
			//一時的なロックをかけたい
			if(++tryCnt >= limitCnt){
				var url = document.querySelector("#charge_form").action;
				var xhr = new XMLHttpRequest();
				xhr.open("GET", url + "?soyshop_ban=payment_pay_jp");
				xhr.send();
				xhr.addEventListener("load", function(){
					//リダイレクト
					if(xhr.status == 200){
						//3秒後にリダイレクト
						setTimeout(function(){location.href = location.href}, 3000);
					}
				});
			}
			var errMsg = (errMsgList[response.error.code]) ? errMsgList[response.error.code] : response.error.message;
			var errMsgArea = document.getElementById("error_message_area");
			errMsgArea.innerHTML = "失敗しました：" + errMsg;
			errMsgArea.style.display = "block";

			//ボタンの禁止を解除
			document.getElementById("credit_button").disabled = false;

			//ローディングの解除
			document.getElementById("button_area").visibility = "visible";
			document.getElementById("loading_area").visibility = "hidden";
		} else {
			var token = response.id;

			document.getElementById("card_token").value = token;

			//値を空にする
			for (var i = 0; i < 4; i++) {
				cardForms[i].required = "";
				cardForms[i].value = "";
			}

			cvcForm.required = "";
			monthForm.required = "";
			yearForm.required = "";
			nameForm.required = "";

			cvcForm.value = "";
			monthForm.value = "";
			yearForm.value = "";
			nameForm.value = "";

			document.getElementById("charge_form").submit();
		}
	});

	return false;
}
