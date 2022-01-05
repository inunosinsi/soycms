<?php
/*
 */
class CommonCustomerCategoryVoiceInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){

		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=common_customer_category_voice").'">お客様の声プラグインの設定方法</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info","common_customer_category_voice","CommonCustomerCategoryVoiceInfo");
?>