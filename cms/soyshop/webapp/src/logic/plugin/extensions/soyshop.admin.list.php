<?php
class SOYShopAdminListBase implements SOY2PluginAction{

	function getTabName(){}
	function getTitle(){}
	function getContent(){}
	function getScripts(){}
	function getCSS(){}
}

class SOYShopAdminListDeletageAction implements SOY2PluginDelegateAction{

	private $_contents;
	private $_scripts;
	private $_css;
	private $mode;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		$array = array();
		switch($this->mode){
			case "tab":
				$array["tab"] = $action->getTabName();
				break;
			case "list":
			default:
				$array["title"] = $action->getTitle();
				$array["content"] = $action->getContent();
				$this->_scripts = $action->getScripts();
				$this->_css = $action->getCSS();
				break;
		}

		$this->_contents[$moduleId] = $array;
	}

	function getContents(){
		return $this->_contents;
	}
	function getScripts(){
		return $this->_scripts;
	}
	function getCSS(){
		return $this->_css;
	}

	function setMode($mode){
		$this->mode = $mode;
	}
}
SOYShopPlugin::registerExtension("soyshop.admin.list", "SOYShopAdminListDeletageAction");
