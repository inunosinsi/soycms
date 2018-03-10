<?php
/*
 * Created on 2009/02/10
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
//プラグイン詳細画面
class SOYShopInfoPageBase implements SOY2PluginAction{

	/**
	 * @return string
	 * 各種プラグインの詳細画面と設定ページの拡張設定にリンクが出力されます
	 */
	function getPage(){
		return "";
	}
}
class SOYShopInfoPageDeletageAction implements SOY2PluginDelegateAction{

	private $active;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		echo $action->getPage($this->getActive());
	}


	function getActive() {
		return $this->active;
	}
	function setActive($active) {
		$this->active = $active;
	}
}
SOYShopPlugin::registerExtension("soyshop.info", "SOYShopInfoPageDeletageAction");
