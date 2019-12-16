<?php
class SOYShopOrderBase implements SOY2PluginAction{

	//管理画面の注文一覧で何かしたい時
	function executeOnListPage(){

	}

	//管理画面の注文詳細で何かしたい時
	function executeOnDetailPage($orderId){

	}
}

class SOYShopOrderDeletageAction implements SOY2PluginDelegateAction{

	private $mode = "list";
	private $orderId;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		switch($this->mode){
			case "detail":
				$action->executeOnDetailPage($this->orderId);
				break;
			default:
			case "list":
				$action->executeOnListPage();
		}

	}

	function setMode($mode){
		$this->mode = $mode;
	}
	function setOrderId($orderId){
		$this->orderId = $orderId;
	}
}
SOYShopPlugin::registerExtension("soyshop.order", "SOYShopOrderDeletageAction");
