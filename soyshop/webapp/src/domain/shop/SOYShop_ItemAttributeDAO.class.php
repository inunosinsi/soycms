<?php
/**
 * @entity shop.SOYShop_ItemAttribute
 */
abstract class SOYShop_ItemAttributeDAO extends SOY2DAO{

    abstract function insert(SOYShop_ItemAttribute $bean);

	/**
     * @query #itemId# = :itemId AND #fieldId# = :fieldId
     */
    abstract function update(SOYShop_ItemAttribute $bean);

    /**
     * @index fieldId
     */
    abstract function getByItemId($itemId);

	/**
	 * @return object
	 * @query #itemId# = :itemId AND #fieldId# = :fieldId
	 */
    abstract function get($itemId,$fieldId);

	/**
	 * @final
	 * isBackwardMatchにすることでfieldId_多言語化のpostfixも検索対象にする
	 * @param int itemId, array $fieldIds, bool $isBackwardMatch
	 * @return array
	 */
	function getByItemIdAndFieldIds(int $itemId, array $fieldIds, bool $isBackwardMatch=false){
		if(!count($fieldIds)) return array();

		if($isBackwardMatch){
			$sql = "SELECT * ".
					"FROM soyshop_item_attribute ".
					"WHERE item_id = :itemId ";
			$q = array();
			foreach($fieldIds as $fieldId){
				$q[] = "item_field_id LIKE '" . htmlspecialchars($fieldId, ENT_QUOTES, "UTF-8") . "%'";
			}
			$sql .=	"AND (" . implode(" OR ", $q) . ")";
		}else{
			$sql = "SELECT * ".
					"FROM soyshop_item_attribute ".
					"WHERE item_id = :itemId ".
					"AND item_field_id IN (\"" . implode("\",\"", $fieldIds) . "\")";
		}

		try{
			$res = $this->executeQuery($sql, array(":itemId" => $itemId));
		}catch(Exception $e){
			$res = array();
		}

		$list = array();
		if(count($res)){
			foreach($res as $v){
				$list[$v["item_field_id"]] = $this->getObject($v);
			}
		}

		if($isBackwardMatch) return $list;	//後方一致モードの場合は値がない場合の穴埋めはしない

		foreach($fieldIds as $fieldId){
			if(!isset($list[$fieldId])){
				$list[$fieldId] = new SOYShop_ItemAttribute();
			}
		}

		return $list;
	}

	/**
	 * @final
	 */
	function getAll($limit=null, $offset=null){
		$sql = "SELECT * FROM soyshop_item_attribute";
		if(isset($limit) && is_numeric($limit)) $sql .= " LIMIT " . $limit;
		if(isset($offset) && is_numeric($offset)) $sql .= " OFFSET " . $offset;
		try{
			$res = $this->executeQuery($sql);
		}catch(Exception $e){
			return array();
		}
		if(!count($res)) return array();

		$list = array();
		foreach($res as $v){
			$list[] = $this->getObject($v);
		}
		return $list;
	}

	/**
	 * @final
	 * isParentは親商品を調べるモードにするか？ isEmptyはitem_valueの値が空文字でも取得する
	 */
	function getOnLikeSearch($itemId, $like, $isParent = false, $isEmpty = true){
		//子商品の場合は親商品のものを調べる
		if($isParent){
			try{
				$results = $this->executeQuery("SELECT item_type FROM soyshop_item WHERE id = :itemId", array(":itemId" => $itemId));
			}catch(Exception $e){
				return array();
			}
			if(isset($results[0]["item_type"]) && is_numeric($results[0]["item_type"])) $itemId = $results[0]["item_type"];
		}

		//指定のキーワードで検索 SQLiteとMySQLで文字列検索の関数が異なるため、値の設定がない項目も一気に取得する
		try{
			$results = $this->executeQuery("SELECT * FROM soyshop_item_attribute WHERE item_id = :itemId AND item_field_id LIKE '" . $like . "'", array(":itemId" => $itemId));
		}catch(Exception $e){
			return array();
		}
		if(!count($results)) return array();

		$attrs = array();
		foreach($results as $res){
			if(!isset($res["item_field_id"]) || !strlen($res["item_field_id"])) continue;
			if(!$isEmpty && !strlen($res["item_value"])) continue;
			$attrs[$res["item_field_id"]] = $this->getObject($res);
		}

		return $attrs;
	}

    abstract function deleteByItemId($itemId);

    /**
     * @query #itemId# = :itemId AND #fieldId# = :fieldId
     */
    abstract function delete($itemId,$fieldId);

    abstract function deleteByFieldId($fieldId);
}
