<?php
/*
 */
class SOYShopDiscountFreeCouponModuleInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=discount_free_coupon") . '">クーポンの設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "discount_free_coupon", "SOYShopDiscountFreeCouponModuleInfo");
?>
