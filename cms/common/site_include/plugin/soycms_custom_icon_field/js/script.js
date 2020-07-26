var add_custom_icon_field = function(src){
	//var hiddenValue = src.replace("@@SITE_URL@@","");
	var hiddenValue = src;
	var values = $("#custom_icon_field_hidden").val().split(",");
	var flag = true;
	for(var i=0; i<values.length;i++){
		if(values[i] == hiddenValue){
			values[i] = "";
			flag = false;
			var tempId = hiddenValue.substring(hiddenValue.lastIndexOf("/")+1).replace(".","_");
			if($("#custom_icon_field_hidden_" + tempId)){
				$("#custom_icon_field_hidden_" + tempId).remove();
			}
			continue;
		}
	}

	if(flag){
		values[values.length] = hiddenValue;
		var image = $(new Image);
		image.attr("src",src);
		var tempId = hiddenValue.substring(hiddenValue.lastIndexOf("/")+1).replace(".","_");
		image.attr("id","custom_icon_field_hidden_" + tempId);
		$("#custom_icon_field_current").append(image);
	}
	$("#custom_icon_field_hidden").val(values.join(","));
};

if(iconfield_labels.length > 0){
	$('input[id^="label_"]').each(function(){
		var label_id = parseInt($(this).prop("id").replace("label_", ""));
		if(iconfield_labels.indexOf(label_id) >= 0){	//画面を開いた時に指定のラベルにチェックがある
			if($(this).prop("checked")){
				$("#custom_icon_field_area").css("display", "block");
			}

			$(this).on("click", toggle_iconfield_area);
		}
	});
}


function toggle_iconfield_area(){
	var isOpen = false;
	if(iconfield_labels.length > 0){
		for(var i = 0; i < iconfield_labels.length; i++){
			if($("#label_" + iconfield_labels[i]).prop("checked")){
				isOpen = true;
				break;
			}
		}
	}

	if(isOpen){
		$("#custom_icon_field_area").css("display", "block");
	}else{
		$("#custom_icon_field_area").css("display", "none");
	}
}
