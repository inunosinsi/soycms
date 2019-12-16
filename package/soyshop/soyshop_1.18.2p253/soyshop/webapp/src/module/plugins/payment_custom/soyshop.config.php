<?php
class PaymentCustomConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");

		include_once(dirname(__FILE__) . "/SOYShopPaymentCustomConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("SOYShopPaymentCustomConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "カスタム支払モジュールの設定";
	}
}
SOYShopPlugin::extension("soyshop.config","payment_custom","PaymentCustomConfig");
?>
