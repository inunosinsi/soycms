<?php
class CommonNoticeStockConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		include_once(dirname(__FILE__) . "/config/CommonNoticeStockConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("CommonNoticeStockConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "在庫数残りわずか通知プラグインの設定";
	}
}
SOYShopPlugin::extension("soyshop.config","common_notice_stock","CommonNoticeStockConfig");
?>