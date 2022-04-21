<?php
class SOYShopAddressBase implements SOY2PluginAction{

	/**
	 * @return array(array("label" => "", "required" => false, "example" => "")...)
	 */
	function getAddressItems(){
		return array();
	}
}

class SOYShopAddressDeletageAction implements SOY2PluginDelegateAction{
	
	private $_items;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		$items = $action->getAddressItems();
		if(is_array($items) && count($items)){
			foreach($items as $item){
				if(!isset($item["label"])) $item["label"] = "";
				if(!isset($item["required"]) || !is_bool($item["required"])) $item["required"] = false;
				if(!isset($item["example"]) || !is_string($item["example"])) $item["example"] = "";
				$this->_items[] = $item;
			}
		}
	}
	
	function getAddressItems(){
		return $this->_items;
	}	
}
SOYShopPlugin::registerExtension("soyshop.address", "SOYShopAddressDeletageAction");
