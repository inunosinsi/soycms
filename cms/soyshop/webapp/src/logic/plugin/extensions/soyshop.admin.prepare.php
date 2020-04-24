<?php
/*
 * soyshop.site.prepare.php
 * Created: 2010/02/20
 */

class SOYShopAdminPrepareAction implements SOY2PluginAction{

	/**
	 * @return string
	 */
	function prepare(){

	}

}
class SOYShopAdminPrepareDelegateAction implements SOY2PluginDelegateAction{

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
		if($action instanceof SOYShopAdminPrepareAction){
			$action->prepare();
		}
	}

}
SOYShopPlugin::registerExtension("soyshop.admin.prepare","SOYShopAdminPrepareDelegateAction");
