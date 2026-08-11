<?php
class SOYShopAreaBase implements SOY2PluginAction{

	/**
	 * @return array("num" => "pref_name")
	 */
	function getArea(){
		
	}
}

class SOYShopAreaDeletageAction implements SOY2PluginDelegateAction{

	private $mode = "area";
	private $_area;
	
	
	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		
		
		switch($this->mode){
			case "area":
				$this->_area = $action->getArea();
				break;
		}
	}
	
	function getArea(){
		return $this->_area;
	}
	
	function setMode($mode){
		$this->mode = $mode;
	}
}
SOYShopPlugin::registerExtension("soyshop.area", "SOYShopAreaDeletageAction");
?>