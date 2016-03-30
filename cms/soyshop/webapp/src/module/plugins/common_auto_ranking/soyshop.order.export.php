<?php
/*
 */
class CommonAutoRankingExport extends SOYShopOrderExportBase{
	
	/**
	 * 検索結果一覧に表示するメニューの表示文言
	 */
	function getMenuTitle(){
		return "売上ランキング集計";
	}

	/**
	 * 検索結果一覧に表示するメニューの説明
	 */
	function getMenuDescription(){
		//最終実行日を表示する
		$displayLogic = SOY2Logic::createInstance("module.plugins.common_auto_ranking.logic.DisplayRankingLogic");
		$lastDate = $displayLogic->getLatestCalcDate();
		if(isset($lastDate)){
			$description = "最終集計日時は" . date("Y-m-d H:i:s", $lastDate) . "です。";
		}else{
			$description = "集計されていません。";
		}
		
		return $description;
	}

	/**
	 * export エクスポート実行
	 */
	function export($orders){
		$calcLogic = SOY2Logic::createInstance("module.plugins.common_auto_ranking.logic.CalculateRankingLogic");
		
		$res = $calcLogic->execute();
		
		/**
		 * @ToDo 集計後の表示
		 */
		$tags = array();
		$tags[] = "<!DOCTYPE html>";
		$tags[] = "<html>";
		$tags[] = "<head>";
		$tags[] = "<meta charset=\"UTF-8\">";
		$tags[] = "<title>売上ランキング集計</title>";
		$tags[] = "</head>";
		$tags[] = "<body>";
		$tags[] = "<div>" . $res . "</div>";
		$tags[] = "</body>";
		$tags[] = "</html>";
		echo implode("\n", $tags);
	}
}

SOYShopPlugin::extension("soyshop.order.export", "common_auto_ranking", "CommonAutoRankingExport");
?>
