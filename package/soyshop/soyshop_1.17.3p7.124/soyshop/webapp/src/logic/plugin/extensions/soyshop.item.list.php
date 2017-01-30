<?php
/*
 * soyshop.item.list.php
 * Created: 2010/10/04
 */

class SOYShopItemListBase implements SOY2PluginAction{

	private $moduleId;
	private $pageId;
	private $isUse = false;


	/**
	 * @return string
	 */
	function getLabel(){
		return $this->moduleId;	
	}
	
	/**
	 * @return array
	 */
	function getItems($pageObj, $offset, $limit){
		
	}
	
	/**
	 * @return number
	 */
	function getTotal($pageObj){
		
	}
	
	/**
	 * @return string
	 */
	function getForm(){
		
	}
	
	function doPost(){
	}


	function getModuleId() {
		return $this->moduleId;
	}
	function setModuleId($moduleId) {
		$this->moduleId = $moduleId;
	}
	function getPageId(){
		return $this->pageId;
	}
	function setPageId($pageId){
		$this->pageId = $pageId;
	}
	function isUse(){
		return $this->isUse;
	}
	function setIsUse($isUse){
		$this->isUse = $isUse;
	}
}
class SOYShopItemListDelegateAction implements SOY2PluginDelegateAction{

	private $mode = "list";
	private $modules = array();
	private $obj = array();
	private $action;
	private $_form = array();
	
	function run($extetensionId,$moduleId,SOY2PluginAction $action){

		if($action instanceof SOYShopItemListBase){

			$action->setModuleId($moduleId);
						
			switch($this->mode){
				case "list":
					$action->setPageId($this->obj->getPage()->getId());
					$label = $action->getLabel();
					$this->modules[$moduleId] = $label;
					if($this->isUse($moduleId)){
						$action->setIsUse(true);
					}
					$this->_form[$moduleId] = $action->getForm();
					break;
				case "post":					
					$action->setPageId($this->obj->getPage()->getId());
					if($this->isUse($moduleId)){
						$action->doPost();
					}
					break;
				case "search":
				default:					
					break;
			}
			
			$this->action = $action;

		}
	}
	
	/* getter setter */

	function getList(){
		return $this->modules;
	}
	function getForm(){
		if(!$this->action) return array();
		return $this->_form;
	}
	function isUse($moduleId){
		if(is_null($this->obj)){
			return false;
		}
		$hasModuleId = $this->obj->getModuleId();
		return (!is_null($hasModuleId) && $hasModuleId == $moduleId) ? true : false;
	}

	function setObj($obj){
		$this->obj = $obj;
	}
	function setMode($mode){
		$this->mode = $mode;
	}

	function getItems($pageObj, $limit, $offset){
		if(!$this->action)return array();
		return $this->action->getItems($pageObj, $limit, $offset);
	}
	
	function getTotal($pageObj){
		if(!$this->action)return 0;
		return $this->action->getTotal($pageObj);
	}
}
SOYShopPlugin::registerExtension("soyshop.item.list","SOYShopItemListDelegateAction");
