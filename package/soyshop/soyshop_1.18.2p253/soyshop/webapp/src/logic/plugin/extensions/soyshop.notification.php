<?php
class SOYShopNotification implements SOY2PluginAction{

	function execute(){

	}

}
class SOYShopNotificationDeletageAction implements SOY2PluginDelegateAction{

	function run($extentionId,$moduleId,SOY2PluginAction $action){

		if($action instanceof SOYShopNotification){
			$action->execute();
		}
	}
}
SOYShopPlugin::registerExtension("soyshop.notification","SOYShopNotificationDeletageAction");
