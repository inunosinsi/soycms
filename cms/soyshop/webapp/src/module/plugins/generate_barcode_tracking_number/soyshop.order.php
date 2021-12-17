<?php
class GenerateBarcodeTrackingNumberOrder extends SOYShopOrderBase{

	//詳細ページを開いた時、注文情報を元にCODE39でバーコードを生成する
	function executeOnDetailPage(int $orderId){
		SOY2Logic::createInstance("module.plugins.generate_barcode_tracking_number.logic.GenerateBarcodeLogic")->generate($orderId);
	}
}
SOYShopPlugin::extension("soyshop.order", "generate_barcode_tracking_number", "GenerateBarcodeTrackingNumberOrder");
