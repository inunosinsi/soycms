var widgetIntervalId;	//メモを開いた時に
var widgetDoEdited = false;	//何らかの編集を行った時にはじめてバックアップを開始する

var $widget = $("#memo_widget_textarea");
$widgetButton = $("#memo_widget_button");

$widgetButton.on("click", function(){
	if($widget.css("display") == "none"){
		$("#memo_widget").css("width", 400);
		//メモを取得 不安定なので廃止
		//memo_widget_get_content();

		$widget.css("display", "block");
		$widgetButton.text("▼メモ");
	}else{
		$("#memo_widget").css("width", 250);
		memo_widget_close();
	}
});

//保存ボタンを押した時
$("#memo_widget_save_button").on("click", function(){
	$.ajax({
		type: "POST",
		url: $("#memo_widget_save_path").val(),
		data: "memo=" + $("#memo_widget_content").val(),
		success: function(msg){
			var res = eval("array="+msg);
			if(res.finished){
				console.log("[successed]saved memo.");
			}else{
				console.log("[successed]not saved memo.");
			}
		},
		failed: function(msg){
			console.log("[error]not saved memo.");
		}
	});
});

function memo_widget_close(){
	$("#memo_widget_save_button").click(); //ウィジェットを閉める時に内容を保存

	//メモの内容を削除しておく　不安定なので廃止
	//$("#memo_widget_content").val("");

	$widget.css("display", "none");
	$widgetButton.text("▲メモ");

	if(widgetIntervalId){	//自動バックアップを解除
		clearInterval(widgetIntervalId);
		widgetIntervalId = null;
	}
}

function memo_widget_get_content(){
	$.ajax({
		type: "GET",
		url: $("#memo_widget_load_path").val(),
		success: function(msg){
			var res = eval("array="+msg);
			if(typeof(res.content) == "string" && res.content.length > 0){
				$("#memo_widget_content").val(res.content);
			}
		},
		failed: function(msg){
			console.log("[error]load failed.");
		}
	});
}

//ページ遷移直前にもデータを保存
$(window).on('beforeunload', function(ele) {
	if(widgetDoEdited){
		$("#memo_widget_save_button").click();
		return;
	}
});

//キーボードで何らかの操作をした時にはじめて自動バックアップが動き出す
$("#memo_widget_content").on("keydown", function(){
	widgetDoEdited = true;
	widgetIntervalId = setInterval(function(){
		$("#memo_widget_save_button").click();
	}, 15000);
});
