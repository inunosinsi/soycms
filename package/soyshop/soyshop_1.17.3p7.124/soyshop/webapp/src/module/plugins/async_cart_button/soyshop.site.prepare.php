<?php

class AsyncCartButtonPrepareAction extends SOYShopSitePrepareAction{

	function prepare(){
		if(isset($_GET["async_cart_button"])){
			$itemId = (int)$_GET["async_cart_button"];
			if(isset($_POST["Standard"])){
				$child = SOY2Logic::createInstance("module.plugins.item_standard.logic.ChildItemLogic")->getChildItem($itemId, $_POST["Standard"]);
				
				echo json_encode(array("price" => $child->getSellingPrice()));
				exit;
			}
			
			echo json_encode(array("price" => 0));
			exit;
		}
	}
}
SOYShopPlugin::extension("soyshop.site.prepare", "async_cart_button", "AsyncCartButtonPrepareAction");
?>