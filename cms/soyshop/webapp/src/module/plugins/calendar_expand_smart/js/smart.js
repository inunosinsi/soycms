(function(){
	var isAggregate = false;	//集計開始
	var count = 0;
	var tds = $("td");
	for (var i = 0; i < tds.length; i++) {
		if($(tds[i]).prop("class").indexOf("today") >= 0){
			isAggregate = true;
		}

		if(isAggregate) count++;

		if(count > 14){
			$(tds[i]).addClass("after");
		}
	}
})();

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
