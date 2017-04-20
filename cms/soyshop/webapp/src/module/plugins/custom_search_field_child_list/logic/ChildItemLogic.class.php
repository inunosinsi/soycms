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
	function search($obj, $current, $limit){
		self::setCondition();
		
		$sql = "SELECT * " .
				"FROM soyshop_item ";
		$sql .= self::buildWhere();	//カウントの時と共通の処理は切り分ける
		$sort = SOY2Logic::createInstance("logic.shop.item.SearchItemUtil", array("sort" => $obj))->getSortQuery();
		if(isset($sort)) $sql .= " ORDER BY " . $sort . " ";
		
		//表示件数
		$sql .= " LIMIT " . (int)$limit;
		
		//OFFSET
		$offset = $limit * ($current - 1);
		if($offset > 0) $sql .= " OFFSET " . $offset;
		
		try{
			$res = $this->itemDao->executeQuery($sql, $this->binds);
		}catch(Exception $e){
			return array();
		}
		
		if(!count($res)) return array();
		
		$items = array();
		foreach($res as $obj){
			$items[] = $this->itemDao->getObject($obj);
		}
		
		return $items;
	}
	
	function getTotal(){
		self::setCondition();
		
		$sql = "SELECT COUNT(id) AS total " .
				"FROM soyshop_item ";
		$sql .= self::buildWhere();	//カウントの時と共通の処理は切り分ける
		
		try{
			$res = $this->itemDao->executeQuery($sql, $this->binds);
		}catch(Exception $e){
			return 0;
		}
				
		return (isset($res[0]["total"])) ? (int)$res[0]["total"] : 0;
	}
	
	private function buildWhere(){
		$where = "WHERE open_period_start < :now ".
				"AND open_period_end > :now ".
				"AND item_is_open = 1 ".
				"AND is_disabled != 1 ";
		
		$item_where = array();
		
		//検索対象を子商品のみとする
		$item_where[] = "(item_type != \"" . SOYShop_Item::TYPE_SINGLE . "\" AND item_type != \"" . SOYShop_Item::TYPE_GROUP . "\" AND item_type != \"" . SOYShop_Item::TYPE_DOWNLOAD . "\")";
		
		//SQLiteでREGEXPを使用できないサーバがあるみたい
		if(SOY2DAOConfig::type() == "mysql"){
			$item_where[] = "item_type REGEXP '^[0-9]+$' ";
		}
		
		if(count($item_where)){
			$where .= "AND (" .implode(" AND ", $item_where) .") ";
		}
		
		foreach($this->where as $key => $w){
			if(!strlen($w)) continue;
			$where .= "AND " . $w . " ";
		}
		return $where;
	}
	
	private function setCondition(){
		if(!count($this->where)){
			//SOYShop_Itemの値
			foreach(array("item_name", "item_code") as $key){
				if(isset($_GET["c_search"][$key]) && strlen($_GET["c_search"][$key])) {
					$this->where[$key] = $key . " LIKE :" . $key;
					$this->binds[":" . $key] = "%" . trim($_GET["c_search"][$key]) . "%";
				}
			}
			
			//カテゴリー
			if(isset($_GET["c_search"]["item_category"]) && is_numeric($_GET["c_search"]["item_category"])){
				//小カテゴリの商品も引っ張ってこれる様にする
				$maps = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO")->getMapping();
				$catId = (int)trim($_GET["c_search"]["item_category"]);
				if(isset($maps[$catId])){
					$this->where["item_category"] = "item_category IN (" . implode(",", $maps[$catId]) . ")";
				}
			}
			
			//サブクエリ内でも子商品のみの指定を行う
			$this->where["item_type"] = "(item_type != \"" . SOYShop_Item::TYPE_SINGLE . "\" AND item_type != \"" . SOYShop_Item::TYPE_GROUP . "\" AND item_type != \"" . SOYShop_Item::TYPE_DOWNLOAD . "\")";
			
			$pmin = "";$pmax = "";
			if(isset($_GET["c_search"]["item_price_min"]) && strlen($_GET["c_search"]["item_price_min"]) && is_numeric($_GET["c_search"]["item_price_min"])) {
				$pmin = "item_price >= :item_price_min";
				$this->binds[":item_price_min"] = (int)$_GET["c_search"]["item_price_min"];
			}
			
			if(isset($_GET["c_search"]["item_price_max"]) && strlen($_GET["c_search"]["item_price_max"]) && is_numeric($_GET["c_search"]["item_price_max"])) {
				$pmax = "item_price <= :item_price_max";
				$this->binds[":item_price_max"] = (int)$_GET["c_search"]["item_price_max"];
			}
			
			if(strlen($pmin) && strlen($pmax)){
				$this->where["item_price"] = "(" . $pmin . " AND " . $pmax . ")";
			}else{
				$this->where["item_price"] = $pmin . $pmax;
			}
			
			$s_where = array();
			
			foreach(CustomSearchFieldUtil::getConfig() as $key => $field){
		
				//まずは各タイプのfield SQLでkeyを指定する場合、s.を付けること。soyshop_custom_searchのaliasがs
				switch($field["type"]){
					//文字列の場合
					case CustomSearchFieldUtil::TYPE_STRING:
					case CustomSearchFieldUtil::TYPE_TEXTAREA:
					case CustomSearchFieldUtil::TYPE_RICHTEXT:
						if(isset($_GET["c_search"][$key]) && strlen($_GET["c_search"][$key])){
							$s_where[$key] = $key . " LIKE :" . $key;
							$this->binds[":" . $key] = "%" . trim($_GET["c_search"][$key]) . "%";
						}
						break;
					
					//範囲の場合
					case CustomSearchFieldUtil::TYPE_RANGE:
						$ws = "";$we = "";	//whereのスタートとエンド
						if(isset($_GET["c_search"][$key . "_start"]) && strlen($_GET["c_search"][$key . "_start"]) && is_numeric($_GET["c_search"][$key . "_start"])){
							$ws = $key . " >= :" . $key . "_start";
							$this->binds[":" . $key . "_start"] = (int)$_GET["c_search"][$key . "_start"];
						}
						if(isset($_GET["c_search"][$key . "_end"]) && strlen($_GET["c_search"][$key . "_end"]) && is_numeric($_GET["c_search"][$key . "_end"])){
							$we = $key .  " <= :" . $key . "_end";
							$this->binds[":" . $key . "_end"] = (int)$_GET["c_search"][$key . "_end"];
						}
						if(strlen($ws) && strlen($we)){
							$s_where[$key] = "(" . $ws . " AND " . $we . ")";
						}else{
							$s_where[$key] = $ws . $we;
						}
						break;
						
					//チェックボックスの場合
					case CustomSearchFieldUtil::TYPE_CHECKBOX:
						if(isset($_GET["c_search"][$key]) && count($_GET["c_search"][$key])){
							$w = array();
							foreach($_GET["c_search"][$key] as $i => $v){
								if(!strlen($v)) continue;
								$w[] = $key . " LIKE :" . $key . $i;
								$this->binds[":" . $key . $i] = "%" . trim($v) . "%";
							}
							if(count($w)) $s_where[$key] = "(" . implode(" OR ", $w) . ")";
						}
						break;
					
					//数字、ラジオボタン、セレクトボックス
					default:
						if(isset($_GET["c_search"][$key]) && strlen($_GET["c_search"][$key])){
							$s_where[$key] = $key . " = :" . $key;
							$this->binds[":" . $key] = $_GET["c_search"][$key];
						}
				}
			}
			$this->binds[":now"] = time();
			
			if(count($s_where)){
				$subquery = "(" .
						"SELECT item_id FROM soyshop_custom_search WHERE ";
				$f = 0;
				foreach($s_where as $sw){
					if(!strlen($sw)) continue;
					if($f == 0){
						$subquery .= $sw . " ";
					}else{
						$subquery .= "AND " . $sw . " ";
					}
					
					$f++;
				}
				$subquery .= ")";
				$config = CustomSearchFieldUtil::getSearchConfig();
				$w = "(id IN " . $subquery;
				if(isset($config["search"]["child"]) && (int)$config["search"]["child"] === 1){
					$w .= " OR item_type IN " . $subquery;
				}
				$w .= ")";
				
				$this->where["custom"] = $w;
			}
		}	
	}
	
	/** 商品一覧ページ用 **/
	function getItemList($obj, $key, $value, $current, $offset, $limit){
		
		$confs = CustomSearchFieldUtil::getConfig();
		if(!isset($confs[$key])) return array();
		
		$binds = array(":now" => time());
		
		$sql = "SELECT i.* " .
				"FROM soyshop_item i ".
				"INNER JOIN soyshop_custom_search s ".
				"ON i.id = s.item_id ";
		$sql .= self::buildListWhere();	//カウントの時と共通の処理は切り分ける
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
		$sql .= self::buildListWhere();	//カウントの時と共通の処理は切り分ける
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
	
	private function buildListWhere(){
		
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