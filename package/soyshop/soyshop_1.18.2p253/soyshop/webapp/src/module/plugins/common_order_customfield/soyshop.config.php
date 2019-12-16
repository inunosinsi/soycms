<?php
class CommonOrderCustomfieldConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.common_order_customfield.config.CommonOrderCustomfieldConfigFormPage");
		$form = SOY2HTMLFactory::createInstance("CommonOrderCustomfieldConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "オーダーカスタムフィールドの設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "common_order_customfield", "CommonOrderCustomfieldConfig");
