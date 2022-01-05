<?php
/*
 */
class OrderInvoiceWithNoteInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		return "";
		// if($active){
		// 	return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=order_invoice_with_note").'">控え有り印刷用納品書作成プラグインの設定</a>';
		// }else{
		// 	return "";
		// }
	}

}
SOYShopPlugin::extension("soyshop.info","order_invoice_with_note","OrderInvoiceWithNoteInfo");
