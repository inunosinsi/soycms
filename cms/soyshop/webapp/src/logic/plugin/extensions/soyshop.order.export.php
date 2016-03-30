<?php

class SOYShopOrderExportBase implements SOY2PluginAction{

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
	 */
	function export($orders){

	}

}

class SOYShopOrderExportDeletageAction implements SOY2PluginDelegateAction{

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
	function export($orders){
		if($this->action){
			return $this->action->export($orders);
		}
	}
}

SOYShopPlugin::registerExtension("soyshop.order.export","SOYShopOrderExportDeletageAction");

?>
