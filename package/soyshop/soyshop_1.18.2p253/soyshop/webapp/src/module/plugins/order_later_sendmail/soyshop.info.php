<?php
/*
 */
class OrderLaterSendmailInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=order_later_sendmail") . '">確認メール後日送信プラグイン設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "order_later_sendmail", "OrderLaterSendmailInfo");
?>