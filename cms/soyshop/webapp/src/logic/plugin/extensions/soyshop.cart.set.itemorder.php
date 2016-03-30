<?php
class SOYShopCartSetItemOrderBase implements SOY2PluginAction{
	
	function setItemOrder(SOYShop_Item $item, $count){
		
	}
}
class SOYShopCartSetItemOrderDeletageAction implements SOY2PluginDelegateAction{

	//ページ番号を入れる
	private $_object;
	private $item;
	private $count;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){		
			$this->_object = $action->setItemOrder($this->getItem(), $this->getCount());
	}
	
	function getObject(){
		return $this->_object;
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
SOYShopPlugin::registerExtension("soyshop.cart.set.itemorder","SOYShopCartSetItemOrderDeletageAction");
?>