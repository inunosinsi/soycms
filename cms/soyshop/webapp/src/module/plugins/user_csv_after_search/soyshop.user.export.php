<?php
/*
 */
class UserCSVAfterSearchUserExport extends SOYShopUserExportBase{
	
	/**
	 * 検索結果一覧に表示するメニューの表示文言
	 */
	function getMenuTitle(){
		return "顧客CSV";
	}

	/**
	 * 検索結果一覧に表示するメニューの説明
	 */
	function getMenuDescription(){
		SOY2::import("module.plugins.user_csv_after_search.form.ExportUserCSVPage");
		$form = SOY2HTMLFactory::createInstance("ExportUserCSVPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * export エクスポート実行
	 */
	function export($users){
		SOY2Logic::createInstance("module.plugins.user_csv_after_search.logic.ExportCSVLogic")->export($users);
		exit;
	}
}

SOYShopPlugin::extension("soyshop.user.export","user_csv_after_search","UserCSVAfterSearchUserExport");
?>