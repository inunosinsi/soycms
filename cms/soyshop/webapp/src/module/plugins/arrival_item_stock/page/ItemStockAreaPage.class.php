<?php

class ItemStockAreaPage extends WebPage{
	
	private $configObj;
	
	function __construct(){}
	
	function execute(){
		WebPage::__construct();
		
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
		
			

		DisplayPlugin::toggle("more_stock", (count($items) > 5));
		DisplayPlugin::toggle("has_stock", (count($items) > 0));
		DisplayPlugin::toggle("no_stock", (count($items) === 0));
		
		$items = array_slice($items, 0, 5);

		$this->createAdd("stock_list", "_common.Item.ItemListComponent", array(
			"list" => $items,
			"config" => $shopConfig,
			"detailLink" => SOY2PageController::createLink("Item.Detail."),
			"itemOrderDAO" => SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO")
		));
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
?>