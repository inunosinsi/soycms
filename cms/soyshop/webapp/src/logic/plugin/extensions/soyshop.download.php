<?php
class SOYShopDownload implements SOY2PluginAction{

	function execute(){

	}

}
class SOYShopDownloadDeletageAction implements SOY2PluginDelegateAction{

	function run($extentionId,$moduleId,SOY2PluginAction $action){

		if($action instanceof SOYShopDownload){
			$action->execute();
		}
	}
}
SOYShopPlugin::registerExtension("soyshop.download","SOYShopDownloadDeletageAction");
