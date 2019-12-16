<?php
/*
 * soyshop.site.onload.php
 * Created: 2010/02/20
 */

class SOYShopSiteOnLoadAction implements SOY2PluginAction{

	/**
	 * @return string
	 */
	function onLoad($page){

	}

}
class SOYShopSiteOnLoadDelegateAction implements SOY2PluginDelegateAction{

	private $page;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
		echo $action->onLoad($this->getPage());
	}
	function getPage() {
		return $this->page;
	}
	function setPage($page) {
		$this->page = $page;
	}

}
SOYShopPlugin::registerExtension("soyshop.site.onload","SOYShopSiteOnLoadDelegateAction");