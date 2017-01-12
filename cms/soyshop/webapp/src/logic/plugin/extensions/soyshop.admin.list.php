<?php
class SOYShopAdminListBase implements SOY2PluginAction{
	
	function getTabName(){}
	function getTitle(){}
	function getContent(){}
}

class SOYShopAdminListDeletageAction implements SOY2PluginDelegateAction{
	
	private $_contents;
	
	function run($extetensionId, $moduleId, SOY2PluginAction $action){		
		$array = array();
		$array["tab"] = $action->getTabName();
		$array["title"] = $action->getTitle();
		$array["content"] = $action->getContent();
		$this->_contents[$moduleId] = $array;
	}
	
	function getContents(){
		return $this->_contents;
	}
}
SOYShopPlugin::registerExtension("soyshop.admin.list", "SOYShopAdminListDeletageAction");
?>