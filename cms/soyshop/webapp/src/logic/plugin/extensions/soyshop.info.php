<?php
/**
 * プラグイン詳細画面
 */
class SOYShopInfoPageBase implements SOY2PluginAction{

	/**
	 * @param  bool
	 * @return string
	 * 各種プラグインの詳細画面と設定ページの拡張設定にリンクが出力されます
	 */
	function getPage(bool $active=false){
		return "";
	}
}
class SOYShopInfoPageDeletageAction implements SOY2PluginDelegateAction{

	private $active;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		echo $action->getPage((bool)$this->active);
	}

	function setActive($active) {
		$this->active = $active;
	}
}
SOYShopPlugin::registerExtension("soyshop.info", "SOYShopInfoPageDeletageAction");
