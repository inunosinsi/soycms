<?php
/*
 */
class OrderInvoiceAddReceiptInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=order_invoice_add_receipt_button").'">領収書出力ボタン追加プラグインの設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "order_invoice_add_receipt_button", "OrderInvoiceAddReceiptInfo");
