<?php
class ItemStandardItemList extends SOYShopItemListBase{

	/**
	 * @return string
	 */
	function getLabel(){
		return "ItemStandard";
	}

	/**
	 * @return array
	 */
	function getItems($pageObj,$offset,$limit){
		$logic = SOY2Logic::createInstance("logic.shop.item.SearchItemUtil", array(
			"sort" => $pageObj
		));

		//カテゴリですべてを渡してみる
		$res = $logic->searchItems(array(), array(), array("is_parent" => 1), $offset, $limit);
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
		$query->where .= " AND (item_type = \"" . SOYShop_Item::TYPE_SINGLE . "\" OR item_type = \"" . SOYShop_Item::TYPE_GROUP . "\" OR item_type = \"" . SOYShop_Item::TYPE_DOWNLOAD . "\")";
		$query->table = "soyshop_item";

		$binds = array();

		try{
			$res = SOY2DAOFactory::create("shop.SOYShop_ItemDAO")->executeOpenItemQuery($query, $binds);
		}catch(Exception $e){
			return 0;
		}

		return (isset($res[0]["item_count"])) ? (int)$res[0]["item_count"] : 0;
	}
}

SOYShopPlugin::extension("soyshop.item.list", "item_standard", "ItemStandardItemList");
