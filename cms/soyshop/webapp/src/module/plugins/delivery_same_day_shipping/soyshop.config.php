<?php
class DeliverySameDayShippingConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		
		include_once(dirname(__FILE__) ."/config/DeliverySameDayShippingConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("DeliverySameDayShippingConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "即日発送設定";
	}
}
SOYShopPlugin::extension("soyshop.config","delivery_same_day_shipping","DeliverySameDayShippingConfig");