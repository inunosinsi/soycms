<?php
include(dirname(__FILE__) . "/common/common.php");
class BuildCustomSearchModule extends SOYShopSearchModule{

	/**
	 * title text
	 */
	function getTitle(){
		return "Custom Search Module";
	}

	/**
	 * @return html
	 */
	function getForm(){
		$query = (isset($_REQUEST["q"])) ? $_REQUEST["q"] : "";
		$query = mb_convert_encoding($query, "UTF-8", "auto");
		$query = htmlspecialchars($query, ENT_QUOTES, "UTF-8");
		return stripslashes("<input name='q' value='$query' />");
	}

	private $count = 0;
	private $config;

	/**
	 * @return array<soyshop_item>
	 */
	function getItems(int $current, int $limit){
		if(!$this->config){
			$this->config = CustomSearchCommon::getConfig();
		}

		if(count($_GET) > 0){
			$condition = $_GET;

			$items = $this->searchByCondition($current, $limit, $condition);
			$this->count = $this->totalCountByCondition($condition);
			return $items;
		}
	}

	/**
	 * @return number
	 */
	function getTotal(){
		return $this->count;
	}


	private $itemDao;

	/**
	 * @return int totalPageCount
	 * @param string nameQuery
	 */
	function totalCountByCondition($condition){

		if(!$this->itemDao){
			$this->itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		}
		$dao = $this->itemDao;

		$sql = new SOY2DAO_Query();
		$binds = array();

		$sql->prefix = "select";
		$sql->table = "soyshop_item i INNER JOIN soyshop_item_attribute a ON i.id = a.item_id";
		$sql->distinct = "id";
		$sql->sql = "count(distinct id)";

		$where = array();

		list($where, $binds) = $this->buildSql($condition);
		$sql->where .= $this->buildWhere($where, $condition);

		try{
			$result = $dao->executeOpenItemQuery($sql, $binds);
		}catch(Exception $e){
			return 0;
		}

		return (isset($result[0]["count(distinct id)"])) ? (int)$result[0]["count(distinct id)"] : 0;
	}

	/**
	 * @return array<soyshop_item>
	 * @param string nameQuery
	 * 商品名の検索
	 */
	function searchByCondition($current, $limit, $condition){

		if(!$this->itemDao){
			$this->itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		}
		$dao = $this->itemDao;

		$sql = new SOY2DAO_Query();
		$binds = array();

		$sql->prefix = "select";
		$sql->table = "soyshop_item i INNER JOIN soyshop_item_attribute a ON i.id = a.item_id";
		$sql->distinct = "id";
		$sql->order = $this->getSortQuery();//"update_date desc";
		$sql->sql = "id";

		$where = array();

		list($where,$binds) = $this->buildSql($condition);
		$sql->where .= $this->buildWhere($where, $condition);

		$dao->setLimit($limit);
		$offset = ($current - 1) * $limit;
		$dao->setOffset($offset);

		try{
			$result = $dao->executeOpenItemQuery($sql, $binds);
		}catch(Exception $e){
			return array();
		}

		$items = array();
		foreach($result as $key => $value){
			try{
				$itemId = (int)$value["id"];
				$items[] = $dao->getById($itemId);
			}catch(Exception $e){
				continue;
			}
		}
		return $items;
	}

	function buildWhere($where, $condition){

		$operator = (isset($_GET["custom_search"])) ? $_GET["custom_search"] : "and";

		$sql = "";

		if(count($where) > 0){
			$sql = " (" . implode(" " . $operator . " ", $where) . ")";
		}

		//検索条件にカテゴリがあった場合、
		if(array_key_exists("category", $condition)){
			$mapping = self::getMapping();
			if(count($where) > 0) $sql .= " AND";
			$categoryIds = $mapping[$condition["category"]];
			$sql .= " i.item_category in (" . implode(",", $categoryIds) . ")";
		}

		return $sql;
	}

	function buildSql($condition){

		if(!$this->config){
			$this->config = CustomSearchCommon::getConfig();
		}

		$list = $this->config;
		$where = array();
		$binds = array();
		$query = array();
		foreach($list as $key => $config){
			switch($config["type"]){
				case "range":
					if($key == "range_price"){
						if(isset($condition[$key."_min"]) && strlen($condition[$key . "_min"]) > 0 && is_numeric($condition[$key . "_min"])){
							$query[] = "i.item_price >= :query" . $key . "_min";
							$binds[":query" . $key . "_min"] = $condition[$key . "_min"];
						}
						if(isset($condition[$key . "_max"]) && strlen($condition[$key . "_max"]) > 0 && is_numeric($condition[$key . "_max"])){
							$query[] = "i.item_price <= :query" . $key . "_max";
							$binds[":query" . $key . "_max"] = $condition[$key . "_max"];
						}
						if(count($query) > 0){
							$where[] = " (" . implode(" AND ", $query) . ")";
						}
					}else{
						if(isset($condition[$key . "_min"]) && strlen($condition[$key . "_min"]) > 0 && is_numeric($condition[$key . "_min"])){
							$query[] = " (a.item_field_id = '" . $key . "' AND a.item_value >= :field" . $key . ")";
							$binds[":query" . $key . "_max"] = $condition[$key . "_max"];
						}
						if(isset($condition[$key . "_max"]) && strlen($condition[$key . "_max"]) > 0 && is_numeric($condition[$key . "_max"])){
							$query[] = " (a.item_field_id = '" . $key . "' AND a.item_value <= :field" . $key . ")";
							$binds[":query" . $key . "_max"] = $condition[$key . "_max"];
						}
					}

					if(count($query) > 0){
						$where[] = " (" . implode(" AND ", $query) . ")";
					}
					break;
				case "checkbox":
					if(!isset($condition[$key]) || count($condition[$key]) == 0) continue;
					$gather = array();
					foreach($condition[$key] as $value){
						$gather[] = $value;
					}
					if(count($gather) > 0){
						$where[] = "(a.item_field_id = '" . $key . "' AND a.item_value IN (" . $this->getGather($gather) . "))";
					}
					break;
				case "select":
				case "radio":
				case "text":
				default:
					if(!isset($condition[$key]) || strlen($condition[$key]) == 0) continue;
					//商品名検索
					if($key == "q"){
						$where[] = "i.item_name LIKE :query" . $key;
						$binds[":query" . $key] = "%" . $condition[$key] . "%";
					}else{
						$where[] = " (a.item_field_id = '" . $key . "' AND a.item_value LIKE :field" . $key . ")";
						$binds[":field" . $key] = "%" . $condition[$key] . "%";
					}
					break;
			}
		}
		return array($where, $binds);
	}

	private function getMapping(){
		static $mapping;
		if(is_null($mapping)) $mapping= SOY2DAOFactory::create("shop.SOYShop_CategoryDAO")->getMapping();
		return $mapping;
	}

	function getGather($value){
		$array = array();
		foreach($value as $obj){
			$array[] = "'" . $obj . "'";
		}
		return implode(",", $array);
	}

	/**
	 * ソート文を作成
	 *
	 * $_GET["r"]		0 降順 1 昇順
	 * $_GET["sort"]	ソート種別
	 * 		・id
	 * 		・name
	 * 		・code
	 * 	 	・stock
	 * 		・cdate
	 * 		・udate
	 * $_GET["csort"]	カスタムフィールドでソート
	 *
	 */
	function getSortQuery(){

		$session = SOY2ActionSession::getUserSession();
		$pageId = $this->getPage()->getId();

		$sort = $session->getAttribute("soyshop_" . SOYSHOP_ID . "_sort" . $pageId);
		$csort = $session->getAttribute("soyshop_" . SOYSHOP_ID . "_csort" . $pageId);
		$suffix = $session->getAttribute("soyshop_" . SOYSHOP_ID . "_suffix" . $pageId);

		if(isset($_GET["sort"])){
			$sort = $_GET["sort"];
			$session->setAttribute("soyshop_" . SOYSHOP_ID . "_sort" . $pageId, $sort);
		}

		//未実装
		if(isset($_GET["csort"])){
			$csort = $_GET["csort"];
			$session->setAttribute("soyshop_" . SOYSHOP_ID . "_csort" . $pageId, $csort);
		}

		if(isset($_GET["r"])){
			$suffix = ($_GET["r"] == 1) ? " desc" : "";
			$session->setAttribute("soyshop_" . SOYSHOP_ID . "_suffix" . $pageId, $suffix);
		}

		//default
		if(!$sort && !$csort){
			$obj = $this->getPage()->getObject();
			$defaultSort = $obj->getDefaultSort();
			if(!$suffix) $suffix = ($obj->getIsReverse()) ? " desc" : "";
			if($defaultSort != "_custom") $sort = $defaultSort;
			$csort = $obj->getCustomSort();
		}

		if($sort){
			switch($sort){
				case "id":
					return "id" . $suffix;
					break;
				case "name":
				case "code":
				case "stock":
					return "item_" .$sort. $suffix;
					break;
				case "price":
					return "item_selling_price" . $suffix;
					break;
				case "cdate":
					return "create_date" . $suffix;
					break;
				case "udate":
					return "update_date" . $suffix;
					break;
			}
		}

		if($csort){
			SOY2::import("domain.shop.SOYShop_ItemAttribute");
			$fields = SOYShop_ItemAttributeConfig::getIndexFields();
			if(!in_array($csort, $fields)){
				$csort = $obj->getCustomSort();
			}

			return SOYShop_ItemDAO::getSortColumnName($csort) . $suffix;
		}

		return "update_date desc";
	}
}
SOYShopPlugin::extension("soyshop.search", "build_custom_search", "BuildCustomSearchModule");
