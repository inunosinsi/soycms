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

	private $mode;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		switch($this->mode){
			case "post":
				$action->doPost();
				break;
			default:
				echo $action->html();
		}
	}

	function setMode($mode){
		$this->mode = $mode;
	}
}
SOYShopPlugin::registerExtension("soyshop.slip.html","SOYShopSlipHtmlDeletageAction");
