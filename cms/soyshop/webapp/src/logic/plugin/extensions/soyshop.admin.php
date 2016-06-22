<?php
class SOYShopAdminBase implements SOY2PluginAction{
	
	function execute(){
		
	}
}

class SOYShopAdminDeletageAction implements SOY2PluginDelegateAction{

	private $_area;
	
	function run($extetensionId, $moduleId, SOY2PluginAction $action){		
		$this->_area = $action->execute();
	}
}
SOYShopPlugin::registerExtension("soyshop.admin", "SOYShopAdminDeletageAction");
?>