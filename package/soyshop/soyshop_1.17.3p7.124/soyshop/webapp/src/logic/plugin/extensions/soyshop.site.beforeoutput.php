<?php
/*
 * soyshop.site.beforeoutput.php
 * Created: 2010/03/11
 */

class SOYShopSiteBeforeOutputAction implements SOY2PluginAction{

	function doPost($page){
		
	}

	/**
	 * @return string
	 */
	function beforeOutput($page){

	}

}
class SOYShopSiteBeforeOutputDelegateAction implements SOY2PluginDelegateAction{

	private $page;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		if(isset($_POST) && count($_POST) > 0){
			$action->doPost($this->getPage());
		}

		$action->beforeOutput($this->getPage());
	}

	function getPage() {
		return $this->page;
	}
	function setPage($page) {
		$this->page = $page;
	}
}
SOYShopPlugin::registerExtension("soyshop.site.beforeoutput", "SOYShopSiteBeforeOutputDelegateAction");