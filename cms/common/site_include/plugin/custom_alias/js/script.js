function custom_alias_copy_url(){
	var url = $("#custom_alias_confirm_url").val();
	var t = document.createElement("textarea");
	document.getElementsByTagName("body")[0].appendChild(t);
	t.value=url;
	t.select();
	document.execCommand('copy');
	t.parentNode.removeChild(t);
	alert("URLをクリップボードにコピーしました。");
}
