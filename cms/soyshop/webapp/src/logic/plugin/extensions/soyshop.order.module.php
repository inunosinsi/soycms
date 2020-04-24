<?php
class SOYShopOrderModule implements SOY2PluginAction{

	private $orderId;
	private $total;
	private $itemOrders;

	function edit($module){

	}

	function getOrderId(){
		return $this->orderId;
	}
	function setOrderId($orderId){
		$this->orderId = $orderId;
	}

	function getTotal(){
		return $this->total;
	}
	function setTotal($total){
		$this->total = $total;
	}

	function getItemOrders(){
		return $this->itemOrders;
	}
	function setItemOrders($itemOrders){
		$this->itemOrders = $itemOrders;
	}
}
class SOYShopOrderModuleDelegateAction implements SOY2PluginDelegateAction{

	private $_module;
	private $mode;
	private $module;
	private $orderId;
	private $total;
	private $itemOrders = array();


	function run($extentionId,$moduleId,SOY2PluginAction $action){

		if(!$action instanceof SOYShopOrderModule)return;

		$action->setTotal($this->total);
		$action->setItemOrders($this->itemOrders);
		$action->setOrderId($this->orderId);

		switch($this->mode){
			case "edit":
				if($this->module->getId() == $moduleId){
					$this->_module = $action->edit($this->module);
				}
				break;
		}
	}

	function getModule(){
		return $this->_module;
	}
	function setMode($mode){
		$this->mode = $mode;
	}
	function setModule($module){
		$this->module = $module;
	}
	function setOrderId($orderId){
		$this->orderId = $orderId;
	}
	function setTotal($total){
		$this->total = $total;
	}
	function setItemOrders($itemOrders){
		$this->itemOrders = $itemOrders;
	}
}
SOYShopPlugin::registerExtension("soyshop.order.module","SOYShopOrderModuleDelegateAction");
