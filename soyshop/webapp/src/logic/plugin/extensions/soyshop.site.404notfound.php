<?php
/*
 * soyshop.site.beforeoutput.php
 * Created: 2010/03/11
 */

class SOYShopSite404NotFoundAction implements SOY2PluginAction{

	function execute(){

	}

}
class SOYShopSite404NotFoundDelegateAction implements SOY2PluginDelegateAction{

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		$action->execute();
	}
}
SOYShopPlugin::registerExtension("soyshop.site.404notfound", "SOYShopSite404NotFoundDelegateAction");