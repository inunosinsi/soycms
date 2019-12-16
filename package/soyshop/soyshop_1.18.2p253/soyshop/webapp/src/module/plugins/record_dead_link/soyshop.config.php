<?php

class RecordDeadLinkConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		include_once(dirname(__FILE__) . "/config/RecordDeadLinkConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("RecordDeadLinkConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "リンク切れページのアクセス履歴の確認";
	}
}
SOYShopPlugin::extension("soyshop.config", "record_dead_link", "RecordDeadLinkConfig");
?>