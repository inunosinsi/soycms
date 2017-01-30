<?php

class UpdateItemAreaPage extends WebPage{
	
	private $configObj;
	
	function __construct(){}
	
	function execute(){
		WebPage::__construct();
		
		$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		$itemDao->setLimit(5);
		try{
			$items = $itemDao->newItems();
		}catch(Exception $e){
			$items = array();
		}

		$this->createAdd("item_list", "_common.Item.ItemListComponent", array(
			"list" => $items,
			"config" => SOYShop_ShopConfig::load(),
			"detailLink" => SOY2PageController::createLink("Item.Detail."),
			"itemOrderDAO" => SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO")
		));
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
?>