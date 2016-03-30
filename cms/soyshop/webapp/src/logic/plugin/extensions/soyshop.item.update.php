<?php

class SOYShopItemUpdateBase implements SOY2PluginAction{

	function addHistory(SOYShop_Item $item,$old){

	}
	
	function display(SOYShop_Item $item){
		
	}
}
class SOYShopItemUpdateDeletageAction implements SOY2PluginDelegateAction{

	private $item;
	private $old;
	private $_list;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
		
		if(isset($this->old) && strlen($this->old) > 0){
			$action->addHistory($this->item, $this->old);
		}else{
			$this->_list[$moduleId] = $action->display($this->item);
		}
		
	}

	function getList(){
		return $this->_list;
	}

	function getItem() {
		return $this->item;
	}
	function setItem($item) {
		$this->item = $item;
	}
	
	function setOld($old){
		$this->old = $old;
	}
}
SOYShopPlugin::registerExtension("soyshop.item.update","SOYShopItemUpdateDeletageAction");
?>