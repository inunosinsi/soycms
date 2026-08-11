<?php

class SOYShopItemOrderBase implements SOY2PluginAction{

	function update(SOYShop_ItemOrder $itemOrder){}

	function edit(SOYShop_ItemOrder $itemOrder){}

	function order($itemOrderId){}

	function complete($orderId){}

}
class SOYShopItemOrderDeletageAction implements SOY2PluginDelegateAction{

	private $mode;
	private $itemOrder;
	private $itemOrderId;
	private $orderId;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){

		switch($this->mode){
			case "update":	//管理画面からの注文画面
				$action->update($this->itemOrder);
				break;
			case "edit":	//管理画面の注文詳細の編集
				$action->edit($this->itemOrder);
				break;
			case "order":
				$action->order($this->itemOrderId);
				break;
			case "complete":
				$action->complete($this->orderId);
				break;
		}
	}

	function setMode($mode){
		$this->mode = $mode;
	}

	function setItemOrder($itemOrder){
		$this->itemOrder = $itemOrder;
	}

	function setItemOrderId($itemOrderId){
		$this->itemOrderId = $itemOrderId;
	}

	function setOrderId($orderId){
		$this->orderId = $orderId;
	}
}
SOYShopPlugin::registerExtension("soyshop.item.order","SOYShopItemOrderDeletageAction");
