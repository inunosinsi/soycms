<?php

class TrackingMoreNotification extends SOYShopNotification{

	function execute(){
		$jsonDir = SOYSHOP_SITE_DIRECTORY . "/log/";
		if(!file_exists($jsonDir)) mkdir($jsonDir);
		$jsonDir .= "trackingmore/";
		if(!file_exists($jsonDir)) mkdir($jsonDir);

		$handle    = fopen($jsonDir . "test.log","a+");
		$json = file_get_contents("php://input");
		if(!empty($json)){
			echo 200;

			//内容を調べて登録
			SOY2Logic::createInstance("module.plugins.tracking_more.logic.TrackLogic")->receiveByWebHook($json);
		}else{
			fwrite($handle, date("Y-m-d H:i:s").": can not get webhook data!\r\n");
		}
	}
}
SOYShopPlugin::extension("soyshop.notification", "tracking_more", "TrackingMoreNotification");
