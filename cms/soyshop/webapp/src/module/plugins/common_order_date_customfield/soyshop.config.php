<?php
class CommonOrderDateCustomfieldConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.common_order_date_customfield.config.CommonOrderDateCustomfieldConfigFormPage");
		$form = SOY2HTMLFactory::createInstance("CommonOrderDateCustomfieldConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "オーダーカスタムフィールド(日付)の設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "common_order_date_customfield", "CommonOrderDateCustomfieldConfig");
