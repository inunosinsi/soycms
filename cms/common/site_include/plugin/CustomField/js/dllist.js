var CustomFieldDlListField = {
	add : function(fieldId){
		$div = $("<div>");
		$div.prop("class", "form-inline custom_field_" + fieldId);

		$ipt = $("<input>");
		$ipt.prop("type", "text");
		$ipt.prop("name", "custom_field[" + fieldId + "][label][]");
		$ipt.prop("class", "form-control");

		$div.html($ipt);

        $ipt = $("<input>");
		$ipt.prop("type", "text");
		$ipt.prop("name", "custom_field[" + fieldId + "][value][]");
		$ipt.prop("class", "form-control");

		$div.append($ipt);
		//console.log($div);

		var $forms = $(".custom_field_" + fieldId);
		$lastForm = $forms[$forms.length - 1];
		$lastForm.after($div[0]);
	}
}

function dllist_field_move_up(formId, idx){
	if(idx > 0){	//idxが0の場合はなにもしない
		var up = idx - 1;
		var types = ["label", "value"];
		for(var i = 0; i <= types.length; i++){
			var l = types[i];
			var tmp = $("." + formId + "_" + up + "_" + l).val();
			$("." + formId + "_" + up + "_" + l).val($("." + formId + "_" + idx + "_" + l).val());
			$("." + formId + "_" + idx + "_" + l).val(tmp);
		}
	}
}