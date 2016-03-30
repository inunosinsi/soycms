<?php
/**
 * カートにその他の処理を追加するための拡張ポイント
 */
class SOYShopOrderProcess implements SOY2PluginAction{

	function execute(CartLogic $cart){

	}
}
class SOYShopOrderProcessDeletageAction implements SOY2PluginDelegateAction{

	private $cart;
	private $mode;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		if($action instanceof SOYShopOrderProcess){
			$cart = $this->getCart();
			switch($this->mode){
				case "cart03post":
				default:
					$action->execute($cart);
					break;
			}
		}
	}
	
	function getCart(){
		return $this->cart;
	}
	function setCart($cart){
		$this->cart = $cart;
	}
	
	function getMode(){
		return $this->mode;
	}
	function setMode($mode){
		$this->mode = $mode;
	}
}
SOYShopPlugin::registerExtension("soyshop.order.process", "SOYShopOrderProcessDeletageAction");
?>