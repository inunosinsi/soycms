<?php
class CommonOrderDateCustomfieldConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		include_once(dirname(__FILE__) . "/config/CommonOrderDateCustomfieldConfigFormPage.class.php");
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
?>