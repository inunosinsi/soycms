var SmartCalendarObject = {
	next_day_column:0,
	current_page:1,
	pagers_remain:parseInt($("#reserve_calendar_expand_smart_pager_count").val())
};

// スマホ表示の際に何日分表示するか？
(function(){
	var n = $("#reserve_calendar_expand_smart_display_count").val();
	var isAggregate = false;	//集計開始
	var count = 0;
	var tds = $("td");
	for (var i = 0; i < tds.length; i++) {
		if($(tds[i]).prop("class").indexOf("today") >= 0){
			isAggregate = true;
		}

		if(isAggregate) count++;

		if(count > n){
			if(SmartCalendarObject.next_day_column === 0) SmartCalendarObject.next_day_column = i;	//最初にボタンを押した時にafterを外すカラムの記録
			$(tds[i]).addClass("after");
		}
	}
})();

// ページャ用のボタン　ボタンを押すと隠されているカラムが表示される
function show_next_page_on_smart_calendar(){
	SmartCalendarObject.current_page++;
	if(SmartCalendarObject.current_page >= SmartCalendarObject.pagers_remain){
		$(".pager_button_area").css("display","none");
	}

	var cnt = parseInt($("#reserve_calendar_expand_smart_display_count").val());
	$(".after").each(function(){
		if(cnt-- > 0){
			$(this).removeClass("after");
		}
	});

}

$(window).on('load orientationchange resize', function(){
    if (Math.abs(window.orientation) === 90) {
        // 横向きになったときの処理
		$("#calendar").hide();
		$("#rotate").show();
    } else {
        // 縦向きになったときの処理
		$("#calendar").show();
		$("#rotate").hide();
    }
});
