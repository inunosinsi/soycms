<?php

class SOYShopOrderStatusUpdate implements SOY2PluginAction{

	/**
	 * @return string
	 */
	function execute(SOYShop_Order $order){

	}

	function executeOnReserve(SOYShop_Order $order){
		
	}
}
class SOYShopOrderStatusUpdateDeletageAction implements SOY2PluginDelegateAction{

	private $order;
	private $mode = "status";

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		if($action instanceof SOYShopOrderStatusUpdate){
			switch($this->mode){
				case "status":
					$action->execute($this->order);
					break;
				case "reserve":
					$action->executeOnReserve($this->order);
					break;
				default:
					break;
			}
		}
	}

	function getOrder(){
		return $this->order;
	}
	function setOrder($order){
		$this->order = $order;
	}

	function getMode(){
		return $this->mode;
	}
	function setMode($mode){
		$this->mode = $mode;
	}
}
SOYShopPlugin::registerExtension("soyshop.order.status.update", "SOYShopOrderStatusUpdateDeletageAction");
