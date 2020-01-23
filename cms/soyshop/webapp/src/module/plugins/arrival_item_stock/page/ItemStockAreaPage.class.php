<?php

class ItemStockAreaPage extends WebPage{

	private $configObj;

	function __construct(){}

	function execute(){
		parent::__construct();

		$shopConfig = SOYShop_ShopConfig::load();
		$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		$itemDao->setLimit(6);

		if($shopConfig->getIgnoreStock()){
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
