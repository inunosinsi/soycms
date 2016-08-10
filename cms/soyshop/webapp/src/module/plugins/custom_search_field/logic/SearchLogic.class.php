<?php

class SearchLogic extends SOY2LogicBase{
	
	private $where = array();
	private $binds = array();
	private $itemDao;
	
	function SearchLogic(){
		SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");
		$this->itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
	}
	
	/**
	 * @params int current:現在のページ, int limit:一ページで表示する商品
	 * @return array<SOYShop_Item>
	 */
	function search($current, $limit){
		self::setCondition();
		
		$sql = "SELECT i.* " .
				"FROM soyshop_item i " .
				"INNER JOIN soyshop_custom_search s ".
				"ON i.id = s.item_id ";
		$sql .= self::buildWhere();	//カウントの時と共通の処理は切り分ける
		
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
		
		$sql = "SELECT COUNT(i.id) AS total " .
				"FROM soyshop_item i " .
				"INNER JOIN soyshop_custom_search s ".
				"ON i.id = s.item_id ";
		$sql .= self::buildWhere();	//カウントの時と共通の処理は切り分ける
		
		try{
			$res = $this->itemDao->executeQuery($sql, $this->binds);
		}catch(Exception $e){
			return 0;
		}
				
		return (isset($res[0]["total"])) ? (int)$res[0]["total"] : 0;
	}
	
	private function buildWhere(){
		$where = "WHERE i.open_period_start < :now ".
				"AND i.open_period_end > :now ".
				"AND i.item_type IN ('" . SOYShop_Item::TYPE_SINGLE . "', '" . SOYShop_Item::TYPE_GROUP ."', '" . SOYShop_Item::TYPE_DOWNLOAD . "') ".
				"AND i.item_is_open = 1 ".
				"AND i.is_disabled != 1 ";
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
					$this->where[$key] = "i." . $key . " LIKE :" . $key;
					$this->binds[":" . $key] = "%" . trim($_GET["c_search"][$key]) . "%";
				}
			}
			
			//カテゴリー
			if(isset($_GET["c_search"]["item_category"]) && is_numeric($_GET["c_search"]["item_category"])){
				//小カテゴリの商品も引っ張ってこれる様にする
				$maps = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO")->getMapping();
				$catId = (int)trim($_GET["c_search"]["item_category"]);
				if(isset($maps[$catId])){
					$this->where["item_category"] = "i.item_category IN (" . implode(",", $maps[$catId]) . ")";
				}
			}
			
			$pmin = "";$pmax = "";
			if(isset($_GET["c_search"]["item_price_min"]) && strlen($_GET["c_search"]["item_price_min"]) && is_numeric($_GET["c_search"]["item_price_min"])) {
				$pmin = "i.item_price >= :item_price_min";
				$this->binds[":item_price_min"] = (int)$_GET["c_search"]["item_price_min"];
			}
			
			if(isset($_GET["c_search"]["item_price_max"]) && strlen($_GET["c_search"]["item_price_max"]) && is_numeric($_GET["c_search"]["item_price_max"])) {
				$pmax = "i.item_price <= :item_price_max";
				$this->binds[":item_price_max"] = (int)$_GET["c_search"]["item_price_max"];
			}
			
			if(strlen($pmin) && strlen($pmax)){
				$this->where["item_price"] = "(" . $pmin . " AND " . $pmax . ")";
			}else{
				$this->where["item_price"] = $pmin . $pmax;
			}
			
			foreach(CustomSearchFieldUtil::getConfig() as $key => $field){
		
				//まずは各タイプのfield SQLでkeyを指定する場合、s.を付けること。soyshop_custom_searchのaliasがs
				switch($field["type"]){
					//文字列の場合
					case CustomSearchFieldUtil::TYPE_STRING:
						if(isset($_GET["c_search"][$key]) && strlen($_GET["c_search"][$key])){
							$this->where[$key] = "s." . $key . " LIKE :" . $key;
							$this->binds[":" . $key] = "%" . trim($_GET["c_search"][$key]) . "%";
						}
						break;
					
					//範囲の場合
					case CustomSearchFieldUtil::TYPE_RANGE:
						$ws = "";$we = "";	//whereのスタートとエンド
						if(isset($_GET["c_search"][$key . "_start"]) && strlen($_GET["c_search"][$key . "_start"]) && is_numeric($_GET["c_search"][$key . "_start"])){
							$ws = "s." . $key . " >= :" . $key . "_start";
							$this->binds[":" . $key . "_start"] = (int)$_GET["c_search"][$key . "_start"];
						}
						if(isset($_GET["c_search"][$key . "_end"]) && strlen($_GET["c_search"][$key . "_end"]) && is_numeric($_GET["c_search"][$key . "_end"])){
							$we = "s." . $key .  " <= :" . $key . "_end";
							$this->binds[":" . $key . "_end"] = (int)$_GET["c_search"][$key . "_end"];
						}
						if(strlen($ws) && strlen($we)){
							$this->where[$key] = "(" . $ws . " AND " . $we . ")";
						}else{
							$this->where[$key] = $ws . $we;
						}
						break;
						
					//チェックボックスの場合
					case CustomSearchFieldUtil::TYPE_CHECKBOX:
						if(isset($_GET["c_search"][$key]) && count($_GET["c_search"][$key])){
							$w = array();
							foreach($_GET["c_search"][$key] as $i => $v){
								if(!strlen($v)) continue;
								$w[] = "s." . $key . " LIKE :" . $key . $i;
								$this->binds[":" . $key . $i] = "%" . trim($v) . "%";
							}
							if(count($w)) $this->where[$key] = "(" . implode(" OR ", $w) . ")";
						}
						break;
					
					//数字、ラジオボタン、セレクトボックス
					default:
						if(isset($_GET["c_search"][$key]) && strlen($_GET["c_search"][$key])){
							$this->where[$key] = "s." . $key . " = :" . $key;
							$this->binds[":" . $key] = $_GET["c_search"][$key];
						}
				}
			}
			$this->binds[":now"] = time();
		}	
	}
	
	/** 商品一覧ページ用 **/
	function getItemList($key, $value, $current, $offset, $limit){
		$confs = CustomSearchFieldUtil::getConfig();
		if(!isset($confs[$key])) return array();
		
		$binds = array(":now" => time());
		
		$sql = "SELECT i.* " .
				"FROM soyshop_item i " .
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
		
		$sql = "SELECT COUNT(i.id) AS total " .
				"FROM soyshop_item i " .
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
		
		return (isset($res[0]["total"])) ? (int)$res[0]["total"] : 0;
	}
}
?>