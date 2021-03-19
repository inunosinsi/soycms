<?php
/*
 * soyshop.item.csv.php
 * Created: 2010/02/15
 */

class SOYShopItemCSVBase implements SOY2PluginAction{

	private $moduleId;


	function getLabel(){

	}

	/**
	 * export
	 */
	function export($itemId){

	}

	/**
	 * import
	 */
	function import($itemId, $value){

	}


	function getModuleId() {
		return $this->moduleId;
	}
	function setModuleId($moduleId) {
		$this->moduleId = $moduleId;
	}
}
class SOYShopItemCSVDeletageAction implements SOY2PluginDelegateAction{

	private $mode = "list";
	private $modules = array();

	function run($extetensionId, $moduleId, SOY2PluginAction $action){

		if($action instanceof SOYShopItemCSVBase){

			$action->setModuleId($moduleId);
			$label = $action->getLabel();

			$this->modules[$moduleId] =  array(
					"label" => $action->getLabel(),
					"plugin" => ($this->mode != "list") ? $action : null
			);

		}
	}

	function getModules(){
		return $this->modules;
	}

	function setMode($mode){
		$this->mode = $mode;
	}

	function import($moduleId, $itemId, $value){
		if(isset($this->modules[$moduleId])){
			$this->modules[$moduleId]["plugin"]->import($itemId, $value);
		}
	}
}
SOYShopPlugin::registerExtension("soyshop.item.csv", "SOYShopItemCSVDeletageAction");
