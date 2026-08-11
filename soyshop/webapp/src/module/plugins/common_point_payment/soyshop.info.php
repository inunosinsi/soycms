<?php
/*
 */
class CommonPointPaymentInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return "このプラグインを使用する際は、必ずポイント制設定プラグインをインストールしてください";
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "common_point_payment", "CommonPointPaymentInfo");
?>