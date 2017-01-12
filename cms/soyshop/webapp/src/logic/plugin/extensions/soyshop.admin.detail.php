<?php
class SOYShopAdminDetailBase implements SOY2PluginAction{
	
	private $detailId;
	
	function getTitle(){}
	function getContent(){}
	
	function getDetailId(){
		return $this->detailId;
	}
	
	function setDetailId($detailId){
		$this->detailId = $detailId;
	}
}

class SOYShopAdminDetailDeletageAction implements SOY2PluginDelegateAction{
	
	private $_content;
	private $detailId;
	
	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		$action->setDetailId($this->detailId);
		
		$array = array();
		$array["title"] = $action->getTitle();
		$array["content"] = $action->getContent();
		$this->_content = $array;
	}
	
	function getContent(){
		return $this->_content;
	}
	function setDetailId($detailId){
		$this->detailId = $detailId;
	}
}
SOYShopPlugin::registerExtension("soyshop.admin.detail", "SOYShopAdminDetailDeletageAction");
?>