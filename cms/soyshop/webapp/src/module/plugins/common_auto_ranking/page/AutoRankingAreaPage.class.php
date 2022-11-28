<?php

class AutoRankingAreaPage extends WebPage{

	private $configObj;

	function __construct(){}

	function execute(){
		parent::__construct();

		$displayLogic = SOY2Logic::createInstance("module.plugins.common_auto_ranking.logic.DisplayRankingLogic");
		$items = $displayLogic->getItems();
		$items = array_slice($items, 0, 5);

		$latestDate = $displayLogic->getLatestCalcDate();

		if($latestDate){
			$calcMessage = "最終集計日時は" . date("Y-m-d H:i:s", $latestDate) . "です。";
		}else{
			$calcMessage = "集計されていません。";
		}

		$this->addLabel("latest_calc_date", array(
			"text" => $calcMessage
		));

		DisplayPlugin::toggle("has_auto_ranking", (count($items) > 0));
		DisplayPlugin::toggle("no_auto_ranking", (count($items) === 0));

		$this->createAdd("auto_ranking_list", "_common.Item.ItemListComponent", array(
			"list" => $items,
			"detailLink" => SOY2PageController::createLink("Item.Detail.")
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
?>
