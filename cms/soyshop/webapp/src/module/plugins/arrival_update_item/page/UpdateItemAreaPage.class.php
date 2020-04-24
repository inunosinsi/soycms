<?php

class UpdateItemAreaPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.arrival_update_item.util.ArrivalUpdateItemUtil");
	}

	function execute(){
		parent::__construct();

		$config = ArrivalUpdateItemUtil::getConfig();
		$count = (isset($config["count"]) && is_numeric($config["count"])) ? (int)$config["count"] : 5;

		$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		$itemDao->setLimit($count);
		try{
			$items = $itemDao->newItems();
		}catch(Exception $e){
			$items = array();
		}

		//更新していない商品はなくす
		$itemList = array();
		foreach($items as $item){
			if(is_null($item->getUpdateDate())) continue;
			$itemList[] = $item;
		}

		$this->addLabel("item_count", array(
			"text" => $count
		));

		$this->createAdd("item_list", "_common.Item.ItemListComponent", array(
			"list" => $itemList,
			"detailLink" => SOY2PageController::createLink("Item.Detail.")
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
