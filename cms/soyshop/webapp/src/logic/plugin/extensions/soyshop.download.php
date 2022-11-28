<?php
class SOYShopDownload implements SOY2PluginAction{

	/**
	 * $_GET["soyshop_download"] or $_GET["soyshop_action"]がある場合に実行される
	 */
	function execute(){}

}
class SOYShopDownloadDeletageAction implements SOY2PluginDelegateAction{

	function run($extentionId,$moduleId,SOY2PluginAction $action){

		if($action instanceof SOYShopDownload){
			$action->execute();
		}
	}
}
SOYShopPlugin::registerExtension("soyshop.download","SOYShopDownloadDeletageAction");
