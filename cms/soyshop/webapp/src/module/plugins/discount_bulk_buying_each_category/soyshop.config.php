<?php
/**
 * プラグイン 管理画面
 */
class DiscountBulkBuyingEachCategoryConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.discount_bulk_buying_each_category.config.DiscountBulkBuyingConfigPage");
		$form = SOY2HTMLFactory::createInstance("DiscountBulkBuyingConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * h2タグ
	 * @return string
	 */
	function getConfigPageTitle(){
		return "カテゴリ版まとめ買い割引";
	}

}
SOYShopPlugin::extension("soyshop.config", "discount_bulk_buying_each_category", "DiscountBulkBuyingEachCategoryConfig");
