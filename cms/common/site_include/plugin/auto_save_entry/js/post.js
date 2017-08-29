$("#restore_from_backup").click(function(){
	AutoSaveEntry.restore();
});

setInterval(function(){
	AutoSaveEntry.save();
}, parseInt($("#save_period_seconds").val()) * 1000);

var AutoSaveEntry = {
	save : function(){
		var content = $('#entry_content_ifr').contents().find("body").html();
		var more = $('#entry_more_ifr').contents().find("body").html();
		$.ajax({
			type: "POST",
			url: $("#auto_save_action").val(),
			data: "soy2_token=" + $("input[name=soy2_token]").val() + "&mode=auto_save&login_id=" + $("#current_login_id").val() + "&title=" + $("#title").val() + "&content=" + content + "&more=" + more,
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
			url: $("#restore_action").val(),
			data: "soy2_token=" + $("input[name=soy2_token]").val() + "&mode=load&login_id=" + $("#current_login_id").val(),
			dataType: 'text',
			success: function(data){
				var res = eval("array="+data);
				$("input[name=soy2_token]").val(res.soy2_token);

				//一瞬だけsubmitボタンを押せない様にする
				$("#update_button").attr("disabled", true);

				$("#title").val(res.title);
				$('#entry_content_ifr').contents().find("body").html(res.content);
				$('#entry_more_ifr').contents().find("body").html(res.more);

				//0.5秒後に戻す
				setTimeout(function(){
					$("#update_button").attr("disabled", false);
				}, 500);
			}
		});
	}
};
