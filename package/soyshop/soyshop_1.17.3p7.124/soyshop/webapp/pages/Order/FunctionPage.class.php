<?php

class FunctionPage extends WebPage{

	function __construct($args) {
		
		//ログインチェック	ログインしていなければ強制的に止める
		if(!soyshop_admin_login()) SOY2PageController::jump("Order");
		
		$moduleId = (isset($_GET["moduleId"])) ? $_GET["moduleId"] : null;
		$id = (isset($args[0])) ? $args[0] : null;
		
		//moduleIdかorderIdのどちらかが取得できない場合は注文トップに飛ばす
		if(is_null($moduleId) || is_null($id)) SOY2PageController::jump("Order");
		
		error_reporting(E_ALL ^ E_NOTICE);
		
		$moduleDAO = SOY2DAOFactory::create("plugin.SOYShop_PluginConfigDAO");
		try{
			$module = $moduleDAO->getByPluginId($moduleId);
		}catch(Exception $e){
			SOY2PageController::jump("Order");
		}
		
		SOYShopPlugin::load("soyshop.order.function", $module);
		
		$html = SOYShopPlugin::display("soyshop.order.function", array(
			"orderId" => $id,
			"mode" => "select"
		));
		
		echo $html;
		exit;
	}
}
?>