<?php

class SearchLogic extends SOY2LogicBase{

	private $itemDao;

	private $limit;
	private $offset;
	private $order;

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

	function __construct(){
		$this->itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
	}

	function get(){
		$sql = self::buildQuery();

		if(strlen($this->order)) $sql .= " " . $this->order;

		if(isset($this->limit) && (int)$this->limit > 0) $sql .= " LIMIT " . $this->limit;
		if(isset($this->offset) && is_numeric($this->offset)) $sql .= " OFFSET " . $this->offset;

		try{
			$res = $this->itemDao->executeQuery($sql, $this->binds);
		}catch(Exception $e){
			$res = array();
		}

		if(!count($res)) return array();

		$items = array();
		foreach($res as $v){
			$items[] = $this->itemDao->getObject($v);
		}

		return $items;
	}

	function getTotalCount(){
		$sql = "SELECT COUNT(*) AS count FROM soyshop_item ".
				"WHERE is_disabled != " . SOYShop_Item::IS_DISABLED . " ";

		foreach($this->where as $where){
			$sql .= " AND " . $where;
		}

		try{
			$res = $this->itemDao->executeQuery($sql, $this->binds);
		}catch(Exception $e){
			return 0;
		}

		return (isset($res[0]["count"])) ? (int)$res[0]["count"] : 0;
	}

	private function buildQuery(){
		$sql = "SELECT * FROM soyshop_item ".
				"WHERE is_disabled != " . SOYShop_Item::IS_DISABLED . " ";

		foreach($this->where as $where){
			$sql .= " AND " . $where;
		}

		return $sql;
	}

	function setCondition($conditions){
		if(is_null($conditions)) $conditions = array();

		if(is_array($conditions) && count($conditions)) {
			foreach($conditions as $key => $value){
				switch($key){
					//カテゴリーの場合は数字を直接指定
					case "item_category":
						$this->where[] = $key . " = :" . $key;
						$this->binds[":" . $key] = (int)$value;
						break;
					case "item_is_open":
						if(count($value)){
							$this->where[] = $key . " IN (" . implode(",", $value) . ") ";
						}
						break;
					case "item_type":
						//何もしない
						break;
					default:
						$values = explode(" ", str_replace("　", " ", $value));
						$subWhere = array();
						foreach($values as $idx => $v){
							$subWhere[] = $key . " LIKE :" . $key . "_" . $idx;
							$this->binds[":" . $key . "_" . $idx] = "%" . trim($v) . "%";
						}
						if(count($subWhere)) $this->where[] = "(" . implode(" OR ", $subWhere) . ")";
				}
			}
		}

		//通常商品の扱い
		$itemTypeParent = (isset($conditions["item_type"]["parent"])) ? $conditions["item_type"]["parent"] : null;
		if(is_null($itemTypeParent) || $itemTypeParent == 1){
			//何もしない
		}else{
			$this->where[] = "item_type NOT IN (\"" . SOYShop_Item::TYPE_SINGLE ."\",\"" . SOYShop_Item::TYPE_GROUP . "\",\"" . SOYShop_Item::TYPE_DOWNLOAD . "\")";
		}

		//子商品の扱い
		if(isset($conditions["item_type"]["child"])){
			//何もしない
		}else{
			$this->where[] = "item_type IN (\"" . SOYShop_Item::TYPE_SINGLE ."\",\"" . SOYShop_Item::TYPE_GROUP . "\",\"" . SOYShop_Item::TYPE_DOWNLOAD . "\")";
		}
	}

	function setLimit($limit){
		$this->limit = $limit;
	}

	function setOffset($value){
		$this->offset = $value;
	}

	function setOrder($order){
		if(isset($this->sorts[$order])){
			$order = $this->sorts[$order];
			$order = str_replace("_desc", " desc", $order);
		}else{
			$order = "id asc";
		}
		$this->order = "order by " . $order;

	}

	function getSorts(){
		return $this->sorts;
	}
}
