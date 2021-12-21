<?php

class ItemStockAreaPage extends WebPage{

	private $configObj;

	function __construct(){}

	function execute(){
		parent::__construct();

		$itemDao = soyshop_get_hash_table_dao("item");
		$itemDao->setLimit(6);

		if(SOYShop_ShopConfig::load()->getIgnoreStock()){
			$items = array();
		}else{
			try{
				$items = $itemDao->getByStock($shopConfig->getDisplayStockCount());
			}catch(Exception $e){
				$items = array();
			}
		}

		$cnt = count($items);
		DisplayPlugin::toggle("more_stock", $cnt > 5);
		DisplayPlugin::toggle("has_stock", $cnt > 0);
		DisplayPlugin::toggle("no_stock", $cnt === 0);

		$this->createAdd("stock_list", "_common.Item.ItemListComponent", array(
			"list" => array_slice($items, 0, 5),
			"detailLink" => SOY2PageController::createLink("Item.Detail."),
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
