function cusotm_alias_insert_generated_random_value(r){
	//念の為に確認
	var res = confirm("エイリアスの値をランダムな文字列で上書きしてもよろしいですか？");
	if(res) $("#custom_alias_input").val(r);
}
