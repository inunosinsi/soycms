<?php

class SOYShopShopNameBase implements SOY2PluginAction{

	/**
	 * @param SOYShop_Item
	 * @return string
	 */
	function getForm(){
		return "";
	}

	/**
	 * doPost
	 */
	function doPost(){}
}
class SOYShopShopNameDeletageAction implements SOY2PluginDelegateAction{

	private $item;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){

		if(strtolower($_SERVER['REQUEST_METHOD']) == "post"){
			$action->doPost();
		}else{
			echo $action->getForm();
		}
	}
}
SOYShopPlugin::registerExtension("soyshop.shop.name","SOYShopShopNameDeletageAction");
