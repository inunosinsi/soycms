<?php
/**
 * session config
 * To change session settings → Create session.config.php
 */
//すでにセッションが開始している場合は設定できない
if((!isset($_SESSION) || is_null($_SESSION))){
	$sessCnf = session_get_cookie_params();
	if(!$sessCnf["httponly"]){	//httponlyがtrueの場合は既に設定済みとして再び実行しない
		//localhostでなければドメインを指定してしまう
		//$sessCnf["domain"] = (is_bool(strpos($_SERVER["HTTP_HOST"], "localhost"))) ? $_SERVER["HTTP_HOST"] : null;
		$sessCnf["domain"] = null;
		$sessCnf["httponly"] = true;
		$sessCnf["secure"] = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on");

		//php 7.3以降 samesiteの指定が出来る
		$vArr = explode(".", phpversion());
		if(($vArr[0] >= 8 || ($vArr[0] >= 7 && $vArr[1] >= 3))){
			$sessCnf["samesite"] = "Lax";
			session_set_cookie_params($sessCnf);
		}else{
			session_set_cookie_params($sessCnf["lifetime"], $sessCnf["path"], $sessCnf["domain"], $sessCnf["secure"], $sessCnf["httponly"]);
		}
		unset($vArr);
	}
	unset($sessCnf);
}
