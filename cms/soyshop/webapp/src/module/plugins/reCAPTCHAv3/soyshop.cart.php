<?php

class reCAPTCHAv3Cart extends SOYShopCartBase{

	/**
	 * @return html
	 */
	private function _displayCommon(){
		SOY2::import("module.plugins.reCAPTCHAv3.util.reCAPTCHAUtil");
		$cnf = reCAPTCHAUtil::getConfig();
		
		if(!isset($cnf["site_key"]) || !isset($cnf["secret_key"])) return "";
		if(!strlen($cnf["site_key"]) || !strlen($cnf["secret_key"])) return "";
	
		return "<script src=\"https://www.google.com/recaptcha/api.js?render=".$cnf["site_key"]."\"></script>";
	}

	function displayPage01(CartLogic $cart){
		return self::_displayCommon();
	}

	function displayPage02(CartLogic $cart){
		return self::_displayCommon();
	}
	
	function displayPage03(CartLogic $cart){
		return self::_displayCommon();
	}

	/**
	 * @param CartLogic
	 * @return html
	 */
	function displayPage04(CartLogic $cart){
		$script = self::_displayCommon();
		if(!strlen($script)) return "";	

		$cnf = reCAPTCHAUtil::getConfig();
	
		$html = array();
		$html[] = $script;
		$script = "<script>".file_get_contents(__DIR__."/js/script.js")."</script>";
		$script = str_replace("##SITE_KEY##", $cnf["site_key"], $script);
		$script = str_replace("##CART_URI##", soyshop_get_cart_uri(), $script);
		$html[] = $script;
		return implode("\n", $html);
	}
}
SOYShopPlugin::extension("soyshop.cart", "reCAPTCHAv3", "reCAPTCHAv3Cart");
