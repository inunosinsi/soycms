(function(){
	//random_alias_configにラベルの設定状況が格納されている
	for(var i = 0; i < random_alias_config.length; i++){
		var $lab = $("#label_" + random_alias_config[i]);
		if($lab.prop("checked")){
			cusotm_alias_insert_random_value();
		}

		$lab.on("click", function(){
			cusotm_alias_insert_random_value();

			//すべてのチェックが外れた時
			custom_alias_remove_random_value();
		});
	}

}());

function cusotm_alias_insert_random_value(){
	var r = $("#custom_alias_random_value").val();
	$("#custom_alias_input").val(r);
}

function custom_alias_remove_random_value(){
	var checked = false;
	for(var i = 0; i < random_alias_config.length; i++){
		if($("#label_" + random_alias_config[i]).prop("checked")) checked = true;
	}
	if(!checked){
		var v = $("#custom_alias_setting_value").val();
		$("#custom_alias_input").val(v);
	}
}
