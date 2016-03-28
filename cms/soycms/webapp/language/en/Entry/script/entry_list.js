//IEで動作しないので変更が必要(onchangeでのフックかな）
function onChangeSelect(form){
	$$(".label_select").each(function(ele){
		ele.selectedIndex = form.selectedIndex;
	});
	var obj = eval('('+form.value+')');
	$("op_code").value = obj.mode;
	$("label_select").value = obj.id;
	$("index_form").submit();
	return false;
}

function onTogglePublic(flag){
	if(flag){
		if(!confirm("Open these entries OK ?")){
			return;
		}
		$("op_code").value = "setPublish";
	}else{
		if(!confirm("Close these entries OK ?")){
			return;
		}
		$("op_code").value = "setnonPublish";
	}
	$("index_form").submit();
}

function deleteEntry(){
	if(!confirm("Delete these entries OK ?")){
		return ;
	}
	$("op_code").value="delete";
	$("index_form").submit();
}

function onOpenListPanel(){
	common_to_layer(listPanelURI);
}