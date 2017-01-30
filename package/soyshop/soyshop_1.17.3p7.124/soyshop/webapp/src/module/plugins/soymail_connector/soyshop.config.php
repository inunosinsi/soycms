<?php
class SOYMailConnectorConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		
		include_once(dirname(__FILE__) . "/config/SOYMailConnectorConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("SOYMailConnectorConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		return "SOY Mail連携プラグインの設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "soymail_connector", "SOYMailConnectorConfig");
?>