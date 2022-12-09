<?php
class SOYShopOrderFunction implements SOY2PluginAction{

	private $orderId;

	/**
	 * title text
	 */
	function getTitle(){}

	function getDialogMessage(){}

	function getTargetSelf(){
		return false;
	}

	/**
	 * @return html
	 */
	function getPage(){}

	function getOrderId() {
		return (is_numeric($this->orderId)) ? (int)$this->orderId : 0;
	}
	function setOrderId($orderId) {
		$this->orderId = $orderId;
	}

}
class SOYShopOrderFunctionDelegateAction implements SOY2PluginDelegateAction{

	private $_list = array();
	private $mode = "list";
	private $orderId;

	function run($extentionId,$moduleId,SOY2PluginAction $action){

		if(!$action instanceof SOYShopOrderFunction)return;

		$action->setOrderId($this->orderId);

		switch($this->mode){
			case "list":
				$this->_list[$moduleId] = array(
					"moduleId" => $moduleId,
					"name" => $action->getTitle(),
					"dialog" => $action->getDialogMessage(),
					"self" => $action->getTargetSelf()
				);
				break;
			case "select":
				echo $action->getPage();

				break;
		}
	}


	function getList() {
		return $this->_list;
	}
	function getMode() {
		return $this->mode;
	}
	function setMode($mode) {
		$this->mode = $mode;
	}
	function getOrderId() {
		return (is_numeric($this->orderId)) ? (int)$this->orderId : 0;
	}
	function setOrderId($orderId) {
		$this->orderId = $orderId;
	}

}
SOYShopPlugin::registerExtension("soyshop.order.function","SOYShopOrderFunctionDelegateAction");
