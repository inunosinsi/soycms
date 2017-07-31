<?php
class SOYCMSConnectorConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.soycms_connector.config.SOYCMSConnectorConfigPage");
		$form = SOY2HTMLFactory::createInstance("SOYCMSConnectorConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		return "SOY CMS連携プラグインの設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "soycms_connector", "SOYCMSConnectorConfig");
?>
