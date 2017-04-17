<?php

class ChildItemLogic extends SOY2LogicBase{
	
	private $where = array();
	private $binds = array();
	private $itemDao;
		
	function __construct(){
		SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");
		$this->itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
	}
	
	/**
	 * @params int current:現在のページ, int limit:一ページで表示する商品
	 * @return array<SOYShop_Item>
	 */
	function search($obj, $current, $limit){return array();}
	function getTotal(){return 0;}
	
	/** 商品一覧ページ用 **/
	function getItemList($obj, $key, $value, $current, $offset, $limit){
		
		$confs = CustomSearchFieldUtil::getConfig();
		if(!isset($confs[$key])) return array();
		
		$binds = array(":now" => time());
		
		$sql = "SELECT i.* " .
				"FROM soyshop_item i ".
				"INNER JOIN soyshop_custom_search s ".
				"ON i.id = s.item_id ";
		$sql .= self::buildWhere();	//カウントの時と共通の処理は切り分ける
		switch($confs[$key]["type"]){
			case CustomSearchFieldUtil::TYPE_CHECKBOX:
				$sql .= "AND s." . $key . " LIKE :" . $key;
				$binds[":" . $key] = "%" . trim($value) . "%";
				break;
			default:
				$sql .= "AND s." . $key . " = :" . $key;
				$binds[":" . $key] = trim($value);
		}
		
		$sql .= self::buildOrderBySQL($obj);
		$sql .= " LIMIT " . $limit;
		
		//OFFSET
		$offset = $limit * ($current - 1);
		if($offset > 0) $sql .= " OFFSET " . $offset;
		
		try{
			$res = $this->itemDao->executeQuery($sql, $binds);
		}catch(Exception $e){
			return array();
		}
		
		if(count($res) === 0) return array();
		
		$items = array();
		foreach($res as $obj){
			if(!isset($obj["id"])) continue;
			$items[] = $this->itemDao->getObject($obj);
		}
		
		return $items;
	}
	
	function countItemList($key, $value){
		$confs = CustomSearchFieldUtil::getConfig();
		if(!isset($confs[$key])) return 0;
		
		$binds = array(":now" => time());
		
		$sql = "SELECT COUNT(i.id) AS TOTAL " .
				"FROM soyshop_item i ".
				"INNER JOIN soyshop_custom_search s ".
				"ON i.id = s.item_id ";
		$sql .= self::buildWhere();	//カウントの時と共通の処理は切り分ける
		switch($confs[$key]["type"]){
			case CustomSearchFieldUtil::TYPE_CHECKBOX:
				$sql .= "AND s." . $key . " LIKE :" . $key;
				$binds[":" . $key] = "%" . trim($value) . "%";
				break;
			default:
				$sql .= "AND s." . $key . " = :" . $key;
				$binds[":" . $key] = trim($value);
		}
		
		try{
			$res = $this->itemDao->executeQuery($sql, $binds);
		}catch(Exception $e){
			return 0;
		}
		
		return (isset($res[0]["TOTAL"])) ? (int)$res[0]["TOTAL"] : 0;
	}
	
	private function buildWhere(){
		
		$sql = "WHERE i.open_period_start < :now ".
				"AND i.open_period_end > :now ".
				"AND i.item_is_open = 1 ".
				"AND i.is_disabled != 1 ";

		//子商品のみ表示
		$sql .= "AND (i.item_type != \"" . SOYShop_Item::TYPE_SINGLE . "\" AND i.item_type != \"" . SOYShop_Item::TYPE_GROUP . "\" AND i.item_type != \"" . SOYShop_Item::TYPE_DOWNLOAD . "\") ";
		
		//SQLiteでREGEXPを使用できないサーバがあるみたい
		if(SOY2DAOConfig::type() == "mysql"){
			$sql .= "AND i.item_type REGEXP '^[0-9]+$' ";
		}
		
		return $sql;
	}
	
	private function buildOrderBySQL(SOYShop_ListPage $obj){
		
		$pageId = $obj->getPage()->getId();
		
		$session = SOY2ActionSession::getUserSession();
		if(isset($_GET["sort"]) || isset($_GET["csort"])){
			$custom_search_sort = null;
		}else{
			$custom_search_sort = $session->getAttribute("soyshop_" . SOYSHOP_ID . "_custom_search" . $pageId);
		}
		
		//カスタムソート
		if(isset($_GET["custom_search_sort"])){
			$custom_search_sort = ($_GET["custom_search_sort"] != "reset") ? htmlspecialchars($_GET["custom_search_sort"], ENT_QUOTES, "UTF-8") : null;
			//存在するフィールドか調べる
			$dao = new SOY2DAO();
			try{
				$dao->executeQuery("SELECT item_id FROM soyshop_custom_search WHERE " . $custom_search_sort . "= '' LIMIT 1");
			}catch(Exception $e){
				$custom_search_sort = null;
			}
			$session->setAttribute("soyshop_" . SOYSHOP_ID . "_custom_search" . $pageId, $custom_search_sort);
		}
		
		if(isset($custom_search_sort)){
			$suffix = $session->getAttribute("soyshop_" . SOYSHOP_ID . "_suffix" . $pageId);
			if(isset($_GET["r"])){
				$suffix = ($_GET["r"] == 1) ? " DESC" : " ASC";
				$session->setAttribute("soyshop_" . SOYSHOP_ID . "_suffix" . $pageId, $suffix);
			}
			
			return " ORDER BY s." . $custom_search_sort . " IS NULL ASC, s." . $custom_search_sort . $suffix;
		}else{
			$sort = SOY2Logic::createInstance("logic.shop.item.SearchItemUtil", array("sort" => $obj))->getSortQuery();
			return " ORDER BY i." . $sort . " ";
		}
	}
}
?>