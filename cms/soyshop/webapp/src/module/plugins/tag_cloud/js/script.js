
if(tag_cloud_plugin_word_list.length > 0){
	var tag_anchors = "";

	for(var tcp_i = 0; tcp_i < tag_cloud_plugin_word_list.length; tcp_i++){
		var tcp_category = tag_cloud_plugin_word_list[tcp_i].category_id;
		var tcp_tags = tag_cloud_plugin_word_list[tcp_i].tags;
		if(!tcp_tags.length) continue;
		tag_anchors += "<div style=\"font-weight:bold;\">○" + tag_cloud_plugin_category_list[tcp_category] + "</div>";
		for(var tcp_j = 0; tcp_j < tcp_tags.length; tcp_j++){
			var tcp_w = tcp_tags[tcp_j];
			if(!tcp_w.length) continue;
			tag_anchors += "<a href=\"javascript:void(0);\" class=\"btn btn-default tag_cloud_anchor\" onclick=\"tag_cloud_plugin_auto_insert_word('" + tcp_w + "');\">" + tcp_w + "</a>";
		}

	}
	$("#tag_cloud_word_candidate").html(tag_anchors);
}

function tag_cloud_plugin_auto_insert_word(word){
	var $ipt = $("#tag_cloud_plugin");
	var tag_string = $ipt.val();
	if(tag_string.length){
		tag_string += "," + word;
	}else{
		tag_string = word;
	}
	$ipt.val(tag_string);
}
