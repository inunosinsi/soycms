<?php
/*
 */
class AffiliateA8flyInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=affiliate_a8fly").'">A8FLYの設定画面へ</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info","affiliate_a8fly","AffiliateA8flyInfo");
