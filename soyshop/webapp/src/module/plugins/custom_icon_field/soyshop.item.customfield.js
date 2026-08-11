function onClickIconLeaf(path,ele){
	if($(ele).hasClass("selected_category")){
		$("#custom_icon_field").val($("#custom_icon_field").val().replace(path,""));
		$("#custom_icon_field_text").html($("#custom_icon_field_text").html().replace($(ele).html(),""));

		$(ele).removeClass("selected_category");
	}else{
		$("#custom_icon_field").val($("#custom_icon_field").val() + "," + path);
		$("#custom_icon_field_text").html($("#custom_icon_field_text").html() + " " + $(ele).html());

		$(ele).addClass("selected_category");
	}
}
