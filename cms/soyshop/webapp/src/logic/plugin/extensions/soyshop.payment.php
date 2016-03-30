<?php
class SOYShopPayment implements SOY2PluginAction{

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

	/**
	 * 追加ページを持っているか
	 */
	function hasOptionPage(){
		return false;
	}

	function getOptionPage(){

	}

	function onPostOptionPage(){

	}

}
class SOYShopPaymentDeletageAction implements SOY2PluginDelegateAction{

	private $_list = array();
	private $mode = "list";
	private $cart;

	function run($extentionId,$moduleId,SOY2PluginAction $action){

		//カートは必要
		if(!$this->getCart()){
			throw new Exception("soyshop.payment needs cart information.");
		}

		$action->setCart($this->getCart());

		//optionの時
		if($extentionId == "soyshop.payment.option"){
			if($this->mode == "post"){
				$action->onPostOptionPage();
			}else{
				echo $action->getOptionPage();
			}
			return;
		}

		$this->getCart()->clearAttribute("has_option");

		switch($this->mode){
			case "list"://支払い方法のリスト
				if(strlen($action->getName())){
					$this->_list[$moduleId] = array(
						"name" => $action->getName(),
						"price" => $action->getPrice(),
						"description" => $action->getDescription(),
					);
				}
				break;
			case "select"://選択された支払いの内部
				$action->onSelect($this->getCart());

				if($action->hasOptionPage()){
					$this->getCart()->setAttribute("has_option", true);
				}

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
SOYShopPlugin::registerExtension("soyshop.payment","SOYShopPaymentDeletageAction");
SOYShopPlugin::registerExtension("soyshop.payment.option","SOYShopPaymentDeletageAction");
?>
