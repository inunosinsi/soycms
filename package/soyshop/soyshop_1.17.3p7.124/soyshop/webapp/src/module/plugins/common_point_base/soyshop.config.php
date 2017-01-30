<?php
class CommonPointBaseConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		include_once(dirname(__FILE__) . "/config/CommonPointBaseConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("CommonPointBaseConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "ポイント制設定プラグインの設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "common_point_base", "CommonPointBaseConfig");
?>