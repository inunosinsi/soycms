<?php

class SOYShopCalendarExportBase implements SOY2PluginAction{

	/**
	 * 表示するメニューの表示文言
	 */
	function getMenuTitle(){
		return "";
	}

	/**
	 * 表示するメニューの説明
	 */
	function getMenuDescription(){
		return "";
	}

	/**
	 * export エクスポート実行
	 */
	function export($reserves){

	}

}

class CalendarExportDeletageAction implements SOY2PluginDelegateAction{

	private $_list = array();
	private $mode = "list";
	private $action;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){

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
	function export($reserves){
		if($this->action){
			return $this->action->export($reserves);
		}
	}
}

SOYShopPlugin::registerExtension("soyshop.calendar.export", "CalendarExportDeletageAction");
