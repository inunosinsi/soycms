<?php
class SOYShopItemListAll extends SOYShopItemListBase{

	/**
	 * @return string
	 */
	function getLabel(){
		return "ItemListAll";
	}
	
	/**
	 * @return array
	 */
	function getItems($pageObj,$offset,$limit){
		$logic = SOY2Logic::createInstance("logic.shop.item.SearchItemUtil", array(
			"sort" => $pageObj
		));
		$res = $logic->searchItems(array(), array(), array(), $offset, $limit);
		return (isset($res[0])) ? $res[0] : array();
	}
	
	/**
	 * @return number
	 */
	function getTotal($pageObj){
		SOY2::import("domain.shop.SOYShop_Item");
		
		$query = new SOY2DAO_Query();
		$query->prefix = "select"; 
		$query->sql = "count(id) as item_count";
		$query->where = "item_is_open = " . SOYShop_Item::IS_OPEN . " AND is_disabled != " . SOYShop_Item::IS_DISABLED . " AND open_period_start < " . time() . " AND open_period_end > " . time();
		$query->table = "soyshop_item";
		
		$binds = array();
		
		$itemDAO = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		try{
			$res = $itemDAO->executeOpenItemQuery($query, $binds);
		}catch(Exception $e){
			return 0;
		}
		
		return (isset($res[0]["item_count"])) ? (int)$res[0]["item_count"] : 0;
	}
}

SOYShopPlugin::extension("soyshop.item.list", "item_list_all", "SOYShopItemListAll");
