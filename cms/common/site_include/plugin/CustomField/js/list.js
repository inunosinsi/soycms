var CustomFieldListField = {
	add : function(fieldId, isUploadMode){
		//name property : custom_field[{fieldId}][]
		//id property : customfield_custom_field_list_listfield_{n} → ファイルアップローダ用

		$div = $("<div>");
		$div.prop("class", "form-inline custom_field_" + fieldId);

		$ipt = $("<input>");
		$ipt.prop("type", "text");
		$ipt.prop("name", "custom_field[" + fieldId + "][]");
		$ipt.prop("style", " width:45%;");
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
	}
}

function list_field_move_up(formId, idx){
	if(idx > 0){	//idxが0の場合はなにもしない
		var up = idx - 1;
		var tmp = $("." + formId + "_" + up).val();
		$("." + formId + "_" + up).val($("." + formId + "_" + idx).val());
		$("." + formId + "_" + idx).val(tmp);

		// extraValueの値の並べ替え
		["alt", "url", "target"].forEach(function(ele){
			tmp = $("." + formId + "_"+ele+"_" + up).val();
			if(tmp != undefined){
				$("." + formId + "_"+ele+"_" + up).val($("." + formId + "_"+ele+"_" + idx).val());
				$("." + formId + "_"+ele+"_" + idx).val(tmp);
			}
		});
		// 移動したらプレビューボタンを強制非表示にする（空白に置き換え）
		list_field_hide_preview_button(formId, up);
		list_field_hide_preview_button(formId, idx);
	}
}

function list_field_move_down(formId, idx){
	var maxIdx = $("#"+formId+"_count").val();
    if(idx < maxIdx){
        var down = idx + 1;
        var tmp = $("." + formId + "_" + down).val();
        $("." + formId + "_" + down).val($("." + formId + "_" + idx).val());
        $("." + formId + "_" + idx).val(tmp);

        // extraValueの値の並べ替え
        ["alt", "url", "target"].forEach(function(ele){
            tmp = $("." + formId + "_"+ele+"_" + down).val();
            if(tmp != undefined){
                $("." + formId + "_"+ele+"_" + down).val($("." + formId + "_"+ele+"_" + idx).val());
                $("." + formId + "_"+ele+"_" + idx).val(tmp);
            }
        });
        // 移動したらプレビューボタンを強制非表示にする（空白に置き換え）
        list_field_hide_preview_button(formId, idx);
        list_field_hide_preview_button(formId, down);
    }
}

function list_field_delete(formId, idx){
    $("." + formId + "_" + idx).val("");
    $("." + formId + "_" + idx).closest(".form-inline").remove();
}

// プレビューボタンをタグごと完全に削除する関数
function list_field_hide_preview_button(formId, idx) {
    var $input = $("." + formId + "_" + idx);
    var $row = $input.closest(".form-inline");
    var $previewBtn = $row.find("a.btn-warning");
    
    if($previewBtn.length > 0) {
        $previewBtn.remove(); // タグごと削除
    }
}
