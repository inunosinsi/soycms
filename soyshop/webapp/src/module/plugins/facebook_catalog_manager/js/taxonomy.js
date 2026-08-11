var taxonomy_types = ["", "first", "second", "third", "fourth"];
(function(){
	var $selectBox = $("#taxonomy_" + taxonomy_types[1]);
	$selectBox.on("change", setSearchChildTaxnomy);

	//最初の分類分だけ表示時に調べておく
	insertChildTaxonomy(2, $("#taxonomy_" + taxonomy_types[1]).val());
}());

function setSearchChildTaxnomy(){
	var id = $(this).prop("id").replace("taxonomy_", "");
	var idx = parseInt(taxonomy_types.indexOf(id));
	if(idx < taxonomy_types.length - 1){
		insertChildTaxonomy(idx + 1, $(this).val());
	}
}

//hierarchyには階層、keyには選択した値を指定する
function insertChildTaxonomy(hierarchy, key){
	if(hierarchy > 1 && hierarchy < taxonomy_types.length){
		//フォームがあれば一旦削除
		for(var i = hierarchy; i < taxonomy_types.length; i++){
			if($("#taxonomy_" + taxonomy_types[i])){
				$("#taxonomy_" + taxonomy_types[i]).remove();
			}
		}

		$.ajax({
			type: "POST",
			url: $("#ajax_path").val(),
			data: "hierarchy=" + hierarchy + "&key=" + key,
			dataType: 'text',
			success: function(data){
				var list = eval("array="+data);
				if(list.length > 1){
					var $newBox = $("<select>");
					$newBox.prop("id", "taxonomy_" + taxonomy_types[hierarchy]);
					$newBox.prop("name", "FbCatalogManager[fb_cat_taxonomy][" + taxonomy_types[hierarchy] + "]");
					$newBox.children().remove();

					var selected = $("#taxonomy_" + taxonomy_types[hierarchy] + "_value").val();
					var isListItem = false;	//取得したリストの中に選択した値はあるか？
					for(var j = 0; j < list.length; j++){
						if(selected == list[j]) isListItem = true;
						var $opt = $("<option>").html(list[j]);
						$newBox.append($opt);
					}

					if(isListItem) $newBox.val(selected);

					$("#taxonomy_selectbox_area").append($newBox);
					$("#taxonomy_" + taxonomy_types[hierarchy]).on("change", setSearchChildTaxnomy);

					console.log(hierarchy, selected);
					if(isListItem) insertChildTaxonomy(hierarchy + 1, selected);
				}
			}
		});
	}
}
