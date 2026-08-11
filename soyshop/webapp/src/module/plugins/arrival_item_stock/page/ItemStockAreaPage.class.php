<?php

class ItemStockAreaPage extends WebPage{

	private $configObj;

	function __construct(){}

	function execute(){
		parent::__construct();

		$items = self::_get();

		$cnt = count($items);
		DisplayPlugin::toggle("more_stock", $cnt > 5);
		DisplayPlugin::toggle("has_stock", $cnt > 0);
		DisplayPlugin::toggle("no_stock", $cnt === 0);

		if($cnt > 5) $items = array_slice($items, 0, 5);

		$this->createAdd("stock_list", "_common.Item.ItemListComponent", array(
			"list" => $items,
			"detailLink" => SOY2PageController::createLink("Item.Detail."),
		));
	}

	private function _get(){
		$dao = soyshop_get_hash_table_dao("item");
		$dao->setLimit(6);

		$cnf = SOYShop_ShopConfig::load();
		if($cnf->getIgnoreStock()) return array();

		try{
			return $dao->getByStock($cnf->getDisplayStockCount());
		}catch(Exception $e){
			return array();
		}
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
