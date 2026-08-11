<?php
class AutoDeleteOrderConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins." . $this->getModuleId() . ".config.AutoDeleteOrderConfigPage");
		$form = SOY2HTMLFactory::createInstance("AutoDeleteOrderConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "キャンセル注文自動削除プラグイン";
	}
}
SOYShopPlugin::extension("soyshop.config", "auto_delete_order", "AutoDeleteOrderConfig");
