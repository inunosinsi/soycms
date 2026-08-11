<?php

class FunctionPage extends WebPage{

	function __construct($args) {

		//ログインチェック	ログインしていなければ強制的に止める
		if(!soyshop_admin_login()) exit;

		$moduleId = (isset($_GET["moduleId"])) ? $_GET["moduleId"] : null;

		//moduleIdかorderIdのどちらかが取得できない場合は注文トップに飛ばす
		if(is_null($moduleId)) exit;

		error_reporting(E_ALL ^ E_NOTICE);

		$module = soyshop_get_plugin_object($moduleId);
		SOYShopPlugin::load("soyshop.user.function", $module);

		$html = SOYShopPlugin::display("soyshop.user.function", array(
			"mode" => "select"
		));

		if(is_null($html) && strlen($html)) return false;

		echo $html;
		exit;
	}
}
