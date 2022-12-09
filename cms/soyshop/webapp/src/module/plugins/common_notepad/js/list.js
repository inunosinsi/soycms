(function(){
	var plugin_id = "";
	if(location.href.indexOf("Extension") > 0 && location.href.indexOf("Detail") > 0){
		var match = location.href.match('Extension/Detail/(.*)');
		if(match[1]){
			if(match[1].indexOf("/")){
				plugin_id = match[1].substr(0, match[1].indexOf("/"));
			}else{
				plugin_id = match[1];
			}
		}
	}

	var $ul = $("<ul>");
	for(var i = 0; i < notepads.length; i++){
		var $li = $("<li>");
		var $a = $("<a>");
		//$li.append(notepads[i].create_date + " " + "<a href=\"" + editor_url + "/" + notepads[i].id + "\""> + notepads[i].title + "</a>");
		var url = editor_url + "/" + notepads[i].id;
		if(plugin_id.length) url += "?plugin_id=" + plugin_id;
		$li.append(notepads[i].create_date + " " + "<a href=\"" + url + "\">" + notepads[i].title + "</a>");
		$ul.append($li);
	}
	$("#notepad_list").append($ul);
})();
