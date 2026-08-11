<?php
class DiscountItemStockConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		include_once(dirname(__FILE__) . "/config/DiscountItemStockConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("DiscountItemStockConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		return "在庫数値引きプラグイン";
	}

}
SOYShopPlugin::extension("soyshop.config", "discount_item_stock", "DiscountItemStockConfig");
?>