<?php
class SOYShopAdminListBase implements SOY2PluginAction{
	
	function getTabName(){}
	function getTitle(){}
	function getContent(){}
}

class SOYShopAdminListDeletageAction implements SOY2PluginDelegateAction{
	
	private $_contents;
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
				break;
		}
				
		$this->_contents[$moduleId] = $array;
	}
	
	function getContents(){
		return $this->_contents;
	}
	
	function setMode($mode){
		$this->mode = $mode;
	}
}
SOYShopPlugin::registerExtension("soyshop.admin.list", "SOYShopAdminListDeletageAction");
?>