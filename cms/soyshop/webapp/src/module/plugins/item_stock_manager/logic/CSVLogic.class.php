<?php

class CSVLogic extends SOY2LogicBase {

	private $limit = 100000;
	private $params;

	function __construct(){

	}

	function getLabels(){

		$labels = array();

		$labels[] = "ID";
		$labels[] = "商品名";
		$labels[] = "商品コード";
		$labels[] = "在庫数";

		return $labels;
	}

	function getLines(){

		$lines = array();

		$searchLogic = SOY2Logic::createInstance("module.plugins.item_stock_manager.logic.SearchLogic");
		$searchLogic->setCondition($this->params);
		$items = $searchLogic->get();
		if(!count($items)) return array();

		foreach($items as $item){
			$line = array();
			$line[] = $item->getId();
			$line[] = $item->getName();
			$line[] = $item->getCode();
			$line[] = $item->getStock();
			$lines[] = implode(",", $line);
		}

		return $lines;
	}

	function setLimit($limit){
		$this->limit = $limit;
	}

	function setParams($params){
		$this->params = $params;
	}
}
