<?php
class SOYShopCartBase implements SOY2PluginAction{

	function doOperation(){
		
	}
	
	function afterOperation(CartLogic $cart){
		
	}

	function displayPage01(CartLogic $cart){
		
	}
	
	function displayPage02(CartLogic $cart){
		
	}
	
	function displayPage03(CartLogic $cart){
		
	}
	
	function displayPage04(CartLogic $cart){
		
	}
	
	function displayPage05(CartLogic $cart){
		
	}
	
	function displayCompletePage(CartLogic $cart){
		
	}
}
class SOYShopCartDeletageAction implements SOY2PluginDelegateAction{

	//ページ番号を入れる
	private $mode = "page01";
	private $_html = array();
	private $_object;
	private $cart;
	private $item;
	private $count;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
		
		switch($this->mode){
			case "doOperation":
				$action->doOperation();
				break;
			case "afterOperation":
				$action->afterOperation($this->getCart());
				break;
			case "page01":
				$this->_html[$moduleId] = array(
					"html" => $action->displayPage01($this->getCart())
				);
				break;
			case "page02":
				$this->_html[$moduleId] = array(
					"html" => $action->displayPage02($this->getCart())
				);
				break;
			case "page03":
				$this->_html[$moduleId] = array(
					"html" => $action->displayPage03($this->getCart())
				);
				break;
			case "page04":
				$this->_html[$moduleId] = array(
					"html" => $action->displayPage04($this->getCart())
				);
				break;
			case "page05":
				$this->_html[$moduleId] = array(
					"html" => $action->displayPage05($this->getCart())
				);
				break;
			case "complete":
				$this->_html[$moduleId] = array(
					"html" => $action->displayCompletePage($this->getCart())
				);
				break;
			default:
				break;
		}
	}
	
	function getHtml(){
		return $this->_html;
	}
	function getObject(){
		return $this->_object;
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
	function getItem(){
		return $this->item;
	}
	function setItem($item){
		$this->item = $item;
	}
	function getCount(){
		return $this->count;
	}
	function setCount($count){
		$this->count = $count;
	}
}
SOYShopPlugin::registerExtension("soyshop.cart","SOYShopCartDeletageAction");
?>
