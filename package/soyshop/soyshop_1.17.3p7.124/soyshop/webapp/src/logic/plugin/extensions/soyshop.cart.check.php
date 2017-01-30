<?php
class SOYShopCartCheckBase implements SOY2PluginAction{

	function checkErrorPage01(CartLogic $cart){
		
	}
	
	function checkErrorPage04(CartLogic $cart){
		
	}
}
class SOYShopCartCheckDeletageAction implements SOY2PluginDelegateAction{

	//ページ番号を入れる
	private $mode = "page01";
	private $cart;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){

		switch($this->mode){
			case "page01":
				$action->checkErrorPage01($this->getCart());
				break;
			case "page04":
				$action->checkErrorPage04($this->getCart());
				break;
			default:
				break;
		}

	}


	function getMode() {
		return $this->mode;
	}
	function setMode($mode) {
		$this->mode = $mode;
	}
	function getCart() {
		return $this->cart;
	}
	function setCart($cart) {
		$this->cart = $cart;
	}
}
SOYShopPlugin::registerExtension("soyshop.cart.check","SOYShopCartCheckDeletageAction");
?>