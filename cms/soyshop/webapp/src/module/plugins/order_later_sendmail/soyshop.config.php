<?php
class OrderLaterSendmailConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		include_once(dirname(__FILE__) . "/config/OrderLaterSendmailConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("OrderLaterSendmailConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "確認メール後日送信プラグイン";
	}
}
SOYShopPlugin::extension("soyshop.config", "order_later_sendmail", "OrderLaterSendmailConfig");
?>