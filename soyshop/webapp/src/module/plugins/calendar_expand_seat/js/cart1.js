var forms = $("input[name^='ItemCount']");
if(forms.length){
	for(var i = 0; i < forms.length; i++) {
		var $form = $(forms[i]);
		$form.prop("type", "hidden");
		$form.prop("id", "order_count_" + i);

		var adult = $("#adult").val();
		var child = $("#child").val();
		var html = "<div>";
		html += "大人：";
		html += '<input type="number" name="Option[adult]" value="' + adult + '" style="width:60px;" id="adult_count_' + i + '">';
		html += "子供：";
		html += '<input type="number" name="Option[child]" value="' + child + '" style="width:60px;" id="child_count_' + i + '">';
		html += "</div>";
		$(forms[i]).after(html);

		$("#adult_count_" + i).on("blur", change_total_count);
		$("#child_count_" + i).on("blur", change_total_count);
	}
}

function change_total_count(){
	var id = $(this).prop("id");
	var l = id.lastIndexOf("_");
	var idx = id.substr(l + 1);

	var adult = parseInt($("#adult_count_" + idx).val());
	console.log(adult);
	var child = parseInt($("#child_count_" + idx).val());
	console.log(child);
	$("#order_count_" + idx).val(adult + child);
}
