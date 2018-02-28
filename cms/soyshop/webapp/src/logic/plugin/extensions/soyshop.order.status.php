<?php

class SOYShopOrderStatus implements SOY2PluginAction{

	/**
	 * @return array(ステータスコード => ラベル)
	 */
	function statusItem(){

	}

	/**
	 * @return array(ステータスコード => ラベル)
	 */
	function paymentStatusItem(){

	}
}
class SOYShopOrderStatusDeletageAction implements SOY2PluginDelegateAction{

	private $mode = "status";
	private $_list = array();

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		if($action instanceof SOYShopOrderStatus){
			switch($this->mode){
				case "status":
					$this->_list[$moduleId] = $action->statusItem();
					break;
				case "payment":
					$this->_list[$moduleId] = $action->paymentStatusItem();
					break;
			}
		}
	}

	function getList(){
		return $this->_list;
	}

	function getMode(){
		return $this->mode;
	}
	function setMode($mode){
		$this->mode = $mode;
	}
}
SOYShopPlugin::registerExtension("soyshop.order.status", "SOYShopOrderStatusDeletageAction");
