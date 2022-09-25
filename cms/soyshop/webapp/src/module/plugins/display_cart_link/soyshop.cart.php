<?php
class DisplayCartLinkCart extends SOYShopCartBase{

	const PLUGIN_ID = "display_cart_link_plugin";

	function doOperation(){
		
		if(isset($_GET["item"]) && is_numeric($_GET["item"])){
			$attr = soyshop_get_item_attribute_object((int)$_GET["item"], self::PLUGIN_ID);
			if(is_null($attr->getValue)) return;
			
			//カート制限をつける場合はtrue
			if((int)$attr->getValue() === 1){
				SOY2::import("module.plugins.display_cart_link.util.DisplayCartLinkUtil");
				$cnf = DisplayCartLinkUtil::getConfig();
				if(isset($cnf["limitation"]) && (int)$cnf["limitation"] === 1){
					header("Location:" . $_SERVER["HTTP_REFERER"]);
					exit;
				}
			}
		}
	}
}
SOYShopPlugin::extension("soyshop.cart", "display_cart_link", "DisplayCartLinkCart");