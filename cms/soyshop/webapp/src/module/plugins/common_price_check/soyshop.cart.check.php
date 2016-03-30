<?php
class CommonPriceCheck extends SOYShopCartCheckBase{

	function checkErrorPage01(CartLogic $cart){
		
		SOY2::import("module.plugins.common_price_check.common.CommonPriceCheckCommon");
		$config = CommonPriceCheckCommon::getConfig();
		
		$configPrice = $config["price"];
		$errorMessage = $config["error"];
		
		$total = $cart->getTotalPrice();
		
		if($total < $configPrice){
			$difference = $configPrice - $total;
			$errorMessage = str_replace("##PRICE##", number_format($configPrice), $errorMessage);
			$errorMessage = str_replace("##DIFFERENCE##", number_format($difference), $errorMessage);
			
			$cart->addErrorMessage("plugin_error", $errorMessage);
			$cart->setAttribute("page", "Cart01");
			$cart->save();
			
			soyshop_redirect_cart();
		}
	}
}
SOYShopPlugin::extension("soyshop.cart.check", "common_price_check", "CommonPriceCheck");
?>