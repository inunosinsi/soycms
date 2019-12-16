<?php
class CommonPurchaseCheckConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		
		include_once(dirname(__FILE__)  . "/config/PurchaseCheckConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("PurchaseCheckConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		return "購入最低金額設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "common_purchase_check", "CommonPurchaseCheckConfig");
?>