<?php
/*
 */
class OrderInvoiceInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=order_invoice").'">印刷用納品書作成プラグインの設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info","order_invoice","OrderInvoiceInfo");
?>