<?php
class ItemBlockCategoryConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.item_block_category.config.ItemBlockCategoryConfigPage");
		$form = SOY2HTMLFactory::createInstance("ItemBlockCategoryConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		return "カテゴリ商品ブロック生成プラグインの設定";
	}

}
SOYShopPlugin::extension("soyshop.config", "item_block_category", "ItemBlockCategoryConfig");
