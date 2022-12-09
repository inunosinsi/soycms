<?php
class MemberSpecialPriceConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.member_special_price.config.AddItemConfigPage");
		$form = SOY2HTMLFactory::createInstance("AddItemConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "会員特別価格の項目設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "member_special_price", "MemberSpecialPriceConfig");
?>