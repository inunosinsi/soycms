<?php
/*
 */
class YayoiOrderCSV extends SOYShopOrderExportBase{
	
	/**
	 * 検索結果一覧に表示するメニューの表示文言
	 */
	function getMenuTitle(){
		return "";
	}

	/**
	 * 検索結果一覧に表示するメニューの説明
	 */
	function getMenuDescription(){
		//プラグインの画面のリンクを表示
		return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=yayoi_order_csv").'">弥生会計のCSV出力</a>';
	}

	/**
	 * export エクスポート実行
	 */
	function export($orders){
		//何もしない
	}
}

SOYShopPlugin::extension("soyshop.order.export", "yayoi_order_csv", "YayoiOrderCSV");
?>
