<?php
class DeliveryNormalConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		
		include_once(dirname(__FILE__) ."/config/DeliveryNormalConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("DeliveryNormalConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "配送料、配達時間帯の設定（標準配送モジュール）";
	}
}
SOYShopPlugin::extension("soyshop.config","delivery_normal","DeliveryNormalConfig");






