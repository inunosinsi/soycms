<?php
class ItemListCategoryCustomfieldConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		
		include_once(dirname(__FILE__) . "/config/ItemListCategoryCustomfieldConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("ItemListCategoryCustomfieldConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		return "カテゴリカスタムフィールド商品一覧モジュールの設定";
	}

}
SOYShopPlugin::extension("soyshop.config", "item_list_category_customfield", "ItemListCategoryCustomfieldConfig");

?>
