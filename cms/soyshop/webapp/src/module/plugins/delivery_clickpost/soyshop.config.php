<?php
class DeliveryClickPostConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.delivery_clickpost.config.DeliveryClickPostConfigFormPage");
		$form = SOY2HTMLFactory::createInstance("DeliveryClickPostConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "郵送(クリックポスト)の設定";
	}
}
SOYShopPlugin::extension("soyshop.config","delivery_clickpost","DeliveryClickPostConfig");
