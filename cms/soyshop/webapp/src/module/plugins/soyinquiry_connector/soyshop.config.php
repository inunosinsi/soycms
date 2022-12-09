<?php
class SOYInquiryConnectorConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.soyinquiry_connector.config.SOYInquiryConnectorConfigPage");
		$form = SOY2HTMLFactory::createInstance("SOYInquiryConnectorConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		return "SOY Inquiry連携プラグインの設定";
	}
}
SOYShopPlugin::extension("soyshop.config","soyinquiry_connector","SOYInquiryConnectorConfig");
