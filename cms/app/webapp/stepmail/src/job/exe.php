<?php
if(isset($argv[1])){
	$shopId = $argv[1];
	
	//SOY2のインクルード
	define("CMS_COMMON_DIR", dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/common/");
	include_once(CMS_COMMON_DIR . "lib/soy2_build.php");
	include_once(CMS_COMMON_DIR . "lib/magic_quote_gpc.php");
	
	//StepMailのconfig.phpをインクルード
	include_once(dirname(dirname(dirname(__FILE__))) . "/config.php");

	/** 設定終了 **/
	
	//実行
	SOY2Logic::createInstance("logic.SendMailLogic")->execute();
}
?>