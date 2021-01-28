<?php
class SOYShopUserBase implements SOY2PluginAction{

	//管理画面の注文一覧で何かしたい時
	function executeOnListPage(){

	}

	//管理画面の注文詳細で何かしたい時
	function executeOnDetailPage($userId){

	}
}

class SOYShopUserDeletageAction implements SOY2PluginDelegateAction{

	private $mode = "list";
	private $userId;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		switch($this->mode){
			case "detail":
				$action->executeOnDetailPage($this->userId);
				break;
			default:
			case "list":
				$action->executeOnListPage();
		}

	}

	function setMode($mode){
		$this->mode = $mode;
	}
	function setUserId($userId){
		$this->userId = $userId;
	}
}
SOYShopPlugin::registerExtension("soyshop.user", "SOYShopUserDeletageAction");
