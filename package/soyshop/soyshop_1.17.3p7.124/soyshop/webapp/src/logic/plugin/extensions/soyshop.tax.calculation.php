<?php
class SOYShopTaxCalculationBase implements SOY2PluginAction{

	private $cart;

	function calculation(CartLogic $cart){
		
	}
	
	function calculationOnEditPage($total){
		
	}
}
class SOYShopTaxCalculationDeletageAction implements SOY2PluginDelegateAction{
	
	private $mode = "post";
	private $cart;
	private $total;
	private $_module;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){

		switch($this->mode){
			case "post":
				$action->calculation($this->cart);
				break;
			case "edit":
				$this->_module = $action->calculationOnEditPage($this->total);
				break;
			default:
				break;
		}
	}
	
	function getModule(){
		return $this->_module;
	}

	function setMode($mode) {
		$this->mode = $mode;
	}
	function setCart($cart) {
		$this->cart = $cart;
	}
	function setTotal($total){
		$this->total = $total;
	}
}
SOYShopPlugin::registerExtension("soyshop.tax.calculation","SOYShopTaxCalculationDeletageAction");
?>