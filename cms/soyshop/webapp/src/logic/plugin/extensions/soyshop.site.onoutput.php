<?php
/*
 * soyshop.site.onload.php
 * Created: 2010/02/20
 */

class SOYShopSiteOnOutputAction implements SOY2PluginAction{
	
	private $page;

	/**
	 * @return string
	 */
	function onOutput($html){

	}
	
	function getPage(){
		return $this->page;
	}
		
	function setPage($page){
		$this->page = $page;
	}

}
class SOYShopSiteOnOutputDelegateAction implements SOY2PluginDelegateAction{

	private $html;
	private $page;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
		$action->setPage($this->getPage());
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
	
	function getPage(){
		return $this->page;
	}
	function setPage($page){
		$this->page = $page;
	}
}
SOYShopPlugin::registerExtension("soyshop.site.onoutput","SOYShopSiteOnOutputDelegateAction");