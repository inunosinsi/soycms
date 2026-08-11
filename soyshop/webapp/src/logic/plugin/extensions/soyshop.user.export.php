<?php

class SOYShopUserExportBase implements SOY2PluginAction{

	/**
	 * 検索結果一覧に表示するメニューの表示文言
	 */
	function getMenuTitle(){
		return "";
	}

	/**
	 * 検索結果一覧に表示するメニューの説明
	 */
	function getMenuDescription(){
		return "";
	}

	/**
	 * export エクスポート実行
	 * @param array users
	 */
	function export(array $users){}

}

class SOYShopUserExportDeletageAction implements SOY2PluginDelegateAction{

	private $_list = array();
	private $mode = "list";
	private $action;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
		
		switch($this->mode){

			case "list":
				$this->_list[$moduleId] = array(
					"title" => $action->getMenuTitle(),
					"description" => $action->getMenuDescription()
				);
				break;

			case "export":
			default:
				$this->action = $action;
				break;

		}
	}

	function getList(){
		return $this->_list;
	}
	function getMode() {
		return $this->mode;
	}
	function setMode($mode) {
		$this->mode = $mode;
	}
	function export(array $users){
		if($this->action){
			return $this->action->export($users);
		}
	}
}

SOYShopPlugin::registerExtension("soyshop.user.export", "SOYShopUserExportDeletageAction");