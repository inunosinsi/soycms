<?php

class ModuleTypeListComponent extends HTMLList{
	
	private $mode;
	private $pluginLogic;
	
	protected function populateItem($entity, $key){
		
		if($this->mode == SOYShop_PluginConfig::MODE_INSTALLED){
			$list = $this->getPluginLogic()->getInstalledModules($key);
		}else{
			$list = $this->getPluginLogic()->getModulesByType($key);
		}
		
		$this->addModel("is_modules", array(
	    	"visible" => (count($list) > 0)
	    ));
	    
	    $this->addLabel("module_type_name", array(
	    	"text" => $entity
	    ));
	    
	    $this->createAdd("module_list", "_common.Plugin.ModuleListComponent", array(
	    	"list" => $list
	    ));
		
		if(is_null($key)) return false;
	}
	
	private function getPluginLogic(){
		if(!$this->pluginLogic){
			$logic = SOY2Logic::createInstance("logic.plugin.SOYShopPluginLogic");
			$logic->prepare();
			$this->pluginLogic = $logic;
		}
		return $this->pluginLogic;
	}
	
	function setMode($mode){
		$this->mode = $mode;
	}
}