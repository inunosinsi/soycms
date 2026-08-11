<?php

class ReducedTaxRateShippingListPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.common_consumption_tax.util.ConsumptionTaxUtil");
		MessageManager::addMessagePath("admin");
	}

	function execute(){

		parent::__construct();
		$items = self::_getItems();
		$cnt = count($items);

		DisplayPlugin::toggle("no_items", $cnt === 0);
		DisplayPlugin::toggle("items", $cnt > 0);

		$this->createAdd("item_list", "_common.Item.ItemListComponent", array(
			"list" => $items,
			"detailLink" => SOY2PageController::createLink("Item.Detail."),			
		));
	}

	//軽減税率対象商品一覧
	private function _getItems(){
		$itemIds = ConsumptionTaxUtil::getItemIdsOfReducedTaxRate();
		if(!count($itemIds)) return array();

		$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		try{
			$res = $itemDao->executeQuery("SELECT * FROM soyshop_item WHERE id IN (" . implode(",", $itemIds) . ")");
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$items = array();
		foreach($res as $v){
			if(!isset($v["id"])) continue;
			$items[] = $itemDao->getObject($v);
		}

		return $items;
	}

	function setConfigObj($configObj) {
		$this->configObj = $configObj;
	}
}
