var $custom_field_input = $();

function open_customfield_filemanager($form){
		$custom_field_input = $form;
		common_to_layer("#FILE_UPLOAD_LINK#");
}

function preview_customfield($form){

		var publicURL = "#PUBLIC_URL#";
		var siteURL = "#SITE_URL#";
		var siteId = "#SITE_ID#";

		var url = "";
		var href = $form.val();
		if(href && href.indexOf("/") == 0){
			url = publicURL + href.substring(1, href.length);
		}else{
			//URLがhttpから始まる場合はhttp(s)://ドメインを除く
			if(href.indexOf("http") == 0){
				href = href.replace("https://", "");
				href = href.replace("http://", "");
				href = href.replace(location.host + "/", "");
				href = href.replace(siteId + "/", "/");
			}
			url = siteURL + href;
		}

		var temp = new Image();
		temp.src = url;
		temp.onload = function(e){
				common_element_to_layer(url, {
						height : Math.min(600, Math.max(400, temp.height + 20)),
						width  : Math.min(800, Math.max(400, temp.width + 20))
				});
		};
		temp.onerror = function(e){
				alert(url+"が見つかりません。");
		}
		return false;
}

function insertHTML(html, src, alt, width, height){
		var id = $custom_field_input.attr("id");
		if(src.length > 2){ //これで/#を省ける
				$custom_field_input.val(src);
				$("#"+id+"_extra_alt").val(alt);
				$("#"+id+"_extra_width").val(width+"px");
				$("#"+id+"_extra_height").val(height+"px");
		}
}
