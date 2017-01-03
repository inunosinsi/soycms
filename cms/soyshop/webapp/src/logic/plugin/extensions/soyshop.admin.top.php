<?php
class SOYShopAdminTopBase implements SOY2PluginAction{
	
	//ログインしているアカウントの権限によって表示するかどうか？
	function allowDisplay(){
		return true;
	}
	function getTitle(){}
	function getContent(){}
	function getLink(){}
	function getLinkTitle(){}
}

class SOYShopAdminTopDeletageAction implements SOY2PluginDelegateAction{
	
	private $_contents;
	
	function run($extetensionId, $moduleId, SOY2PluginAction $action){		
		if($action->allowDisplay()){
			$array = array();
			$array["title"] = $action->getTitle();
			$array["content"] = $action->getContent();
			$array["link"] = $action->getLink();
			$array["link_title"] = $action->getLinkTitle();
			$this->_contents[$moduleId] = $array;
		}
	}
	
	function getContents(){
		return $this->_contents;
	}
}
SOYShopPlugin::registerExtension("soyshop.admin.top", "SOYShopAdminTopDeletageAction");
?>