var PageCustomFieldListField = {
	add : function(fieldId, isUploadMode){
		//name property : custom_field[{fieldId}][]
		//id property : customfield_custom_field_list_listfield_{n} → ファイルアップローダ用

		$div = $("<div>");
		$div.prop("class", "form-inline custom_field_" + fieldId);

		$ipt = $("<input>");
		$ipt.prop("type", "text");
		$ipt.prop("name", "custom_field[" + fieldId + "][]");
		if(isUploadMode) $ipt.prop("placeholder", "直接入力可");
		$ipt.prop("class", "form-control");

		//idプロパティ値の最後の{n}の値を取得
		var n = 0;
		$("input[name='custom_field[" + fieldId + "][]']").each(function(){
			n = parseInt(this.id.replace("customfield_custom_field_"+fieldId+"_listfield_", ""));	
		});
		n += 1;
		$ipt.prop("id", "customfield_custom_field_"+fieldId+"_listfield_"+n);

		$div.html($ipt);

		//<input type="button" onclick="open_listfield_filemanager('id');" class="btn" value="ファイルを指定する">
		if(isUploadMode){
			$ipt = $("<input>");
			$ipt.prop("type", "button");
			$ipt.prop("class", "btn");
			$ipt.prop("value", "ファイルを指定する");
			$ipt.on("click", function(){
				open_listfield_filemanager("customfield_custom_field_"+fieldId+"_listfield_"+n);
			});
			$div.append($ipt);
		}
		
		var $forms = $(".custom_field_" + fieldId);
		$lastForm = $forms[$forms.length - 1];
		$lastForm.after($div[0]);
	},

	insertAllPage: function(fieldId, idx){
		var classPropVal = $(".custom_field_" + fieldId + "_" + idx).val();
		classPropVal = classPropVal.trim();
		if(classPropVal.length > 0){
			var url = "/site/?page_customfield_insert_all_page="+$("#page_customfield_token").val()+"&change="+classPropVal+"&field_id="+fieldId;
			// post
			$.ajax({
				type: 'GET',
				url: url
			}).done(function(msg){
				console.log(msg);
				if(msg == 1){
					alert("successed");
				}else{
					alert("faiiled");
				}
			}).fail(function(){
				alert("failed");
			});
		}
	},

	removeAllPage: function(fieldId, idx){
		var classPropVal = $(".custom_field_" + fieldId + "_" + idx).val();
		classPropVal = classPropVal.trim();
		if(classPropVal.length > 0){
			var url = "/site/?page_customfield_remove_all_page="+$("#page_customfield_token").val()+"&change="+classPropVal+"&field_id="+fieldId;
			// post
			$.ajax({
				type: 'GET',
				url: url
			}).done(function(msg){
				if(msg == 1){
					alert("successed");
				}else{
					alert("faiiled");
				}
			}).fail(function(){
				alert("failed");
			});
		}
	}
}

function list_field_move_up(formId, idx){
	if(idx > 0){	//idxが0の場合はなにもしない
		var up = idx - 1;
		var tmp = $("." + formId + "_" + up).val();
		$("." + formId + "_" + up).val($("." + formId + "_" + idx).val());
		$("." + formId + "_" + idx).val(tmp);
	}
}