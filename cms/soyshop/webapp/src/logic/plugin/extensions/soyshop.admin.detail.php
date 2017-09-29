<?php
class SOYShopAdminDetailBase implements SOY2PluginAction{

	private $detailId;

	function getTitle(){}
	function getContent(){}
	function getScripts(){}
	function getCSS(){}

	function getDetailId(){
		return $this->detailId;
	}

	function setDetailId($detailId){
		$this->detailId = $detailId;
	}
}

class SOYShopAdminDetailDeletageAction implements SOY2PluginDelegateAction{

	private $_content;
	private $_scripts;
	private $_css;
	private $detailId;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		$action->setDetailId($this->detailId);

		$array = array();
		$array["title"] = $action->getTitle();
		$array["content"] = $action->getContent();
		$this->_content = $array;
		$this->_scripts = $action->getScripts();
		$this->_css = $action->getCSS();
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
	function setDetailId($detailId){
		$this->detailId = $detailId;
	}
}
SOYShopPlugin::registerExtension("soyshop.admin.detail", "SOYShopAdminDetailDeletageAction");
