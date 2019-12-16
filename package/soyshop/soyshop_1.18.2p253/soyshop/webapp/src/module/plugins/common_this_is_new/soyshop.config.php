<?php
class CommonThisIsNewConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		include_once(dirname(__FILE__) . "/config/CommonThisIsNewConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("CommonThisIsNewConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "新着商品マーク表示プラグインの設定";
	}
}
SOYShopPlugin::extension("soyshop.config","common_this_is_new","CommonThisIsNewConfig");
?>