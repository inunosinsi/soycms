<?php
/*
 */
class AggregateExport extends SOYShopOrderExportBase{
	
	private $csvLogic;

	/**
	 * 検索結果一覧に表示するメニューの表示文言
	 */
	function getMenuTitle(){
		return "集計";
	}

	/**
	 * 検索結果一覧に表示するメニューの説明
	 */
	function getMenuDescription(){
		include_once(dirname(__FILE__) . "/form/AggregateFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("AggregateFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * export エクスポート実行
	 */
	function export($orders){
		
		set_time_limit(0);
		SOY2::import("module.plugins.aggregate.util.AggregateUtil");

		$mode = (isset($_POST["Aggregate"]["type"])) ? $_POST["Aggregate"]["type"] : "month";
		
		switch($mode){
			case AggregateUtil::MODE_ITEMRATE:
				$label = AggregateUtil::MODE_ITEMRATE;
				$logic = SOY2Logic::createInstance("module.plugins.aggregate.logic.ItemRateLogic");
				$lines = $logic->calc();
				break;
			case AggregateUtil::MODE_MONTH:
				$label = AggregateUtil::MODE_MONTH;
				$logic = SOY2Logic::createInstance("module.plugins.aggregate.logic.MonthLogic");
				$lines = $logic->calc();
				break;
			case AggregateUtil::MODE_DAY:
				$label = AggregateUtil::MODE_DAY;
				$logic = SOY2Logic::createInstance("module.plugins.aggregate.logic.DayLogic");
				$lines = $logic->calc();
				break;
		}

		$charset = (isset($_REQUEST["charset"])) ? $_REQUEST["charset"] : "Shift-JIS";
		
		header("Cache-Control: public");
		header("Pragma: public");
    	header("Content-Disposition: attachment; filename=" . $label . "_" . date("YmdHis") . ".csv");
		header("Content-Type: text/csv; charset=" . htmlspecialchars($charset) . ";");
		
		ob_start();
		echo implode("," , $logic->getLabels());
		echo "\r\n";
		echo implode("\r\n",$lines);
		$csv = ob_get_contents();
		ob_end_clean();
		
		echo mb_convert_encoding($csv,$charset,"UTF-8");
		
		exit;	//csv output
	}
}

SOYShopPlugin::extension("soyshop.order.export","common_aggregate","AggregateExport");
?>