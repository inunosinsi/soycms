<?php
//ドットを外し、前2桁取得
$phpVersion = (int)substr(str_replace(".", "", phpversion()), 0, 2);
//magic_quotes_gpc対策 PHP7.4.Xでは推奨されていないらしい
if($phpVersion < 74 && get_magic_quotes_gpc()){

	if(!function_exists("_stripslashes")){
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
}
