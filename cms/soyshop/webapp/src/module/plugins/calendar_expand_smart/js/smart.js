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
