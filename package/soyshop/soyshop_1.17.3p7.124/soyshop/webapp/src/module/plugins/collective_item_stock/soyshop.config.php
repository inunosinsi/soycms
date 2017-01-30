<?php
class CollectiveItemStockConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		
		include_once(dirname(__FILE__). "/config/CollectiveItemStockConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("CollectiveItemStockConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		return "在庫一括設定";
	}

}
SOYShopPlugin::extension("soyshop.config", "collective_item_stock", "CollectiveItemStockConfig");
?>