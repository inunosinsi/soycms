<?php
class SOYShopAdminBase implements SOY2PluginAction{
	
	function execute(){
		
	}
}

class SOYShopAdminDeletageAction implements SOY2PluginDelegateAction{
	
	function run($extetensionId, $moduleId, SOY2PluginAction $action){		
		$action->execute();
	}
}
SOYShopPlugin::registerExtension("soyshop.admin", "SOYShopAdminDeletageAction");
?>