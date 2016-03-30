<?php

class ItemStandardCart extends SOYShopCartBase{

	function doOperation(){
		//Standardが無ければ通常のdoOperation
		if(isset($_POST["Standard"])){
			$child = SOY2Logic::createInstance("module.plugins.item_standard.logic.ChildItemLogic")->getChildItem($_REQUEST["item"], $_POST["Standard"]);
			if(!is_null($child->getId())) {
				$_REQUEST["item"] = $child->getId();
			}
		}
	}
}
SOYShopPlugin::extension("soyshop.cart", "item_standard", "ItemStandardCart");
?>