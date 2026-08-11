var $subInfo = $("#3d_secure_subwindow");
$subInfo.hide();

var $redInfo = $("#3d_secure_redirect");
$redInfo.hide();

if($('input[name="Config[secure_type]"]:checked').val() == 0){
	$redInfo.show();
}else{
	$subInfo.show();
}

function select_3d_secure_type(typ){
	if(typ == 0){
		$redInfo.show();
		$subInfo.hide();			
	}else{
		$redInfo.hide();
		$subInfo.show();
	}
}
