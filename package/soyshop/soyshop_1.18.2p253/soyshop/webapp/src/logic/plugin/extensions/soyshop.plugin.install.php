<?php
//プラグイン詳細画面
class SOYShopPluginInstallerBase implements SOY2PluginAction{

	/**
	 * インストール
	 */
	function onInstall(){

	}

	/**
	 * アンインストール
	 */
	function onUnInstall(){

	}

}
class SOYShopPluginInstallerDeletageAction implements SOY2PluginDelegateAction{

	private $mode = "install";

	function run($extetensionId,$moduleId,SOY2PluginAction $action){

		if($action instanceof SOYShopPluginInstallerBase){


			if($this->mode == "install"){
				$action->onInstall();
			}else{
				$action->onUnInstall();
			}


		}


	}


	function getMode() {
		return $this->mode;
	}
	function setMode($mode) {
		$this->mode = $mode;
	}
}
SOYShopPlugin::registerExtension("soyshop.plugin.install","SOYShopPluginInstallerDeletageAction");
?>