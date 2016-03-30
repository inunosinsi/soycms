<?php
class CustomIconFieldConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		
		SOY2::import("module.plugins.custom_icon_field.config.CustomIconFieldConfigFormPage");
		$form = SOY2HTMLFactory::createInstance("CustomIconFieldConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "カスタムアイコンフィールドの設定";
	}
	
}
SOYShopPlugin::extension("soyshop.config", "custom_icon_field", "CustomIconFieldConfig");
?>