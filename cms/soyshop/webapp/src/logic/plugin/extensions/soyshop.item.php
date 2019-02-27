<?php
class SOYShopItemBase implements SOY2PluginAction{

	//管理画面の注文一覧で何かしたい時
	function executeOnListPage(){

	}

	//管理画面の注文詳細で何かしたい時
	function executeOnDetailPage($itemId){

	}
}

class SOYShopItemDeletageAction implements SOY2PluginDelegateAction{

	private $mode = "list";
	private $itemId;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		switch($this->mode){
			case "detail":
				$action->executeOnDetailPage($this->itemId);
				break;
			default:
			case "list":
				$action->executeOnListPage();
		}

	}

	function setMode($mode){
		$this->mode = $mode;
	}
	function setItemId($itemId){
		$this->itemId = $itemId;
	}
}
SOYShopPlugin::registerExtension("soyshop.item", "SOYShopItemDeletageAction");
