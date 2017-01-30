<?php
/**
 * プラグイン 管理画面
 */
class DiscountBulkBuyingConfig extends SOYShopConfigPageBase{
	
	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.discount_bulk_buying.util.DiscountBulkBuyingConfigUtil");
		SOY2::import("module.plugins.discount_bulk_buying.util.DiscountBulkBuyingConditionUtil");
		include_once(dirname(__FILE__) . "/config_form/DiscountBulkBuyingConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("DiscountBulkBuyingConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}
	
	/**
	 * h2タグ
	 * @return string
	 */
	function getConfigPageTitle(){
		return "まとめ買い割引";
	}
	
}
SOYShopPlugin::extension("soyshop.config", "discount_bulk_buying", "DiscountBulkBuyingConfig");
?>