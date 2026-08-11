$(function(){
	$("#restore_from_backup").click(function(){
		AutoSaveEntry.restore();
	});

	setInterval(function(){
		AutoSaveEntry.save();
	}, 10000);
});

var AutoSaveEntry = {
	save : function(){
		var content = $('#main_content_ifr').contents().find("body").html();

		$.ajax({
			type: "POST",
			url: AutoSavePage,
			data: "soy2_token=" + $("input[name=soy2_token]").val() + "&mode=auto_save&login_id=" + CurrentLoginId + "&title=" + $("#title").val() + "&content=" + content + "&dir=notepad",
			dataType: 'text',
			success: function(data){
				var res = eval("array="+data);
				$("input[name=soy2_token]").val(res.soy2_token);

				//一瞬だけsubmitボタンを押せない様にする
				$("#update_button").attr("disabled", true);

				//バックアップに成功した場合
				if( res.result){
					var now = new Date();
					var m = now.getMonth() + 1;
					$("#auto_save_entry_message").html(now.getFullYear() + "-" + m + "-" + now.getDate() + " " + now.getHours() + ":" + now.getMinutes() + ":" + now.getSeconds() + " 記事のバックアップを行いました。");
				//失敗した場合
				} else {
					//
				}

				//0.5秒後に戻す
				setTimeout(function(){
					$("#update_button").attr("disabled", false);
				}, 500);
			}
		});

	},

	restore : function(){
		$("#restoratoin_area").css("display", "none");
		$.ajax({
			type: "POST",
			url: AutoLoadPage,
			data: "soy2_token=" + $("input[name=soy2_token]").val() + "&mode=load&login_id=" + CurrentLoginId + "&dir=notepad",
			dataType: 'text',
			success: function(data){
				var res = eval("array="+data);
				$("input[name=soy2_token]").val(res.soy2_token);

				//一瞬だけsubmitボタンを押せない様にする
				$("#update_button").attr("disabled", true);

				$("#title").val(res.title);
				$('#main_content_ifr').contents().find("body").html(res.content);

				//0.5秒後に戻す
				setTimeout(function(){
					$("#update_button").attr("disabled", false);
				}, 500);
			}
		});
	}

};
