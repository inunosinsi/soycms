<?php

class SOYShopItemUpdateBase implements SOY2PluginAction{

	private $isFirst = false;

	function addHistory(SOYShop_Item $item,$old){

	}

	function display(SOYShop_Item $item){

	}

	function getIsFirst(){
		return $this->isFirst;
	}
	function setIsFirst($isFirst){
		$this->isFirst = $isFirst;
	}
}
class SOYShopItemUpdateDeletageAction implements SOY2PluginDelegateAction{

	private $item;
	private $old;
	private $_list;
	private $isFirst = false;	//商品登録時はtrue

	function run($extetensionId,$moduleId,SOY2PluginAction $action){

		if(is_numeric($this->old)){
			$action->setIsFirst($this->isFirst);
			$action->addHistory($this->item, $this->old);
		}else{
			$this->_list[$moduleId] = $action->display($this->item);
		}

	}

	function getList(){
		return $this->_list;
	}
	function setItem($item) {
		$this->item = $item;
	}
	function setOld($old){
		$this->old = $old;
	}
	function setIsFirst($isFirst){
		$this->isFirst = $isFirst;
	}
}
SOYShopPlugin::registerExtension("soyshop.item.update","SOYShopItemUpdateDeletageAction");
