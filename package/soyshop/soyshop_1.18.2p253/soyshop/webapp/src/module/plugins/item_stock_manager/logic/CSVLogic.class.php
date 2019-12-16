<?php

class CSVLogic extends SOY2LogicBase {

	private $limit = 100000;
	private $params;

	function __construct(){

	}

	function getLabels(){

		$labels = array();

		$labels[] = "ID";
		$labels[] = "商品コード";
		$labels[] = "商品名";
		$labels[] = "公開状態";
		$labels[] = "在庫数";
		$labels[] = "価格";
		$labels[] = "セール価格";
		$labels[] = "セール";

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
			$line[] = $item->getId();		//ID
			$line[] = $item->getCode();		//商品コード
			$line[] = $item->getName();		//商品名
			$line[] = ($item->getIsOpen() == 1) ? "公開" : "非公開";		//公開状態
			$line[] = $item->getStock();	//在庫数
			$line[] = $item->getPrice();	//価格
			$line[] = $item->getSalePrice();	//セール価格
			$line[] = ($item->getSaleFlag() == 1) ? "セール中" : "";		//セール中
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
