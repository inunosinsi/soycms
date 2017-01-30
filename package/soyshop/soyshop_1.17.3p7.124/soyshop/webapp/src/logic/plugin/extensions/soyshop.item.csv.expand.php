<?php

class SOYShopItemCSVExpandBase implements SOY2PluginAction{

	private $itemId;

	function execute(){
		
	}
	
	function getItemId(){
		return $this->itemId;
	}
	
	function setItemId($itemId){
		$this->itemId = $itemId;
	}
}
class SOYShopItemCSVExpandDeletageAction implements SOY2PluginDelegateAction{

	private $mode = "expand";
	private $itemId;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		
		if($action instanceof SOYShopItemCSVExpandBase){
			$action->setItemId($this->itemId);
			switch($this->mode){
				case "expand":
					$action->execute();
					break;
			}
		}
	}
	
	function setItemId($itemId){
		$this->itemId = $itemId;
	}
}
SOYShopPlugin::registerExtension("soyshop.item.csv.expand", "SOYShopItemCSVExpandDeletageAction");