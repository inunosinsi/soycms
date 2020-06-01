<?php

/**
 * ソート用のinterface
 * 別に実装してなくても良い
 */
interface SearchItemUtil_Sort{

	function getDefaultSort();
	function getIsReverse();
	function getCustomSort();

}

class SearchItemUtil_SortImpl implements SearchItemUtil_Sort{

	private $obj;

	function __construct($obj){
		$this->obj = $obj;
	}

	function getDefaultSort(){
		if(method_exists($this->obj, "getDefaultSort")){
			return $this->obj->getDefaultSort();
		}else if(method_exists($this->obj, "getObject") && method_exists($this->obj->getObject(), "getDefaultSort")){
			return $this->obj->getObject()->getDefaultSort();
		}

		return "id";
	}
	function getIsReverse(){
		if(method_exists($this->obj, "isReverse")){
			return $this->obj->isReverse();
		}else if(method_exists($this->obj, "getIsReverse")){
			return $this->obj->getIsReverse();
		}

		if(method_exists($this->obj, "getObject")){
			if(method_exists($this->obj->getObject(), "isReverse")){
				return $this->obj->getObject()->isReverse();
			}else if(method_exists($this->obj->getObject(), "getIsReverse")){
				return $this->obj->getObject()->getIsReverse();
			}
		}

		return false;
	}

	function getCustomSort(){
		if(method_exists($this->obj, "getCustomSort")){
			return $this->obj->getCustomSort();
		}

		return null;
	}

	function getObject(){
		return $this->obj;
	}
}

/**
 * この商品検索クラスはサイト側で使用する
 */
class SearchItemUtil extends SOY2LogicBase{

	private $sort;
	private $mode;

	function getByCategoryIds($categoryId, $offset = null, $limit = null){
		return $this->getByCategoryId($categoryId, $offset, $limit, true);
	}

	/**
	 * 子商品を取得
	 * @return array<SOYShop_Item>
	 */
	function getChildItems($parent, $order="item_code desc"){
		$itemDAO = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		$itemDAO->setMode("open");
		$itemDAO->setOrder($order);
		return $itemDAO->getByType($parent);
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
		if(method_exists($this, "getObject")){
			$pageId = $this->getSort()->getObject()->getPage()->getId();
		//詳細ページ対策
		}else{
			$pageId = null;
		}

		$sort = $session->getAttribute("soyshop_" . SOYSHOP_ID . "_sort" . $pageId);
		$csort = $session->getAttribute("soyshop_" . SOYSHOP_ID . "_csort" . $pageId);
		$suffix = $session->getAttribute("soyshop_" . SOYSHOP_ID . "_suffix" . $pageId);

		if(isset($_GET["sort"])){
			$sort = ($_GET["sort"] != "reset") ? $_GET["sort"] : null;
			$session->setAttribute("soyshop_" . SOYSHOP_ID . "_sort" . $pageId, $sort);
		}

		if(isset($_GET["csort"])){
			$csort = ($_GET["csort"] != "reset") ? $_GET["csort"] : null;
			$session->setAttribute("soyshop_" . SOYSHOP_ID . "_csort" . $pageId, $csort);
		}

		if(isset($_GET["r"])){
			$suffix = ($_GET["r"] == 1) ? " desc" : "";
			$session->setAttribute("soyshop_" . SOYSHOP_ID . "_suffix" . $pageId, $suffix);
		}

		//default
		if(!$sort && !$csort && $this->getSort()){

			$obj = $this->getSort();
			$defaultSort = $obj->getDefaultSort();
			$suffix = ($obj->getIsReverse()) ? " desc" : "";

			//カテゴリによってソートの設定を出し分ける
			SOYShopPlugin::load("soyshop.item.list");
			$extSort = SOYShopPlugin::invoke("soyshop.item.list", array(
				"mode" => "sort"
			))->getSort();

			if(!is_null($extSort) && is_array($extSort)){
				$sort = (isset($extSort["sort"])) ? $extSort["sort"] : $defaultSort;
				$csort = (isset($extSort["csort"])) ? $extSort["csort"] : null;
				//suffixの上書き
				if(isset($extSort["suffix"])) $suffix = $extSort["suffix"];
			//通常
			}else{
				if($defaultSort != "custom") $sort = $defaultSort;
				$csort = $obj->getCustomSort();
			}
		}

		if($sort){
			switch($sort){
				case "id":
					return "id" . $suffix;
					break;
				case "name":
				case "code":
				case "stock":
					return "item_" . $sort . $suffix;
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
				//カスタムフィールドによるソート
				default:
					//ソート用のカラムがあるか調べる
					if(SOY2DAOConfig::type() == "mysql"){
						try{
							$res = SOY2DAOFactory::create("shop.SOYShop_ItemDAO")->executeQuery("SHOW COLUMNS FROM soyshop_item LIKE :pattern", array(":pattern" => $sort));
							if(count($res)) return $sort . $suffix;
						}catch(Exception $e){
							//
						}
					//SQLiteの場合はカラムがあるかチェックせずに返す
					}else{
						return $sort . $suffix;
					}
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


		return null;
	}

	/**
	 * カテゴリIDを指定して取得
	 *
	 * @return array
	 */
    function getByCategoryId($categoryId, $offset = null, $limit = null, $withChild = false){
		SOY2::import("domain.config.SOYShop_ShopConfig");
		$config = SOYShop_ShopConfig::load();
		$useMultiCategory = ($config->getMultiCategory() == 1);

		if($withChild && $categoryId){
			$mapping = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO")->getMapping();
			$ids = (isset($mapping[$categoryId])) ? $mapping[$categoryId] : array();
		}else{
			$ids = array($categoryId);
		}

		$itemDAO = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");

		try{
			/* まずoffset,limit,sortなしでカウントだけ行う */

			//マルチカテゴリモード
			if($useMultiCategory){
				$categoriesDAO = SOY2DAOFactory::create("shop.SOYShop_CategoriesDAO");
				//TODO 非公開商品をカウントしない
				$total = $categoriesDAO->countItemsByCategoryIds($ids);
			//通常モード
			}else{
				$total = $itemDAO->countOpenItemByCategories($ids);
			}

			/* offset,limit,sortを反映してデータを取得する */

			if($offset)$itemDAO->setOffset($offset);
			if($limit)$itemDAO->setLimit($limit);

	   		$sort = $this->getSortQuery();
	   		if($sort)$itemDAO->setOrder($sort);

			//マルチカテゴリモードならここでカテゴリIDから商品IDを取得する
			if($useMultiCategory){
				$itemIds = $categoriesDAO->getItemIdsByCategoryIds($ids);
				$items = $itemDAO->getOpenItemByMultiCategories($itemIds);
			}else{
				$items = $itemDAO->getOpenItemByCategories($ids);
			}
		}catch(Exception $e){
			$total = 0;
			$items = array();
		}

		return array($items, $total);
    }

	/**
	 * カテゴリ指定して数え上げ
	 */
    function countByCategoryId($categoryId, $withChild = false){

		if($withChild){
			$mapping = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO")->getMapping();
			$ids = $mapping[$categoryId];
		}else{
			$ids = array($categoryId);
		}

		$itemDAO = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		$total = $itemDAO->countOpenItemByCategories($ids);

		return $total;
    }

    /**
     * カスタムフィールドで検索
     * @param search_items
     * @param offset
     * @param limit
     * @return array(items,total)
     */
    function searchByAttribute($searchItems, $offset, $limit, $isAnd = true){
		$customFieldCordination = array();
		foreach($searchItems as $key => $value){
			$operation = (strpos($value, "%")===false) ? "=" : "LIKE";
			$customFieldCordination[$key] = array(
				"fieldId" => $key,
				"type" => $operation,
				"value" => $value
			);
		}

		return $this->searchItems(array(), $customFieldCordination, array(), $offset, $limit, $isAnd);
    }

    /**
     * アイテムの検索
     *
     * @param $categoryCordination カテゴリIDを指定
     * @param $customFieldCordination カスタムフィールドの検索条件を指定
     * @param $params 他の検索条件を指定 array("column" => $value)
     * @param $offset number
     * @param $limit number
     * @param $isAnd boolean
     * @return array($result,$total)
     */
    function searchItems($categories, $customFieldCordination, $params, $offset, $limit, $isAnd = true){

		list($isParent, $isChild) = self::getDisplayGroupItemMode($params);

    	$itemDAO = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
    	$itemAttributeDAO = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");

    	$table = SOYShop_Item::getTableName();
    	$attrTable = SOYShop_ItemAttribute::getTableName();

		$query = new SOY2DAO_Query();
    	$binds = array();

    	//build query
    	$query->prefix = "select";
	    	$query->sql = "$table.*";//",$attrTable.item_field_id as field_id,$attrTable.item_value as field_value";
	    	$query->table =
	    		$table
	    		. " left outer join " .
	    		$attrTable
	    		. " on ($table.id = $attrTable.item_id)";
	    $query->group = "$table.id";

		//$queries = array();
		$parentqueris = array();
		$parentbinds = array();
		$childqueries = array();	//子商品との出し分け設定用
		$childbinds = array();

    	//append where(categories)
    	if(count($categories) > 0) {
			if($isParent) $parentqueries[] = "item_category in (" . implode(",", $categories) . ")";
			if($isChild) {	//親商品で設定しているカテゴリを調べる
				$childqueries[] = "item_category in (" . implode(",", $categories) . ")";
				//$childqueries[] = "item_type IN (SELECT id FROM soyshop_item WHERE item_type = '" . SOYSHOP_ITEM::TYPE_GROUP . "' AND item_category in (" . implode(",", $categories) . "))";
			}
		}

    	//append where(params)
    	if(is_array($params) && count($params)){
    		foreach($params as $column => $value){
    			switch($column){
    				//フラグ系
    				case "item_sale_flag":
    				case "item_type":
    				case "item_is_open":
    				case "is_disabled":
						if($isParent){
							$parentqueries[] = $column . " = :" . $column;
	    					$parentbinds[":" . $column] = $value;
						}
						if($isChild){
							$childqueries[] = $column . " = :" . $column ."_child";
							$childbinds[":" . $column . "_child"] = $value;
						}
    					break;
    				//数字系
    				case "item_price":
    				case "item_sale_price":
    				case "item_selling_price":
    				case "item_stock":
    				/**
    				 * @ToDo 価格等の数字の値が入るカラムの場合を追加
    				 */
    					break;
    				//時間系
    				case "create_date":
					case "update_date":
					case "open_period_start":
					case "open_period_end":
					/**
    				 * @ToDo 時刻等の数字の値が入るカラムの場合を追加
    				 */
						break;
					case "is_parent":
					case "is_child":
						//処理は今回のswitch文より後
						break;
    				//文字列系
    				case "item_name":
    				case "item_code":
    				case "item_alias":
    				default:
						if($isParent){
							$parentqueries[] = $column . " LIKE :" . $column;
	    					$parentbinds[":" . $column] = "%" . $value . "%";
						}
						if($isChild){
							$childqueries[] = $column . " = :" . $column ."_child";
							$childbinds[":" . $column . "_child"] = $value;
						}
    					break;
    			}
    		}
    	}

    	//append where(customfield)
    	$parentwhere = array();
		$parentqueries = array();
		$childwhere = array();
    	$counter = 0;
    	foreach($customFieldCordination as $key => $array){
    		if((int)$key < 0 && (!isset($array["fieldId"]) || (int)$array["fieldId"] < 1)) continue;

    		$operation = (isset($array["type"])) ? $array["type"] : "=";
    		if(!in_array($operation, array("=", "<>", "LIKE", "NOT LIKE"))) $operation = "=";

			if($isParent){
				$customWhere = "(item_field_id = :field_id${counter} and item_value ${operation} :field_value${counter})";
				$parentwhere[] = $customWhere;
				$parentbinds[":field_id${counter}"] = (isset($array["fieldId"])) ? $array["fieldId"] : $key;
	    		$parentbinds[":field_value${counter}"] = $array["value"];
			}

			if($isChild){
				$customWhere = "(item_field_id = :field_id${counter}_child and item_value ${operation} :field_value${counter}_child)";
				$childwhere[] = $customWhere;
				$childbinds[":field_id${counter}_child"] = (isset($array["fieldId"])) ? $array["fieldId"] : $key;
	    		$childbinds[":field_value${counter}_child"] = $array["value"];
			}

    		$counter++;
    	}
		if($counter > 0){
			if(count($parentwhere)) $parentqueries[] = "("  . implode(" OR ",$parentwhere) . ")";
			if(count($childwhere)) $childqueries[] = "("  . implode(" OR ",$childwhere) . ")";

			//カスタムフィールドの複数条件対応(AND用)
	    	if($isAnd && count($parentwhere)){
				$query->having = "count(item_field_id) = " . count($parentwhere);
	    	}
		}

		//append plugin
		if(get_class($this->sort->getObject()) == "SOYShop_ListPage" && $this->sort->getObject()->getType() == "custom"){
			if(count($params)){
				foreach($params as $column => $value){
					switch($column){
						case "is_parent":
							$parentqueries[] = "(soyshop_item.item_type = \"" . SOYShop_Item::TYPE_SINGLE . "\" OR soyshop_item.item_type = \"" . SOYShop_Item::TYPE_GROUP . "\" OR soyshop_item.item_type = \"" . SOYShop_Item::TYPE_DOWNLOAD . "\")";
							break;
						case "is_child":
							break;
					}
				}
			}
		}

		if($isChild){
			$childquery = "SELECT soyshop_item.id FROM soyshop_item LEFT OUTER JOIN soyshop_item_attribute ON (soyshop_item.id = soyshop_item_attribute.item_id) WHERE " . implode(" AND ", $childqueries);
			//if(count($childwhere)) $childquery .= " HAVING count(item_field_id) = " . count($childwhere);
		}else{
			$childquery = "";
		}

		if($isParent && $isChild){
			$query->where = "(" . implode(" AND ", $parentqueries) . ") OR (item_type IN (" . $childquery . "))";
			$binds = $parentbinds + $childbinds;
			$query->having = "";	//@ToDo この対応で良いのか？
		}else if($isParent && !$isChild){
			if(count($parentqueries)) $query->where = implode(" AND ", $parentqueries);
			$binds = $parentbinds;
		}else if(!$isParent && $isChild){
			$query->where = "item_type IN (" . $childquery . ")";
			$binds = $childbinds;
			$query->having = "";
		}else{
			$query->where = "item_type = 'dummy'";
			$binds = array();
		}

    	if($limit) $itemDAO->setLimit($limit);
    	if($offset) $itemDAO->setOffset($offset);

    	//append sort
    	$sort = $this->getSortQuery();
    	if($sort) $query->order = $sort;

    	if(isset($_GET["debug"]) && DEBUG_MODE){

			$binds[":field_value0"] = "秋";

			$query->where = "";
			$binds = array();

			echo "<textarea cols=100 rows=10>";
			echo $query;
			echo "</textarea>";
			echo "<pre>";
			var_dump($binds);
			echo "<hr />";

			$res = $itemDAO->executeOpenItemQuery($query, $binds);
			var_dump($res);

			foreach($res as $row){
				$ids[$row["id"]] = $row;
			}
			$ids = array_keys($ids);
			sort($ids);
			var_dump($ids);

    		exit;

    	}

		//countQuery
		$countQuery = clone($query);
		if($isAnd){
			$countQuery->sql = "$table.id";
		}else{
			$countQuery->sql = "count(distinct $table.id) as row_count";
			$countQuery->group = "";
			$countQuery->having = "";
		}

		//execute query
    	try{
    		$res = $itemDAO->executeOpenItemQuery($query, $binds);
    	}catch(Exception $e){
			return array(array(), 0);
    	}

    	$items = array();
    	foreach($res as $row){
    		try{
    			$item = $itemDAO->getObject($row);
    		}catch(Exception $e){
    			continue;
    		}

    		$items[$item->getId()] = $item;
    	}

    	//count
    	$itemDAO->setLimit(null);
	   	$itemDAO->setOffset(null);
	   	try{
	   		$res = $itemDAO->executeOpenItemQuery($countQuery, $binds);
	   	}catch(Exception $e){
	   		return array(array(), 0);
	   	}

    	$total = ($isAnd) ? count($res) : $res[0]["row_count"];

    	return array($items, $total);
    }

	private function getDisplayGroupItemMode($params){
		if($this->mode != "complex") return array(true, false);	//標準では通常商品のみ

		//ナビゲーションページの商品ブロックのみの条件
		return array(isset($params["is_parent"]), isset($params["is_child"]));
	}

	private function getItemType(){
		$array = SOYShop_Item::getItemTypes();
		$obj = array();
		foreach($array as $value){
			$obj[] = "'" . $value . "'";
		}
		return implode(",", $obj);
	}


    function getSort() {
    	return $this->sort;
    }
    function setSort($sort) {
    	$this->sort = new SearchItemUtil_SortImpl($sort);
    }

	function setMode($mode){
		$this->mode = $mode;
	}
}
