<?php
class SOYShopOrderUpload implements SOY2PluginAction{

	/**
	 * @return string
	 */
	function getUploadPageTitle(){
		return "";
	}

	function getTitle(){}
	function getContent(){}
	function getScripts(){}
	function getCSS(){}
}
class SOYShopOrderUploadDeletageAction implements SOY2PluginDelegateAction{

	private $mode;
	private $pluginId;
	private $_list = array();
	private $_content;
	private $_scripts;
	private $_css;

	function run($extensionId, $moduleId, SOY2PluginAction $action){

		switch($this->mode){
			case "list":
				$this->_list[$moduleId] = $action->getUploadPageTitle();
				break;
			default:
				if($this->pluginId == $moduleId){
					$array = array();
					$array["title"] = $action->getTitle();
					$array["content"] = $action->getContent();
					$this->_content = $array;
					$this->_scripts = $action->getScripts();
					$this->_css = $action->getCSS();
				}
		}
	}

	function setMode($mode){
		$this->mode = $mode;
	}
	function setPluginId($pluginId){
		$this->pluginId = $pluginId;
	}

	function getList(){
		return $this->_list;
	}

	function getContent(){
		return $this->_content;
	}
	function getScripts(){
		return $this->_scripts;
	}
	function getCSS(){
		return $this->_css;
	}
}
SOYShopPlugin::registerExtension("soyshop.order.upload","SOYShopOrderUploadDeletageAction");
