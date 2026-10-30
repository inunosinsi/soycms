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

		// 移動したらプレビューボタンを強制非表示にする（空白に置き換え）
		dllist_field_hide_preview_button(formId, up);
		dllist_field_hide_preview_button(formId, idx);
	}
}

function dllist_field_move_down(formId, idx){
	var maxIdx = $("#" + formId + "_count").val();
	if(idx < maxIdx){
		var down = idx + 1;
		var types = ["label", "value"];
		for(var i = 0; i < types.length; i++){
			var l = types[i];
			var selectorDown = "." + formId + "_" + down + "_" + l;
			var selectorIdx = "." + formId + "_" + idx + "_" + l;
			
			var tmp = $(selectorDown).val();
			$(selectorDown).val($(selectorIdx).val());
			$(selectorIdx).val(tmp);
		}

		// 移動したらプレビューボタンを強制非表示にする（空白に置き換え）
        dllist_field_hide_preview_button(formId, idx);
        dllist_field_hide_preview_button(formId, down);
	}
}

function dllist_field_delete(formId, idx){
var types = ["label", "value"];
	for(var i = 0; i <= types.length; i++){
		$("." + formId + "_" + idx + "_" + types[i]).val("");
	}
	$("." + formId + "_" + idx).closest(".form-inline").hide();
}

// プレビューボタンをタグごと完全に削除する関数
function dllist_field_hide_preview_button(formId, idx) {
    // labelのクラス名（例: .custom_field_dtli_0_label）を起点にして行を取得する
    var $row = $("." + formId + "_" + idx + "_label").closest(".form-inline");
    var $previewBtn = $row.find("a.btn-warning");
        
    if($previewBtn.length > 0) {
        $previewBtn.remove(); // タグごと完全に削除する場合
    }
}
