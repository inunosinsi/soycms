<?php
class PaymentDaibikiConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");

		include_once(dirname(__FILE__) . "/config/PaymentDaibikiConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("PaymentDaibikiConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "代引き手数料の設定";
	}
}
SOYShopPlugin::extension("soyshop.config","payment_daibiki","PaymentDaibikiConfig");
?>