<?php
class SOYShopTaxCalculationBase implements SOY2PluginAction{

	private $cart;

	function calculation(CartLogic $cart){
		
	}
	function getCart(){
		return $this->cart;
	}
	function setCart($cart){
		$this->cart = $cart;
	}
}
class SOYShopTaxCalculationDeletageAction implements SOY2PluginDelegateAction{
	
	private $mode = "post";
	private $cart;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){

		switch($this->mode){
			case "post":
				$action->calculation($this->getCart());
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
SOYShopPlugin::registerExtension("soyshop.tax.calculation","SOYShopTaxCalculationDeletageAction");
?>