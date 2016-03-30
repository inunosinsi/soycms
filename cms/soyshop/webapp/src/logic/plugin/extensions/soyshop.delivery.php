<?php
class SOYShopDelivery implements SOY2PluginAction{

	private $cart;

	function onSelect(CartLogic $cart){

	}

	function getName(){
		return "";
	}

	function getDescription(){
		return "";
	}

	function getPrice(){
		return "";
	}

	function getCart() {
		return $this->cart;
	}
	function setCart($cart) {
		$this->cart = $cart;
	}
}
class SOYShopDeliveryDeletageAction implements SOY2PluginDelegateAction{

	private $_list = array();
	private $mode = "list";
	private $cart;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){

		//カートは必要
		if(!$this->getCart()){
			throw new Exception("soyshop.delivery needs cart information.");
		}

		$action->setCart($this->getCart());

		switch($this->mode){
			case "list":
				if(strlen($action->getName())){
					$this->_list[$moduleId] = array(
						"name" => $action->getName(),
						"description" => $action->getDescription(),
						"price" => $action->getPrice()
					);
				}
				break;
			case "select":
				$action->onSelect($this->getCart());
				break;


		}

	}


	function getList(){
		return $this->_list;
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
SOYShopPlugin::registerExtension("soyshop.delivery","SOYShopDeliveryDeletageAction");
?>
