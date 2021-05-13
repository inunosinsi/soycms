<?php
SOY2::import("domain.shop.SOYShop_Item");
class SearchItemLogic extends SOY2LogicBase{

	private $query;
	private $mode;
	private $limit;
	private $offset;
	private $order;
	private $group;
	private $having;
	private $where = array();
	private $binds = array();



	private $sorts = array(

		"category" =>  "item_category",
		"category_desc" =>  "item_category desc",

		"name" =>  "item_name",
		"name_desc" =>  "item_name desc",

		"code" =>  "item_code",
		"code_desc" =>  "item_code desc",

		"price" =>  "item_price",
		"price_desc" =>  "item_price desc",

		"stock" =>  "item_stock",
		"stock_desc" =>  "item_stock desc",

		"create_date" => "create_date",
		"create_date_desc" => "create_date desc",

		"update_date" => "update_date",
		"update_date_desc" => "update_date desc"

	);

	const TABLE_NAME = "soyshop_item";

	function getQuery(){
		if(is_null($this->query)){
			SOY2DAOConfig::setOption("limit_query", true);
			$this->query = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		}

		return $this->query;
	}
	function setMode($mode){
		$this->mode = $mode;
	}
	function setLimit($value){
		$this->limit = $value;
	}
	function setOffset($value){
		$this->offset = $value;
	}

	function setOrder($order){
		if(isset($this->sorts[$order])){
			$order = $this->sorts[$order];
			$order = str_replace("_desc", " desc", $order);
		}else{
			$order = "update_date desc";
		}
		$this->order = "order by " . $order;

	}

	function getSorts(){
		return $this->sorts;
	}

	function setSearchCondition($cnds){
		$where = array();
		$binds = array();

		//配列がない場合は何もしない
		if(!is_array($cnds)) return;

		if(count($cnds)){
			SOY2::import("domain.config.SOYShop_ShopConfig");
			$cnf = SOYShop_ShopConfig::load();

			foreach($cnds as $key => $value){
				if(is_string($value)) {
					$value = trim($value);
					if(!strlen($value)) continue;
				}
				switch($key){
					case "name":
					case "code":
						$values = explode(" ", str_replace("　", " ", $value));
						$subWhere = array();
						foreach($values as $idx => $v){
							switch($this->mode){
								case "admin":	//管理画面での注文の際の商品検索では、子商品も合わせて検索対象にする
									if($cnf->getAddSearchChildItemNameOnAdmin()){	//子商品も検索対象にする
										$subWhere[] = "(item_" . $key . " LIKE :item_" . $key . "_" . $idx . " OR id IN (SELECT item_type FROM soyshop_item WHERE item_" . $key . " LIKE :child_" . $key . "_" . $idx . "))";
										$binds[":child_" . $key . "_" . $idx] = "%" . $v . "%";
									}else{	//子商品を検索対象から外す→最適化
										$subWhere[] = "item_" . $key . " LIKE :item_" . $key . "_" . $idx;
									}
									$binds[":item_" . $key . "_" . $idx] = "%" . $v . "%";
									break;
								default:
									$subWhere[] = "item_" . $key . " LIKE :item_" . $key . "_" . $idx;
									$binds[":item_" . $key . "_" . $idx] = "%" . $v . "%";
							}
						}
						$logicType = (isset($cnds["search_type"][$key])) ? $cnds["search_type"][$key] : "OR";	// AND(論理積) or OR(論理和)
						if(count($subWhere)) $where[] = "(" . implode(" " . $logicType . " ", $subWhere) . ")";
						break;
					case "categories":
						$values = explode(" ", $value);
						$mappings = SOYShop_DataSets::get("category.mapping", array());

						$ids = array();
						foreach($values as $value){
							if(!isset($mappings[$value])) continue;
							$ids = array_merge($ids, $mappings[$value]);
						}
						$ids = array_unique($ids);
						if(count($ids) > 0){
							if(isset($cnds["is_child"])){
								$where[] = "(item_category in (" . implode(",", $ids) . ") OR item_type in (SELECT id FROM soyshop_item WHERE item_category in (" . implode(",", $ids) . ")))";
							}else{
								$where[] = "item_category in (" . implode(",", $ids) . ")";
							}
						}
						break;
					//カテゴリ単体で調べたい時に使う
					case "category":
						if($value < 0){
							$where[] = "item_category IS NULL";
						}else{
							//子商品の指定がある場合
							if(isset($cnds["is_child"])){
								$where[] = "(item_category = :item_category OR item_type in (SELECT id FROM soyshop_item WHERE item_category = :item_category))";
							}else{
								$where[] = "item_category = :item_category";
							}
							$binds[":item_category"] = $value;
						}
						break;
					case "type":
						if(is_array($value)){
							$where[] = "item_type IN (\"". implode("\",\"", $value) . "\")";
						}
						break;
					case "attributes":
						$attributes = $value;
						foreach($attributes as $key => $value){

						}
						break;
					case "search_type":
						//何もしない
						break;
				}
			}

			//公開条件
			$openConditions = array();
			if(isset($cnds["is_open"])){
				$openConditions[] = "item_is_open = 1 ";
			}
			if(isset($cnds["is_close"])){
				$openConditions[] = "item_is_open = 0 ";
			}
			if(isset($cnds["is_sale"])){
				$openConditions[] = "item_sale_flag = 1";
			}
			if(count($openConditions) > 0){
				$where[] = "(" . implode(" OR ", $openConditions) .")";
			}

			//子商品は表示しない
			if(!isset($cnds["is_child"])){
				$where[] = " item_type in (" . self::getItemType() . ")";
			}
		}

		//拡張ポイントから出力したフォーム用
		SOYShopPlugin::load("soyshop.item.search");
		$queries = SOYShopPlugin::invoke("soyshop.item.search", array(
			"mode" => "search",
			"params" => (isset($cnds["customs"])) ? $cnds["customs"] : array()
		))->getQueries();

		if(is_array($queries) && count($queries)){
			foreach($queries as $moduleId => $values){
				if(!isset($values["queries"]) || !is_array($values["queries"]) || !count($values["queries"])) continue;
				$where = array_merge($where, $values["queries"]);
				if(isset($values["binds"])) $binds = array_merge($binds, $values["binds"]);
			}
		}


		$this->where = $where;
		$this->binds = $binds;
	}

	protected function _countSql(){
		$countSql = "select count(*) as count from " . self::TABLE_NAME . " ";
		if(count($this->where) > 0){
			$countSql .= " where ".implode(" and ", $this->where);
		}else{
			$countSql .= " where item_type in (" . self::getItemType() . ") ";
		}

		//削除フラグ
		$countSql .= "and is_disabled != 1 ";
		return $countSql;
	}

	protected function _sql(){
		$sql = "select * from " . self::TABLE_NAME . " ";
		if(count($this->where) > 0){
			$sql .= " where ".implode(" and ", $this->where);
		//一覧ページの時
		}else{
			$sql .= " where item_type in (" . self::getItemType() . ") ";
		}

		//削除フラグ
		$sql .= " and is_disabled != 1 ";
		if(strlen($this->order)) $sql .= " " . $this->order;
		return $sql;
	}

	private function getItemType(){
		$array = SOYShop_Item::getItemTypes();
		$obj = array();
		foreach($array as $value){
			$obj[] = "'" . $value . "'";
		}
		return implode(",", $obj);
	}

	//合計件数取得
	function getTotalCount(){
		try{
			$countResult = $this->getQuery()->executeQuery(self::_countSql(), $this->binds);
		}catch(Exception $e){
			return 0;
		}
		return $countResult[0]["count"];
	}

	//商品取得
	function getItems(){
		$this->getQuery()->setLimit($this->limit);
		$this->getQuery()->setOffset($this->offset);
		$sql = self::_sql();

		try{
			$result = $this->getQuery()->executeQuery($sql, $this->binds);
		}catch(Exception $e){
			return array();
		}

		$items = array();
		if(count($result)){
			foreach($result as $raw){
				$items[] = $this->getQuery()->getObject($raw);
			}
		}

		return $items;
	}
}
