<?php
class OrderInvoiceAddReceiptConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.order_invoice_add_receipt_button.config.ReceiptConfigPage");
		$form = SOY2HTMLFactory::createInstance("ReceiptConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "領収書出力ボタン追加プラグインの設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "order_invoice_add_receipt_button", "OrderInvoiceAddReceiptConfig");
