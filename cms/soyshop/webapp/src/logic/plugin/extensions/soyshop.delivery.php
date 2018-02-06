<?php
class SOYShopDelivery implements SOY2PluginAction{

	private $cart;
	private $order;

	function onSelect(CartLogic $cart){}

	function getName(){
		return "";
	}

	function getDescription(){
		return "";
	}

	function getPrice(){
		return "";
	}

	function edit(){
		return "";
	}

	function update(){

	}

	function getCart() {
		return $this->cart;
	}
	function setCart($cart) {
		$this->cart = $cart;
	}

	function getOrder(){
		return $this->order;
	}
	function setOrder($order){
		$this->order = $order;
	}
}
class SOYShopDeliveryDeletageAction implements SOY2PluginDelegateAction{

	private $_list = array();
	private $mode = "list";
	private $cart;
	private $order;
	private $_changes = array();

	function run($extetensionId,$moduleId,SOY2PluginAction $action){

		//カートは必要　マイページでも使用できるようにするため、Cartのチェックはいらない
		// if(!$this->getCart()){
		// 	throw new Exception("soyshop.delivery needs cart information.");
		// }

		if(!is_null($this->getCart())) $action->setCart($this->getCart());
		if(!is_null($this->getOrder())) $action->setOrder($this->getOrder());

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
				//念の為、ここでも再度調べる
				if($_POST["delivery_module"] === $moduleId){
					$action->onSelect($this->getCart());
				}
				break;
			case "mypage":
				if(strlen($action->getName())){
					$this->_list[$moduleId] = $action->edit();
				}
				break;
			case "update":
				$this->_changes[$moduleId] = $action->update();
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
	function getOrder(){
		return $this->order;
	}
	function setOrder($order){
		$this->order = $order;
	}

	function getChanges(){
		return $this->_changes;
	}
}
SOYShopPlugin::registerExtension("soyshop.delivery","SOYShopDeliveryDeletageAction");
