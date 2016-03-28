<?php
//magic_quotes_gpc対策
if(get_magic_quotes_gpc()){

	function _stripslashes($value){
		$value = is_array($value) ?
					array_map('_stripslashes', $value) :
					stripslashes($value);
    	return $value;
	}
	
	$_POST = _stripslashes($_POST);
	$_GET = _stripslashes($_GET);
	$_COOKIE = _stripslashes($_COOKIE);
	$_REQUEST = _stripslashes($_REQUEST);

}
?>
