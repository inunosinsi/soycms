<?php
/*
 * soyshop.site.prepare.php
 * Created: 2010/02/20
 */

class SOYShopSitePrepareAction implements SOY2PluginAction{

	/**
	 * @return string
	 */
	function prepare(){

	}

}
class SOYShopSitePrepareDelegateAction implements SOY2PluginDelegateAction{

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
		if($action instanceof SOYShopSitePrepareAction){
			$action->prepare();
		}
	}

}
SOYShopPlugin::registerExtension("soyshop.site.prepare","SOYShopSitePrepareDelegateAction");