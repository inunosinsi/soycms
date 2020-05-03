function toggle_multi_uploader_delete(hash){
	$ipt = $("#multi_uploader_delete_" + hash);
	$btn = $("#multi_uploader_delete_btn_" + hash);
	if($ipt.val() == 1){	//削除になっている時
		$ipt.val(0);
		$btn.prop("class", "btn btn-warning");
	}else{
		$ipt.val(1);
		$btn.prop("class", "btn btn-danger");
	}
}
