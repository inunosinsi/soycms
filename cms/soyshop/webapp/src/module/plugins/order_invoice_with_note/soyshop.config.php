<?php
class OrderInvoiceWithNoteConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		// include_once(dirname(__FILE__) . "/config/OrderInvoiceConfigFormPage.class.php");
		// $form = SOY2HTMLFactory::createInstance("OrderInvoiceConfigFormPage");
		// $form->setConfigObj($this);
		// $form->execute();
		// return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "控え有り印刷用納品書作成プラグインの設定";
	}
}
SOYShopPlugin::extension("soyshop.config","order_invoice_with_note","OrderInvoiceWithNoteConfig");
