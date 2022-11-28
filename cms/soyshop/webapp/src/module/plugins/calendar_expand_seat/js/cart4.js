var tds = $('td[id^="order_number_"]');
if(tds.length){
	for(var i = 0; i < tds.length; i++){
		var $td = $(tds[i]);
		var idx = $td.prop("id");
		$td.html("");

		var adult = $("#adult").val();
		var child = $("#child").val();

		var html = "<div>";
		html += "大人：" + adult + "人 ";
		html += "子供：" + child + "人";
		html += "</div>";

		$td.html(html);
	}
}
