function build_entry_table(){
	$("#entry_list_table").html("");
	var endpoint = "/"+$("#site_id").val()+"/CustomFieldBulkChange.json";
	var labelId = $("#first_label_id").val();
	var fieldId = $("#customfield_select_box").val();
	var mode = $("#customfield_value_mode").val();
	var pub = $("#entry_published_mode").val();
	var lim = $("#entry_limit").val();

	$.getJSON(endpoint+"?label_id="+labelId+"&field_id="+fieldId+"&mode="+mode+"&pub="+pub+"&lim="+lim, function(data){
		if(data.entries.length > 0){
			var $table = $("<table>");
			$table.prop("class", "table table-striped");

			var $thead = $("<thead>");
			var $tr = $("<tr>");
			$tr.append("<th>&nbsp;</th><th>記事名</th><th>カスタムフィールドの値</th><th>状態</th>");
			$thead.append($tr);
			$table.append($thead);

			var $tbody = $("<tbody>");

			for(var i = 0; i < data.entries.length; i++){
				var entry = data.entries[i];
			
				$tr = $("<tr>");
				var $td = $("<td>");
				$td.prop("class", "text-center");
				var $chk = $("<input>");
				$chk.prop("type", "checkbox");
				$chk.prop("name", "bulk_change[entry_id][]");
				$chk.prop("value", entry.id);
				$td.append($chk);
				$tr.append($td);

				$td = $("<td>");
				$td.html(entry.title);
				$tr.append($td);
				

				$td = $("<td>");
				$td.html(entry.customfield);
				$tr.append($td);
				
				$td = $("<td>");

				if(entry.isPublished == 1){
					$td.html("公開");	
				}else{
					$td.html("非公開");
				}
				
				$tr.append($td);

				$tbody.append($tr);

				$table.append($tbody);
			}

			$("#entry_list_table").append($table);
		}
	});
}

function change_selectbox_value(){
	$("#first_label_id").val($("#label_select_box").val());
	build_entry_table();
}

// 初期化
$("#first_label_id").val($("#label_select_box").val());
build_entry_table();

$("#label_select_box").on("change", change_selectbox_value);
$("#customfield_select_box").on("change", change_selectbox_value);
$("#customfield_value_mode").on("change", change_selectbox_value);
$("#entry_published_mode").on("change", change_selectbox_value);
$("#entry_limit").on("change", change_selectbox_value);
