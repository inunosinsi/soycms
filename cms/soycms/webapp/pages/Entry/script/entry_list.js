//IEで動作しないので変更が必要(onchangeでのフックかな）
function onChangeSelect(form){
	$(".label_select").each(function(ele){
		ele.selectedIndex = form.selectedIndex;
	});
	var obj = eval('('+form.value+')');
	$("#op_code").val(obj.mode);
	$("#label_select").val(obj.id);
	$("#index_form").submit();
	return false;
}

function onTogglePublic(flag){
	if(flag){
		if(!confirm(soycms.lang.entry_list.confirm_to_publish)){
			return;
		}
		$("#op_code").val("setPublish");
	}else{
		if(!confirm(soycms.lang.entry_list.confirm_to_unpublish)){
			return;
		}
		$("#op_code").val("setnonPublish");
	}
	$("#index_form").submit();
}

function deleteEntry(){
	if(!confirm(soycms.lang.entry_list.confirm_to_delete)){
		return ;
	}
	$("#op_code").val("delete");
	$("#index_form").submit();
}

function copyEntry(){
	if(!confirm(soycms.lang.entry_list.confirm_to_copy)){
		return ;
	}
	$("#op_code").val("copy");
	$("#index_form").submit();
}

function onOpenListPanel(){
	common_to_layer(listPanelURI);
}

function toggleAllEntryCheck(value){
	$("input.entry_check").each(function(){
		$(this).prop("checked", value);
	});
}