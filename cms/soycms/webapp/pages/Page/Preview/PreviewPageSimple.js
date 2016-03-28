$(function(){
	
	//Create entry edit button
	$(".cms_hidden_entry_id").each(function(){
		$(this).click(function(){
			var entryId = $(this).attr("entryid");
			var blockLabelId = $(this).attr("blocklabelid");
			if(entryId != null && entryId > 0){
				common_to_layer(EntryEditPage+"/"+entryId,{width:800,height:600});
			}else{
				common_to_layer(EntryEditPage+"/addlabeldentry_"+blockLabelId,{width:800,height:600});
			}
		});
	});
	
	common_element_to_layer($("#soy_cms_operation"),{
		targetId:'soy_cms_operation_frame',
		width:300,
		height:230,
		onclose:function(){
			return false;	
		},
		disableClose : true
	});
	
	$("#soy_cms_operation").css("width","100%");
	$("#soy_cms_operation").css("height","100%");
	$("#soy_cms_operation").show();
	
	//Toggle entry edit button
	$("#soy_cms_operation_toggle_edit_entry_button").click(function(){
		
		if(!$(this).is(':checked')){
			$(".cms_hidden_entry_id").each(function(){
				$(this).hide();
		  	});
		}else{
			$(".cms_hidden_entry_id").each(function(){
			    $(this).show();
		  	});
		}
	});
	
	//Toggle display draft entry or not
	$("#soy_cms_operation_toggle_show_entry_button").click(function(){
		
//		var query = location.search;
//		if(query.search("show_all")){
//			query.replace("show_all=[01]","");
//		}
		
		var search = {};
		
				
		if($(this).prop('checked')){
			search.show_all = 1;
		}else{
			search.show_all = 0;
		}

		window.location.search = "?" + $.param(search);
	});
	
	if($("#soy_cms_operation_toggle_show_entry_button").attr("checked")){
		$("a").each(function(){
			var tmp = $(this).html();//for IE
			var href = $(this).attr("href");
			if($.inArray("?",href) != -1){
				href += "&show_all=1";
			}else{
				href += "?show_all=1";
			}
			$(this).html(tmp);//for IE
		});
	}
	
	//Template edit button
	//if($("soy_cms_operation_edit_template_form")){
	//	$("soy_cms_operation_edit_template_form").action = templateEditAddress;
	//	$("soy_cms_operation_edit_template_form").submit(function(){
	//		return common_submit_to_layer($(this),{width:800,height:600,onclose:function(){location.reload();}});
	//	});
	//}
});
