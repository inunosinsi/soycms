<?php
class SOYShopAdminBase implements SOY2PluginAction{

	function execute(){

	}

	/** return array(array("link" => ""))**/
	function initLinks(){

	}
}

class SOYShopAdminDeletageAction implements SOY2PluginDelegateAction{

	private $mode = "exec";
	private $_list = array();

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		switch($this->mode){
			case "init":
				$links = $action->initLinks();
				if(isset($links)) $this->_list[$moduleId] = $action->initLinks();
				break;
			default:
				$action->execute();
		}
	}

	function getList(){
		return $this->_list;
	}

	function setMode($mode){
		$this->mode = $mode;
	}
}
SOYShopPlugin::registerExtension("soyshop.admin", "SOYShopAdminDeletageAction");
