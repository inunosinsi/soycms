var CustomFieldDlListMultiField = {
	add : function(fieldId){
		$div = $("<div>");
		$div.prop("class", "form-inline custom_field_" + fieldId);

		$textarea = $("<textarea>");
		$textarea.prop("name", "custom_field[" + fieldId + "][label][]");
		$textarea.prop("style", " width:30%;");
		$textarea.prop("class", "form-control");

		$div.html($textarea);

        $textarea = $("<textarea>");
		$textarea.prop("name", "custom_field[" + fieldId + "][value][]");
		$textarea.prop("style", " width:30%;");
		$textarea.prop("class", "form-control");

		$div.append($textarea);
		//console.log($div);

		var $forms = $(".custom_field_" + fieldId);
		$lastForm = $forms[$forms.length - 1];
		$lastForm.after($div[0]);
	}
}

function dllist_multi_field_move_up(formId, idx){
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

function dllist_multi_field_delete(formId, idx){
	var types = ["label", "value"];
	for(var i = 0; i <= types.length; i++){
		$("." + formId + "_" + idx + "_" + types[i]).val("");
	}
	$("." + formId + "_" + idx).hide();
}
