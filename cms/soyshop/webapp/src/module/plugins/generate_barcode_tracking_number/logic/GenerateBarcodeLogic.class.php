<?php

class GenerateBarcodeLogic extends SOY2LogicBase {

	function __construct(){
		SOY2::import("module.plugins.generate_barcode_tracking_number.util.GenerateBarcodeUtil");
	}

	function generate(int $orderId){
		$trackingNumber = (string)soyshop_get_order_object($orderId)->getTrackingNumber();
		$jpgFile = GenerateBarcodeUtil::getBarcodeDirectory() . $trackingNumber . ".jpg";

		if(!file_exists($jpgFile)){
			require_once(dirname(dirname(__FILE__)) . "/vendor/autoload.php");
			$generator = new Picqer\Barcode\BarcodeGeneratorJPG();
			try{
				file_put_contents($jpgFile, $generator->getBarcode($trackingNumber, $generator::TYPE_CODE_39));
				usleep(100);

				//横のみリサイズ
    			list($width, $height, $type) = getimagesize($jpgFile);
				soy2_resizeimage($jpgFile, $jpgFile, (int)$width/2, $height);
			}catch(Exception $e){
				//
			}
		}
	}
}
