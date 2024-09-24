var CustomFieldDlListField = {
	add : function(fieldId, isUploadMode){
		$div = $("<div>");
		$div.prop("class", "form-inline custom_field_" + fieldId);

		$ipt = $("<input>");
		$ipt.prop("type", "text");
		$ipt.prop("name", "custom_field[" + fieldId + "][label][]");
		$ipt.prop("style", " width:30%;");
		if(isUploadMode) $ipt.prop("placeholder", "直接入力可");
		$ipt.prop("class", "form-control");

		$div.html($ipt);

        $ipt = $("<input>");
		$ipt.prop("type", "text");
		$ipt.prop("name", "custom_field[" + fieldId + "][value][]");
		$ipt.prop("style", " width:30%;");
		if(isUploadMode) $ipt.prop("placeholder", "直接入力可");
		$ipt.prop("class", "form-control");

		$div.append($ipt);
		//console.log($div);

		//<input type="button" onclick="open_listfield_filemanager('id');" class="btn" value="ファイルを指定する">
		if(isUploadMode){
			$ipt = $("<input>");
			$ipt.prop("type", "button");
			$ipt.prop("class", "btn");
			$ipt.prop("value", "ファイルを指定する");
			$ipt.on("click", function(){
				open_dllistfield_filemanager("customfield_custom_field_"+fieldId+"_listfield_"+n);
			});
			$div.append($ipt);
		}

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

function dllist_field_delete(formId, idx){
	var types = ["label", "value"];
	for(var i = 0; i <= types.length; i++){
		$("." + formId + "_" + idx + "_" + types[i]).val("");
	}
	$("." + formId + "_" + idx).hide();
}