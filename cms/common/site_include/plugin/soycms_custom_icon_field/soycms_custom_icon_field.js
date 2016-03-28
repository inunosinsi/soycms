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