<?php
/*
 */
class SimpleAggregateExport extends SOYShopOrderExportBase{

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
		SOY2::import("module.plugins.simple_aggregate.form.SimpleAggregateFormPage");
		$form = SOY2HTMLFactory::createInstance("SimpleAggregateFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * export エクスポート実行
	 */
	function export($orders){
		set_time_limit(0);
		SOY2::import("module.plugins.simple_aggregate.util.SimpleAggregateUtil");

		$mode = (isset($_POST["Aggregate"]["type"])) ? $_POST["Aggregate"]["type"] : AggregateUtil::MODE_MONTH;
		$label = "";
		$lines = array();

		switch($mode){
			case SimpleAggregateUtil::MODE_ITEMRATE:
				$label = SimpleAggregateUtil::MODE_ITEMRATE;
				$logic = SOY2Logic::createInstance("module.plugins.simple_aggregate.logic.ItemRateLogic");
				$lines = $logic->calc($orders);
				break;
			case SimpleAggregateUtil::MODE_MONTH:
				$label = SimpleAggregateUtil::MODE_MONTH;
				$logic = SOY2Logic::createInstance("module.plugins.simple_aggregate.logic.MonthLogic");
				$lines = $logic->calc($orders);
				break;
			case SimpleAggregateUtil::MODE_DAY:
				$label = SimpleAggregateUtil::MODE_DAY;
				$logic = SOY2Logic::createInstance("module.plugins.simple_aggregate.logic.DayLogic");
				$lines = $logic->calc($orders);
				break;
			case SimpleAggregateUtil::MODE_AGE:
				$label = SimpleAggregateUtil::MODE_AGE;
				$logic = SOY2Logic::createInstance("module.plugins.simple_aggregate.logic.AgeLogic");
				$lines = $logic->calc($orders);
				break;
			case SimpleAggregateUtil::MODE_CUSTOMER:
				$label = SimpleAggregateUtil::MODE_CUSTOMER;
				$logic = SOY2Logic::createInstance("module.plugins.simple_aggregate.logic.CustomerLogic");
				$lines = $logic->calc($orders);
				break;

			/** 隠しモード **/
			//オーダーカスタムフィールド(日付)で集計
			// case SimpleAggregateUtil::MODE_ORDER_DATE_CUSTOMFIELD:
			// 	if(isset($_POST["AggregateHiddenValue"])){
			// 		$v = $_POST["AggregateHiddenValue"];
			// 		$label = (isset($v["label"])) ? $v["label"] : "error";
			// 		$fieldId = (isset($v["field_id"])) ? $v["field_id"] : null;
			// 		$dateFieldId = (isset($v["date_field_id"])) ? $v["date_field_id"] : null;
			// 		$logic = SOY2Logic::createInstance("module.plugins.simple_aggregate.logic.OrderDateCustomFieldLogic", array("fieldId" => $fieldId, "dateFieldId" => $dateFieldId));
			// 		$lines = $logic->calc();
			// 	}
			// 	break;
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

SOYShopPlugin::extension("soyshop.order.export","simple_aggregate","SimpleAggregateExport");
