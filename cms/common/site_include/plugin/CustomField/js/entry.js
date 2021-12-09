var CustomFieldEntryField = {
	changeSite : function(ele, formId, name, selectedLabelId){
		var siteId = $(ele).val();
		$.ajax({
			type: "POST",
			url: CustomFieldEntryField.getEndpointPath(),
			data: "soy2_token=" + $("input[name=soy2_token]").val() + "&mode=site&site_id=" + siteId,
			dataType: 'text',
			success: function(data){
				var res = eval("array="+data);
				$("input[name=soy2_token]").val(res.soy2_token);

				CustomFieldEntryField.empty(formId + "_label");
				CustomFieldEntryField.empty(formId);

				//データ取得成功
				if(res.result && res.list && res.list.length > 0){
					var $select = $("<select id=\"custom_field_ent_select\" onchange='CustomFieldEntryField.change(this, " + siteId + ", \"" + formId + "\", \"" + name + "\", 0);'>");
					$select.append('<option value=""></option>');
					for(var i = 0; i < res.list.length; i++){
						var label = res.list[i];
						if(selectedLabelId == label.id){
							$select.append('<option value="' + label.id + '" selected>' + label.caption + '</option>');
						}else{
							$select.append('<option value="' + label.id + '">' + label.caption + '</option>');
						}
					}
					$("#" + formId + "_label").append($select);
				}
			}
		});
	},
	change : function(ele, siteId, formId, name, selectedEntryId){
		var labelId = $(ele).val();

		if(labelId.length){
			//ラベルに格納されたデータを取得する
			$.ajax({
				type: "POST",
				url: CustomFieldEntryField.getEndpointPath(),
				data: "soy2_token=" + $("input[name=soy2_token]").val() + "&mode=label&site_id=" + siteId + "&label_id=" + labelId,
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
								$select.append('<option value="' + siteId + '-' + labelId + '-' + entry.id + '" selected>' + entry.title + '</option>');
							}else{
								$select.append('<option value="' + siteId + '-' + labelId + '-' + entry.id + '">' + entry.title + '</option>');
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
	},
	getEndpointPath : function(){
		var pathname = location.pathname;
		if(pathname.indexOf("/Blog/") > 0){
			pathname = pathname.substr(0, pathname.indexOf("/Blog/"));
		}else{
			pathname = pathname.substr(0, pathname.indexOf("/Entry/"));
		}
		return pathname + "/Entry/CustomField/";
	}
}
