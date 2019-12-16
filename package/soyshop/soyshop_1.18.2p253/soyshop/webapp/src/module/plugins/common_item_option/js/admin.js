(function(){
	var btn = $("#back_link");
	var url = btn.prop("href");
	btn.prop("href", "javascript:void(0);");

	btn.click(function(){
		var res = confirm("商品オプションの値を変更していませんが、ページを移動しても良いですか？");

		if(res){
			location.href = url;
		}
	});
}());
