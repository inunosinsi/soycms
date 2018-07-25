function operateBefore(){
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
			var errMsg = (errMsgList[response.error.code]) ? errMsgList[response.error.code] : response.error.message;
			var errMsgArea = document.getElementById("error_message_area");
			errMsgArea.innerHTML = "失敗しました：" + errMsg;
			errMsgArea.style.display = "block";
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
