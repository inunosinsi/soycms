<?php
/*
 */
class SOYShopDiscountCouponModuleInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			$html = "";
			$html .= '<li><a href="'.SOY2PageController::createLink("Config.Detail?plugin=discount_coupon").'">クーポンの設定</a></li>';
			$html .= '<li><a href="'.SOY2PageController::createLink("Config.Detail?plugin=discount_coupon&amp;action=issue").'">クーポンの発行</a></li>';
			$html .= '<li><a href="'.SOY2PageController::createLink("Config.Detail?plugin=discount_coupon&amp;action=list").'">発行済クーポン</a></li>';
			return "<ul>" . $html."</ul>";
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info","discount_coupon","SOYShopDiscountCouponModuleInfo");
?>
