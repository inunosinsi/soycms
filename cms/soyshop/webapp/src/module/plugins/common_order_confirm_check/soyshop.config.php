<?php
class CommonOrderConfirmCheckConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		include_once(dirname(__FILE__) . "/config/CommonOrderConfirmCheckConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("CommonOrderConfirmCheckConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "入力内容確認の設定";
	}
}
SOYShopPlugin::extension("soyshop.config","common_order_confirm_check","CommonOrderConfirmCheckConfig");
?>