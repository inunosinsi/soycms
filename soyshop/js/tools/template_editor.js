//エディタからHTMLを取得
function template_editor_get_code(){
	return $("#template_editor").val();
}

//テンプレートのみ保存
function save_template(url, ele){
	var loading;

	//ローディング
	if(ele != null){
		ele = $(ele);

		loading = $("<span/>");
		loading.attr("class","loading");
		loading.html("&nbsp;&nbsp;&nbsp;&nbsp;");

		ele.prop("disabled", true);
		ele.after(loading);
	}

	//AJAXで保存：soy2_tokenでこけたら5回までやり直す
	save_template_ajax(url, 5, loading, ele);
}

function save_template_ajax(url, trials, loading, ele){

	var content = template_editor_get_code();

	if(trials > 0){
		$.ajax({
			url: url,
			type : "post",
			data : "template=" + encodeURIComponent(content) + "&soy2_token=" + $("#main_form").children('input[name=soy2_token]').val(),
			success : function(data){

					var res = eval("array="+data);

					if($("#main_form")){
						$("#main_form").children('input[name=soy2_token]').val(res.soy2_token);
					}

					if(res.res != 1){
						trials--;
						//soy2_tokenが古い場合に備えて何回かやり直す
						save_template_ajax(url, trials, loading, ele);
					}else{
						$(".loading").remove();
						ele.prop("disabled", false);
					}

				}
		});
	}else{
		alert("保存に失敗しました");
	}
}
