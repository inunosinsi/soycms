<?php

class SOYShopSlipHtmlBase implements SOY2PluginAction{

	/**
	 * @return string
	 */
	function html(){}

	/**
	 * doPost
	 */
	function doPost(){}
}

class SOYShopSlipHtmlDeletageAction implements SOY2PluginDelegateAction{

	function run($extetensionId, $moduleId, SOY2PluginAction $action){

		if(strtolower($_SERVER['REQUEST_METHOD']) == "post"){
			$action->doPost();
		}else{
			echo $action->html();
		}
	}
}
SOYShopPlugin::registerExtension("soyshop.slip.html","SOYShopSlipHtmlDeletageAction");
