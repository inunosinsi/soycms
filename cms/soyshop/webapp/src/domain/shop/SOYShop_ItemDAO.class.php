<?php
/**
 * @entity shop.SOYShop_Item
 */
abstract class SOYShop_ItemDAO extends SOY2DAO{

	private $mode = "normal";

	/**
	 * @final
	 */
	function setMode($mode){
		$this->mode = $mode;
	}

	/**
	 * @final
	 * @override
	 */
	function executeQuery($query, $binds = array(), $keepStatement = false){

		if($this->mode == "open"){
			$this->mode = "tmp";
			$res = $this->executeOpenItemQuery($query, $binds);
			$this->mode = "open";
			return $res;
		}

		return parent::executeQuery($query, $binds, $keepStatement);
	}


	/**
	 * @index id
	 * @query is_disabled != 1
	 * @order id desc
	 */
    abstract function get();

    /**
     * @return list
     * @query is_disabled != 1
     * @order create_date desc
     */
    abstract function getByIsOpen($isOpen);

    /**
     * @return list
     * @query item_is_open = :isOpen AND item_type IN ('single','group','download') AND is_disabled != 1
     * @order create_date desc
     */
    abstract function getByIsOpenOnlyParent($isOpen);

	/**
	 * @order id desc
	 */
    abstract function getDesc();

	/**
	 * @return object
	 */
   	abstract function getById($id);

   	/**
   	 * @return object
   	 */
   	abstract function getByCode($code);

	/**
	 * @return object
	 */
	abstract function getByName($name);

   	/**
   	 * @return object
   	 */
   	abstract function getByAlias($alias);

   	/**
   	 * @return list
   	 * @query item_stock <= :stock AND is_disabled != 1
   	 */
   	abstract function getByStock($stock);

   	/**
   	 * @index id
   	 */
   	abstract function getByType($type);

	/**
	 * @index id
	 * @query item_type = :type AND item_is_open = 1 AND is_disabled = 0
	 */
	abstract function getByTypeIsOpenNoDisabled($type);

   	/**
   	 * @index id
   	 * @query item_type = :type AND is_disabled = 0
   	 */
   	abstract function getByTypeNoDisabled($type);

	/**
	 * @query #alias# = :alias
	 */
   	abstract function checkAlias($alias);

	/**
	 * @order update_date desc
	 */
	abstract function getByDetailPageId($detailPageId);

	/**
	 * @query detail_page_id = :detailPageId AND item_is_open = 1 AND is_disabled != 1
	 * @order update_date desc
	 */
	abstract function getByDetailPageIdIsOpen($detailPageId);

	/**
	 * @query detail_page_id = :detailPageId AND is_disabled != 1
	 * @order update_date desc
	 */
	abstract function getByDetailPageIdIsPublished($detailPageId);

	/**
	 * @query is_disabled != 1
	 * @order #updateDate# desc
	 */
	abstract function newItems();

	/**
	 * @return object
	 * @query item_is_open = 1 AND is_disabled != 1 AND open_period_start < :now AND open_period_end > :now
	 * @order create_date DESC
	 * @limit 1
	 */
	abstract function getLatestRegisteredItem($now);

   	/**
	 * @trigger onInsert
	 * @return id
	 */
   	abstract function insert(SOYShop_Item $item);

   	/**
   	 * @final
   	 */
   	function onInsert($query, $binds){

		$binds[":alias"] = $binds[":code"] . ".html";

		//価格系すべて
		foreach(array("price", "purchasePrice", "salePrice", "sellingPrice", "stock") as $t){
			if(!isset($binds[":" . $t]) || !is_numeric($binds[":" . $t])){
				$binds[":" . $t] = 0;
			}
		}

		if(!isset($binds[":category"]) || strlen($binds[":category"]) < 1){
			$binds[":category"] = null;
		}

		if(!isset($binds[":orderPeriodStart"]) || strlen($binds[":orderPeriodStart"]) < 1){
			$binds[":orderPeriodStart"] = SOYSHOP_DATA_MIN;
		}
		if(!isset($binds[":openPeriodEnd"]) || strlen($binds[":orderPeriodEnd"]) < 1){
			$binds[":orderPeriodEnd"] = SOYSHOP_DATA_MAX;
		}

		if(!isset($binds[":openPeriodStart"]) || strlen($binds[":openPeriodStart"]) < 1){
			$binds[":openPeriodStart"] = SOYSHOP_DATA_MIN;
		}
		if(!isset($binds[":openPeriodEnd"]) || strlen($binds[":openPeriodEnd"]) < 1){
			$binds[":openPeriodEnd"] = SOYSHOP_DATA_MAX;
		}

		if(!isset($binds[":isOpen"])) $binds[":isOpen"] = 0;
		if(!isset($binds[":isDisabled"])) $binds[":isDisabled"] = 0;

		if(!isset($binds[":createDate"])) $binds[":createDate"] = time();
		if(!isset($binds[":updateDate"])) $binds[":updateDate"] = time();

   		return array($query, $binds);
   	}

   	/**
   	 * @final
   	 */
   	function onUpdate($query, $binds){
		if(!isset($binds[":alias"]) || strlen($binds[":alias"]) < 1){
			$binds[":alias"] = $binds[":code"] . ".html";
		}

		//価格系すべて
		foreach(array("price", "purchasePrice", "salePrice", "sellingPrice", "stock") as $t){
			if(!isset($binds[":" . $t]) || !is_numeric($binds[":" . $t])){
				$binds[":" . $t] = 0;
			}
		}

		if(!isset($binds[":category"]) || strlen($binds[":category"]) < 1){
			$binds[":category"] = null;
		}

		if(!isset($binds[":orderPeriodStart"]) || strlen($binds[":orderPeriodStart"]) < 1){
			$binds[":orderPeriodStart"] = SOYSHOP_DATA_MIN;
		}
		if(!isset($binds[":openPeriodEnd"]) || strlen($binds[":orderPeriodEnd"]) < 1){
			$binds[":orderPeriodEnd"] = SOYSHOP_DATA_MAX;
		}

		if(!isset($binds[":openPeriodStart"]) || strlen($binds[":openPeriodStart"]) < 1){
			$binds[":openPeriodStart"] = SOYSHOP_DATA_MIN;
		}
		if(!isset($binds[":openPeriodEnd"]) || strlen($binds[":openPeriodEnd"]) < 1){
			$binds[":openPeriodEnd"] = SOYSHOP_DATA_MAX;
		}

		$binds[":updateDate"] = time();

		return array($query, $binds);
   	}

	/**
	 * @trigger onUpdate
	 */
	abstract function update(SOYShop_Item $item);

	/**
	 * @column #id#,#stock#
	 */
	abstract function updateStock(SOYShop_Item $item);

	/**
	 * @columns #id#,#isOpen#
	 */
	abstract function updateIsOpen($id, $isOpen);

	/**
	 * カテゴリ指定
	 */
	function getByCategories($categories){

		$query = $this->getQuery();
		$binds = $this->getBinds();

		$query->where = "item_category in (" . implode(",", $categories) . ") AND is_disabled != 1";
		if($this->getOrder()){
			$query->order = $this->getOrder();
		}

		$res = array();
		$result = $this->executeQuery($query, $binds);
		foreach($result as $row){
			$obj = $this->getObject($row);
			$res[$obj->getId()] = $obj;
		}

		return $res;
	}

	/* 以下、サイト側で使用 */

	/**
	 * @final
	 * 公開している商品に限定するQueryを追加
	 */
	function executeOpenItemQuery($query, $binds){
		$query->where .= (strlen($query->where) > 0) ? " AND " : "";
		$query->where .= "item_is_open = 1 AND open_period_start <= :now AND open_period_end >= :now AND is_disabled != 1";

		$binds[":now"] = SOY2_NOW;

		return $this->executeQuery($query, $binds);

	}

	/**
	 * カテゴリ指定（公開商品のみ）
	 */
	function getOpenItemByCategories($categories){

		//カテゴリの選択をしていない場合は空の配列を返す
		if(is_array($categories) && count($categories) === 0) return array();

		$query = $this->getQuery();
		$binds = $this->getBinds();

		$query->where = "item_category in (" . implode(",", $categories) . ") AND is_disabled != 1";
		if($this->getOrder()){
			$query->order = $this->getOrder();
		}

		$res = array();
		$result = $this->executeOpenItemQuery($query, $binds);
		foreach($result as $row){
			$obj = $this->getObject($row);
			$res[$obj->getId()] = $obj;
		}

		return $res;
	}

	/**
	 * カテゴリ指定（公開商品のみ+マルチカテゴリモード）
	 */
	function getOpenItemByMultiCategories($itemIds){

		if(is_array($itemIds) && count($itemIds) === 0) return array();

		$query = $this->getQuery();
		$binds = $this->getBinds();

		$query->where = "id in (" . implode(",", $itemIds) . ") AND is_disabled != 1";
		if($this->getOrder()){
			$query->order = $this->getOrder();
		}

		$res = array();
		$result = $this->executeOpenItemQuery($query, $binds);
		foreach($result as $row){
			$obj = $this->getObject($row);
			$res[$obj->getId()] = $obj;
		}

		return $res;
	}

	/**
	 * @columns count(id) as item_count
	 */
	function countOpenItemByCategories($categories){

		//カテゴリの選択をしていない場合は0を返す
		if(count($categories) === 0) return 0;

		$query = $this->getQuery();
		$binds = $this->getBinds();

		$query->where = "item_category in (" . implode(",", $categories) . ") AND is_disabled != 1";

		$result = $this->executeOpenItemQuery($query, $binds);

		if(count($result) > 0){
			return $result[0]["item_count"];
		}

		return 0;
	}

	/**
	 * @final
	 */
	function getStockTotalListByItemIds($itemIds){
		if(!is_array($itemIds) || !count($itemIds)) return array();

		try{
			$res = $this->executeQuery("SELECT id, item_stock FROM soyshop_item WHERE id IN (" . implode(",", $itemIds) . ") AND (item_type = '" . SOYShop_Item::TYPE_SINGLE . "' OR item_type = '" . SOYShop_Item::TYPE_DOWNLOAD . "')");
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$list = array();
		foreach($res as $v){
			$list[(int)$v["id"]] = (int)$v["item_stock"];
		}

		return $list;
	}

	/**
     * @columns sum(item_stock) as item_stock
     * @return column_item_stock
     * @query item_type = :itemId and is_disabled != 1
     */
	abstract function getChildStockTotalByItemId($itemId);

	/**
	 * @final
	 */
	function getChildStockListByItemIds($itemIds){
		if(!is_array($itemIds) || !count($itemIds)) return array();


		try{
			$res = $this->executeQuery("SELECT item_type, SUM(item_stock) AS item_stock FROM soyshop_item WHERE item_type IN (" . implode(",", $itemIds) . ") GROUP BY item_type");
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$list = array();
		foreach($res as $v){
			$list[(int)$v["item_type"]] = (int)$v["item_stock"];
		}
		return $list;
	}

	/* end サイト側で使用 */

	/* 以下、ソート周り */

	/**
	 * @final
	 */
	function dropSortColumn($name){
		$sql = "drop index " . self::getSortColumnName($name) . "_index";

		$this->executeUpdateQuery($sql);

		$sql = "alter table " . SOYShop_Item::getTableName() .
			 " drop ". self::getSortColumnName($name);

		$this->executeUpdateQuery($sql);
	}

	/**
	 * @final
	 */
	function createSortColumn($name){
	 	//sort用には30確保
	 	$sql = "alter table " . SOYShop_Item::getTableName() .
			 " add " . self::getSortColumnName($name) . " varchar(30) default '_' not null";

		$this->executeUpdateQuery($sql);

		$sql = "create index " . self::getSortColumnName($name) ."_index on "
			 . SOYShop_Item::getTableName() . "(" . self::getSortColumnName($name) . ")";

		$this->executeUpdateQuery($sql);
	 }

	 /**
	  * @final
	  */
	public static function getSortColumnName($name){
		return "custom_" . strtolower($name);
	}

	/**
	 * ソートで使用する値を更新する
	 *
	 * @columns id
	 * @param $id
	 * @param $name
	 * @param $value
	 */
	function updateSortValue($id, $name, $value){
		$query = $this->getQuery();
		$query->sql = self::getSortColumnName($name) . " = :sort_column";

		if(strlen($value) > 30){
			$value = mb_substr($value , 0 , 30);
		}else if(empty($value)){
			$value = "__________________"; //適当な値
		}

		try{
			$this->executeUpdateQuery($query, array(
				":id" => $id,
				":sort_column" => $value
			));
		}catch(Exception $e){
			//
		}
	 }

	 /**
	  * @final
	  */
	function getItemNameListByIds($ids){
		if(!is_array($ids) || !count($ids)) return array();

		try{
			$res = $this->executeQuery("SELECT id, item_name FROM soyshop_item WHERE id IN (" . implode(",", $ids) . ")");
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$list = array();
		foreach($res as $v){
			$list[(int)$v["id"]] = $v["item_name"];
		}
		return $list;
	}


	 /* 以下、削除周り */

	/**
	 * @trigger onDelete
	 */
	abstract function delete($id);

	/**
	 * @final
	 */
	function deleteByCode($code){
		try{
			$item = $this->getByCode($code);
			$id = $item->getId();
			$this->delete($id);
		}catch(Exception $e){

		}
	}

	/**
	 * 商品を削除する
	 * @final
	 */
	function onDelete($query, $binds){
		$id = $binds[":id"];

		SOYShopPlugin::load("soyshop.item.customfield");
		SOYShopPlugin::invoke("soyshop.item.customfield", array(
			"deleteItemId" => $id
		));

		return array($query, $binds);

	}


	/* 注文実行 */

	/**
	 * @final
	 */
	function orderItem($id, $count){
		$item = $this->getById($id);
		$newStock = $item->getStock() - $count;

		$item->setStock($newStock);
		$this->updateStock($item);
	}
}
