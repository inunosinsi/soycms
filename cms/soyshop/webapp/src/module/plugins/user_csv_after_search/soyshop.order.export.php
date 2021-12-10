<?php

class UserCSVAfterSearchOrderExport extends SOYShopOrderExportBase{
	
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
	function export(array $orders){
		$logic = SOY2Logic::createInstance("module.plugins.user_csv_after_search.logic.ExportCSVLogic");
		$users = $logic->getUserIdListByOrders($orders);
		$logic->export($users);
		exit;
	}
}

SOYShopPlugin::extension("soyshop.order.export","user_csv_after_search","UserCSVAfterSearchOrderExport");
?>
