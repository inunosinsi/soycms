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
	
	$("#soy_cms_operation").css({width:"100%", height:"100%"}).show();
	
	
	//Toggle entry edit button
	$("#soy_cms_operation_toggle_edit_entry_button").click(function(){
		
		if(!$(this).prop('checked')){
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
		
		var query = document.location.search.substring( 1 );
		if(query.length>0){
			query = query.replace(/&/,"");
			query = query.replace(/show_all=1/,"");
			query = query.replace(/show_all=0/,"");
			if(query.match(/id/)){
				query = query + "&";
			}
		}
		
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

		window.location.search = "?" + query + $.param(search);
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
	
	//CSS
	var temp = new Image();

	temp.src = "/";
	var serverRoot = temp.src;

	temp.src = layerCSS;
	layerCSS = temp.src;

	var cssCount = 0;
	$("link").each(function(){
		if(!$(this).attr("rel") || $(this).attr("rel") != "stylesheet"){
			return;
		}
		
		var image = new Image;
		image.src = $(this).attr("href");
		var href = image.src;
		
		if(href && href != layerCSS){
			var option = document.createElement('option');
			
			option.value = href;
			option.innerHTML = ($.inArray(serverRoot,href) == 0) ? "/" + href.replace(serverRoot,"") : href;
			$("#soy_cms_operation_edit_css_select").append(option);
			cssCount ++;
		}
	});
	
	
	/**
	 * @ToDo
	 * submitでiframeに表示するページのテンプレートが表示されないのを何とかする
	 */
	if(cssCount != 0){
		$("#soy_cms_operation_edit_css_form_wrapper").show();
		$("#soy_cms_operation_edit_css_form")
			.attr("action",cssEditAddress)
			.submit(function(){
				return common_submit_to_layer($(this),{width:800,height:600});
			});
	}
		
	//Template edit button
	if($("#soy_cms_operation_edit_template_form")){
		$("#soy_cms_operation_edit_template_form")
			.attr("action",templateEditAddress)
			.submit(function(){
				return common_submit_to_layer($(this),{width:800,height:600,onclose:function(){location.reload();}});
			});
	}
});
