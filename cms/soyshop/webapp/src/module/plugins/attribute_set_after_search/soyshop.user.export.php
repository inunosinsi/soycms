<?php
/*
 */
class AttributeSetAfterUserSearchExport extends SOYShopUserExportBase{
	
	private $csvLogic;

	/**
	 * 検索結果一覧に表示するメニューの表示文言
	 */
	function getMenuTitle(){
		return "顧客属性の一括設定";
	}

	/**
	 * 検索結果一覧に表示するメニューの説明
	 */
	function getMenuDescription(){
		SOY2::import("module.plugins.attribute_set_after_search.form.AttributeSettingPage");
		$form = SOY2HTMLFactory::createInstance("AttributeSettingPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * export エクスポート実行
	 */
	function export($users){
		SOY2Logic::createInstance("module.plugins.attribute_set_after_search.logic.RegisterLogic")->setUserAttribute($users);
		echo "設定しました。";
		exit;
	}
}

SOYShopPlugin::extension("soyshop.user.export","attribute_set_after_search","AttributeSetAfterUserSearchExport");
?>