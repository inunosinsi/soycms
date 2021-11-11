var CustomFieldListField = {
	add : function(fieldId){
		$div = $("<div>");
		$div.prop("class", "form-inline custom_field_" + fieldId);

		$ipt = $("<input>");
		$ipt.prop("type", "text");
		$ipt.prop("name", "custom_field[" + fieldId + "][]");
		$ipt.prop("class", "form-control");

		$div.html($ipt);
		//console.log($div);

		var $forms = $(".custom_field_" + fieldId);
		$lastForm = $forms[$forms.length - 1];
		$lastForm.after($div[0]);
	}
}
