var CustomFieldEntryField = {
	change : function(ele, formId, name, selectedEntryId){
		var labelId = $(ele).val();

		if(labelId.length){
			var pos = location.pathname.indexOf("index.php/Entry/") + 16;
			var url = location.pathname.substr(0, 29) + "CustomField/";

			//ラベルに格納されたデータを取得する
			$.ajax({
				type: "POST",
				url: url,
				data: "soy2_token=" + $("input[name=soy2_token]").val() + "&label_id=" + labelId,
				dataType: 'text',
				success: function(data){
					var res = eval("array="+data);
					$("input[name=soy2_token]").val(res.soy2_token);

					CustomFieldEntryField.empty(formId);

					//データ取得成功
					if(res.result && res.list && res.list.length > 0){
						var $select = $("<select>");
						$select.prop("name", name);
						$select.append('<option value="' + labelId + '-0"></option>');
						for(var i = 0; i < res.list.length; i++){
							var entry = res.list[i];
							if(selectedEntryId == entry.id){
								$select.append('<option value="' + labelId + '-' + entry.id + '" selected>' + entry.title + '</option>');
							}else{
								$select.append('<option value="' + labelId + '-' + entry.id + '">' + entry.title + '</option>');
							}
						}
						$("#" + formId).append($select);
					}
				}
			});
		}else{
			CustomFieldEntryField.empty(formId);
		}
	},
	empty : function(formId){
		$("#" + formId).empty();
	}
}
