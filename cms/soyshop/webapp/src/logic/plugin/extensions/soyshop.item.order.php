<?php

class SOYShopItemOrderBase implements SOY2PluginAction{

	function order($itemOrderId){}

	function complete($orderId){}

}
class SOYShopItemOrderDeletageAction implements SOY2PluginDelegateAction{

	private $mode;
	private $itemOrderId;
	private $orderId;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){

		switch($this->mode){
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

	function setItemOrderId($itemOrderId){
		$this->itemOrderId = $itemOrderId;
	}

	function setOrderId($orderId){
		$this->orderId = $orderId;
	}
}
SOYShopPlugin::registerExtension("soyshop.item.order","SOYShopItemOrderDeletageAction");
