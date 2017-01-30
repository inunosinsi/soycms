<?php
/*
 * soyshop.site.onload.php
 * Created: 2010/02/20
 */

class SOYShopSiteUserOnOutputAction implements SOY2PluginAction{

	/**
	 * @return string
	 */
	function onOutput($html){

	}

}
class SOYShopSiteUserOnOutputDelegateAction implements SOY2PluginDelegateAction{

	private $html;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
		$res = $action->onOutput($this->getHtml());
		if(!is_null($res)){
			$this->setHtml($res);
		}
	}
	function getHtml() {
		return $this->html;
	}
	function setHtml($html) {
		$this->html = $html;
	}
}
SOYShopPlugin::registerExtension("soyshop.site.user.onoutput","SOYShopSiteUserOnOutputDelegateAction");
