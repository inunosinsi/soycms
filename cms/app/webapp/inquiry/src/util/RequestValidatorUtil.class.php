<?php

class RequestValidatorUtil {

	/**
	 * 許可していないGETパラメータ付与のアクセスを禁止
	 * @return bool
	 */
	public static function validate(){
		if(!is_array($_GET) || !count($_GET)) return false;
		if(isset($_GET["block"])) return true;

		$whiteList = array(
			"confirm",
			"complete",
			"trackid",
			"captcha",
			"stylesheet",
			"calendar_js",
			"soy2_token",
			"data",
			"confirm",
			"form_hash",
			"form_value",
			"captcha_value",
			"send",
			"form"
		);

		foreach($_GET as $key => $v){
			if($key == "block") continue;
			if(is_numeric($key)) continue;
			if(is_bool(array_search($key, $whiteList))) return true;
		}
		
    	return false;
    }
}
