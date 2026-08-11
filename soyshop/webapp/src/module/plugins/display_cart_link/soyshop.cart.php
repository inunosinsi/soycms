<?php
class DisplayCartLinkCart extends SOYShopCartBase{

	const PLUGIN_ID = "display_cart_link_plugin";

	function doOperation(){
		
		if(isset($_GET["item"]) && is_numeric($_GET["item"])){
			$checked = (int)soyshop_get_item_attribute_value((int)$_GET["item"], self::PLUGIN_ID, "int");
			if($checked !== 1) return;
			
			//カート制限をつける場合はtrue
			SOY2::import("module.plugins.display_cart_link.util.DisplayCartLinkUtil");
			$cnf = DisplayCartLinkUtil::getConfig();
			if(isset($cnf["limitation"]) && (int)$cnf["limitation"] === 1){
				header("Location:" . $_SERVER["HTTP_REFERER"]);
				exit;
			}
		}
	}
}
SOYShopPlugin::extension("soyshop.cart", "display_cart_link", "DisplayCartLinkCart");