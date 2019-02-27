<?php
class GenerateBarcodeTrackingNumberOrder extends SOYShopOrderBase{

	//詳細ページを開いた時、注文情報を元にCODE39でバーコードを生成する
	function executeOnDetailPage($orderId){
		SOY2::import("module.plugins.generate_barcode_tracking_number.util.GenerateBarcodeUtil");
		$dir = GenerateBarcodeUtil::getBarcodeDirectory();

		$trackingNumber = SOY2Logic::createInstance("logic.order.OrderLogic")->getById($orderId)->getTrackingNumber();
		$jpgFile = $dir . $trackingNumber . ".jpg";

		if(!file_exists($jpgFile)){
			require_once(dirname(__FILE__) . "/vendor/autoload.php");
			$generator = new Picqer\Barcode\BarcodeGeneratorJPG();
			try{
				file_put_contents($jpgFile, $generator->getBarcode($trackingNumber, $generator::TYPE_CODE_39));
			}catch(Exception $e){
				//
			}
		}
	}
}
SOYShopPlugin::extension("soyshop.order", "generate_barcode_tracking_number", "GenerateBarcodeTrackingNumberOrder");
