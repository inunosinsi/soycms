<?php
class SOYShopTaxCalculationBase implements SOY2PluginAction{

	private $cart;

	/**
	 * @param CartLogic
	 * @return void
	 */
	function calculation(CartLogic $cart){}

	/**
	 * @param int, int
	 * @return SOYShop_ItemModule
	 */
	function calculationOnEditPage(int $total, int $reducedRateTotal){
		return new SOYShop_ItemModule();
	}
}
class SOYShopTaxCalculationDeletageAction implements SOY2PluginDelegateAction{

	private $mode = "post";
	private $cart;
	private $total;
	private $reducedRateTotal;	//軽減税率商品金額の合計
	private $_module;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){

		switch($this->mode){
			case "post":
				$action->calculation($this->cart);
				break;
			case "edit":
				if(is_numeric($this->total) && is_numeric($this->reducedRateTotal)) $this->_module = $action->calculationOnEditPage($this->total, $this->reducedRateTotal);
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
	function setReducedRateTotal($reducedRateTotal){
		$this->reducedRateTotal = $reducedRateTotal;
	}
}
SOYShopPlugin::registerExtension("soyshop.tax.calculation","SOYShopTaxCalculationDeletageAction");
