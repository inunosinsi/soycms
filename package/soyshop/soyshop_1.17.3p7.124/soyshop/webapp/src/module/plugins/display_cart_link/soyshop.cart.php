<?php
class DisplayCartLinkCart extends SOYShopCartBase{

	const PLUGIN_ID = "display_cart_link_plugin";

	function doOperation(){
		
		if(isset($_GET["item"]) && is_numeric($_GET["item"])){
			$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
			try{
				$obj = $dao->get($_GET["item"], self::PLUGIN_ID);
			}catch(Exception $e){
				return;
			}
			
			//カート制限をつける場合はtrue
			$hasCart = ($obj->getValue() == 1);
			
			if($hasCart){
				SOY2::import("module.plugins.display_cart_link.util.DisplayCartLinkUtil");
				$config = DisplayCartLinkUtil::getConfig();
				if(isset($config["limitation"]) && $config["limitation"] == 1){
					header("Location:" . $_SERVER["HTTP_REFERER"]);
					exit;
				}
			}
		}
			
	}
}
SOYShopPlugin::extension("soyshop.cart", "display_cart_link", "DisplayCartLinkCart");
?>