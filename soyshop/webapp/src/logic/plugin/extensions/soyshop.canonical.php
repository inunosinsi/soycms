<?php
class SOYShopCanonicalBase implements SOY2PluginAction{

	function canonical(){

	}
}
class SOYShopCanonicalDeletageAction implements SOY2PluginDelegateAction{

	//ページ番号を入れる
	private $mode = "list";
	private $_alias;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
		switch($this->mode){
			case "list":
			default:
				$alias = $action->canonical();
				if(isset($alias)) $this->_alias = $alias;
				break;
		}
	}

	function setMode($mode) {
		$this->mode = $mode;
	}
	function getAlias(){
		return $this->_alias;
	}
}
SOYShopPlugin::registerExtension("soyshop.canonical", "SOYShopCanonicalDeletageAction");
