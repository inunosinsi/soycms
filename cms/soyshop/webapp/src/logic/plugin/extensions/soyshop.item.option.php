<?php

class SOYShopItemOptionBase implements SOY2PluginAction{

	function clear($index, CartLogic $cart){
		
	}
	
	function compare($index, CartLogic $cart){
		
	}
	
	function doPost($index, CartLogic $cart){
		
	}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, $index){

	}
	
	function order($index){
		
	}
	
	function display($item){
		
	}
	
	function edit($key){
		
	}
	
	function addition($index){
		
	}
}
class SOYShopItemOptionDeletageAction implements SOY2PluginDelegateAction{

	private $_id;
	private $_htmls;
	private $_attributes;
	private $_addition;
	private $_label;
	private $mode;
	private $cart;
	private $index;
	private $key;
	private $item;
	private $htmlObj;
	private $option;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){

		switch($this->mode){
			case "clear":
				$action->clear($this->index, $this->cart);
				break;
			case "compare":
				$this->_id = $action->compare($this->option, $this->cart);
				break;
			case "post":
				$action->doPost($this->index, $this->cart);
				break;
			case "item":
				$this->_htmls = $action->onOutput($this->htmlObj, $this->index);
				break;
			case "order":
				$this->_attributes = $action->order($this->index);
				break;
			case "addition":
				$this->_addition = $action->addition($this->index);
				break;
			case "display":
				$this->_htmls = $action->display($this->item);
				break;
			case "edit":
			default:
				$this->_label = $action->edit($this->key);
				break;
		}
	}
	function getCartOrderId(){
		return $this->_id;
	}
	function getHtmls(){
		return $this->_htmls;
	}
	function getAttributes(){
		return $this->_attributes;
	}
	function getAddition(){
		return $this->_addition;
	}
	function getLabel(){
		return $this->_label;
	}

	function setMode($mode){
		$this->mode = $mode;
	}
	function setCart($cart){
		$this->cart = $cart;
	}
	function setIndex($index){
		$this->index = $index;
	}	
	function setKey($key){
		$this->key = $key;
	}	
	function setItem($item){
		$this->item = $item;
	}
	function setHtmlObj($htmlObj) {
		$this->htmlObj = $htmlObj;
	}
	function setOption($option) {
		$this->option = $option;
	}
}
SOYShopPlugin::registerExtension("soyshop.item.option","SOYShopItemOptionDeletageAction");
?>