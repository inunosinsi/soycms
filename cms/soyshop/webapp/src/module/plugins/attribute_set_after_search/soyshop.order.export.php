<?php

class AttributeSetAfterUserSearchExport extends SOYShopOrderExportBase{

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
	function export(array $orders){
		$logic = SOY2Logic::createInstance("module.plugins.attribute_set_after_search.logic.RegisterLogic");
		$users = $logic->getUserIdListByOrders($orders);
		$logic->setUserAttribute($users);
		echo "設定しました。";
		exit;
	}
}

SOYShopPlugin::extension("soyshop.order.export","attribute_set_after_search","AttributeSetAfterUserSearchExport");
